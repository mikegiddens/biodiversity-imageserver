Ext.define('BIS.view.CtxMnuCollection', {
    extend: 'Ext.menu.Menu',
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'filter':
                    Ext.getCmp('imagesGrid').getStore().getProxy().extraParams = {
                        cmd: 'imageList',
                        code: this.record.data.code
                    }
                    Ext.getCmp('imagesGrid').getStore().load();
                    break;
                case 'update':
                    var me = this;
                    var tmpWindow = Ext.create('Ext.window.Window', {
                        title: 'Edit ' + me.record.data.name,
                        iconCls: 'icon_editCollection',
                        modal: true,
                        height: 150,
                        width: 350,
                        layout: 'fit',
                        items: [
                            Ext.create('widget.formcreatecollection', {
                                record: me.record,
                                mode: 'edit'
                            })
                        ]
                    }).show();
                    tmpWindow.on( 'collectionCreated', function( data ) {
                        tmpWindow.close();
                        Ext.getCmp('collectionTreePanel').getStore().load();
                    });
                    tmpWindow.on( 'cancel', function( data ) {
                        tmpWindow.close();
                    });
                    break;
                case 'delete':
                    Ext.Msg.confirm('Remove Collection', 'Are you sure you want remove "' + this.record.data.name + '"?', function( btn, nothing, item ) {
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
                    text: 'Filter by ' + this.record.data.name,
                    iconCls: 'icon_magnifier',
                    identifier: 'filter'
                },
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
                    text: 'Edit Collection',
                    iconCls: 'icon_editCollection',
                    identifier: 'update'
                },
                {
                    text: 'Remove Collection',
                    iconCls: 'icon_removeCollection',
                    identifier: 'delete'
                }
            ]
        });
        me.callParent(arguments);
    },
    
    handleAssignment: function( menu, item ) {
        var imagesAffected = '(n/a)';
        var filteredImagesId = [];
        var params = {
            cmd: 'imageAddToCollection',
            code: this.record.get('code')
        }
        switch ( item.identifier ) {
            case 'selected':
                Ext.each( Ext.getCmp('imagesGrid').getSelectionModel().getSelection(), function( image ) { filteredImagesId.push( image.get('imageId') ) });
                imagesAffected = filteredImagesId.length;
                params.imageId = JSON.stringify( filteredImagesId );
                break;
            case 'filtered':
                filteredImagesId  =  Ext.getCmp('imagesGrid').getStore().collect( 'imageId', false, false );
                imagesAffected = filteredImagesId.length;
                params.imageId = JSON.stringify( filteredImagesId );
               /* params.imageId = Ext.getCmp('imagesGrid').getStore().collect( 'imageId', false, false );
                imagesAffected = Ext.getCmp('imagesGrid').getStore().totalCount;*/
                break;
            case 'all':
                params.advFilter = JSON.stringify({ node: "group", logop: "and", children: [] }); // global filter
                imagesAffected = 'all';
                break;
        }
        Ext.Msg.confirm( 'Associate Collection with Images', 'Are you sure you want to associate "' + this.record.get('name') + '" with <span style="font-weight:bold">' + imagesAffected + '</span> images?', function( btn, text, opts ) {
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
                            detailsPanel.loadImages( detailsPanel.images );
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
                cmd: 'collectionDelete',
                collectionId: this.record.data.collectionId
            },
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                if ( res.success ) {
                    Ext.getCmp('collectionTreePanel').getStore().load();
                }
            }
        });
    }
});
