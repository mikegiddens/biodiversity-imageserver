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
                scope: this,
                load: function( store, records, isSuccessful, operation, opts ) {
                    if (!(isSuccessful)) {
                        Ext.get('imagesPanel-body').update('<span style="position: relative; left: 10px; top: 10px">No images found. Click "Add Image" below or drag and drop one or more onto this panel to add a new one.</span>');
                    }
                    Ext.select('div.imageSelector').each( function( el ) {
                        var dropTarget = new Ext.dd.DropTarget( el.dom, {
                            ddGroup: 'categoryDD',
                            copy: false,
                            notifyDrop: function (dragSource, e, data) {
                                console.log( dragSource, e, data );
                                var record = data.records[0].data;
                                var imgId = '';
                                console.log( 'Associate ' + data.title + ' with ' );
                            }
                        });
                    });
                }
            },
            proxy: {
                url: Config.baseUrl + 'resources/api/api.php',
                type: 'ajax',
                extraParams: {
                    cmd: 'imageList'
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
