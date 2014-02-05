Ext.define('BIS.view.SearchFilterPanel', {
    extend: 'Ext.panel.Panel',
    requires: [
        'BIS.view.ObjectsFormPanel',
        'BIS.view.FilterTreePanel',
        'BIS.view.FormCreateFilter'
    ],

    layout: 'border',
    title: false,

    initComponent: function() {
        var me = this;
        me.filter = null;

        Ext.applyIf(me, {

            items: [
                {
                    xtype: 'panel',
                    flex: 5,
                    region: 'center',
                    border: false,
                    layout: 'border',
                    items: [
                        Ext.create( 'BIS.view.ObjectsFormPanel', {
                            flex: 1,
                            split: true,
                            region: 'north'
                        }),
                        Ext.create( 'BIS.view.FilterTreePanel', {
                            flex: 3,
                            region: 'center'
                        })
                    ]
                }
            ],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    ui: 'footer',
                    items: [
                        '->',
                        {
                            text: 'Apply Filter',
                            id: 'advFilterSubmitButton',
                            iconCls: 'icon_magnifier',
                            scope: this,
                            handler: this.activateFilter
                        },
                        {
                            text: 'Cancel',
                            scope: this,
                            handler: this.cancel
                        }
                    ]
                },
                {
                    xtype: 'toolbar',
                    dock: 'top',
                    items: [
                        {
                            xtype: 'combo',
                            id: 'advFilterSelect',
                            store: 'SavedFilterStore',
                            displayField: 'name',
                            valueField: 'filter',
                            editable: false,
                            scope: me,
                            listeners: {
                                scope: me,
                                change: function( combo, newVal, oldVal, opts ) {
                                    Ext.getCmp('advFilterUpdateButton').show();
                                    Ext.getCmp('advFilterRemoveButton').show();
                                    me.filter = combo.findRecordByValue( newVal );
                                    Ext.each( Ext.getCmp('objectFormFields').items.items, function( item ) { item.hide() } );
                                    Ext.getCmp('filterToText').update('');
                                    var store = Ext.StoreManager.lookup('FilterTreeStore');
                                    store.setRootNode( JSON.parse(newVal) );
                                    store.getRootNode().expand( true );
                                }
                            }
                        },
                        {
                            text: 'Reset',
                            id: 'advFilterResetButton',
                            iconCls: 'icon_resetFilter',
                            scope: this,
                            handler: this.resetFilter
                        },
                        {
                            text: 'Save as New',
                            id: 'advFilterSaveButton',
                            iconCls: 'icon_addFilter',
                            scope: this,
                            handler: this.saveFilter
                        },
                        {
                            text: 'Update',
                            hidden: true,
                            id: 'advFilterUpdateButton',
                            iconCls: 'icon_saveFilter',
                            scope: this,
                            handler: this.updateFilter
                        },
                        {
                            text: 'Remove',
                            hidden: true,
                            id: 'advFilterRemoveButton',
                            iconCls: 'icon_deleteFilter',
                            scope: this,
                            handler: function() {
                                Ext.Msg.confirm('Remove Filter', 'Are you sure you want remove "' + this.filter.get('name') + '"?', function( btn, nothing, item ) {
                                    if ( btn == 'yes' ) this.removeFilter();
                                }, this);
                            }
                        }
                    ]
                }
            ]
        });

        me.callParent(arguments);
    },

    resetFilter: function() {
        Ext.getCmp('advFilterUpdateButton').hide();
        Ext.getCmp('advFilterRemoveButton').hide();
        Ext.getCmp('advFilterSelect').clearValue();
        Ext.StoreManager.lookup('FilterTreeStore').setRootNode( { node: 'group', logop: 'and', children: [] } )
        Ext.each( Ext.getCmp('objectFormFields').items.items, function( item ) { item.hide() } );
        Ext.getCmp('filterToText').update('');
    },
    updateFilter: function() {
        if ( this.filter ) {
            var me = this;
            Ext.Ajax.request({
                method: 'POST',
                url: Config.baseUrl + 'resources/api/api.php',
                params: {
                    cmd: 'advFilterUpdate',
                    advFilterId: me.filter.get('advFilterId'),
                    name: me.filter.get('name'),
                    description: me.filter.get('description'),
                    filter: JSON.stringify( Ext.getCmp('filterTreePanel').exportFilterGraph() )
                },
                scope: this,
                success: function( resObj ) {
                    var res = Ext.decode( resObj.responseText );
                    if ( res.success ) {
                        Ext.StoreManager.lookup('SavedFilterStore').load();
                    } else {
                        Ext.Msg.alert( 'Failed', 'Unable to update filter. ' + res.error.msg );
                    }
                },
                failure: function( form, action ) {
                    var res = Ext.decode( resObj.responseText );
                    Ext.Msg.alert( 'Failed', 'Unable to update filter. ' + res.error.msg );
                }
            });
        }
    },
    removeFilter: function() {
        if ( this.filter ) {
            var me = this;
            Ext.Ajax.request({
                method: 'POST',
                url: Config.baseUrl + 'resources/api/api.php',
                params: {
                    cmd: 'advFilterDelete',
                    advFilterId: me.filter.get('advFilterId')
                },
                scope: this,
                success: function( resObj ) {
                    var res = Ext.decode( resObj.responseText );
                    if ( res.success ) {
                        Ext.getCmp('advFilterSelect').clearValue();
                        Ext.StoreManager.lookup('SavedFilterStore').load();
                        Ext.StoreManager.lookup('FilterTreeStore').setRootNode( { node: 'group', logop: 'and', children: [] } )
                        Ext.each( Ext.getCmp('objectFormFields').items.items, function( item ) { item.hide() } );
                        Ext.getCmp('filterToText').update('');
                    } else {
                        Ext.Msg.alert( 'Failed', 'Unable to update filter. ' + res.error.msg );
                    }
                },
                failure: function( form, action ) {
                    Ext.Msg.alert( 'Failed', 'Unable to remove filter. ' + res.error.msg );
                }
            });
        }
    },
    saveFilter: function() {
		var tmpWindow = Ext.create('Ext.window.Window', {
			title: 'Save Filter',
			iconCls: 'icon_addFilter',
			modal: true,
			height: 150,
			width: 500,
			layout: 'fit',
			resizable: false,
			bodyBorder: false,
			items: [{
                xtype: 'formcreatefilter',
                filter: JSON.stringify( Ext.getCmp('filterTreePanel').exportFilterGraph() )
            }]
		});
        tmpWindow.on('done', function( data ) {
            Ext.StoreManager.lookup('SavedFilterStore').load();
            tmpWindow.close();
        });
        tmpWindow.on('cancel', function( data ) {
            tmpWindow.close();
        });
        tmpWindow.show();
    },
	activateFilter: function() {
        var me = this;
        if (Ext.getCmp('filterTreePanel').exportFilterGraph().children.length > 0){
            Ext.getCmp('imagesGrid').setAdvancedFilter( Ext.getCmp('filterTreePanel').exportFilterGraph(), function( success ) {
                if ( success ) me.ownerCt.fireEvent('done');
            });
        }else{
            Ext.MessageBox.alert('Status', 'Please select any filter values.');
            Ext.getCmp('id_clearFilter').disabled = false;
        }

    },
    cancel: function() {
        this.ownerCt.fireEvent('cancel');
    }

});
