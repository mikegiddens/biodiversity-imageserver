Ext.define('BIS.store.KeyStore', {
    extend: 'Ext.data.Store',
    alias: 'store.keyStore',
    autoLoad: true,
    requires: [
        'BIS.model.KeyModel'
    ],
    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {
        };
        me.callParent([Ext.apply({
            storeId: 'keyStore',
            model: 'BIS.model.KeyModel',
            proxy: {
                url: Config.baseUrl + 'resources/api/api.php',
                type: 'ajax',
                extraParams: {
                    cmd: 'remoteAccessKeyList',
                    showNames: false
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
