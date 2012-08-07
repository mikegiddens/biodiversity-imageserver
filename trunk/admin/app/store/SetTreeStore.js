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
            proxy: {
                type: 'jsonp',
                url: Config.baseUrl + 'resources/api/api.php',
                extraParams: {
                    cmd: 'listSets'
                },
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        }, cfg)]);
    }
});
