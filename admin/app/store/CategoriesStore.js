Ext.define('BIS.store.CategoriesStore', {
    extend: 'Ext.data.Store',

    requires: [ 'BIS.model.CategoryModel' ],

    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            autoLoad: true,
            storeId: 'categoriesStore',
            model: 'BIS.model.CategoryModel',
            proxy: {
                type: 'jsonp',
                url: Config.baseUrl + 'resources/api/api.php',
                extraParams: {
                    cmd: 'categoryList',
                    group: 'title'
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
