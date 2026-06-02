<?php declare(strict_types=1);

namespace Vic\ProductPdf\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class ProductPdfController
{
    public function __construct(
        private readonly EntityRepository $productRepository,
        private readonly SystemConfigService $systemConfigService,
        private readonly Environment $twig,
        private readonly string $projectDir,
    ) {}

    #[Route(
        path: '/product-pdf/{productId}',
        name: 'frontend.vic.product.pdf',
        methods: ['GET']
    )]
    public function download(string $productId): Response
    {
        $context = Context::createDefaultContext();

        $criteria = new Criteria([$productId]);
        $criteria->addAssociations([
            'cover.media',
            'manufacturer',
            'tax',
            'properties.group',
            'children.options.group',
        ]);

        $product = $this->productRepository->search($criteria, $context)->first();

        if (!$product) {
            return new Response('Product not found', Response::HTTP_NOT_FOUND);
        }

        $shopName = (string) ($this->systemConfigService->get('core.basicInformation.shopName') ?? 'Shop');

        $imageDataUri = $this->buildImageDataUri($product->getCover()?->getMedia());

        $priceGross = null;
        $priceNet   = null;
        $priceObj   = $product->getPrice();
        if ($priceObj && $priceObj->count() > 0) {
            $first      = $priceObj->first();
            $priceGross = $first ? number_format($first->getGross(), 2, ',', '.') . ' €' : null;
            $priceNet   = $first ? number_format($first->getNet(), 2, ',', '.') . ' €' : null;
        }

        $properties   = $this->collectProperties($product);
        $variants     = $this->collectVariants($product);
        $customFields = $this->collectCustomFields($product, $this->resolveWhitelist());

        $html = $this->twig->render('@VicProductPdf/vic-product-pdf/product-pdf.html.twig', [
            'product'      => $product,
            'shopName'     => $shopName,
            'imageDataUri' => $imageDataUri,
            'priceGross'   => $priceGross,
            'priceNet'     => $priceNet,
            'properties'   => $properties,
            'variants'     => $variants,
            'customFields' => $customFields,
            'generatedAt'  => (new \DateTime())->format('d.m.Y'),
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $sku      = $product->getProductNumber() ?? $productId;
        $filename = 'product_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $sku) . '.pdf';

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control'       => 'private',
                'Pragma'              => 'private',
            ]
        );
    }

    private function buildImageDataUri(mixed $media): ?string
    {
        if (!$media) {
            return null;
        }

        $path = $this->projectDir . '/public/' . $media->getPath();

        if (!file_exists($path)) {
            return null;
        }

        $data = file_get_contents($path);

        if ($data === false) {
            return null;
        }

        return 'data:' . ($media->getMimeType() ?? 'image/jpeg') . ';base64,' . base64_encode($data);
    }

    private function collectProperties(mixed $product): array
    {
        $result     = [];
        $properties = $product->getProperties();
        if (!$properties) {
            return $result;
        }

        foreach ($properties->getElements() as $property) {
            $group = $property->getGroup()?->getName() ?? '';
            $value = $property->getName() ?? '';
            if ($group !== '' && $value !== '') {
                $result[$group][] = $value;
            }
        }

        return $result;
    }

    private function collectVariants(mixed $product): array
    {
        $result   = [];
        $children = $product->getChildren();
        if (!$children || $children->count() === 0) {
            return $result;
        }

        foreach ($children->getElements() as $child) {
            $optionParts = [];
            if ($child->getOptions()) {
                foreach ($child->getOptions()->getElements() as $option) {
                    $groupName = $option->getGroup()?->getName() ?? '';
                    $optName   = $option->getName() ?? '';
                    if ($groupName !== '' && $optName !== '') {
                        $optionParts[] = $groupName . ': ' . $optName;
                    }
                }
            }

            $variantPrice = null;
            $childPrice   = $child->getPrice();
            if ($childPrice && $childPrice->count() > 0) {
                $fp           = $childPrice->first();
                $variantPrice = $fp ? number_format($fp->getGross(), 2, ',', '.') . ' €' : null;
            }

            $result[] = [
                'options'       => implode(', ', $optionParts) ?: $child->getProductNumber(),
                'productNumber' => $child->getProductNumber() ?? '',
                'price'         => $variantPrice ?? '—',
                'stock'         => $child->getStock(),
            ];
        }

        return $result;
    }

    /** @param string[] $whitelist */
    private function collectCustomFields(mixed $product, array $whitelist): array
    {
        $fields = $product->getCustomFields();
        if (empty($fields)) {
            return [];
        }

        $result = [];
        foreach ($fields as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if (!\in_array($key, $whitelist, true)) {
                continue;
            }
            $label          = ucwords(str_replace(['_', '-'], ' ', $key));
            $result[$label] = is_array($value) ? implode(', ', $value) : (string) $value;
        }

        return $result;
    }

    /** @return string[] */
    private function resolveWhitelist(): array
    {
        $raw = $this->systemConfigService->get('VicProductPdf.config.customFieldsWhitelist');
        if (!\is_string($raw) || trim($raw) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}
