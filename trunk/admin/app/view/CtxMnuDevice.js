Ext.define('BIS.view.CtxMnuDevice', {
    extend: 'Ext.menu.Menu',
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'update':
                    this.update();
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
                    text: 'Edit',
                    iconCls: 'icon_editDevice',
                    identifier: 'update'
                },
                {
                    text: 'Remove',
                    iconCls: 'icon_removeDevice',
                    identifier: 'delete'
                }
            ]
        });
        me.callParent(arguments);
    },
    remove: function() {
        var cmd = 'deleteStorageDevice'
            params = { storage_id: this.record.storage_id }
    },
    update: function() {
        Ext.create('Ext.window.Window', {
            title: 'Edit Device ' + this.record.data.name,
            iconCls: 'icon_editDevice',
            modal: true,
            height: 500,
            width: 800,
            layout: 'fit',
            items: [
                Ext.create('widget.formcreatedevice', {
                    record: this.record
                })
            ]
        }).show();
    }
});
