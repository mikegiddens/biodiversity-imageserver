/**
 * @copyright SilverBiology, LLC
 * @website http://www.silverbiology.com
*/
Ext.namespace('ImagePortal');

ImagePortal.IVIntractive = function(config){
	var overlay;

  

  
 	Ext.apply( this, config, {
			mapConfOpts:['enableScrollWheelZoom','enableDoubleClicZoom','enableDragging']
		,	mapControls:['GMapControl','GMapTypeControl','NonExistantControl','GLargeMapControl']
		,	featureConfig:['enableContinuousZoom','enableScrollWheelZoom','enableDragging']
  		, 	controls:[new GLargeMapControl(),new GOverviewMapControl()]
		,	iconCls: 'x-icon-templates'
		,	border:false
		,	autoScroll: true
		,	title: 'Intractive'
		,	gmapType:'map'
		,	id:'map-canvas'
		,	border: false
		,   setCenter: {
                    lat: 90
            	,	lng: -60
                 }
			 
	});
	
	ImagePortal.IVIntractive.superclass.constructor.call(this, config);

};

Ext.extend(ImagePortal.IVIntractive, Ext.ux.GMapPanel, {
		
	drawImage: function(path){
		this.path = path;
		map = this.getMap();
		this.temp();	
	}
,	temp: function() {
	/*	var myLatLng = new google.maps.LatLng(62.323907, -150.109291);
		var myOptions = {
		  zoom: 11,
		  center: myLatLng,
		  mapTypeId: google.maps.MapTypeId.SATELLITE
		};

		var map = new google.maps.Map(Ext.getCmp("map_canvas"),
			myOptions);
*/		map = this.getMap();
		var swBound = new google.maps.LatLng(62.281819, -150.287132);
		var neBound = new google.maps.LatLng(62.400471, -150.005608);
		var bounds = new google.maps.LatLngBounds(swBound, neBound);
		var srcImage = 'images/biodiversity-image-server-logo.jpg';
		overlay = new USGSOverlay(bounds, srcImage, map);
	}	
	,	 
	});
	
	Ext.reg('ivinteractive', ImagePortal.IVIntractive );
