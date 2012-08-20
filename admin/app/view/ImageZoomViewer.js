Ext.define('BIS.view.ImageZoomViewer', {
    extend: 'Ext.panel.Panel',
    alias: ['widget.imagezoomviewer'],
    requires: ['BIS.view.ImageZoom', 'BIS.view.ImageZoomTile'],
    id: 'imageZoomViewerPanel',
    listeners: {
        afterrender: function() {
            this.zoomcmp.loadImage( Config.baseUrl + 'resources/api/api.php?cmd=imageTilesGet&imageId=' + this.imageId );
        }
    },
    initComponent: function( config ) {
        var me = this;
        this.zoomcmp = Ext.create('BIS.view.ImageZoom');
        Ext.applyIf(me, {
            items: [
                this.zoomcmp
            ]
        });
        me.callParent(arguments);
    }
});
