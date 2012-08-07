Ext.define('BIS.store.QueueStore', {
    extend: 'Ext.data.Store',
    alias: 'store.queueStore',
    requires: [
        'BIS.model.QueueModel'
    ],
    autoLoad: true,
    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {
        };
        me.callParent([Ext.apply({
            storeId: 'queueStore',
            model: 'BIS.model.QueueModel',
            proxy: {
                url: Config.baseUrl + 'resources/api/api.php',
                type: 'jsonp',
                extraParams: {
                    cmd: 'list_process_queue'
                },
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        }, cfg)]);
    }
});
