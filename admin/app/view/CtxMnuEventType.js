Ext.define('BIS.view.CtxMnuEventType', {
    extend: 'Ext.menu.Menu',
    requires: ['BIS.view.FormCreateEventType'],
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'add':
                    this.addEvent();
                    break;
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
                    text: 'Add Event',
                    iconCls: 'icon_newEvent',
                    identifier: 'add'
                },
                '-',
                {
                    text: 'Edit Event Type',
                    iconCls: 'icon_editEventType',
                    identifier: 'update'
                },
                {
                    text: 'Remove Event Type',
                    iconCls: 'icon_removeEventType',
                    identifier: 'delete'
                }
            ]
        });
        me.callParent(arguments);
    },
    addEvent: function() {
        var tmpWindow = Ext.create('Ext.window.Window', {
            title: 'Create New Event',
            iconCls: 'icon_newEvent',
            modal: true,
            height: 225,
            width: 350,
            layout: 'fit',
            items: [
                Ext.create('widget.formcreateevent', {
                    record: this.record,
                    border: false,
                    mode: 'add'
                })
            ]
        }).show();
        tmpWindow.on('eventAdded',function(data){
            tmpWindow.close();
            Ext.getCmp('eventTreePanel').getStore().load();
        });
        tmpWindow.on('cancel',function(data){
            tmpWindow.close();
        });
    },
    remove: function() {
        Ext.Ajax.request({
            method: 'POST',
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'eventTypeDelete',
                eventTypeId: this.record.data.eventTypeId
            },
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                if ( res.success ) {
                    Ext.getCmp('eventTreePanel').getStore().load();
                }
            },
            failure: function( form, action ) {
                Ext.Msg.alert('Failed', 'Request failed.');
            }
        });
    },
    update: function() {
        var tmpWindow = Ext.create('Ext.window.Window', {
            title: 'Edit Event Type ' + this.record.data.title,
            iconCls: 'icon_editEventType',
            modal: true,
            height: 150,
            width: 250,
            layout: 'fit',
            items: [
                Ext.create('widget.formcreateeventtype', {
                    record: this.record,
                    border: false,
                    mode: 'edit'
                })
            ]
        }).show();
        tmpWindow.on('eventTypeAdded',function(data){
            tmpWindow.close();
            Ext.getCmp('eventTreePanel').getStore().load();
        });
        tmpWindow.on( 'cancel', function( data ) {
            tmpWindow.close();
        });
    }
});

