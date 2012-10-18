Ext.define('BIS.view.CtxMnuAttribute', {
    extend: 'Ext.menu.Menu',
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'update':
                    this.update();
                    break;
                case 'delete':
                    Ext.Msg.confirm('Remove ' + this.record.data.title + '?', 'Are you sure you want remove ' + this.record.data.title + '?', function( btn, nothing, item ) {
                        if ( btn == 'yes' ) this.remove();
                    }, this);
                    break;
            }
        }
    },
    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    text: 'Add to',
                    iconCls: 'icon_',
                    menu: {
                        xtype: 'menu',
                        scope: me,
                        listeners: {
                            scope: me,
                            click: me.handleAssignment
                        },
                        items: [
                            {
                                text: 'Selected',
                                identifier: 'selected'
                            },
                            {
                                text: 'Filter Results',
                                identifier: 'filtered'
                            },
                            {
                                text: 'All Images',
                                identifier: 'all'
                            }
                        ]
                    }
                },
                '-',
                {
                    text: 'Edit',
                    iconCls: 'icon_editAttribute',
                    identifier: 'update'
                },
                {
                    text: 'Remove',
                    iconCls: 'icon_removeAttribute',
                    identifier: 'delete'
                }
            ]
        });
        me.callParent(arguments);
    },
    handleAssignment: function( menu, item ) {
        var imagesAffected = '(n/a)';
        var params = {
            cmd: 'imageAddAttribute',
            category: this.record.get('categoryId'),
            attribType: 'attributeId',
            attribute: this.record.get('attributeId')
        }
        switch ( item.identifier ) {
            case 'selected':
                var images = [];
                Ext.each( Ext.getCmp('imagesGrid').getSelectionModel().getSelection(), function( image ) { images.push( image.get('imageId') ) });
                imagesAffected = images.length;
                params.imageId = images;
                break;
            case 'filtered':
                params.advFilter = Ext.getCmp('imagesGrid').getStore().getProxy().extraParams.advFilter // last used advanced filter
                imagesAffected = Ext.getCmp('imagesGrid').getStore().totalCount;
                break;
            case 'all':
                params.advFilter = { node: "group", logop: "and", children: [] } // global filter
                imagesAffected = 'all';
                break;
        }
        Ext.Msg.confirm( 'Add Attribute to Images', 'Are you sure you want to add "' + this.record.get('name') + '" to <span style="font-weight:bold">' + imagesAffected + '</span> images?', function( btn, text, opts ) {
            if ( btn == 'yes' ) {
                Ext.Ajax.request({
                    url: Config.baseUrl + 'resources/api/api.php',
                    params: params,
                    scope: this,
                    success: function( data ) {
                        data = Ext.decode( data.responseText );
                        console.log( data );
                        if ( data.success ) {
                            // reload image details panel
                            var detailsPanel = Ext.getCmp('imageDetailsPanel');
                            detailsPanel.loadImage( detailsPanel.image );
                        }
                    }
                });
            }
        });
    },
    remove: function() {
        Ext.Ajax.request({
            method: 'POST',
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'attributeDelete',
                attributeId: this.record.data.attributeId
            },
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                if ( res.success ) {
                    /*
                    Ext.getCmp('categoryTreePanel').getStore().load({
                        node: this.record.parentNode
                    });
                    */
                    this.record.remove();
                }
            }
        });
    },
    update: function() {
        var tmpWindow = Ext.create('Ext.window.Window', {
            title: 'Edit Attribute ' + this.record.data.title,
            iconCls: 'icon_editAttribute',
            modal: true,
            height: 100,
            width: 350,
            layout: 'fit',
            items: [
                Ext.create('widget.formcreateattribute', {
                    record: this.record,
                    mode: 'edit'
                })
            ]
        }).show();
        tmpWindow.on( 'attributeCreated', function( data ) {
            tmpWindow.close();
            Ext.getCmp('categoryTreePanel').getStore().load({
                node: this.record.parentNode
            });
        });
        tmpWindow.on( 'cancel', function( data ) {
            tmpWindow.close();
        });
    }
});
