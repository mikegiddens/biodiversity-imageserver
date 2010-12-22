/**
 * @copyright SilverBiology, LLC
 * @author SilverBiology, LLC
 * @website http://www.silverbiology.com
*/
Ext.ns("ImagingProgress");

ImagingProgress.ImageLayer = function(config){
	
	var position = new google.maps.LatLng(30, -90);
		
	var mapOptions = {
				    center: position,
				    zoom: 6,    
				    mapTypeId: google.maps.MapTypeId.TERRAIN
				  }
				  
	var mapdemo = new Ext.Map({
            mapOptions:mapOptions
		,	id:'fusionidmap'	
        });			  
	
	Ext.apply(this, config, { 
				items:[mapdemo]
			,	listeners:{
					activate: this.drawImage
				}
			,	dockedItems: [{
						xtype: 'toolbar'
					,	dock: 'top'
					,	title: 'Fusion Id Map'
					,	items: [{
									xtype:'spacer'
								},{
									text: 'Refresh'
	                            ,	ui: 'action'
								,	id:'fmrefresh'
								,	iconCls:'refresh'
								,	handler: function() {
											var zoomLevel = Ext.getCmp('fusionidmap').map.getZoom();
											Ext.getCmp('fusionidmap').map.setZoom(zoomLevel * 1);						
										}	
								}]
						}]	
		});
		
		ImagingProgress.FusionMapPanel.superclass.constructor.call(this,config);
};
Ext.extend(ImagingProgress.ImageLayer , Ext.Panel, {
		
		addLayerfusionid : function(){
				this.fusionidlayer.setMap(Ext.getCmp('fusionidmap').map)
				var zoomLevel = Ext.getCmp('fusionidmap').map.getZoom();
				Ext.getCmp('fusionidmap').map.setZoom(zoomLevel * 1);
			}					
	,	CustomGetTileUrl: function( a, b ) {
		//			var path = this.path + "google_tiles/" + (5 - b) + "/tile_"+ 1 + "_" + a.x + "_" + a.y + ".jpg";
					var path = this.path + "google_tiles/" + (5 - b) + "/tile_" + (5 - b) + "_" + a.x + "_" + a.y + ".jpg";
					return path;
		}

	,	drawImage: function(){
				//this.path = path;
				// ====== Create a copyright entry =====
				var copyright = new GCopyright(1, new GLatLngBounds(new GLatLng(-90, -180), new GLatLng(90, 180)), 0, this.copyright);
				
				// ====== Create a copyright collection =====
				// ====== and add the copyright to it   =====
				var copyrightCollection = new GCopyrightCollection('(Specimen: ...)');
				copyrightCollection.addCopyright(copyright);
				
				// == Write our own getTileUrl function ========
				// In this case the tiles are names like  8053_5274_3.jpg      
				
				// ===== Create the GTileLayer =====
				// ===== adn apply the CustomGetTileUrl to it
				var tileLayers = [new GTileLayer(copyrightCollection, 2, 5)];
				tileLayers[0].getTileUrl = this.CustomGetTileUrl.createDelegate(this);
				
				//var gmaptype = new GMapTypeControl();
				// this.map.addControl(gmaptype);
				
				// ===== Create the GMapType =====
				// ===== and add it to the map =====
				tileLayers.setMap(Ext.getCmp('fusionidmap').map)
				Ext.getCmp('fusionidmap').map.setZoom(zoomLevel + 2);
		}
});	
