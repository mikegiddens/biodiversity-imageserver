Ext.define('BIS.store.SavedFilterStore', {
    extend: 'Ext.data.Store',
    alias: 'store.savedFilterStore',
    autoLoad: true,
    requires: [
        'BIS.model.SavedFilterModel'
    ],
    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {
        };
        me.callParent([Ext.apply({
            storeId: 'savedFilterStore',
            model: 'BIS.model.SavedFilterModel',
            proxy: {
                url: Config.baseUrl + 'resources/api/api.php',
                type: 'ajax',
                extraParams: {
                    cmd: 'advFilterList'
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
