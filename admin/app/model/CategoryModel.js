Ext.define('BIS.model.CategoryModel', {
    extend: 'Ext.data.Model',
    alias: 'model.categoryModel',

    fields: [
        {
            name: 'typeID'
        },
        {
            name: 'title'
        },
        {
            name: 'modelClass',
            defaultValue: 'category'
        }
    ],
    hasMany: 'AttributeModel'
});
