Ext.define('BIS.model.CattributeModel', {
    extend: 'Ext.data.Model',
    alias: 'model.cattributeModel',

    fields: [
        {
            name: 'attributeId'
        },
        {
            name: 'categoryId'
        },
        {
            name: 'title'
        },
        {
            name: 'name'
        },
        {
            name: 'leaf',
            defaultValue: false
        },
        {
            name: 'checked',
            defaultValue: null
        },
        {
            name: 'modelClass',
            defaultValue: 'category'
        }
    ]
});
