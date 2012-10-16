Ext.define('BIS.view.ObjectContextMenu', {
    extend: 'Ext.menu.Menu',
    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            
            items: [
                {
                    text: 'Remove Node',
                    identifier: 'remove',
                    scope: me,
                    handler: this.remove
                }
            ]

        });
        me.callParent(arguments);
    },

    remove: function() {
        this.record.remove();
    }
});
