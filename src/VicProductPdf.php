<?php declare(strict_types=1);

namespace Vic\ProductPdf;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class VicProductPdf extends Plugin
{
    public function boot(): void
    {
        parent::boot();
        $autoload = __DIR__ . '/../vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }
    }

    public function install(InstallContext $context): void
    {
        $this->createCustomFields($context->getContext());
    }

    public function update(UpdateContext $context): void
    {
        $this->createCustomFields($context->getContext());
    }

    public function uninstall(UninstallContext $context): void
    {
        if ($context->keepUserData()) {
            return;
        }

        $repo     = $this->container->get('custom_field_set.repository');
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', 'vic_product_pdf'));
        $result = $repo->search($criteria, $context->getContext());

        if ($result->getTotal() === 0) {
            return;
        }

        $ids = array_values(array_map(fn ($e) => ['id' => $e->getId()], $result->getElements()));
        $repo->delete($ids, $context->getContext());
    }

    private function createCustomFields(\Shopware\Core\Framework\Context $context): void
    {
        $repo     = $this->container->get('custom_field_set.repository');
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', 'vic_product_pdf'));

        if ($repo->search($criteria, $context)->getTotal() > 0) {
            return;
        }

        $repo->create([
            [
                'name'         => 'vic_product_pdf',
                'config'       => [
                    'label' => [
                        'en-GB' => 'Product PDF Download',
                        'de-DE' => 'Produkt PDF-Download',
                        'es-ES' => 'Descarga PDF del producto',
                    ],
                ],
                'customFields' => [
                    [
                        'name'   => 'vic_product_pdf_enabled',
                        'type'   => CustomFieldTypes::BOOL,
                        'config' => [
                            'label' => [
                                'en-GB' => 'Show PDF download button',
                                'de-DE' => 'PDF-Download-Button anzeigen',
                                'es-ES' => 'Mostrar botón de descarga PDF',
                            ],
                            'helpText' => [
                                'en-GB' => 'Displays a "Download as PDF" button on the storefront product page.',
                                'de-DE' => 'Zeigt einen „Als PDF herunterladen"-Button auf der Produktseite an.',
                                'es-ES' => 'Muestra un botón "Descargar como PDF" en la ficha del producto.',
                            ],
                            'componentName'      => 'sw-field',
                            'customFieldType'    => 'checkbox',
                            'customFieldPosition' => 1,
                        ],
                    ],
                ],
                'relations'    => [
                    ['entityName' => 'product'],
                ],
            ],
        ], $context);
    }
}
