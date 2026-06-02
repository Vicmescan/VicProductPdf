import template from './vic-custom-fields-select.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('vic-custom-fields-select', {
    template,

    inject: ['repositoryFactory'],

    props: {
        value: {
            type: String,
            required: false,
            default: null,
        },
    },

    emits: ['update:value'],

    data() {
        return {
            customFieldSets: [],
            selected: [],
            isLoading: false,
        };
    },

    computed: {
        groupedFields() {
            return this.customFieldSets
                .map(set => ({
                    id: set.id,
                    name: set.config?.label?.[Shopware.Context.app?.fallbackLocale]
                        ?? set.config?.label?.['de-DE']
                        ?? set.config?.label?.['en-GB']
                        ?? set.name,
                    fields: set.customFields ? [...set.customFields] : [],
                }))
                .filter(group => group.fields.length > 0);
        },
    },

    watch: {
        value(newVal) {
            this.selected = this.parseValue(newVal);
        },
    },

    created() {
        this.selected = this.parseValue(this.value);
        this.loadCustomFieldSets();
    },

    methods: {
        parseValue(val) {
            if (!val || typeof val !== 'string' || val.trim() === '') {
                return [];
            }
            return val.split(',').map(v => v.trim()).filter(Boolean);
        },

        async loadCustomFieldSets() {
            this.isLoading = true;
            try {
                const repository = this.repositoryFactory.create('custom_field_set');
                const criteria = new Criteria();
                criteria.addAssociation('customFields');
                criteria.addAssociation('relations');

                const result = await repository.search(criteria, Shopware.Context.api);
                this.customFieldSets = result.filter(set =>
                    set.relations && [...set.relations].some(r => r.entityName === 'product')
                );
            } finally {
                this.isLoading = false;
            }
        },

        getFieldLabel(field) {
            const labels = field.config?.label;
            if (!labels) return field.name;
            const locale = Shopware.Context.app?.fallbackLocale ?? 'de-DE';
            return labels[locale] ?? labels['de-DE'] ?? labels['en-GB'] ?? Object.values(labels)[0] ?? field.name;
        },

        isChecked(fieldName) {
            return this.selected.includes(fieldName);
        },

        onToggle(fieldName, checked) {
            if (checked) {
                if (!this.selected.includes(fieldName)) {
                    this.selected = [...this.selected, fieldName];
                }
            } else {
                this.selected = this.selected.filter(n => n !== fieldName);
            }
            this.$emit('update:value', this.selected.join(', '));
        },
    },
});
