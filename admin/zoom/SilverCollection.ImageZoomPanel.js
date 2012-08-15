Ext.namespace('SilverCollection');
SilverCollection.ImageZoomPanel = function(config) {

	this.imageZoom = new SilverCollection.ImageZoom();

	Ext.apply(this, config, {
		defaults: {
			border: false
		},
		items: [ this.imageZoom ]
	});

	SilverCollection.ImageZoomPanel.superclass.constructor.call(this, config);
	
};
Ext.extend( SilverCollection.ImageZoomPanel, Ext.Panel, {
	
	loadImage: function( data ) {
		this.imageZoom.loadImage( data.tileUrl );
	}

});
