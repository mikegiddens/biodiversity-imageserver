Ext.define('BIS.view.ImageDetailPanel', {
	extend: 'Ext.panel.Panel',
	alias: ['widget.imagedetailpanel'],

    id: 'imageDetailsPanel',
	initComponent: function() {
		var me = this;

        this.on( 'selectionchange', function( data ) {
            var images = [];
            Ext.each( data, function( record ) {
                images.push( record.raw );
            });
            this.loadImages( images );
        });

		Ext.applyIf(me, {
			title: 'Image Properties',
			layout: 'fit',
			autoScroll: true,
            defaultMessage: {message:'<div style="padding: 10px">Click an image to view it\'s properties.</div><br><span class="imagePropertyPill" style="background-color:#A5DC4B"><span class="imagePropertyPillText">All images have this attribute.</span></span><br><span class="imagePropertyPill" style="background-color:#FEFFBF"><span class="imagePropertyPillText">Not all images have this attribute.</span></span>'},

			tpl: new Ext.XTemplate('<tpl>'+
                '<tpl if="typeof message != \'undefined\'">'+
					'{message}'+
                '</tpl><tpl if="typeof message == \'undefined\'">'+
                    '<div>{title}</div>'+
                    '<div>In collection: <span style="font-weight:bold;">{collection}</span></div>'+
					'<div class="imagePropertyGroupHeader">Attributes</div>'+
                    '<div class="imagePropertyGroupContainer">'+
                            '<tpl for="metadata">{[this.renderMetadata(values)]}</tpl>'+
                    '</div>'+
					'<div class="imagePropertyGroupHeader">Events</div>'+
							'<div class="imagePropertyGroupContainer">'+
									'<tpl for="events">{[this.renderEvents(values)]}</tpl>'+
							'</div>'+/*
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
							'</div>'+*/
                '</tpl>'+
			'</tpl>',
            {
                renderMetadata: function( i ) {
                    return '<span class="imgmetadata imagePropertyPill" style="background-color:' + i.pillColor + '">'+
                                '<span class="imagePropertyPillText">' + i.key + ': ' + '<span style="font-weight:bold">' + i.value + '</span></span>'+
                                '<span pilldata="' + i.aid + '" valdata="' + i.value + '" class="del imagePropertyPillRemove"></span>'+
                        '</span>'
                },
                renderEvents: function( i ) {
                    return '<span class="imgevents imagePropertyPill" style="background-color:' + i.pillColor + '">'+
                                '<span class="imagePropertyPillText">' + i.value + '</span>'+
                                '<span pilldata="' + i.eid + '" valdata="' + i.value + '" class="del imagePropertyPillRemove"></span>'+
                        '</span>'
                }
            }),

			listeners: {
				scope: this,
				afterrender: function() {
					this.update( this.defaultMessage );
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
						emptyText: 'Type to search attributes.',
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
							select: function( combo, selection ) {
								var property = selection[0];
								if ( property ) {
                                    var ids = [];
                                    Ext.each( this.images, function( record ) {
                                        ids.push( record.imageId );
                                    });
                                    Ext.Ajax.request({
                                        url: Config.baseUrl + 'resources/api/api.php',
                                        params: {
                                            cmd: 'imageAddAttribute',
                                            category: property.data.categoryId,
                                            attribType: 'attributeId',
                                            attribute: property.data.attributeId,
                                            //force: true,
                                            imageId: Ext.encode( ids )
                                        },
                                        scope: this,
                                        success: function( data ) {
                                            data = Ext.decode( data.responseText );
                                            if ( data.success ) {
                                                this.loadImages( this.images );
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
	
	loadImages: function( records ) {
        this.images = records;
        var ids = [];
        Ext.each( records, function( record ) {
            ids.push( record.imageId );
        });
        
        if ( ids.length == 0 ) {
            // show default message
            this.update( this.defaultMessage );
        } else { 
            Ext.Ajax.request({
                url: Config.baseUrl + 'resources/api/api.php',
                method: 'GET',
                params: {
                    cmd: 'imageList',
                    imageId: Ext.encode( ids ),
                    associations: Ext.encode(['attributes','events','geography','sets'])
                },
                scope: this,
                success: function( res ) {
                    var properties = {},
                        finalProperties = [],
                        propertyCount = {},
                        events = {},
                        finalEvents = [],
                        eventCount = {},
                        geography = {},
                        finalGeography = [],
                        geographyCount = {},
                        sets = {},
                        finalSets = [],
                        setCount = {},
                        collections = {},
                        collectionCount = {},
                        collection = '(multiple)';

                    this.images = Ext.decode( res.responseText ).records;

                    Ext.each( this.images, function( image ) {
                        // attributes
                        Ext.each( image.attributes, function( attr ) {
                            if ( !properties[ attr.attributeId ] ) {
                                // add new attribute
                                properties[ attr.attributeId ] = { key: attr.category, value: attr.attribute, aid: attr.attributeId, cid: attr.categoryId };
                                propertyCount[ attr.attributeId ] = 1;
                            } else {
                                propertyCount[ attr.attributeId ]++;
                            }
                        });

                        // events
                        Ext.each( image.events, function( ev ) {
                            if ( !events[ ev.eventId ] ) {
                                // add new event
                                events[ ev.eventId ] = { /*key: ev.eventTypeTitle,*/ value: ev.title, eid: ev.eventId, etid: ev.eventTypeId };
                                eventCount[ ev.eventId ] = 1;
                            } else {
                                eventCount[ ev.eventId ]++;
                            }
                        });

                        // collections
                        if ( !collections[ image.collectionCode ] ) {
                            // add new collection
                            collections[ image.collectionCode ] = null;
                            collectionCount[ image.collectionCode ] = 1;
                        } else {
                            collectionCount[ image.collectionCode ]++;
                        }
                    });

                    // check data for intersections
                    for ( var attrId in properties ) {
                        if ( propertyCount[ attrId ] == this.images.length ) {
                            properties[ attrId ].pillColor = '#A5DC4B';
                        } else {
                            properties[ attrId ].pillColor = '#FEFFBF'
                        }
                        finalProperties.push( properties[ attrId ] );
                    }
                    for ( var evId in events ) {
                        if ( eventCount[ evId ] == this.images.length ) {
                            events[ evId ].pillColor = '#A5DC4B';
                        } else {
                            events[ evId ].pillColor = '#FEFFBF'
                        }
                        finalEvents.push( events[ evId ] );
                    }
                    for ( var cc in collections ) {
                        if ( collectionCount[ cc ] == this.images.length ) {
                            collection = cc;
                        }
                    }

                    var filename = '(multiple images)';
                    var widthHeight = '';
                    var collection = '';
                    if ( this.images.length == 1 ) {
                        filename = this.images[0].filename;
                        widthHeight = 'Dimensions: ' + this.images[0].width + ' x ' + this.images[0].height + ' px'
                    }

                    // add collectioon and title header as well
                    this.addProperties({
                        metadata: finalProperties,
                        events: finalEvents,
                        geography: finalGeography,
                        sets: finalSets,
                        collection: collection,
                        title: '<span style="font-weight:bold;">' + filename + '</span><br><span style="font-size: 10px;">' + widthHeight + '</span>'
                    });
                }
            });
        }
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
        var relevantImageIds = [];
        Ext.each( this.images, function( image ) {
            Ext.each( image.attributes, function( attr ) {
                if ( attr.attributeId == id ) relevantImageIds.push( image.imageId );
            });
        });
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'imageDeleteAttribute',
                attributeId: id,
                imageId: Ext.encode( relevantImageIds )
            },
            scope: this,
            success: function( data ) {
                data = Ext.decode( data.responseText );
                if ( data.success ) {
                    this.loadImages( this.images );
                }
            }
        });
	},

	removeEvent: function( id ) {
        var relevantImageIds = [];
        Ext.each( this.images, function( image ) {
            Ext.each( image.events, function( ev ) {
                if ( ev.eventId == id ) relevantImageIds.push( image.imageId );
            });
        });
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'imageDeleteFromEvent',
                eventId: id,
                imageId: Ext.encode( relevantImageIds )
            },
            scope: this,
            success: function( data ) {
                data = Ext.decode( data.responseText );
                if ( data.success ) {
                    this.loadImages( this.images );
                }
            }
        });
	}


});
