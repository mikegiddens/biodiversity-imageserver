Ext.define('BIS.view.SearchFilterPanel', {
    extend: 'Ext.panel.Panel',

    height: 650,
    width: 1250,
    layout: {
        type: 'border'
    },
    title: 'Search Filter',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {

            items: [
                {
                    xtype: 'treepanel',
                    flex: 1,
                    region: 'west',
                    id: 'objectsTreePanel',
                    width: 150,
                    store: 'ObjectsTreeStore',
                    viewConfig: {
                        rootVisible: false
                    },
                    scope: me,
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
                    ]
                },
                {
                    xtype: 'panel',
                    flex: 3,
                    region: 'center',
                    border: false,
                    id: 'borderEastPanel',
                    layout: {
                        type: 'border'
                    },
                    items: [
                        {
                            xtype: 'form',
                            flex: 1,
                            region: 'north',
                            height: 150,
                            id: 'objectFormPanel'
                        },
                        {
                            xtype: 'treepanel',
                            flex: 3,
                            region: 'center',
                            id: 'filterTreePanel',
                            store: 'FilterTreeStore',
                            scope: me,
                            listeners: {
                                afterrender: function( tree, opts ) {
                                    var node = tree.getRootNode();
                                    node.set('object', node.get('logop') );
                                },
                                itemappend: function( thisNode, newNode, index, opts ) {
                                    console.log( thisNode, newNode );
                                    if ( newNode.get('node') == 'group' ) newNode.set('object', newNode.get('logop') );
                                },
                                itemcontextmenu: function( tree, record, item, ind, e ) {
                                    e.stopEvent();
                                    //var ctx = Ext.create('', { record: record });
                                    //ctx.showAt( e.getXY );
                                }
                            },
                            columns: [
                                {
                                    xtype: 'treecolumn',
                                    dataIndex: 'node',
                                    flex: 1,
                                    text: 'Node Type'
                                },
                                {
                                    xtype: 'gridcolumn',
                                    dataIndex: 'object',
                                    flex: 3,
                                    text: 'Object'
                                }
                            ]
                        }
                    ]
                }
            ]
        });

        me.callParent(arguments);
    }

});
