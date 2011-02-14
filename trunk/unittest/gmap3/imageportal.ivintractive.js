/**
 * @copyright SilverBiology, LLC
 * @website http://www.silverbiology.com
*/
Ext.namespace('ImagePortal');

ImagePortal.IVIntractive = function(config){
	
 	Ext.apply( this, config, {
			zoomLevel: 2
		,	id: 'map-canvas'
		,	border: true
		,	mapConfOpts: ['enableScrollWheelZoom', 'enableDoubleClickZoom', 'enableDragging']
		,	mapControls: ['GSmallMapControl','GMapTypeControl']
		,	setCenter: {
				lat: 30
			,	lng: -90
			}
		,	width: 600
		,	height: 400
		,	title: 'Specimen Image'					
	});
		
	ImagePortal.IVIntractive.superclass.constructor.call(this, config);

};

Ext.extend(ImagePortal.IVIntractive, Ext.ux.GMapPanel, {
	drawImage: function(imagePath){
		var imgTiles = new google.maps.ImageMapType({
				getTileUrl: function(ll, z) {
					var X = ll.x % (1 << z);  
					var path = imagePath + "google_tiles/" + (5-z) + "/tile_"+ (5-z) + "_" + X + "_" + ll.y + ".jpg";
					return path;				
				},
				tileSize: new google.maps.Size(256, 256),
				isPng: false,
				maxZoom: 5,
				name: "Image",
				minZoom: 0,
				alt: "Specimen Sheet Image"
			});
		this.on('mapready', function(map){  
				map.getMap().mapTypes.set('image', imgTiles);
				map.getMap().setMapTypeId('image');
				map.getMap().unbind(map.getMap().mapTypes.roadmap);
			})
	}

});
	
Ext.reg('ivinteractive', ImagePortal.IVIntractive );
