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
            name: 'elementSet'
        },
        {
            name: 'term'
        },
        {
            name: 'description'
        },
        {
            name: 'leaf',
            defaultValue: false
        },
        {
            name: 'iconCls',
            defaultValue: 'icon_category'
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
