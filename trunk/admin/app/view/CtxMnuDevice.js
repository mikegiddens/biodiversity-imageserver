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
                case 'default':
                    this.makeDefault();
                    break;
            }
        }
    },
    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    text: 'Edit Device',
                    iconCls: 'icon_editDevice',
                    identifier: 'update'
                },
                {
                    text: 'Make Default',
                    iconCls: 'icon_defaultDevice',
                    identifier: 'default',
                    disabled: this.record.data.defaultStorage == '1'
                },
                '-',
                {
                    text: 'Remove Device',
                    iconCls: 'icon_removeDevice',
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
            params: { storageDeviceId: this.record.data.storageDeviceId, cmd: 'storageDeviceDelete' },
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                if ( res.success ) {
                    this.fireEvent('deviceDeleted');
                }
            }
        });
    },
    update: function() {
        var tmpWindow = Ext.create('Ext.window.Window', {
            title: 'Edit Device ' + this.record.data.name,
            iconCls: 'icon_editDevice',
            modal: true,
            height: 500,
            width: 800,
            layout: 'fit',
            items: [
                Ext.create('widget.formcreatedevice', {
                    device: this.record,
                    mode: 'edit'
                })
            ]
        });
        tmpWindow.on('deviceCreated', function( data ) {
            Ext.getCmp('storageDevicesGrid').getStore().load();
            tmpWindow.close();
        });
        tmpWindow.on('cancel', function( data ) {
            tmpWindow.close();
        });
        tmpWindow.show();
    },
    makeDefault: function() {
        Ext.Ajax.request({
            method: 'POST',
            url: Config.baseUrl + 'resources/api/api.php',
            params: { storageDeviceId: this.record.data.storageDeviceId, cmd: 'storageDeviceSetDefault' },
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                if ( res.success ) {
                    this.fireEvent('deviceUpdated');
                }
            }
        });
    }
});
