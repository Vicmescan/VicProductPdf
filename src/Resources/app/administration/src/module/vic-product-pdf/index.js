import enGB from './snippet/en-GB.json';
import deDE from './snippet/de-DE.json';
import esES from './snippet/es-ES.json';

import './extension/sw-product-detail';

Shopware.Locale.extend('en-GB', enGB);
Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('es-ES', esES);
