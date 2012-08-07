Ext.define('BIS.store.CategoryTreeStore', {
    extend: 'Ext.data.TreeStore',
    requires: [
        'BIS.model.CategoryModel',
        'BIS.model.AttributeModel'
    ],
    autoLoad: true,
    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            storeId: 'categoryTreeStore',
            model: 'BIS.model.CategoryModel',
            defaultRootProperty: 'data',
            proxy: {
                type: 'jsonp',
                url: Config.baseUrl + 'resources/api/api.php',
                extraParams: {
                    cmd: 'list_categories'
                },
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        }, cfg)]);
    }
});
