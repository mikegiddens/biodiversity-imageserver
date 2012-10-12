Ext.define('BIS.view.ObjectsTreePanel', {
    extend: 'Ext.tree.Panel',

    id: 'objectsTreePanel',
    store: 'ObjectsTreeStore',
    viewConfig: {
        rootVisible: false
    },
    scope: this,
    listeners: {
        itemdblclick: function( tree, record, el, ind, e, opts ) {
            console.log( 'Setting up form for', record.get('node'), record.get('type'), '.' );
        }
    },
    columns: [
        {
            xtype: 'treecolumn',
            dataIndex: 'text',
            flex: 1,
            text: 'Object'
        }
    ],

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            
        });

        me.callParent(arguments);
    }

});
