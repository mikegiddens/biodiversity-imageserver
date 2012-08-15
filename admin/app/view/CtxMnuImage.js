Ext.define('BIS.view.CtxMnuImage', {
    extend: 'Ext.menu.Menu',
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'open':
                    Ext.create('Ext.window.Window', {
                        title: 'View Image ' + record.data.filename,
                        iconCls: 'icon_image',
                        modal: true,
                        height: 500,
                        width: 800,
                        layout: 'fit',
                        items: [
                            {
                                xtype: 'tabpanel',
                                border: false,
                                activeItem: 0,
                                items: [
                                    {
                                        xtype: 'panel',
                                        title: 'Static Image',
                                        iconCls: 'icon_image',
                                        html: '<img src="'+record.data.path + record.data.filename.substr( 0, record.data.filename.indexOf('.') ) + '_l.' + record.data.ext+'">'
                                    },
                                    {
                                        xtype: 'imagezoomviewer',
                                        title: 'Zooming Image',
                                        iconCls: 'icon_magnifier'
                                    }
                                ]
                            }
                        ]
                    }).show();
                    break;
                case 'delete':
                    Ext.Msg.confirm('Remove ' + this.record.data.filename + '?', 'Are you sure you want remove ' + this.record.data.filename + '?', function( btn, nothing, item ) {
                        this.remove();
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
        var cmd = 'imageDelete'
            params = { imageId: this.record.imageId }
    }
    rotateCW: function() {
        var cmd = 'imageRotateCW'
            params = { imageId: this.record.imageId }
    }
    rotateCCW: function() {
        var cmd = 'imageRotateCCW'
            params = { imageId: this.record.imageId }
    }
    rotate180: function() {
        var cmd = 'imageRotate180'
            params = { imageId: this.record.imageId }
    }
});
