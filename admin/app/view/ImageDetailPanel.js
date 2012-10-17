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
                            '<tpl for="metadata">{[this.renderMetadata(values)]}</tpl>'+
                    '</div>'+
					'<div class="imagePropertyGroupHeader">Events</div>'+
							'<div class="imagePropertyGroupContainer">'+
									'<tpl for="events">{[this.renderEvents(values)]}</tpl>'+
							'</div>'+
					'<div class="imagePropertyGroupHeader">Geography</div>'+
							'<div class="imagePropertyGroupContainer">'+
									'<tpl for="geography">'+
											'<span class="imggeography imagePropertyPill">'+
													'<span class="imagePropertyPillText">{.}</span>'+
													'<span pilldata="{.}" class="del imagePropertyPillRemove"></span>'+
											'</span>'+
									'</tpl>'+
							'</div>'+
					'<div class="imagePropertyGroupHeader">Sets</div>'+
							'<div class="imagePropertyGroupContainer">'+
									'<tpl for="sets">'+
											'<span class="imgsets imagePropertyPill">'+
													'<span class="imagePropertyPillText">{.}</span>'+
													'<span pilldata="{.}" class="del imagePropertyPillRemove"></span>'+
											'</span>'+
									'</tpl>'+
							'</div>'+
			'</tpl>',
            {
                renderMetadata: function( i ) {
                    return '<span class="imgmetadata imagePropertyPill">'+
                                '<span class="imagePropertyPillText"><span style="font-weight:bold">' + i.key + '</span>: ' + i.value + '</span>'+
                                '<span pilldata="' + i.aid + '" valdata="' + i.value + '" class="del imagePropertyPillRemove"></span>'+
                        '</span>'
                },
                renderEvents: function( i ) {
                    return '<span class="imgevents imagePropertyPill">'+
                                '<span class="imagePropertyPillText">' + /*<span style="font-weight:bold">' + i.key + '</span>: ' +*/ i.value + '</span>'+
                                '<span pilldata="' + i.eid + '" valdata="' + i.value + '" class="del imagePropertyPillRemove"></span>'+
                        '</span>'
                }
            }),

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
						store: 'PropertiesStore',
						displayField: 'name',
						typeAhead: false,
                        queryParam: 'value',
                        minChars: 2,
						hideLabel: true,
						hideTrigger: true,
						flex: 1,
						listConfig: {
								loadingText: 'Looking for properties...',
								emptyText: 'No matching properties found.',
								getInnerTpl: function() {
                                    return '<div class="propertySearchItem">'+
                                        '{title}: <span style="font-weight:bold;">{name}</span>'+
                                    '</div>';
								}
						},
						pageSize: 5,
						listeners: {
                            scope: this,
							select: function(combo, selection) {
								var property = selection[0];
								if ( property ) {
                                    Ext.Ajax.request({
                                        url: Config.baseUrl + 'resources/api/api.php',
                                        params: {
                                            cmd: 'imageAddAttribute',
                                            category: property.data.categoryId,
                                            attribType: 'attributeId',
                                            attribute: property.data.attributeId,
                                            //force: true,
                                            imageId: this.image.imageId
                                        },
                                        scope: this,
                                        success: function( data ) {
                                            data = Ext.decode( data.responseText );
                                            if ( data.success ) {
                                                this.loadImage( this.image );
                                            }
                                        }
                                    });
								}
							}
						}
				}]
			}]
		});
		me.callParent(arguments);
	},
	
	loadImage: function( record ) {
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            method: 'GET',
            params: {
                cmd: 'imageList',
                imageId: record.imageId,
                associations: Ext.encode(['attributes','events','geography','sets'])
            },
            scope: this,
            success: function( res ) {
                var properties = [], events = [], geography = [], sets = [];
                var data = Ext.decode( res.responseText ).records[0];
                this.image = data;
                Ext.each( data.attributes, function( attr ) {
                    properties.push({ key: attr.category, value: attr.attribute, aid: attr.attributeId, cid: attr.categoryId });
                });
                Ext.each( data.events, function( ev ) {
                    events.push({ /*key: ev.eventType,*/ value: ev.title, eid: ev.eventId, etid: ev.eventTypeId });
                });
                this.addProperties({
                    metadata: properties,
                    events: events,
                    geography: [],
                    sets: []
                });
            }
        });
	},

	addProperties: function( data ) {
		Ext.getCmp('imageDetailsPanel').update( data );
        // metadata
        Ext.select('span.imgmetadata span.imagePropertyPillRemove').on('click', function( e, el, opts ) {
            Ext.Msg.confirm( 'Delete Image Attribute', 'Are you sure you want to delete "' + el.getAttribute('valdata') + '"?', function( btn, text, opts ) {
                if ( btn == 'yes' ) {
                    Ext.getCmp('imageDetailsPanel').removeProperty( el.getAttribute('pilldata') );
                    Ext.fly( el ).up('span').remove();
                }
            });
        });
        // events
        Ext.select('span.imgevents span.imagePropertyPillRemove').on('click', function( e, el, opts ) {
            Ext.Msg.confirm( 'Remove Event Association', 'Are you sure you want dissociate "' + el.getAttribute('valdata') + '"?', function( btn, text, opts ) {
                if ( btn == 'yes' ) {
                    Ext.getCmp('imageDetailsPanel').removeEvent( el.getAttribute('pilldata') );
                    Ext.fly( el ).up('span').remove();
                }
            });
        });
	},

	removeProperty: function( id ) {
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'imageDeleteAttribute',
                attributeId: id,
                imageId: this.image.imageId
            },
            scope: this,
            success: function( data ) {
                data = Ext.decode( data.responseText );
                if ( data.success ) {
                    this.loadImage( this.image );
                }
            }
        });
	},

	removeEvent: function( id ) {
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'imageDeleteFromEvent',
                eventId: id,
                imageId: this.image.imageId
            },
            scope: this,
            success: function( data ) {
                data = Ext.decode( data.responseText );
                if ( data.success ) {
                    this.loadImage( this.image );
                }
            }
        });
	}


});
