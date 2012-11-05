Ext.define('BIS.store.GeographyTreeStore', {
    extend: 'Ext.data.TreeStore',

    requires: [ 'BIS.model.GeographyModel' ],

    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            autoLoad: true,
            storeId: 'geographyTreeStore',
            model: 'BIS.model.GeographyModel',
            proxy: {
                type: 'jsonp',
                url: Config.baseUrl + 'resources/api/api.php',
                extraParams: {
                    cmd: 'geographyList',
                    group: 'name',
                    limit: 500,
                    parentId: 0
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
