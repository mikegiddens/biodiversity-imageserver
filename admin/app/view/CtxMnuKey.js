Ext.define('BIS.view.CtxMnuKey', {
    extend: 'Ext.menu.Menu',
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'remove':
                    Ext.Msg.confirm('Remove Access Key?', 'Are you sure you want remove key for "' + this.record.data.ip + '"?', function( btn, nothing, item ) {
                        if ( btn == 'yes' ) this.remove();
                    }, this);
                    break;
                case 'enable':
                case 'disable':
                    this.toggleActive();
            }
        }
    },
    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    text: 'Remove',
                    iconCls: 'icon_removeKey',
                    identifier: 'remove'
                },
                {
                    text: ( this.record.get('active') == 'true' ) ? 'Disable Key' : 'Enable Key',
                    iconCls: ( this.record.get('active') == 'true' ) ? 'icon_locked' : 'icon_unlocked',
                    identifier: ( this.record.get('active') == 'true' ) ? 'disable' : 'enable'
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
                cmd: 'remoteAccessKeyDelete',
                remoteAccessId: this.record.data.remoteAccessId
            },
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                if ( res.success ) {
                    Ext.getCmp('keyGrid').getStore().load();
                }
            }
        });
    },
    toggleActive: function() {
        Ext.Ajax.request({
            method: 'POST',
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: ( this.record.data.active ) ? 'remoteAccessKeyDisable' : 'remoteAccessKeyEnable',
                remoteAccessId: this.record.data.remoteAccessId
            },
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                if ( res.success ) {
                    Ext.getCmp('keyGrid').getStore().load();
                } else {
                    Ext.Msg.alert('Failed', res.error.msg );
                }
            }
        });
    }

});
