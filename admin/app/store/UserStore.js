Ext.define('BIS.store.UserStore', {
    extend: 'Ext.data.Store',
    requires: [
        'BIS.model.UserModel'
    ],

    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            storeId: 'userStore',
            model: 'BIS.model.UserModel',
            proxy: {
                type: 'ajax',
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        }, cfg)]);
    }
});
