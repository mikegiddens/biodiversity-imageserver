Ext.define('BIS.view.CtxMnuCategory', {
    extend: 'Ext.menu.Menu',
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'create':
                    Ext.create('Ext.window.Window', {
                        title: 'Add Attribute to ' + this.record.data.title,
                        iconCls: 'icon_newAttribute',
                        modal: true,
                        height: 500,
                        width: 800,
                        layout: 'fit',
                        items: [
                            Ext.create('widget.formcreateattribute', {
                                record: this.record,
                                mode: 'add'
                            })
                        ]
                    }).show();
                    break;
                case 'update':
                    Ext.create('Ext.window.Window', {
                        title: 'Edit ' + this.record.data.title,
                        iconCls: 'icon_editCategory',
                        modal: true,
                        height: 500,
                        width: 800,
                        layout: 'fit',
                        items: [
                            Ext.create('widget.formcreatecategory', {
                                record: this.record,
                                mode: 'edit'
                            })
                        ]
                    }).show();
                    break;
            }
        }
    },
    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    text: 'Add Attribute',
                    iconCls: 'icon_newAttribute',
                    identifier: 'create'
                },
                {
                    text: 'Edit Category',
                    iconCls: 'icon_editCategory',
                    identifier: 'update'
                }
            ]
        });
        me.callParent(arguments);
    }
});
