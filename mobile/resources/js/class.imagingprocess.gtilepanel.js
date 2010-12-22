/**
 * @copyright SilverBiology, LLC
 * @website http://www.silverbiology.com
*/
Ext.namespace('ImagingProgress');

ImagingProgress.GtilePanel = function(config){

 	Ext.apply( this, config, {
			mapConfOpts:['enableScrollWheelZoom','enableDoubleClicZoom','enableDragging']
		,	mapControls:['GSmallControl','GMapTypeControl','NonExistantControl','GLargeMapControl']
		,	featureConfig:['enableContinuousZoom','enableScrollWheelZoom','enableDragging']
  		, 	controls:[new GLargeMapControl(),new GOverviewMapControl()]
		,	iconCls: 'x-icon-templates'
		,	border:false
		,	autoScroll: true
	//	,	title: 'Intractive'
//		,	height:500
		,	id:'map-canvas'
		,	border: false
		,	style: 'background-color: #FFF'
		,	listeners:{
				render:function(){
					this.drawImage();
				}
			}
	});
		
	ImagingProgress.GtilePanel.superclass.constructor.call(this, config);

};

Ext.extend(ImagingProgress.GtilePanel, Ext.ux.GMapPanel, {

		varMap: function(){
			var map = this.map
			return this.map;
		}
	
	,	CustomGetTileUrl: function( a, b ) {
//			var path = this.path + "google_tiles/" + (5 - b) + "/tile_"+ 1 + "_" + a.x + "_" + a.y + ".jpg";
			var path = this.path + "google_tiles/" + (5 - b) + "/tile_" + (5 - b) + "_" + a.x + "_" + a.y + ".jpg";
			return path;
		}

	,	drawImage: function(){
			
			console.log("this",this);
			
			//this.path = path;
			this.path = 'http://images.cyberfloralouisiana.com/images/specimensheets/nlu/0/6/32/89/'
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
			var map = this.getMap();
			var mapTypes = map.getMapTypes();
			var custommap = new GMapType(tileLayers, new GMercatorProjection(18), "Images");
			map.addMapType(custommap);
			Ext.each(mapTypes, function(item){
				if (item.getName() == "Images") {
					map.removeMapType(item);
				}
			});

			map.removeMapType(G_SATELLITE_MAP);
			map.removeMapType(G_HYBRID_MAP);
			map.removeMapType(G_NORMAL_MAP);
			
			var centerLat = 50, centerLong = -80, initialZoom = 1;
			map.setCenter(new GLatLng(centerLat, centerLong), initialZoom, custommap);
			map.setZoom( parseInt(initialZoom) );
		}

	,	getImagePath:function(){
			if(this.ImgCount < 0){
				this.ImgCount = this.specimenRecord.totalCount-1;
			}else if(this.ImgCount > this.specimenRecord.totalCount-1){
				this.ImgCount = 0;
			}
				this.getTopToolbar().info.setText((this.ImgCount+1) + ' OF ' + this.specimenRecord.totalCount);
				return (this.specimenRecord.images[this.ImgCount].path);
		}	
		,setupMap:function () {
  			if (GBrowserIsCompatible()) {
    			map = new GMap2(document.getElementById("map_canvas"));
    			map.addControl(new GLargeMapControl());
    			map.setCenter(new GLatLng(60, -98), 4);
    		//	window.setTimeout(setupWeatherMarkers, 0);
  			}
		}

		
	});
	
	Ext.reg('ivinteractive', ImagingProgress.GtilePanel );
