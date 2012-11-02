Ext.define('BIS.store.GeographyStore', {
    extend: 'Ext.data.Store',

    requires: [ 'BIS.model.GeographyModel' ],

    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            autoLoad: true,
            storeId: 'geographyStore',
            model: 'BIS.model.GeographyModel',
            proxy: {
                type: 'jsonp',
                url: Config.baseUrl + 'resources/api/api.php',
                extraParams: {
                    cmd: 'geographyList',
                    group: 'name'
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
