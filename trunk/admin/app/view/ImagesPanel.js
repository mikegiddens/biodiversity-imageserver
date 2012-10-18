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
    beginLayout: Ext.emptyFn,
	initComponent: function() {
		var me = this;
        var advancedFilter = null;
		Ext.applyIf(me, {
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
				},{
					xtype: 'tbseparator'
				},{
					xtype: 'searchfield',
					name: 'searchval',
					emptyText: 'Query OCR',
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
                width: 500,
                layout: 'fit',
                resizable: false,
                bodyBorder: false,
                closeAction: 'hide',
                items: [
                    Ext.create( 'BIS.view.SearchFilterPanel' )
                ]
            });
            this.advancedFilter.on('done', function( data ) {
                me.advancedFilter.hide();
            });
            this.advancedFilter.on('cancel', function( data ) {
                me.advancedFilter.hide();
            });
        }
        this.advancedFilter.show(); 
    },
	setFilter: function( params, reset ) {
		if ( reset ) this.getStore().clearFilter();
		var parsedParams = [];
		for ( var p in params ) {
			parsedParams.push({property: p, value: params[p]});
		}
		this.getStore().filter( parsedParams );
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
    },
	clearFilter: function() {
        this.getStore().getProxy().extraParams = {
            cmd: 'imageList'
        }
        this.getStore().load();
	},
	changeView: function( cycleBtn, item ) {
		if ( item.type != 'details' ) {
			this.getView().setTpl( item.type );
		}
	},
    // this is called by searchfield (plugin was hacked)
	search: function( val ) {
        this.searchOcr( val );
	},
    searchOcr: function( val ) {
    }
});
