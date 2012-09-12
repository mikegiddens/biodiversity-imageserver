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
                }
            ]
        });
        me.callParent(arguments);
    },
    remove: function() {
        Ext.Ajax.request({
            method: 'POST',
            url: Config.baseUrl + 'resouces/api/api.php',
            params: {
                cmd: 'remoteAccessKeyDelete',
                remoteAccessId: this.record.data.id
            },
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                console.log( res );
                if ( res.success ) {
                    Ext.getCmp('keyGrid').getStore().load();
                }
            }
        });
    }
});
