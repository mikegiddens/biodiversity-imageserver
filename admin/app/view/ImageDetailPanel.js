Ext.define('BIS.view.ImageDetailPanel', {
	extend: 'Ext.panel.Panel',
	alias: ['widget.imagedetailpanel'],
	requires: [
	],
    id: 'imageDetailsPanel',
	initComponent: function() {
		var me = this;
		Ext.applyIf(me, {
			title: 'Image Properties',
			layout: 'fit',
			autoScroll: true,

			tpl: new Ext.XTemplate('<tpl>'+
					'{message}'+
					'<div class="imagePropertyGroupHeader">Metadata</div>'+
							'<div class="imagePropertyGroupContainer">'+
									'<tpl for="metadata">'+
											'<span class="imgmetadata imagePropertyPill">'+
													'<span class="imagePropertyPillText">{.}</span>'+
													'<span catdata="metadata" pilldata="{.}" class="del imagePropertyPillRemove"></span>'+
											'</span>'+
									'</tpl>'+
							'</div>'+
					'<div class="imagePropertyGroupHeader">Events</div>'+
							'<div class="imagePropertyGroupContainer">'+
									'<tpl for="events">'+
											'<span class="imgevents imagePropertyPill">'+
													'<span class="imagePropertyPillText">{.}</span>'+
													'<span catdata="events" pilldata="{.}" class="del imagePropertyPillRemove"></span>'+
											'</span>'+
									'</tpl>'+
							'</div>'+
					'<div class="imagePropertyGroupHeader">Geography</div>'+
							'<div class="imagePropertyGroupContainer">'+
									'<tpl for="geography">'+
											'<span class="imggeography imagePropertyPill">'+
													'<span class="imagePropertyPillText">{.}</span>'+
													'<span catdata="geography" pilldata="{.}" class="del imagePropertyPillRemove"></span>'+
											'</span>'+
									'</tpl>'+
							'</div>'+
					'<div class="imagePropertyGroupHeader">Sets</div>'+
							'<div class="imagePropertyGroupContainer">'+
									'<tpl for="sets">'+
											'<span class="imgsets imagePropertyPill">'+
													'<span class="imagePropertyPillText">{.}</span>'+
													'<span catdata="sets" pilldata="{.}" class="del imagePropertyPillRemove"></span>'+
											'</span>'+
									'</tpl>'+
							'</div>'+
			'</tpl>'),

			listeners: {
				scope: this,
				render: function ( panel ) {
					var dropTarget = new Ext.dd.DropTarget(panel.el, {
						ddGroup: 'categoryDD',
						copy: false,
						notifyDrop: function (dragSource, e, data) {
							var record = data.records[0].data;
							console.log( record );
							console.log( this );
						}
					});
				},
				afterrender: function() {
					this.update({message:'<div style="padding: 10px">Click an image to view it\'s properties.</div>'});
				}
			},
			
			dockedItems: [{
				xtype: 'container',
				dock: 'top',
				style: 'padding: 5px',
				layout: 'hbox',
				items: [{
						xtype: 'combo',
						id: 'propertySeachCombo',
						disabled: true,
						emptyText: 'Type to search attributes or add a new one.',
						store: 'ImagesStore',
						displayField: 'fileName',
						typeAhead: false,
						hideLabel: true,
						hideTrigger: true,
						flex: 1,
						listConfig: {
								loadingText: 'Looking for properties...',
								emptyText: 'No matching properties found.',
								getInnerTpl: function() {
										return '<div class="propertySearchItem">'+
												'<h3><span>{fileName}</h3>'+
												'{path}'+
										'</div>';
								}
						},
						pageSize: 5,
						listeners: {
							select: function(combo, selection) {
								var property = selection[0];
								if ( property ) {
									console.log( 'selected', property );
								}
							}
						}
				}]
			}]
		});
		me.callParent(arguments);
	},
	
	loadImage: function( record ) {
		var properties = [];
		for ( var p in record ) {
			properties.push( p );
		}
		this.addProperties({
			metadata: properties,
			events: properties,
			geography: properties,
			sets: properties
		});
	},

	addProperties: function( data ) {
		Ext.getCmp('imageDetailsPanel').update( data );
		for ( var category in data ) {
			Ext.select('span.img'+category).select('.del').on('click', function( e, el, opts ) {
				Ext.getCmp('imageDetailsPanel').removeProperty( el.getAttribute('catdata'), el.getAttribute('pilldata') );
			});
		}
	},

	removeProperty: function( type, id ) {
		console.log( 'removing', type, id );
	}

});
