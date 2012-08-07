Ext.define('BIS.store.CollectionsTreeStore', {
    extend: 'Ext.data.TreeStore',
    requires: [
        'BIS.model.CollectionModel'
    ],
    autoLoad: true,
    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            storeId: 'collectionTreeStore',
            model: 'BIS.model.CollectionModel',
            proxy: {
                type: 'jsonp',
                url: Config.baseUrl + 'resources/api/api.php',
                extraParams: {
                    cmd: 'collections'
                },
                reader: {
                    type: 'json',
                    root: 'records'
                }
            }
        }, cfg)]);
    }
});
