if ( typeof BIS == 'undefined' ) {
    BIS = {};
}
BIS.ImageViewer = function( config ) {
    // init
	if ( config ) $.extend( this, config );
	if ( !(this.webportal) || !(this.containerId)) {
		console.log( 'Error instantiating program. A URL must be provided to load images and the id of a container component.' );
	}
	this.el	= $('#'+this.containerId);
    this.el.html('<div id="imagecollectionview"></div><div id="imagedataview"></div>');
    this.imageCollectionViewEl = $('#imagecollectionview');
    this.imageDataViewEl = $('#imagedataview');
	var me = this;
	this.panel = new BIS.ImageDataView({
		el: me.imageDataViewEl,
		parent: me
	});
    this.loadCollections( function( collections ) { if ( !($.isEmptyObject(collections)) ) me.loadImageView( collections[0] ) } );
    // util functions
	this.numberWithCommas = function( x ) {
	    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}
	// main functions
    this.loadCollections = function( callback ) {
        $.get( this.webportal, function( data ) {
            console.log( data );
            callback( data );
        });
    }
    this.loadImageView = function( collection ) {
        console.log( 'loading images...' );
    }

}

BIS.ImageDataView = function( config ) {

	if ( config ) $.extend( this, config );
	var me = this;

    this.el.html( '<hr>' );
    var html = [];
    for ( var c = 0; c < 10; c++ ) {
        html.push( '<div class="imageDataViewBox"></div>' );
    }
    this.el.append( html.join('') );

}

var observer = new EventTarget();
BIS.ImageViewer.prototype = observer;
BIS.ImageDataView.prototype = observer;
