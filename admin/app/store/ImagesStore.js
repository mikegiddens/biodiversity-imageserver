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
            pageSize: 50,
            listeners: {
                scope: this,
                load: function( store, records, isSuccessful, operation, opts ) {
                    if ( !isSuccessful ) {
                        Ext.get('imagesPanel-body').update('<span style="position: relative; left: 10px; top: 10px">No images found. Click "Add Image" below or drag and drop one or more onto this panel to add a new one.</span>');
                    }
                    // make sure store is on the correct page
                    var numRecords = this.getTotalCount();
                    var pageWeShouldBeOn = Math.ceil( numRecords / this.pageSize );
                    var pageWeAreOn = this.currentPage;
                    if ( pageWeAreOn > pageWeShouldBeOn ) {
                        this.loadPage( pageWeShouldBeOn );
                    }
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
