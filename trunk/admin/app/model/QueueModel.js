Ext.define('BIS.model.QueueModel', {
    extend: 'Ext.data.Model',
    alias: 'model.queueModel',

    fields: [
        {
            name: 'imageId'
        },
        {
            name: 'processType'
        },
        {
            name: 'extra'
        },
        {
            name: 'dateAdded'
        },
        {
            name: 'processed'
        },
        {
            name: 'errors'
        },
        {
            name: 'errorDetails'
        }
    ]
});
