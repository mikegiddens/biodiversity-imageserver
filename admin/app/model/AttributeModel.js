Ext.define('BIS.model.AttributeModel', {
    extend: 'Ext.data.Model',
    alias: 'model.attributeModel',

    fields: [
        {
            name: 'attributeId'
        },
        {
            name: 'categoryId'
        },
        {
            name: 'title',
            mapping: 'name'
        },
        {
            name: 'leaf',
            defaultValue: true
        },
        {
            name: 'checked',
            defaultValue: null
        },
        {
            name: 'modelClass',
            defaultValue: 'attribute'
        }
    ],
    belongsTo: 'CategoryModel'
});
