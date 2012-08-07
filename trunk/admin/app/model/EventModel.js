Ext.define('BIS.model.EventModel', {
    extend: 'Ext.data.Model',
    alias: 'model.eventModel',

    fields: [
        {
            name: 'eventId'
        },
        {
            name: 'geoId'
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
            name: 'leaf',
            defaultValue: true
        },
        {
            name: 'checked',
            defaultValue: null
        },
        {
            name: 'modelClass',
            defaultValue: 'event'
        }
    ]
});
