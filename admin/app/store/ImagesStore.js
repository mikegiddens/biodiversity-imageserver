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
            listeners: {
                load: function( store, records, isSuccessful, operation, opts ) {
                    if (!(isSuccessful)) {
                        Ext.get('imagesPanel-body').update('<span style="position: relative; left: 10px; top: 10px">No images found. Click "Add Image" below or drag and drop one or more onto this panel to add a new one.</span>');
                    }
                }
            },
            proxy: {
                url: Config.baseUrl + 'resources/api/api_old.php',
                type: 'jsonp',
                extraParams: {
                    cmd: 'images'
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
