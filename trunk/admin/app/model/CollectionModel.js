Ext.define('BIS.model.CollectionModel', {
    extend: 'Ext.data.Model',
    alias: 'model.collectionModel',

    fields: [
        {
            name: 'collectionId'
        },
        {
            name: 'name'
        },
        {
            name: 'code'
        },
        {
            name: 'collectionSize'
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
            defaultValue: 'collection'
        }
    ]
});
