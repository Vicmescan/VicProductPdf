import template from './vic-product-detail.html.twig';

const { Component } = Shopware;

Component.override('sw-product-detail', {
    template,

    data() {
        return {
            isPdfLoading: false,
        };
    },

    methods: {
        onDownloadPdf() {
            if (!this.productId) {
                return;
            }
            window.open(`/product-pdf/${this.productId}`, '_blank');
        },
    },
});
