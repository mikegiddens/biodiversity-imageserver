Ext.define('BIS.model.KeyModel', {
    extend: 'Ext.data.Model',
    alias: 'model.keyModel',
    fields: [
        {
            name: 'title'
        },
        {
            name: 'description'
        },
        {
            name: 'remoteAccessId'
        },
        {
            name: 'ip'
        },
        {
            name: 'originalIp'
        },
        {
            name: 'key'
        },
        {
            name: 'active',
        }
    ]
});
