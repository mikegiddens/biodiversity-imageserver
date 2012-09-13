Ext.define('BIS.view.CtxMnuCollection', {
    extend: 'Ext.menu.Menu',
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'filter':
                    Ext.getCmp('imagesGrid').setFilter({
                        code: this.record.data.code
                    }, true);
                    break;
                case 'update':
                    var me = this;
                    var tmpWindow = Ext.create('Ext.window.Window', {
                        title: 'Edit ' + me.record.data.name,
                        iconCls: 'icon_editCollection',
                        modal: true,
                        height: 225,
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
