Ext.define('BIS.view.ImagesPanel', {
	extend: 'Ext.grid.Panel',
	alias: ['widget.imagespanel'],
	requires: [
		'BIS.view.ImagesGridView',
		'Ext.ux.form.SearchField',
        'BIS.view.SearchFilterPanel'
	],
	id: 'imagesGrid',
	autoScroll: true,
    bodyStyle: 'overflow-x:hidden ! important;',
	store: 'ImagesStore',
    viewType: 'imagesgridview',
    viewConfig :{
        emptyText: '<span class="noImages">No images found. Drag and drop one or more onto this panel to add new ones.</span>'
    },
    beginLayout: Ext.emptyFn,
	initComponent: function() {

		var me = this;
        console.log(me);
        var advancedFilter = null;
		Ext.applyIf(me, {
			columns: [{
				xtype: 'gridcolumn',
				dataIndex: 'imageId',
				text: 'Identifier'
			},{
				xtype: 'gridcolumn',
				dataIndex: 'barcode',
				text: 'Barcode'
			},{
				xtype: 'gridcolumn',
				dataIndex: 'filename',
				text: 'Filename'
			},{
				xtype: 'gridcolumn',
				dataIndex: 'path',
				text: 'File Path'
			},{
				xtype: 'datecolumn',
				dataIndex: 'timestampAdded',
				text: 'Date Added'
            },{
				xtype: 'datecolumn',
				dataIndex: 'timestampModified',
				text: 'Last Modified'
			}],
			dockedItems: [{
				xtype: 'pagingtoolbar',
                id: 'imagesPager',
				displayInfo: true,
				store: 'ImagesStore',
				displayMsg: 'Displaying {0} - {1} of {2}',
				dock: 'bottom'
			},{
				xtype: 'toolbar',
				id: 'imagesToolbar',
				dock: 'top',
				items: [{
					xtype: 'button',
					text: 'Clear Filter',
                    id:'id_clearFilter',
                    disabled: true,
					iconCls: 'icon_cancel',
					scope: this,
					handler: this.clearFilter
				},{
                    xtype: 'tbseparator'
                },{
                    xtype: 'button',
                    text: 'Advanced Filter',
                    iconCls: 'icon_magnifier',
                    scope: this,
                    handler: this.toggleAdvancedFilter
                },{
					xtype: 'tbseparator'
				},{
					xtype: 'cycle',
					showText: true,
					prependText: 'View ',
					scope: this,
					changeHandler: this.changeView,
					menu: {
						items: [{
							text: 'Both',
							iconCls: 'icon_viewBoth',
							type: 'both'
						},{
							text: 'Small',
							iconCls: 'icon_viewSmall',
							type: 'small'
						},{
							text: 'Large',
							iconCls: 'icon_viewLarge',
							type: 'tile'
						},{
							text: 'Details',
							iconCls: 'icon_viewList',
							disabled: true,
							type: 'details'
						}]
					}
				},
			    '->',
				{
					xtype: 'searchfield',
					name: 'searchval',
					emptyText: 'Search Images',
					handlerCmp: this,
					width: 200,
					scope: this
				}]
			}]
		});

		me.callParent(arguments);
	},
    toggleAdvancedFilter: function( btn, e ) {
        var me = this;
        if ( !this.advancedFilter ) {
            this.advancedFilter = Ext.create('Ext.window.Window', {
                title: 'Advanced Filter',
                iconCls: 'icon_magnifier',
                modal: true,
                height: 400,
                width: 650, // this is wide enough to fit all the combo fields
                layout: 'fit',
                resizable: false,
                bodyBorder: false,
                closeAction: 'hide',
                items: [
                    Ext.create( 'BIS.view.SearchFilterPanel' )
                ]
            });
            this.advancedFilter.on('done', function( data ) {
                Ext.getCmp('id_clearFilter').enable();
                Ext.getCmp('id_clearFilter').disabled = false;
                me.advancedFilter.hide();
            });
            this.advancedFilter.on('cancel', function( data ) {
                me.advancedFilter.hide();
            });

        }
        var store = Ext.StoreManager.lookup('FilterTreeStore');
        Ext.getCmp('filterTreePanel').getRootNode({})
        Ext.each( appendChildTreeFilter, function( item ) {
            Ext.getCmp('filterTreePanel').getRootNode().appendChild(item.children);
        });
        appendChildTreeFilter = [];
        store.getRootNode().expand( true );
        this.advancedFilter.show(); 
    },
	setFilter: function( params, reset ) {
		if ( reset ) this.getStore().clearFilter();
		var parsedParams = [];
		for ( var p in params ) {
			parsedParams.push({property: p, value: params[p]});
		}
		this.getStore().filter( parsedParams );
        this.getStore().loadPage(1);
	},
    setAdvancedFilter: function( filterGraph, callback ) {
        this.getStore().getProxy().extraParams = {
            cmd: 'imageList',
            advFilter: JSON.stringify( filterGraph )
        }
        this.getStore().load({
            callback: function( success, res ) {
                if ( res.success ) {
                    if ( callback ) callback( true );
                } else {
                    if ( callback ) callback( false );
                    alert( 'There was a problem filtering images. ' + res.error.msg );
                }
            }
        });
     //   this.getStore().loadPage(1);
    },
	clearFilter: function() {
        this.getStore().getProxy().extraParams = {};
        this.getStore().getProxy().extraParams = {
            cmd: 'imageList'
        }
        Ext.getCmp('id_clearFilter').disable();

        // reset Filters
        Ext.getCmp('advFilterUpdateButton').hide();
        Ext.getCmp('advFilterRemoveButton').hide();
        Ext.getCmp('advFilterSelect').clearValue();
        Ext.StoreManager.lookup('FilterTreeStore').setRootNode( { node: 'group', logop: 'and', children: [] } );
        Ext.each( Ext.getCmp('objectFormFields').items.items, function( item ) { item.hide() } );
        Ext.getCmp('filterToText').update('');
       appendChildTreeFilter = [];
       testingFilter = [];
        this.getStore().loadPage(1);
	},
	changeView: function( cycleBtn, item ) {
		if ( item.type != 'details' ) {
			this.getView().setTpl( item.type );
		}
	},
    // this is called by searchfield (plugin was hacked)
	search: function( val ) {
        this.getStore().getProxy().extraParams = {
            cmd: 'imageList',
            value: val
        }
        this.getStore().load();
        this.getStore().loadPage(1);
	}
});
