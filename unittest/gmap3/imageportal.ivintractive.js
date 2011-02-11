/**
 * @copyright SilverBiology, LLC
 * @website http://www.silverbiology.com
*/
Ext.namespace('ImagePortal');

ImagePortal.IVIntractive = function(config){

 	Ext.apply( this, config, {
			mapConfOpts:['enableScrollWheelZoom','enableDoubleClicZoom','enableDragging']
		,	mapControls:['GMapControl','GMapTypeControl','NonExistantControl','GLargeMapControl']
		,	featureConfig:['enableContinuousZoom','enableScrollWheelZoom','enableDragging']
	//	,	controls: [new GLargeMapControl(),new GOverviewMapControl()]
		,	scope: this	
  		,	iconCls: 'x-icon-templates'
		,	border:false
		,	autoScroll: true
		,	id:'map-canvas'
		,	border: false
		,   setCenter: {
                    lat: 90
            	,	lng: -60
                 }
		,	listeners: {
				afterLayout: function() {
					this.body.setStyle('background-color', '#FFF');
					var dt = new Ext.util.DelayedTask();
					dt.delay(100, function(){
						this.setMap(new GLatLng(45, -90) );
					});
				}
			}
	});
		
	ImagePortal.IVIntractive.superclass.constructor.call(this, config);

};

Ext.extend(ImagePortal.IVIntractive, Ext.ux.GMapPanel, {

		varMap: function(){
			var map = this.map
			return this.map;
		}
	
	,	CustomGetTileUrl: function( a, b ) {
//			var path = this.path + "google_tiles/" + (5 - b) + "/tile_"+ 1 + "_" + a.x + "_" + a.y + ".jpg";
			var path = this.path + "google_tiles/" + (5 - b) + "/tile_" + (5 - b) + "_" + a.x + "_" + a.y + ".jpg";
			return path;
		}

	,	drawImage: function(path){
			this.path = path;
			var copyright = new Copyright(1, new google.maps.LatLngBounds(new google.maps.LatLng(-90, -180), new google.maps.LatLng(90, 180)), 0, this.copyright);
			var copyrightCollection = new CopyrightCollection('(Specimen: ...)');
			copyrightCollection.addCopyright(copyright);
		//	var tileLayers = [new GTileLayer(copyrightCollection, 2, 5)];
		//	tileLayers[0].getTileUrl = this.CustomGetTileUrl.createDelegate(this);
			tileLayers =  new google.maps.ImageMapType({
					name: copyrightCollection,
					getTileUrl: this.CustomGetTileUrl.createDelegate(this),
					tileSize: new google.maps.Size(256, 256),
					opacity:0.60,
					isPng: true,
			});
			var myLatLng = new google.maps.LatLng(62.323907, -150.109291);
			var myOptions = {
				zoom: 11,
				center: myLatLng,
				mapTypeId: google.maps.MapTypeId.ROADMAP 
			};
//mapTypes.set('osm', openStreet); 
			var map = new google.maps.Map(Ext.getCmp('map-canvas'), myOptions)
		//	var map = this.getMap();
			map.overlayMapTypes.push(null); // create empty overlay entry
			map.overlayMapTypes.setAt("0",tileLayers);
		//	this.overlayMapTypes.push(tileLayers)
			var mapTypes = map.mapTypes[map.getMapTypeId()]
			
		/*	var myLatLng = new google.maps.LatLng(62.323907, -150.109291);
			var myOptions = {
				zoom: 11,
				center: myLatLng,
				mapTypeId: google.maps.MapTypeId.ROADMAP 
			};

			var map = new google.maps.Map(Ext.getCmp('map-canvas'), myOptions);
			var swBound = new google.maps.LatLng(62.281819, -150.287132);
			var neBound = new google.maps.LatLng(62.400471, -150.005608);
			var bounds = new google.maps.LatLngBounds(swBound, neBound);*/

  // Photograph courtesy of the U.S. Geological Survey
		
	//		overlay = new USGSOverlay(bounds, path, map);
	//		overlay = new MCustomTileLayer(map);

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
	
	Ext.reg('ivinteractive', ImagePortal.IVIntractive );
