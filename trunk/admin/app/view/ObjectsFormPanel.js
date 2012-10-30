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
                '=': 'is',
                '!=': 'is not',
                '>': 'is after',
                '<': 'is before',
                '>=': 'is after or at',
                '<=': 'is before or at',
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
                        xtype: 'combo',
                        style: 'margin: 10px;',
                        editable: false,
                        hidden: true,
                        scope: me
                    },
                    items: [
                        {
                            id: 'searchFiltercategory',
                            displayField: 'title',
                            valueField: 'categoryId',
                            store: 'CategoriesStore',
                            listeners: {
                                scope: me,
                                change: function( combo, newVal, oldVal, opts ) {
                                    if ( this.filterGraphRecord ) {
                                        var rec = combo.findRecordByValue( newVal );
                                        this.filterGraphRecord.set( 'keyText', rec.get('title') );
                                        this.filterGraphRecord.set( 'key', newVal );
                                        var attrCombo = Ext.getCmp( 'searchFilterattribute' );
                                        attrCombo.clearValue();
                                        attrCombo.getStore().getProxy().extraParams = {};
                                        attrCombo.getStore().getProxy().extraParams.cmd = 'attributeList';
                                        attrCombo.getStore().getProxy().extraParams.showNames = false;
                                        attrCombo.getStore().getProxy().extraParams.order = 'name';
                                        attrCombo.getStore().getProxy().extraParams.categoryId = newVal;
                                        attrCombo.getStore().load();
                                        Ext.getCmp('filterToText').update( this.convertFilterToPlainText() );
                                    }
                                }
                            }
                        },
                        {
                            id: 'searchFiltereventType',
                            displayField: 'title',
                            valueField: 'eventTypeId',
                            store: 'EventTypesStore',
                            listeners: {
                                scope: me,
                                change: function( combo, newVal, oldVal, opts ) {
                                    if ( this.filterGraphRecord ) {
                                        var rec = combo.findRecordByValue( newVal );
                                        this.filterGraphRecord.set( 'keyText', rec.get('title') );
                                        this.filterGraphRecord.set( 'key', newVal );
                                        var evCombo = Ext.getCmp( 'searchFilterevent' );
                                        evCombo.clearValue();
                                        evCombo.getStore().getProxy().extraParams = {};
                                        evCombo.getStore().getProxy().extraParams.cmd = 'eventList';
                                        evCombo.getStore().getProxy().extraParams.order = 'title';
                                        evCombo.getStore().getProxy().extraParams.eventTypeId = newVal;
                                        evCombo.getStore().load();
                                        Ext.getCmp('filterToText').update( this.convertFilterToPlainText() );
                                    }
                                }
                            }
                        },
                        {
                            id: 'searchFiltergeography',
                            displayField: 'country',
                            valueField: 'geographyId',
                            store: 'GeographyStore',
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
                            id: 'searchFiltercollection',
                            displayField: 'name',
                            valueField: 'collectionId',
                            store: 'CollectionsStore',
                            listeners: {
                                scope: me,
                                change: function( combo, newVal, oldVal, opts ) {
                                    if ( this.filterGraphRecord ) {
                                        var rec = combo.findRecordByValue( newVal );
                                        this.filterGraphRecord.set( 'keyText', rec.get('name') );
                                        this.filterGraphRecord.set( 'key', newVal );
                                        this.filterGraphRecord.set( 'valueText', rec.get('code') );
                                        this.filterGraphRecord.set( 'value', rec.get('code') );
                                        Ext.getCmp('filterToText').update( this.convertFilterToPlainText() );
                                    }
                                }
                            }
                        },
                        {
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
                            id: 'searchFilterclientStation',
                            displayField: 'title',
                            valueField: 'remoteAccessKeyId',
                            store: 'ClientStationsStore',
                            listeners: {
                                scope: me,
                                change: function( combo, newVal, oldVal, opts ) {
                                    if ( this.filterGraphRecord ) {
                                        var rec = combo.findRecordByValue( newVal );
                                        this.filterGraphRecord.set( 'keyText', rec.get('originalIp') );
                                        this.filterGraphRecord.set( 'key', rec.get('title') );
                                        this.filterGraphRecord.set( 'valueText', rec.get('title') );
                                        this.filterGraphRecord.set( 'value', rec.get('key') );
                                        Ext.getCmp('filterToText').update( this.convertFilterToPlainText() );
                                    }
                                }
                            }
                        },
                        {
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
                            id: 'searchFilterattribute',
                            displayField: 'name',
                            valueField: 'attributeId',
                            store: 'AttributesStore',
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
                            id: 'searchFilterevent',
                            displayField: 'title',
                            valueField: 'eventId',
                            store: 'EventsStore',
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
                            id: 'searchFilterText',
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
                            id: 'searchFilterDate1',
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
                            id: 'searchFilterDate2',
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
        var textCondition = this.conditionTranslations[ record.get('condition') ] || 'is';
        switch ( record.get('object') ) {
            case 'attribute':
                if ( record.get('key') ) {
                    if ( record.get('value') ) {
                        return 'Image has an attribute that ' + textCondition + ' <span style="font-weight:bold">' + record.get('valueText') + '</span> in category "' + record.get('keyText') + '".';
                    } else {
                        return 'Image has any attribute that ' + textCondition + ' in category "' + record.get('keyText') + '".';
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
                        return 'Image has any event that ' + textCondition + ' of type "' + record.get('keyText') + '".';
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
                break;
            case 'collection':
                return 'Collection ' + textCondition + ' ' + record.get('valueText') + '.';
            case 'clientStation':
                return 'Client station ' + textCondition + ' ' + record.get('valueText') + '.';
        }
    }

});
