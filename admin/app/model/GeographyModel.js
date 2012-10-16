Ext.define('BIS.model.GeographyModel', {
    extend: 'Ext.data.Model',

    fields: [
        {
            name: 'geographyId'
        },
        {
            name: 'country'
        },
        {
            name: 'countryIso'
        },
        {
            name: 'admin0'
        },
        {
            name: 'admin1'
        },
        {
            name: 'admin2'
        },
        {
            name: 'admin3'
        }
    ]
});
