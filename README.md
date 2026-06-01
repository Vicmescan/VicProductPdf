# VicProductPdf — Shopware 6.7 Plugin

Adds a **"Download as PDF"** button to the storefront product detail page, allowing customers to download a product data sheet as a PDF file.

---

## Features

- Download button on the storefront product detail page
- PDF includes: main image, name, SKU, gross/net price, manufacturer, stock, EAN, description, properties, variants and custom fields
- **Per-product toggle** via a custom field checkbox in the admin (Custom fields tab)
- Public route, no authentication required: `/product-pdf/{productId}`
- PDF generation powered by [DOMPDF](https://github.com/dompdf/dompdf) (already bundled with Shopware)
- Admin product detail bar also shows a download button (opens the same public URL)
- Custom fields are automatically removed on plugin uninstall (unless user data is kept)

---

## Requirements

| Dependency | Version |
|---|---|
| Shopware | ~6.7.0 |
| PHP | ^8.2 |
| dompdf/dompdf | ^3.0 (bundled with Shopware) |

---

## Installation

```bash
bin/console plugin:refresh
bin/console plugin:install --activate VicProductPdf
bin/build-administration.sh
bin/console cache:clear
```

---

## Usage

### Enabling the PDF button on a product

1. Go to **Catalogue → Products** in the admin and open a product
2. Open the **Custom fields** tab
3. Under the **"Product PDF Download"** group, check **"Show PDF download button"**
4. Save the product

The download button will appear on the storefront product page.

### Direct URL

```
https://your-shop.com/product-pdf/{productId}
```

Publicly accessible without authentication. The `productId` is the product UUID (no hyphens).

---

## Structure

```
VicProductPdf/
├── composer.json
└── src/
    ├── VicProductPdf.php                          # Lifecycle: creates/removes custom fields
    ├── Controller/
    │   └── ProductPdfController.php               # Public route, generates the PDF
    └── Resources/
        ├── config/
        │   ├── routes.xml
        │   └── services.xml
        ├── views/
        │   ├── storefront/component/buy-widget/
        │   │   └── buy-widget-form.html.twig      # Download button on the product page
        │   └── vic-product-pdf/
        │       └── product-pdf.html.twig          # HTML template for the PDF
        └── app/administration/src/
            └── module/vic-product-pdf/            # Admin download button
```

---

## Author

**Vicmescan** · [github.com/Vicmescan](https://github.com/Vicmescan)
