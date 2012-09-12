Ext.define('BIS.view.CtxMnuImage', {
    extend: 'Ext.menu.Menu',
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'open':
                    Ext.create('Ext.window.Window', {
                        title: 'View Image ' + this.record.data.filename,
                        iconCls: 'icon_image',
                        bodyCls: 'x-docked-noborder-top x-docked-noborder-bottom x-docked-noborder-right x-docked-noborder-left',
                        modal: true,
                        height: 500,
                        width: 800,
                        layout: 'fit',
                        maximizable: true,
                        items: [
                            {
                                xtype: 'tabpanel',
                                border: false,
                                activeItem: 0,
                                items: [
                                    {
                                        xtype: 'panel',
                                        border: false,
                                        title: 'Static Image',
                                        iconCls: 'icon_image',
                                        autoScroll: true,
                                        html: '<img src="'+this.record.data.path + this.record.data.filename.substr( 0, this.record.data.filename.indexOf('.') ) + '_l.' + this.record.data.ext+'">'
                                    },
                                    {
                                        xtype: 'imagezoomviewer',
                                        border: false,
                                        title: 'Zooming Image',
                                        iconCls: 'icon_magnifier',
                                        imageId: this.record.data.imageId
                                    }
                                ]
                            }
                        ]
                    }).show();
                    break;
                case 'delete':
                    Ext.Msg.confirm('Remove ' + this.record.data.filename + '?', 'Are you sure you want remove ' + this.record.data.filename + '?', function( btn, nothing, item ) {
                        if ( btn == 'yes' ) this.remove();
                    }, this);
                    break;
                case 'cw':
                    this.rotateCW();
                    break;
                case 'ccw':
                    this.rotateCCW();
                    break;
                case 'mirror':
                    this.rotate180();
                    break;
            }
        }
    },
    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    text: 'View Image',
                    iconCls: 'icon_image',
                    identifier: 'open'
                },
                {
                    text: 'Remove Image',
                    iconCls: 'icon_editCategory',
                    identifier: 'delete'
                },
                '-',
                {
                    text: 'Rotate 90&#176; Right',
                    iconCls: 'icon_arrowCW',
                    identifier: 'cw'
                },
                {
                    text: 'Rotate 90&#176; Left',
                    iconCls: 'icon_arrowCCW',
                    identifier: 'ccw'
                },
                {
                    text: 'Rotate 180&#176;',
                    iconCls: 'icon_arrowAlternating',
                    identifier: 'mirror'
                }
            ]
        });
        me.callParent(arguments);
    },
    remove: function() {
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'imageDelete',
                imageId: this.record.data.imageId
            },
            scope: this,
            success: function() {
                Ext.getCmp('imagesGrid').getStore().load();
            }
        });
    },
    rotateCW: function() {
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'imageModifyRotate',
                imageId: this.record.data.imageId,
                degree: 90
            }
        });
    },
    rotateCCW: function() {
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'imageModifyRotate',
                imageId: this.record.data.imageId,
                degree: 270
            }
        });
    },
    rotate180: function() {
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'imageModifyRotate',
                imageId: this.record.data.imageId,
                degree: 270
            }
        });
    }
});
