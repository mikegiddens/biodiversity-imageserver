Ext.define('BIS.view.ImageZoomViewer', {
    extend: 'Ext.panel.Panel',
    alias: ['widget.imagezoomviewer'],
    requires: ['BIS.view.ImageZoom', 'BIS.view.ImageZoomTile'],
    id: 'imageZoomViewerPanel',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'imagezoom'
                }
            ]
        });
        me.callParent(arguments);
    }
});
