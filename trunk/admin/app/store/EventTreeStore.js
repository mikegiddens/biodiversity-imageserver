Ext.define('BIS.store.EventTreeStore', {
    extend: 'Ext.data.TreeStore',
    requires: [
        'BIS.model.EventModel',
        'BIS.model.EventTypeModel'
    ],
    autoLoad: true,
    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            storeId: 'eventTreeStore',
            model: 'BIS.model.EventTypeModel',
            defaultRootId: 'results',
            proxy: {
                type: 'jsonp',
                url: Config.baseUrl + 'resources/api/api.php',
                extraParams: {
                    cmd: 'listEventTypes'
                },
                reader: {
                    type: 'json',
                    root: 'results'
                }
            }
        }, cfg)]);
    }
});
