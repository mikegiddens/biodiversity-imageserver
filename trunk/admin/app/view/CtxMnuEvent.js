Ext.define('BIS.view.CtxMnuEvent', {
    extend: 'Ext.menu.Menu',
    requires: ['BIS.view.FormCreateEvent'],
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'update':
                    this.update();
                    break;
                case 'delete':
                    Ext.Msg.confirm('Remove ' + this.record.data.title + '?', 'Are you sure you want remove ' + this.record.data.title + '?', function( btn, nothing, item ) {
                        if( btn == 'yes' ) this.remove();
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
                    text: 'Edit Event',
                    iconCls: 'icon_editEvent',
                    identifier: 'update'
                },
                {
                    text: 'Remove Event',
                    iconCls: 'icon_removeEvent',
                    identifier: 'delete'
                }
            ]
        });
        me.callParent(arguments);
    },
    handleAssignment: function( menu, item ) {
        var params = {
            cmd: 'imageAddToEvent',
            eventId: this.record.get('eventId')
        }
        switch ( item.identifier ) {
            case 'selected':
                console.log( 'this one isn\'t hooked up yet' );
                break;
            case 'filtered':
                params.advFilter = Ext.getCmp('imagesGrid').getStore().getProxy().extraParams.advFilter // last used advanced filter
                break;
            case 'all':
                console.log( 'this one isn\'t hooked up yet' );
                break;
        }
        Ext.Msg.confirm( 'Add Attribute to Images', 'Are you sure you want to add "' + this.record.get('title') + '" to ' + Ext.getCmp('imagesGrid').getStore().totalCount + ' images?', function( btn, text, opts ) {
            if ( btn == 'yes' ) {
                Ext.Ajax.request({
                    url: Config.baseUrl + 'resources/api/api.php',
                    params: params,
                    scope: this,
                    success: function( data ) {
                        data = Ext.decode( data.responseText );
                        if ( data.success ) {
                        }
                        console.log( data );
                    }
                });
            }
        });
    },
    remove: function() {
        var me = this;
        Ext.Ajax.request({
            method: 'POST',
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'eventDelete',
                eventId: this.record.get('eventId'),
                eventTypeId: this.record.get('eventTypeId')
            },
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                if ( res.success ) {
                    me.record.remove();
                }
            },
            failure: function( form, action ) {
                var res = Ext.decode( action.response.responseText );
                Ext.Msg.alert('Failed', res.error.msg);
            }
        });
    },
    update: function() {
        var me = this;
        var tmpWindow = Ext.create('Ext.window.Window', {
            title: 'Edit Event ' + this.record.data.title,
            iconCls: 'icon_editEvent',
            modal: true,
            height: 225,
            width: 350,
            layout: 'fit',
            items: [
                Ext.create('widget.formcreateevent', {
                    record: this.record,
                    mode: 'edit'
                })
            ]
        }).show();
        tmpWindow.on( 'eventCreated', function( data ) {
            tmpWindow.close();
            var store = Ext.getCmp('eventTreePanel').getStore();
            var node = store.getRootNode().findChild( 'eventTypeId', Number( me.record.get('eventTypeId') ), true );
            if ( node ) {
                store.load({
                    node: node
                });
            }
        });
        tmpWindow.on( 'cancel', function( data ) {
            tmpWindow.close();
        });
    }
});
