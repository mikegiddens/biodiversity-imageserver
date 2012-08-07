Ext.define('BIS.model.EventTypeModel', {
    extend: 'Ext.data.Model',
    alias: 'model.eventTypeModel',

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
        },
        {
            name: 'modelClass',
            defaultValue: 'eventtype'
        }
    ]
});
