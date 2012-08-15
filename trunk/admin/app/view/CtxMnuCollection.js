Ext.define('BIS.view.CtxMnuCollection', {
    extend: 'Ext.menu.Menu',
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'update':
                    Ext.create('Ext.window.Window', {
                        title: 'Edit ' + this.record.data.name,
                        iconCls: 'icon_editCollection',
                        modal: true,
                        height: 500,
                        width: 800,
                        layout: 'fit',
                        items: [
                            Ext.create('widget.formcreatecollection', {
                                record: this.record,
                                mode: 'edit'
                            })
                        ]
                    }).show();
                    break;
                case 'delete':
                    Ext.Msg.confirm('Remove ' + this.record.data.name + '?', 'Are you sure you want remove ' + this.record.data.name + '?', function( btn, nothing, item ) {
                        this.remove();
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
            url: Config.baseUrl + 'collectionDelete',
            params: { collectionId: this.record.data.collectionId },
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                console.log( res );
                if ( res.success ) {
                    
                }
            }
        });
    }
});
