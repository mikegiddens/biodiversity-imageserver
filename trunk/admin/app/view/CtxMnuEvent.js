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
    remove: function() {
        var cmd = 'deleteEvent'
            params = { eventTypeId: this.record.eventTypeId }
    },
    update: function() {
        var me = this;
        var tmpWindow = Ext.create('Ext.window.Window', {
            title: 'Edit Event ' + this.record.data.title,
            iconCls: 'icon_editEvent',
            modal: true,
            height: 100,
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
            Ext.getCmp('eventTreePanel').getStore().load();
        });
    }
});
