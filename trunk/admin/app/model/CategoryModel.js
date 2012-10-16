Ext.define('BIS.model.CategoryModel', {
    extend: 'Ext.data.Model',
    alias: 'model.categoryModel',

    fields: [
        {
            name: 'categoryId'
        },
        {
            name: 'title'
        },
        {
            name: 'description'
        },
        {
            name: 'elementSet'
        },
        {
            name: 'term'
        },
        {
            name: 'modelClass',
            defaultValue: 'category'
        }
    ],
    hasMany: 'BIS.model.AttributeModel'
});
