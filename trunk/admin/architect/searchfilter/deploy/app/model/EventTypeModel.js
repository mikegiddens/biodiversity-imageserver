Ext.define('BIS.model.EventTypeModel', {
    extend: 'Ext.data.Model',

    fields: [
        {
            name: 'eventTypeId'
        },
        {
            name: 'title'
        },
        {
            name: 'description'
        },
        {
            name: 'lastModifiedBy'
        },
        {
            name: 'modifiedTime'
        }
    ]
});
