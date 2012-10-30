Ext.define('BIS.store.EventTypesStore', {
    extend: 'Ext.data.Store',

    requires: [ 'BIS.model.EventTypeModel' ],

    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            autoLoad: true,
            storeId: 'eventTypesStore',
            model: 'BIS.model.EventTypeModel',
            proxy: {
                type: 'jsonp',
                url: Config.baseUrl + 'resources/api/api.php',
                extraParams: {
                    cmd: 'eventTypeList',
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
