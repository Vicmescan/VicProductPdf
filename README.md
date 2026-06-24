# VicProductPdf — Shopware 6.7 Plugin

Adds a **"Download as PDF"** button to the storefront product detail page, allowing customers to download a product data sheet as a PDF file.

---

## Features

- Download button on the storefront product detail page
- PDF includes: main image, name, SKU, gross/net price, manufacturer, stock, EAN, description, properties, variants and custom fields
- **Per-product toggle** via a custom field checkbox in the admin (Custom fields tab)
- **Configurable custom fields whitelist** — choose exactly which Zusatzfelder appear in the PDF via a checkbox UI in the plugin settings
- Public route, no authentication required: `/product-pdf/{productId}`
- PDF generation powered by [DOMPDF](https://github.com/dompdf/dompdf) (bundled inside the plugin — no extra installation needed)
- **Multilingual PDF** — labels and product data adapt to the active storefront language (DE, EN, ES; falls back to EN)
- Admin product detail bar also shows a download button (opens the same public URL)
- Custom fields are automatically removed on plugin uninstall (unless user data is kept)

---

<img width="1039" height="788" alt="Captura desde 2026-06-02 11-34-59" src="https://github.com/user-attachments/assets/9a6b4b14-a990-406a-a9eb-0d6df4be7ef4" />
<img width="1081" height="914" alt="Captura desde 2026-06-02 11-35-39" src="https://github.com/user-attachments/assets/98c429c5-2822-4e79-a56f-df208d7edf80" />
<img width="1415" height="957" alt="Captura desde 2026-06-02 11-41-52" src="https://github.com/user-attachments/assets/41001390-9426-4d6d-bd50-f780b2cd3cae" />
<img width="844" height="912" alt="Captura desde 2026-06-02 11-36-14" src="https://github.com/user-attachments/assets/19be431e-b4bd-42c7-b925-b6be73ed6f2e" />


## Requirements

| Dependency | Version |
|---|---|
| Shopware | ~6.7.0 |
| PHP | ^8.2 |
| dompdf/dompdf | ^3.0 (bundled in the plugin) |

---

## Installation

The plugin is self-contained — dompdf is bundled inside the `vendor/` folder, so no extra Composer step is needed.

```bash
# 1. Clone or copy the plugin into your Shopware project
git clone https://github.com/Vicmescan/VicProductPdf custom/plugins/VicProductPdf

# 2. Activate it
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

### Configuring which custom fields appear in the PDF

1. Go to **Extensions → My extensions** and open the **VicProductPdf** settings
2. Under **"Custom fields to show in PDF"**, check each field you want to include
3. Save

Only the selected fields will appear in the PDF. If no fields are selected, the custom fields section is omitted entirely.

### Direct URL

```
https://your-shop.com/product-pdf/{productId}
```

Publicly accessible without authentication. The `productId` is the product UUID (no hyphens).

---

## Structure

```
VicProductPdf/
├── composer.json          # Plugin metadata & Shopware version constraint
├── composer-bundle.json   # Minimal manifest used to generate vendor/ (dompdf only)
├── composer-bundle.lock
├── vendor/                # Bundled dompdf — committed so the plugin is drop-in
└── src/
    ├── VicProductPdf.php                          # Lifecycle + boot() loads bundled vendor/
    ├── Controller/
    │   └── ProductPdfController.php               # Public route, generates the PDF
    └── Resources/
        ├── config/
        │   ├── config.xml                         # Plugin settings (custom fields whitelist)
        │   ├── routes.xml
        │   └── services.xml
        ├── views/
        │   ├── storefront/component/buy-widget/
        │   │   └── buy-widget-form.html.twig      # Download button on the product page
        │   └── vic-product-pdf/
        │       └── product-pdf.html.twig          # HTML template for the PDF
        └── app/administration/src/
            └── module/vic-product-pdf/
                ├── component/
                │   └── vic-custom-fields-select/  # Checkbox UI for the whitelist setting
                └── extension/
                    └── sw-product-detail/         # Admin download button
```

---

## Author

**Vicmescan** · [github.com/Vicmescan](https://github.com/Vicmescan)
