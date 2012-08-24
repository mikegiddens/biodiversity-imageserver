Ext.define('BIS.model.StorageDeviceModel', {
    extend: 'Ext.data.Model',
    alias: 'model.storageDeviceModel',

    fields: [
        {
            name: 'storageId'
        },
        {
            name: 'name'
        },
        {
            name: 'description'
        },
        {
            name: 'type'
        },
        {
            name: 'baseUrl'
        },
        {
            name: 'basePath'
        },
        {
            name: 'user'
        },
        {
            name: 'pw'
        },
        {
            name: 'key'
        },
        {
            name: 'active'
        },
        {
            name: 'default_storage'
        },
        {
            name: 'extra2'
        }
    ]
});
