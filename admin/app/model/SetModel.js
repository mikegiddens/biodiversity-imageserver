Ext.define('BIS.model.SetModel', {
    extend: 'Ext.data.Model',
    alias: 'model.setModel',

    fields: [
        {
            name: 'id'
        },
        {
            name: 'name'
        },
        {
            name: 'description'
        },
        {
            name: 'values'
        },
        {
            name: 'value'
        },
        {
            name: 'rank'
        }
    ]
});
