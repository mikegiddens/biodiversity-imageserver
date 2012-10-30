Ext.define('BIS.view.CtxMnuEvernote', {
    extend: 'Ext.menu.Menu',
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'update':
                    this.update();
                    break;
                case 'delete':
                    Ext.Msg.confirm('Remove Evernote Account', 'Are you sure you want remove "' + this.record.data.name + '"?', function( btn, nothing, item ) {
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
                    text: 'Edit Account',
                    iconCls: 'icon_evernote',
                    identifier: 'update'
                },
                {
                    text: 'Make Default',
                    iconCls: 'icon_evernote',
                    identifier: 'default',
                    disabled: true
                },
                '-',
                {
                    text: 'Remove Account',
                    iconCls: 'icon_evernote',
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
            params: { enAccountId: this.record.data.enAccountId, cmd: 'evernoteAccountDelete' },
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                if ( res.success ) {
                    this.fireEvent('accountDeleted');
                }
            }
        });
    },
    update: function() {
        var tmpWindow = Ext.create('Ext.window.Window', {
            title: 'Edit Account ' + this.record.data.name,
            iconCls: 'icon_evernote',
            modal: true,
            height: 300,
            width: 500,
            layout: 'fit',
            items: [
                Ext.create('widget.formcreateevernoteaccount', {
                    device: this.record,
                    mode: 'edit'
                })
            ]
        });
        tmpWindow.on('accountCreated', function( data ) {
            Ext.getCmp('evernoteSettingsGrid').getStore().load();
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
            params: { enAccountId: this.record.data.enAccountId, cmd: 'evernoteAccountSetDefault' },
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                if ( res.success ) {
                    this.fireEvent('accountUpdated');
                }
            }
        });
    }
});
