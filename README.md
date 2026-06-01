# VicProductPdf — Shopware 6.7 Plugin

Añade un botón **"Als PDF herunterladen"** en la ficha de producto del storefront para que los clientes puedan descargar un dossier del producto en PDF.

---

## Características

- Botón de descarga en la ficha de producto (storefront)
- El PDF incluye: imagen, nombre, SKU, precio bruto/neto, fabricante, stock, EAN, descripción, propiedades, variantes y campos personalizados
- **Activación por producto** mediante un campo personalizado (checkbox en la pestaña *Campos personalizados* del admin)
- Ruta pública sin autenticación: `/product-pdf/{productId}`
- Generación de PDF con [DOMPDF](https://github.com/dompdf/dompdf) (ya incluido en Shopware)
- El admin también muestra un botón de descarga en la barra del detalle de producto
- Limpieza automática de custom fields al desinstalar (salvo que se conserven los datos)

---

## Requisitos

| Dependencia | Versión |
|---|---|
| Shopware | ~6.7.0 |
| PHP | ^8.2 |
| dompdf/dompdf | ^3.0 (incluido en Shopware) |

---

## Instalación

```bash
# Desde el directorio raíz de Shopware
bin/console plugin:refresh
bin/console plugin:install --activate VicProductPdf
bin/build-administration.sh
bin/console cache:clear
```

---

## Uso

### Activar el PDF en un producto

1. Admin → **Catálogo → Productos** → abre el producto
2. Pestaña **Campos personalizados**
3. Grupo **"Produkt PDF-Download"** → activa el checkbox **"PDF-Download-Button anzeigen"**
4. Guarda

El botón aparecerá automáticamente en la ficha del producto en el storefront.

### URL directa

```
https://tu-tienda.com/product-pdf/{productId}
```

Accesible sin autenticación. El `productId` es el UUID del producto (sin guiones).

---

## Estructura

```
VicProductPdf/
├── composer.json
└── src/
    ├── VicProductPdf.php                          # Lifecycle: crea/elimina custom fields
    ├── Controller/
    │   └── ProductPdfController.php               # Ruta pública, genera el PDF
    └── Resources/
        ├── config/
        │   ├── routes.xml
        │   └── services.xml
        ├── views/
        │   ├── storefront/component/buy-widget/
        │   │   └── buy-widget-form.html.twig      # Botón en la ficha de producto
        │   └── vic-product-pdf/
        │       └── product-pdf.html.twig          # Plantilla HTML del PDF
        └── app/administration/src/
            └── module/vic-product-pdf/            # Botón en el admin (abre URL pública)
```

---

## Autor

**Vicmescan** · [github.com/Vicmescan](https://github.com/Vicmescan)
