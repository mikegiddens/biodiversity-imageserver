Ext.define('BIS.store.ClientStationsStore', {
    extend: 'Ext.data.Store',
    alias: 'store.clientStationsStore',
    autoLoad: true,
    requires: [
        'BIS.model.KeyModel'
    ],
    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {
        };
        me.callParent([Ext.apply({
            storeId: 'clientStationsStore',
            model: 'BIS.model.KeyModel',
            proxy: {
                url: Config.baseUrl + 'resources/api/api.php',
                type: 'ajax',
                extraParams: {
                    cmd: 'remoteAccessKeyList'
                },
                reader: {
                    type: 'json',
                    root: 'records',
                    successProperty: 'success',
                    totalProperty: 'totalCount'
                }
            }
        }, cfg)]);
    }
});
