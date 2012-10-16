Ext.define('BIS.model.ObjectModel', {
    extend: 'Ext.data.Model',

    fields: [
        {
            name: 'text'
        },
        {
            name: 'node'
        },
        {
            name: 'type'
        },
        {
            name: 'children'
        },
        {
            name: 'leaf'
        }
    ]
});
