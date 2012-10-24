Ext.define('BIS.model.EventModel', {
    extend: 'Ext.data.Model',
    alias: 'model.eventModel',

    fields: [
        {
            name: 'eventId'
        },
        {
            name: 'geographyId'
        },
        {
            name: 'eventDate'
        },
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
            name: 'leaf',
            defaultValue: false
        },
        {
            name: 'iconCls',
            defaultValue: 'icon_eventType'
        },
        {
            name: 'checked',
            defaultValue: null
        },
        {
            name: 'modelClass',
            defaultValue: 'eventtype'
        }
    ]
});
