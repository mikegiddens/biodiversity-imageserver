Ext.define('BIS.model.CategoryModel', {
    extend: 'Ext.data.Model',

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
        }
    ]
});
