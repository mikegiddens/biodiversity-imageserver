Ext.define('BIS.model.QueueModel', {
    extend: 'Ext.data.Model',
    alias: 'model.queueModel',

    fields: [
        {
            name: 'image_id'
        },
        {
            name: 'process_type'
        },
        {
            name: 'extra'
        },
        {
            name: 'date_added'
        },
        {
            name: 'processed'
        },
        {
            name: 'errors'
        },
        {
            name: 'error_details'
        }
    ]
});
