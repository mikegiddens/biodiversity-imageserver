Ext.define('BIS.view.FilterTreePanel', {
    extend: 'Ext.tree.Panel',

    requires: [ 'BIS.view.FilterContextMenu', 'BIS.view.ObjectContextMenu' ],

    id: 'filterTreePanel',
    store: 'FilterTreeStore',
    border: false,
    scope: this,
    listeners: {
        afterrender: function( tree, opts ) {
            var node = tree.getRootNode();
            node.set('object', node.get('logop') );
        },
        itemappend: function( thisNode, newNode, index, opts ) {
            if ( newNode.get('node') == 'group' ) newNode.set('object', newNode.get('logop') );
            this.getSelectionModel().select( newNode );
            this.fireEvent( 'itemclick', this, newNode );
        },
        itemcontextmenu: function( tree, record, item, ind, e ) {
            e.stopEvent();
            if ( record.get('node') == 'group' ) {
                var ctx = Ext.create( 'BIS.view.FilterContextMenu', { record: record } );
                ctx.showAt( e.getXY() );
            }
            if ( record.get('node') == 'condition' ) {
                var ctx = Ext.create( 'BIS.view.ObjectContextMenu', { record: record } );
                ctx.showAt( e.getXY() );
            }
        },
        itemclick: function( tree, record, clickedItem, ind, e ) {
            var objectsFormPanel = Ext.getCmp( 'objectsFormPanel' );
            var conditionCombo = Ext.getCmp( 'searchFilterCondition' );
            Ext.getCmp('filterToText').update('');
            Ext.getCmp('searchFilterText').setValue('');
            objectsFormPanel.conditionalComponent = null;
            objectsFormPanel.filterGraphRecord = null;
            Ext.each( Ext.getCmp('objectFormFields').items.items, function( item ) { item.hide() } );

            if ( record.get('node') == 'condition' ) {
                switch ( record.get('object') ) {
                    case 'attribute':
                        Ext.getCmp( 'searchFiltercategory' ).setValue( record.get('key') ).show();
                        objectsFormPanel.conditionalComponent = Ext.getCmp( 'searchFilterattribute' );
                        objectsFormPanel.conditionalComponent.setValue( record.get('value') ).show();
                        conditionCombo.getStore().clearFilter( true );
                        conditionCombo.getStore().filterBy( function( record, id ) {
                            return record.get('type') != 'time';
                        });
                        break;
                    case 'event':
                        Ext.getCmp( 'searchFiltereventType' ).setValue( record.get('key') ).show();
                        objectsFormPanel.conditionalComponent = Ext.getCmp( 'searchFilterevent' );
                        objectsFormPanel.conditionalComponent.setValue( record.get('value') ).show();
                        conditionCombo.getStore().clearFilter( true );
                        conditionCombo.getStore().filterBy( function( record, id ) {
                            return record.get('type') != 'time';
                        });
                        break;
                    case 'geography':
                        Ext.getCmp( 'searchFiltergeography' ).setValue( record.get('key') ).show();
                        Ext.getCmp( 'searchFilterText' ).setValue( record.get('value') ).show();
                        conditionCombo.getStore().clearFilter( true );
                        conditionCombo.getStore().filterBy( function( record, id ) {
                            return record.get('type') != 'time';
                        });
                        break;
                    case 'collection':
                        Ext.getCmp( 'searchFiltercollection' ).setValue( record.get('key') ).show();
                        Ext.getCmp( 'searchFilterText' ).setValue( record.get('value') ).show();
                        objectsFormPanel.conditionalComponent = Ext.getCmp( 'searchFiltercollection' );
                        conditionCombo.getStore().clearFilter( true );
                        conditionCombo.getStore().filterBy( function( record, id ) {
                            return record.get('type') != 'time';
                        });
                        break;
                    case 'time':
                        Ext.getCmp( 'searchFilterDateType' ).setValue( record.get('key') ).show();
                        Ext.getCmp( 'searchFilterDate1' ).setValue( record.get('value') ).show();
                        Ext.getCmp( 'searchFilterDate2' ).setValue( record.get('value2') );
                        conditionCombo.getStore().clearFilter( true );
                        conditionCombo.getStore().filterBy( function( record, id ) {
                            return record.get('type') != 'str';
                        });
                        break;
                    case 'clientStation':
                        Ext.getCmp( 'searchFilterclientStation' ).setValue( record.get('key') ).show();
                        Ext.getCmp( 'searchFilterText' ).setValue( record.get('value') ).show();
                        objectsFormPanel.conditionalComponent = Ext.getCmp( 'searchFilterclientStation' );
                        conditionCombo.getStore().clearFilter( true );
                        conditionCombo.getStore().filterBy( function( record, id ) {
                            return record.get('type') != 'time';
                        });
                        break;
                }
                conditionCombo.setValue( record.get('condition') );
                if ( record.get('condition') != null && typeof record.get('condition') != 'undefined' && record.get('condition') != '=' && record.get('condition') != '!=' && record.get('object') != 'time' ) {
                    Ext.getCmp( 'searchFilterText' ).setValue( record.get('value') ).show();
                    objectsFormPanel.conditionalComponent.hide();
                } else {
                    Ext.getCmp( 'searchFilterText' ).hide();
                }
                if ( record.get('object') != 'time' ) {
                    Ext.getCmp( 'searchFilterDateType' ).hide();
                }
                if ( objectsFormPanel.conditionalComponent || record.get('object') == 'time' || record.get('object') == 'collection' ) {
                    conditionCombo.show();
                }
                objectsFormPanel.filterGraphRecord = record;
                objectsFormPanel.convertFilterToPlainText();
            }
        }
    },
    columns: [
        {
            xtype: 'treecolumn',
            dataIndex: 'object',
            flex: 3,
            text: 'Node'
        },
        {
            xtype: 'gridcolumn',
            dataIndex: 'keyText',
            flex: 2,
            text: 'Key'
        },
        {
            xtype: 'gridcolumn',
            dataIndex: 'condition',
            flex: 2,
            text: 'Condition'
        },
        {
            xtype: 'gridcolumn',
            dataIndex: 'valueText',
            flex: 2,
            text: 'Value'
        },
        {
            xtype: 'gridcolumn',
            dataIndex: 'value2Text',
            flex: 2,
            text: 'Value 2'
        }
    ],

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            
        });

        me.callParent(arguments);
    },

    exportFilterGraph: function() {
        // set up root
        var rootNode = this.getStore().getRootNode();
        root = {
            node: 'group',
            logop: rootNode.get('logop'),
            children: []
        }
        var walk = function( node, parentNode ) {
            // convert self
            var convertedNode;
            if ( node.get('node') == 'group' ) {
                convertedNode = {
                    node: 'group',
                    logop: node.get('logop'),
                    children: []
                }
            }
            if ( node.get('node') == 'condition' ) {
                convertedNode = {
                    node: 'condition',
                    object: node.get('object'),
                    key: node.get('key'),
                    keyText: node.get('keyText'),
                    value: node.get('value'),
                    valueText: node.get('valueText'),
                    value2: node.get('value2'),
                    value2Text: node.get('value2Text'),
                    condition: node.get('condition')
                }
            }
            // push on parent
            if ( node && parentNode ) parentNode.children.push( convertedNode );
            // call recursively
            Ext.each( node.childNodes, function( child ) {
                walk( child, convertedNode );
            });
        }
        Ext.each( rootNode.childNodes, function( child ) {
            walk( child, root );
        });
        return root;
    }

});
