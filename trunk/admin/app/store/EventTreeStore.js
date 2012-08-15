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
            listeners: {
                load: function( store, records, isSuccessful, operation, opts ) {
                    if (!(isSuccessful)) {
                        Ext.get('eventTreePanel-body').update('<span style="position: relative; left: 10px; top: 10px">No event types found. Click "New Event Type" above to add a new one.</span>');
                    }
                }
            },
            proxy: {
                type: 'jsonp',
                url: Config.baseUrl + 'resources/api/api.php',
                extraParams: {
                    cmd: 'eventTypeList'
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
