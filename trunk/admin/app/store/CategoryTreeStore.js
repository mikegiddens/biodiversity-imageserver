Ext.define('BIS.store.CategoryTreeStore', {
    extend: 'Ext.data.TreeStore',
    requires: [
        'BIS.model.CategoryModel',
        'BIS.model.AttributeModel'
    ],
    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            storeId: 'categoryTreeStore',
            model: 'BIS.model.CategoryModel',
            //defaultRootProperty: 'data', // for tree store to locate children
            listeners: {
                load: function( store, records, isSuccessful, operation, opts ) {
                    if (!(isSuccessful)) {
                        Ext.get('categoryTreePanel-body').update('<span style="position: relative; left: 10px; top: 10px">No categories found. Click "New Category" above to create a new one.</span>');
                    }
                }
            },
            proxy: {
                type: 'ajax',
                url: Config.baseUrl + 'resources/api/api.php',
                extraParams: {
                    cmd: 'categoryList'
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
