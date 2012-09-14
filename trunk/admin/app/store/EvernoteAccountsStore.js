Ext.define('BIS.store.EvernoteAccountsStore', {
    extend: 'Ext.data.Store',
    alias: 'store.evernoteAccountsStore',
    requires: [
        'BIS.model.EvernoteAccountModel'
    ],
    autoLoad: true,
    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {
        };
        me.callParent([Ext.apply({
            storeId: 'evernoteAccountsStore',
            model: 'BIS.model.EvernoteAccountModel',
            proxy: {
                url: Config.baseUrl + 'resources/api/api.php',
                type: 'ajax',
                extraParams: {
                    cmd: 'evernoteAccountList'
                },
                reader: {
                    type: 'json',
                    root: 'records'
                }
            }
        }, cfg)]);
    }
});
