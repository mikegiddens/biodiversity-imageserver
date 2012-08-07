Ext.define('BIS.store.ImagesStore', {
    extend: 'Ext.data.Store',
    alias: 'store.imagesStore',
    autoLoad: true,
    requires: [
        'BIS.model.ImageModel'
    ],
    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {
        };
        me.callParent([Ext.apply({
            storeId: 'imagesStore',
            model: 'BIS.model.ImageModel',
            proxy: {
                url: Config.baseUrl + 'resources/api/api.php',
                type: 'jsonp',
                extraParams: {
                    cmd: 'images'
                },
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        }, cfg)]);
    }
});
