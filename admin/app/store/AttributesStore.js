Ext.define('BIS.store.AttributesStore', {
    extend: 'Ext.data.Store',

    requires: [ 'BIS.model.AttributeModel' ],

    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            autoLoad: true,
            storeId: 'attributesStore',
            model: 'BIS.model.AttributeModel',
            proxy: {
                type: 'jsonp',
                url: Config.baseUrl + 'resources/api/api.php',
                extraParams: {
                    cmd: 'attributeList',
                    showNames: false
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
