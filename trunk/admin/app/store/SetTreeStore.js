Ext.define('BIS.store.SetTreeStore', {
    extend: 'Ext.data.TreeStore',
    requires: [
        'BIS.model.SetModel'
    ],
    autoLoad: true,
    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {
        };
        me.callParent([Ext.apply({
            storeId: 'setTreeStore',
            model: 'BIS.model.SetModel',
            defaultRootProperty: 'values',
            listeners: {
                load: function( store, records, isSuccessful, operation, opts ) {
                    if (!(isSuccessful)) {
                        Ext.get('setTreePanel-body').update('<span style="position: relative; left: 10px; top: 10px">No sets found. Click "Add Set" above to create a new one.</span>');
                    }
                }
            },
            proxy: {
                type: 'jsonp',
                url: Config.baseUrl + 'resources/api/api.php',
                extraParams: {
                    cmd: 'setList'
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
