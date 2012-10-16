Ext.define('BIS.view.FilterContextMenu', {
    extend: 'Ext.menu.Menu',
    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {

            scope: me,
            listeners: {
                click: me.onClick
            },
            
            items: [
                {
                    text: 'Add Group',
                    menu: {
                        xtype: 'menu',
                        scope: me,
                        listeners: {
                            scope: me,
                            click: me.onClick
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
                            click: me.onClick
                        },
                        items: [
                            {
                                text: 'Attribute',
                                identifier: 'condition:attribute'
                            },
                            {
                                text: 'Event',
                                identifier: 'condition:event'
                            },
                            {
                                text: 'Geography',
                                identifier: 'condition:geography'
                            },
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
                    text: 'Remove Node',
                    identifier: 'remove'
                }
            ]

        });
        me.callParent(arguments);
    },

    remove: function() {
        this.record.remove();
    },
    onClick: function( menu, item ) {
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
                                condition: null
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
                                condition: null
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
                                condition: null
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
                                condition: null
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
                                condition: null
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
                                condition: null
                            });
                            this.record.expand();
                            break;
                    }
                    break;
            }
        }
    }
});
