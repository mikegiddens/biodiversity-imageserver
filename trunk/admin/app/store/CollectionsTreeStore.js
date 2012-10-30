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
            listeners: {
                load: function( store, records, isSuccessful, operation, opts ) {
                    if (!(isSuccessful)) {
                        Ext.get('collectionTreePanel-body').update('<span style="position: relative; left: 10px; top: 10px">No collections found. Click "New Collection" above to create a new one.</span>');
                    }
                }
            },
            proxy: {
                type: 'ajax',
                url: Config.baseUrl + 'resources/api/api.php',
                extraParams: {
                    cmd: 'collectionList',
                    group: 'name'
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
