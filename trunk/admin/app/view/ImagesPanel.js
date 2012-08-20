Ext.define('BIS.view.ImagesPanel', {
	extend: 'Ext.grid.Panel',
	alias: ['widget.imagespanel'],
	requires: [
		'BIS.view.ImagesGridView',
		'Ext.ux.form.SearchField'
	],
	id: 'imagesGrid',
	autoScroll: true,
	store: 'ImagesStore',
	listeners: {
/*
		afterrender: function( grid, e ) {
			this.grid = Ext.getCmp('imagesGrid');
			grid.dropZone = new Ext.dd.DropZone( Ext.get('imagesGrid-body'), {
				overClass: 'highlight'
			});
		}
*/
	},
	initComponent: function() {
		var me = this;
		Ext.applyIf(me, {
			viewType: 'imagesgridview',
			columns: [{
				xtype: 'gridcolumn',
				dataIndex: 'image_id',
				text: 'Identifier'
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
				dataIndex: 'timestamp_modified',
				text: 'Last Modified'
			}],
			dockedItems: [{
				xtype: 'pagingtoolbar',
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
					iconCls: 'icon_cancel',
					scope: this,
					handler: this.clearFilter
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
				},{
					xtype: 'tbseparator'
				},{
					xtype: 'searchfield',
					name: 'searchval',
					emptyText: 'Search images',
					handlerCmp: this,
					width: 200,
					scope: this
				}]
			}]
		});
		me.callParent(arguments);
	},
	setFilter: function( params, reset ) {
		if ( reset ) this.grid.getStore().clearFilter();
		var parsedParams = [];
		for ( var p in params ) {
			parsedParams.push({property: p, value: params[p]});
		}
		this.grid.getStore().filter( parsedParams );
	},
	clearFilter: function() {
		this.grid.getStore().clearFilter();
	},
	changeView: function( cycleBtn, item ) {
		console.log(this.getView(), item.type);
		if ( item.type != 'details' ) {
//			this.getView().setTpl( item.type );
		}
	},
	search: function( val ) {
		this.grid.getStore().filterBy(function( record, id ) {
			for ( var p in record.data ) {
				if ( String(record.data[p]).indexOf(val) > 0 ) {
					return true;
				}
			}
		});
	}
});