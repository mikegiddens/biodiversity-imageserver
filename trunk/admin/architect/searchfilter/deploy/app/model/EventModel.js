Ext.define('BIS.model.EventModel', {
    extend: 'Ext.data.Model',

    fields: [
        {
            name: 'eventId'
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
            name: 'geographyId'
        },
        {
            name: 'eventDate'
        }
    ]
});
