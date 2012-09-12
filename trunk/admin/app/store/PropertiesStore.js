Ext.define('BIS.store.PropertiesStore', {
    extend: 'Ext.data.Store',
    alias: 'store.propertiesStore',
    autoLoad: true,
    requires: [
        'BIS.model.AttributeModel'
    ],
    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {
        };
        me.callParent([Ext.apply({
            storeId: 'propertiesStore',
            model: 'BIS.model.AttributeModel',
            proxy: {
                url: Config.baseUrl + 'resources/api/api.php',
                type: 'ajax',
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
