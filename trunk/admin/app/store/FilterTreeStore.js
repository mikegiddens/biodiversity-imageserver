Ext.define('BIS.store.FilterTreeStore', {
    extend: 'Ext.data.TreeStore',

    requires: [ 'BIS.model.FilterModel' ],

    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            autoLoad: true,
            storeId: 'filterTreeStore',
            model: 'BIS.model.FilterModel',
            root: { node: 'group', logop: 'and', children: [] },
            proxy: {
                type: 'memory',
                reader: {
                    type: 'json'
                }
            }
        }, cfg)]);
    }
});
