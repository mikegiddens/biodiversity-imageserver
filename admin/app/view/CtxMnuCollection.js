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
                }
            ]
        });
        me.callParent(arguments);
    }
});
