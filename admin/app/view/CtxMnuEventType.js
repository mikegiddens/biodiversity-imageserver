Ext.define('BIS.view.CtxMnuEventType', {
    extend: 'Ext.menu.Menu',
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'do':
                    Ext.Msg.prompt('Do Something', 'This menu allows you to do something.', function( btnTextClicked, val, promptCmp ) {
                        alert(val);
                    });
                    break;
                case 'hi':
                    alert('hola!');
                    break;
            }
        }
    },
    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    text: 'Do something',
                    identifier: 'do'
                },
                {
                    text: 'Diga hola',
                    identifier: 'hi'
                }
            ]
        });
        me.callParent(arguments);
    }
});
