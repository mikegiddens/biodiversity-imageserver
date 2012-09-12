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
    remove: function() {
        Ext.Ajax.request({
            method: 'POST',
            url: Config.baseUrl + 'attributeDelete',
            params: { attributeId: this.record.data.attributeId },
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                console.log( res );
                if ( res.success ) {
                    
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
            // this shouldn't reload the whole store - just the parent node
            Ext.getCmp('categoryTreePanel').getStore().load();
        });
    }
});
