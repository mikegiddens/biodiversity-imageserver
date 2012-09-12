Ext.define('BIS.model.KeyModel', {
    extend: 'Ext.data.Model',
    alias: 'model.keyModel',
    fields: [
        {
            name: 'remoteAccessId'
        },
        {
            name: 'ip'
        },
        {
            name: 'key'
        },
        {
            name: 'active',
        }
    ]
});
