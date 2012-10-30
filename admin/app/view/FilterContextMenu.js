Ext.define('BIS.view.FilterContextMenu', {
    extend: 'Ext.menu.Menu',
    
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch ( item.identifier ) {
                case 'toggleGroup':
                    this.toggleGroup();
                    break;
                case 'remove':
                    this.remove();
                    break;
            }
        }
    },
    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {

            items: [
                {
                    text: 'Add Group',
                    menu: {
                        xtype: 'menu',
                        scope: me,
                        listeners: {
                            scope: me,
                            click: me.handleClick
                        },
                        items: [
                            {
                                text: 'and',
                                identifier: 'group:and'
                            },
                            {
                                text: 'or',
                                identifier: 'group:or'
                            }
                        ]
                    }
                },
                {
                    text: 'Add Condition',
                    menu: {
                        xtype: 'menu',
                        scope: me,
                        listeners: {
                            scope: me,
                            click: me.handleClick
                        },
                        items: [
                            {
                                text: 'Attribute',
                                identifier: 'condition:attribute'
                            },
                            {
                                text: 'Event',
                                identifier: 'condition:event'
                            },/*
                            {
                                text: 'Geography',
                                identifier: 'condition:geography'
                            },*/
                            {
                                text: 'Collection',
                                identifier: 'condition:collection'
                            },
                            {
                                text: 'Time',
                                identifier: 'condition:time'
                            },
                            {
                                text: 'Client Station',
                                identifier: 'condition:clientStation'
                            }
                        ]
                    }
                },
                '-',
                {
                    text: 'Switch to "' + ((me.record.get('object') == 'and') ? 'or' : 'and') + '"',
                    identifier: 'toggleGroup'
                },
                {
                    text: 'Remove Node',
                    identifier: 'remove',
                    disabled: Ext.getCmp('filterTreePanel').getStore().getRootNode() == me.record
                }
            ]

        });
        me.callParent(arguments);
    },

    remove: function() {
        Ext.each( Ext.getCmp('objectFormFields').items.items, function( item ) { item.hide() } );
        Ext.getCmp('filterToText').update('');
        this.record.remove();
    },
    toggleGroup: function() {
        this.record.set('object', (this.record.get('object') == 'and') ? 'or' : 'and');
    },
    handleClick: function( menu, item ) {
        if ( item.identifier ) {
            var identifier = item.identifier.split( ':' );
            var filterGraph = Ext.getCmp('filterTreePanel').getStore();
            switch ( identifier[0] ) {
                case 'remove':
                    this.remove();
                    break;
                case 'group':
                    switch ( identifier[1] ) {
                        case 'and':
                            this.record.appendChild({
                                node: 'group',
                                logop: 'and',
                                children: []
                            });
                            this.record.expand();
                            break;
                        case 'or':
                            this.record.appendChild({
                                node: 'group',
                                logop: 'or',
                                children: []
                            });
                            this.record.expand();
                            break;
                        break;
                    }
                    break;
                case 'condition':
                    switch ( identifier[1] ) {
                        case 'attribute':
                            this.record.appendChild({
                                node: 'condition',
                                object: 'attribute',
                                key: null,
                                value: null,
                                value2: null,
                                condition: null,
                                leaf: true
                            });
                            this.record.expand();
                            break;
                        case 'event':
                            this.record.appendChild({
                                node: 'condition',
                                object: 'event',
                                key: null,
                                value: null,
                                value2: null,
                                condition: null,
                                leaf: true
                            });
                            this.record.expand();
                            break;
                        case 'geography':
                            this.record.appendChild({
                                node: 'condition',
                                object: 'geography',
                                key: null,
                                value: null,
                                value2: null,
                                condition: null,
                                leaf: true
                            });
                            this.record.expand();
                            break;
                        case 'collection':
                            this.record.appendChild({
                                node: 'condition',
                                object: 'collection',
                                key: null,
                                value: null,
                                value2: null,
                                condition: null,
                                leaf: true
                            });
                            this.record.expand();
                            break;
                        case 'time':
                            this.record.appendChild({
                                node: 'condition',
                                object: 'time',
                                key: null,
                                value: null,
                                value2: null,
                                condition: null,
                                leaf: true
                            });
                            this.record.expand();
                            break;
                        case 'clientStation':
                            this.record.appendChild({
                                node: 'condition',
                                object: 'clientStation',
                                key: null,
                                value: null,
                                value2: null,
                                condition: null,
                                leaf: true
                            });
                            this.record.expand();
                            break;
                    }
                    break;
            }
        }
    }
});
