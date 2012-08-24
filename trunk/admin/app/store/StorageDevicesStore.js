Ext.define('BIS.store.StorageDevicesStore', {
    extend: 'Ext.data.Store',
    alias: 'store.storageDevicesStore',
    requires: [
        'BIS.model.StorageDeviceModel'
    ],
    autoLoad: true,
    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {
        };
        me.callParent([Ext.apply({
            storeId: 'storageDevicesStore',
            model: 'BIS.model.StorageDeviceModel',
            proxy: {
                url: Config.baseUrl + 'resources/api/api.php',
                type: 'ajax',
                extraParams: {
                    cmd: 'storageDeviceList'
                },
                reader: {
                    type: 'json',
                    root: 'records'
                }
            }
        }, cfg)]);
    }
});
