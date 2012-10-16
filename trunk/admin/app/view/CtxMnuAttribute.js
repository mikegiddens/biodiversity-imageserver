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
        switch ( item.identifier ) {
            case 'selected':
                break;
            case 'filtered':
                break;
            case 'all':
                break;
        }
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
