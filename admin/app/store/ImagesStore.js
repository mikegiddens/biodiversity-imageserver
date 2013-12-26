Ext.define('BIS.store.ImagesStore', {
    extend: 'Ext.data.Store',
    alias: 'store.imagesStore',
    autoLoad: true,
    remoteSort: true,
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
            pageSize: 50,
            listeners: {
                scope: this,
                load: function( store, records, isSuccessful, operation, opts ) {
                    // set no images text on error and no results
                    // this updates the view element's html
                    if ( Ext.getCmp('imagesGrid').getView().getEl() != undefined ){
                        var cmp = Ext.getCmp('imagesGrid').getView().getEl();
                    if ( !isSuccessful ) {
                        if ( cmp ) {
                            cmp.update('<span class="noImages">No images found. Drag and drop one or more onto this panel to add new ones.</span>');
                        }
                    } else {
                        if ( records.length == 0 ) {
                            if ( cmp ) {
                                cmp.update('<span class="noImages">No images found. Drag and drop one or more onto this panel to add new ones.</span>');
                            }
                        }
                    }
                    }
                    // unload image details panel
                    Ext.getCmp('imageDetailsPanel').loadImages([]);
                    // make sure store is on the correct page
                    var numRecords = this.getTotalCount();
                    var pageWeShouldBeOn = Math.ceil( numRecords / this.pageSize );
                    var pageWeAreOn = this.currentPage;
                    if ( pageWeAreOn > pageWeShouldBeOn && pageWeShouldBeOn > 0 ) {
                        this.loadPage( pageWeShouldBeOn );
                    }
                }
            },
            proxy: {
                url: Config.baseUrl + 'resources/api/api.php',
                type: 'ajax',
                sortParam: 'gridSort',
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
