Ext.define('BIS.view.ObjectsFormPanel', {
    extend: 'Ext.form.Panel',

    id: 'objectsFormPanel',
    padding: 10,
    border: false,
    style: 'background-color:#fff',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {

            conditionalComponent: null,
            filterGraphRecord: null,

            conditionTranslations: {
                '=': 'is equal to',
                '!=': 'is not equal to',
                '>': 'is greater than',
                '<': 'is less than',
                '>=': 'is greater than or equal to',
                '<=': 'is less than or equal to',
                'between': 'is between ',
                'is': 'is',
                '%s': 'ends with',
                's%': 'starts with',
                '%s%': 'contains',
                'in': 'is in',
                'matches (regex)': 'matches regex pattern'
            },

            items: [

                {
                    xtype: 'panel',
                    id: 'objectFormFields',
                    border: false,
                    layout: 'hbox',
                    defaults: {
                        style: 'margin: 10px;',
                    },
                    items: [
                        {
                            xtype: 'combo',
                            hidden: true,
                            id: 'searchFiltercategory',
                            displayField: 'title',
                            valueField: 'categoryId',
                            store: 'CategoriesStore',
                            scope: me,
                            listeners: {
                                scope: me,
                                change: function( combo, newVal, oldVal, opts ) {
                                    if ( this.filterGraphRecord ) {
                                        var rec = combo.findRecordByValue( newVal );
                                        this.filterGraphRecord.set( 'keyText', rec.get('title') );
                                        this.filterGraphRecord.set( 'key', newVal );
                                        var attrCombo = Ext.getCmp( 'searchFilterattribute' );
                                        attrCombo.clearValue();
                                        attrCombo.getStore().getProxy.extraParams = { cmd: 'attributeList', showNames: false, order: 'title', categoryId: newVal };
                                        attrCombo.getStore().load();
                                        Ext.getCmp('filterToText').update( this.convertFilterToPlainText() );
                                    }
                                }
                            }
                        },
                        {
                            xtype: 'combo',
                            hidden: true,
                            id: 'searchFiltereventType',
                            displayField: 'title',
                            valueField: 'eventTypeId',
                            store: 'EventTypesStore',
                            scope: me,
                            listeners: {
                                scope: me,
                                change: function( combo, newVal, oldVal, opts ) {
                                    if ( this.filterGraphRecord ) {
                                        var rec = combo.findRecordByValue( newVal );
                                        this.filterGraphRecord.set( 'keyText', rec.get('title') );
                                        this.filterGraphRecord.set( 'key', newVal );
                                        var evCombo = Ext.getCmp( 'searchFilterevent' );
                                        evCombo.clearValue();
                                        evCombo.getStore().getProxy().extraParams = { cmd: 'attributeList', order: 'title', showNames: false, eventTypeId: newVal };
                                        evCombo.getStore().load();
                                        Ext.getCmp('filterToText').update( this.convertFilterToPlainText() );
                                    }
                                }
                            }
                        },
                        {
                            xtype: 'combo',
                            hidden: true,
                            id: 'searchFiltergeography',
                            displayField: 'country',
                            valueField: 'geographyId',
                            store: 'GeographyStore',
                            scope: me,
                            listeners: {
                                scope: me,
                                change: function( combo, newVal, oldVal, opts ) {
                                    if ( this.filterGraphRecord ) {
                                        var rec = combo.findRecordByValue( newVal );
                                        this.filterGraphRecord.set( 'keyText', rec.get('country') );
                                        this.filterGraphRecord.set( 'key', newVal );
                                        Ext.getCmp('filterToText').update( this.convertFilterToPlainText() );
                                    }
                                }
                            }
                        },
                        {
                            xtype: 'combo',
                            hidden: true,
                            id: 'searchFiltercollection',
                            displayField: 'name',
                            valueField: 'collectionId',
                            store: 'CollectionsStore',
                            scope: me,
                            listeners: {
                                scope: me,
                                change: function( combo, newVal, oldVal, opts ) {
                                    if ( this.filterGraphRecord ) {
                                        var rec = combo.findRecordByValue( newVal );
                                        this.filterGraphRecord.set( 'keyText', rec.get('name') );
                                        this.filterGraphRecord.set( 'key', newVal );
                                        Ext.getCmp('filterToText').update( this.convertFilterToPlainText() );
                                    }
                                }
                            }
                        },
                        {
                            xtype: 'combo',
                            hidden: true,
                            id: 'searchFilterDateType',
                            queryMode: 'local',
                            displayField: 'value',
                            valueField: 'value',
                            store: Ext.create( 'Ext.data.Store', {
                                fields: [ 'key', 'value' ],
                                data: [
                                    { key: 'Added', value: 'added' },
                                    { key: 'Modified', value: 'modified' }
                                ]
                            }),
                            scope: me,
                            listeners: {
                                scope: me,
                                change: function( combo, newVal, oldVal, opts ) {
                                    if ( this.filterGraphRecord ) {
                                        this.filterGraphRecord.set( 'key', newVal );
                                        this.filterGraphRecord.set( 'keyText', newVal );
                                        Ext.getCmp('filterToText').update( this.convertFilterToPlainText() );
                                    }
                                }
                            }
                        },
                        {
                            xtype: 'combo',
                            hidden: true,
                            id: 'searchFilterCondition',
                            queryMode: 'local',
                            displayField: 'value',
                            valueField: 'value',
                            store: Ext.create( 'Ext.data.Store', {
                                fields: [ 'type', 'value' ],
                                data: [
                                    { type: 'ex', value: '=' },
                                    { type: 'ex', value: '!=' },
                                    { type: 'time', value: '>' },
                                    { type: 'time', value: '<' },
                                    { type: 'time', value: '>=' },
                                    { type: 'time', value: '<=' },
                                    { type: 'time', value: 'between' },
                                    { type: 'str', value: 'is' },
                                    { type: 'str', value: '%s' },
                                    { type: 'str', value: 's%' },
                                    { type: 'str', value: '%s%' },
                                    { type: 'str', value: 'in' },
                                    { type: 'str', value: 'matches (regex)' }
                                ]
                            }),
                            scope: me,
                            listeners: {
                                scope: me,
                                change: function( combo, newVal, oldVal, opts ) {
                                    if ( this.filterGraphRecord ) {
                                        this.filterGraphRecord.set( 'condition', newVal );
                                        // toggle exact string match (existing values) to user-input value
                                        if ( newVal != '=' && newVal != '!=' && this.filterGraphRecord.get('object') != 'time' ) {
                                            if ( this.conditionalComponent ) {
                                                this.conditionalComponent.hide();
                                                Ext.getCmp( 'searchFilterText' ).show();
                                            }
                                        } else {
                                            if ( this.conditionalComponent ) {
                                                this.conditionalComponent.show();
                                                Ext.getCmp( 'searchFilterText' ).hide();
                                            }
                                        }
                                        // toggle single date to date range
                                        if ( newVal == 'between' && this.filterGraphRecord.get('object') == 'time' ) {
                                            Ext.getCmp( 'searchFilterDate2' ).show();
                                        }
                                        if ( newVal != 'between' && this.filterGraphRecord.get('object') == 'time' ) {
                                            this.filterGraphRecord.set( 'value2', null );
                                            this.filterGraphRecord.set( 'value2Text', null );
                                            Ext.getCmp( 'searchFilterDate2' ).hide();
                                        }
                                        Ext.getCmp('filterToText').update( this.convertFilterToPlainText() );
                                    }
                                }
                            }
                        },
                        {
                            xtype: 'combo',
                            hidden: true,
                            id: 'searchFilterattribute',
                            displayField: 'name',
                            valueField: 'attributeId',
                            store: 'AttributesStore',
                            scope: me,
                            listeners: {
                                scope: me,
                                change: function( combo, newVal, oldVal, opts ) {
                                    if ( this.filterGraphRecord ) {
                                        var rec = combo.findRecordByValue( newVal );
                                        if ( rec ) {
                                            this.filterGraphRecord.set( 'valueText', rec.get('name') );
                                            this.filterGraphRecord.set( 'value', newVal );
                                            Ext.getCmp('filterToText').update( this.convertFilterToPlainText() );
                                        }
                                    }
                                }
                            }
                        },
                        {
                            xtype: 'combo',
                            hidden: true,
                            id: 'searchFilterevent',
                            displayField: 'title',
                            valueField: 'eventId',
                            store: 'EventsStore',
                            scope: me,
                            listeners: {
                                scope: me,
                                change: function( combo, newVal, oldVal, opts ) {
                                    if ( this.filterGraphRecord ) {
                                        var rec = combo.findRecordByValue( newVal );
                                        this.filterGraphRecord.set( 'valueText', rec.get('title') );
                                        this.filterGraphRecord.set( 'value', newVal );
                                        Ext.getCmp('filterToText').update( this.convertFilterToPlainText() );
                                    }
                                }
                            }
                        },
                        {
                            xtype: 'textfield',
                            hidden: true,
                            id: 'searchFilterText',
                            scope: me,
                            listeners: {
                                scope: me,
                                change: function( field, e, opts ) {
                                    if ( this.filterGraphRecord ) {
                                        this.filterGraphRecord.set( 'valueText', field.getValue() );
                                        this.filterGraphRecord.set( 'value', field.getValue() );
                                        Ext.getCmp('filterToText').update( this.convertFilterToPlainText() );
                                    }
                                }
                            }
                        },
                        {
                            xtype: 'datefield',
                            hidden: true,
                            id: 'searchFilterDate1',
                            scope: me,
                            listeners: {
                                scope: me,
                                change: function( dtInput, val, e, opts ) {
                                    val = Ext.Date.format(new Date(val), 'Y-m-j');
                                    if ( this.filterGraphRecord ) {
                                        this.filterGraphRecord.set( 'valueText', val );
                                        this.filterGraphRecord.set( 'value', val );
                                        Ext.getCmp('filterToText').update( this.convertFilterToPlainText() );
                                    }
                                }
                            }
                        },
                        {
                            xtype: 'datefield',
                            hidden: true,
                            id: 'searchFilterDate2',
                            scope: me,
                            listeners: {
                                scope: me,
                                change: function( dtInput, val, e, opts ) {
                                    val = Ext.Date.format(new Date(val), 'Y-m-j');
                                    if ( this.filterGraphRecord ) {
                                        this.filterGraphRecord.set( 'value2Text', val );
                                        this.filterGraphRecord.set( 'value2', val );
                                        Ext.getCmp('filterToText').update( this.convertFilterToPlainText() );
                                    }
                                }
                            }
                        }
                    ]
                },
                {
                    xtype: 'component',
                    id: 'filterToText'
                }
            ]

        });

        me.callParent(arguments);
    },

    convertFilterToPlainText: function() {
        var record = this.filterGraphRecord;
        var textCondition = this.conditionTranslations[ record.get('condition') ];
        switch ( record.get('object') ) {
            case 'attribute':
                if ( record.get('key') ) {
                    if ( record.get('value') ) {
                        return 'Image has an attribute that ' + textCondition + ' <span style="font-weight:bold">' + record.get('valueText') + '</span> in category "' + record.get('keyText') + '".';
                    } else {
                        return 'Image has any attribute in category "' + record.get('keyText') + '".';
                    }
                } else {
                    return 'Image has an attribute that ' + textCondition + ' <span style="font-weight:bold">' + record.get('valueText') + '</span> in any category.';
                }
                break;
            case 'event':
                if ( record.get('key') ) {
                    if ( record.get('value') ) {
                        return 'Image has an event that ' + textCondition + ' <span style="font-weight:bold">' + record.get('valueText') + '</span> of type "' + record.get('keyText') + '".';
                    } else {
                        return 'Image has any event of type "' + record.get('keyText') + '".';
                    }
                } else {
                    return 'Image has an event that ' + textCondition + ' <span style="font-weight:bold">' + record.get('valueText') + '</span> of any type.';
                }
                break;
            case 'time':
                if ( record.get('value2') ) {
                    return 'Image\'s time ' + record.get('keyText') + ' ' + textCondition + ' ' + record.get('valueText') + ' and ' + record.get('value2Text') + '.';
                } else {
                    return 'Image\'s time ' + record.get('keyText') + ' ' + textCondition + ' ' + record.get('valueText') + '.';
                }
        }
    }

});
