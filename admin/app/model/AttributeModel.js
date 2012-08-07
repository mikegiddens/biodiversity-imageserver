Ext.define('BIS.model.AttributeModel', {
    extend: 'Ext.data.Model',
    alias: 'model.attributeModel',

    fields: [
        {
            name: 'valueID'
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
