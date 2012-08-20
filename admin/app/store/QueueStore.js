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
            listeners: {
                load: function( store, records, isSuccessful, operation, opts ) {
                    if (!(isSuccessful)) {
                        //Ext.get('queuePanel-body').update('<span style="position: relative; left: 10px; top: 10px">Queue is empty.</span>');
                    }
                }
            },
            proxy: {
                url: Config.baseUrl + 'resources/api/api.php',
                type: 'ajax',
                extraParams: {
                    cmd: 'queueList'
                },
                reader: {
                    type: 'json',
                    root: 'records',
                    successProperty: 'success'
                }
            }
        }, cfg)]);
    }
});
