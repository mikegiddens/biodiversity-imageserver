if ( typeof BIS == 'undefined' ) {
    BIS = {};
}
BIS.ImageViewer = function( config ) {
    // util functions
	this.numberWithCommas = function( x ) {
	    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}
	// main functions
    this.loadCollections = function( callback ) {
        $.ajax({
            url: this.webportal + 'resources/api/api.php?cmd=collections&filter=[{"data":{"type":"list","value":"27,28,29"},"field":"collection_id"}]&sort=code&dir=desc',
            dataType: 'jsonp',
            success: function( collections ) {
                var threshold = collections.records.length;
                var counter = 0;
                var firstCollection;
                var executeCallback = function() {
                    if ( counter == threshold ) {
                        callback( firstCollection );
                        me.currentCollection = firstCollection;
                        $('.collectionBox').click( function( e ) {
                            $('.collectionBox').removeClass('glow');
                            me.currentCollection = me.collectionStore[$(e.delegateTarget).attr('bis-data-code')];
                            me.loadImageView( me.currentCollection );
                            $(e.delegateTarget).unbind('click').addClass('glow');
                        });
                    }
                }
                var incr = function( collection, hasImages ) {
                    if ( (!(firstCollection)) && hasImages ) firstCollection = collection;
                    counter++;
                    executeCallback();
                }
                for ( var i = 0; i < collections.records.length; i++ ) {
                    var record = collections.records[i];
                    me.collectionStore[record.code] = record;
                    $.ajax({
                        url: me.webportal + 'resources/api/api.php?cmd=images&collectionCode='+record.code+'&limit=1&useRating=true',
                        col: record,
                        dataType: 'jsonp',
                        success: function( data ) {
                            incr( this.col, !($.isEmptyObject(data.data)) );
                            if ( (!($.isEmptyObject(data.data))) ) {
                                var image = data.data[0];
                                me.imageCollectionViewEl.append('<div class="collectionBox" bis-data-code="'+this.col.code+'"><img class="squished" src="'+me.panel.composeThumbnailPath(image.path,image.filename,image.ext)+'"><div class="collectionBoxMask">'+this.col.name+'</div></div>');
                            }
                        }
                    });
                }
            },
            failure: function( err ) {
                console.log( 'err:',err );
            }
        });
    }
    this.loadImageView = function( collection, values ) {
        var route = this.webportal + 'resources/api/api.php?cmd=images&collectionCode='+collection.code+'&limit=100&useRating=true';
        if ( values ) {
            route += '&characters=[{"node_value":"'+encodeURI(values)+'"}]'
        }
        $.ajax({
            url: route,
            dataType: 'jsonp',
            success: function( data ) {
                me.panel.loadImages( collection, data.data );
            }
        });
    }
    this.submitSearch = function( e ) {
        me.loadImageView( me.currentCollection, $('#search').attr('value') );
    }
    // init
	if ( config ) $.extend( this, config );
	if ( !(this.webportal) || !(this.containerId)) {
		console.log( 'Error instantiating program. A URL must be provided to load images and the id of a container component.' );
	}
    this.collectionStore = {};
	this.el	= $('#'+this.containerId);
    this.el.html(
            '<div id="imagecollectionview"></div>'+
            '<hr style="width: 80%; margin-top: 10px; margin-bottom: 10px;">'+
            '<div>'+
                '<p class="galleryTitle" style="color: white; font-weight: bold; font-size: 24px; float: left;"></p>'+
                '<span style="float: right"><button id="searchButton" style="float: left; padding-right: 5px;">Search:</button><input id="search" class="searchfield" type="text"></span>'+
            '</div>'+
            '<div id="imagedataview"></div>');
    $('#search').tagsInput({
        //autocomplete_url: this.webportal + 'resources/api/api.php?cmd=listAttributes'
        defaultText: 'add term',
        height: 60,
        width: 350,
        onAddTag: function( tag ) { me.loadImageView( me.currentCollection, tag ) }
    });
    this.imageCollectionViewEl = $('#imagecollectionview');
    this.imageDataViewEl = $('#imagedataview');
    $('#searchButton').button().click( this.submitSearch );
    var me = this;
	this.panel = new BIS.ImageDataView({
		el: me.imageDataViewEl,
		parent: me
	});
    this.loadCollections( function( firstCollection ) {
        if ( firstCollection ) {
            me.loadImageView( firstCollection )
        }
    });
}

BIS.ImageDataView = function( config ) {

    this.replaceImage404 = function( element ) {
        element.src = '/resources/img/no-image.gif';
        element.onerror = '';
        return true;
    }

    this.composeThumbnailPath = function( path, filename, ext ) {
        return path + filename.substr( 0, filename.indexOf('.') ) + '_m.' + ext;
    }
    this.composeImagePath = function( path, filename, ext ) {
        return path + filename.substr( 0, filename.indexOf('.') ) + '_l.' + ext;
    }

    this.loadImages = function( collection, json ) {
        $('.galleryTitle').html( collection.name );
        this.el.html('');
        if ( (!($.isEmptyObject(json))) ) {
            var html = [];
            threshold = json.length;
            for ( var i = 0; i < json.length; i++ ) {
                var record = json[i];
                var subHtml = ''+
                    '<div class="imageDataViewContainer">'+
                        '<div class="imageDataViewBox">'+
                            '<a '+
                                'class="fancybox-thumb" '+
                                'rel="'+record.code+'" '+
                                'href="'+this.composeImagePath(record.path,record.filename,record.ext)+'" '+
                                'title="'+record.filename+'">'+
                            '<img '+
                                'class="squished" '+
                                'src="'+this.composeThumbnailPath(record.path,record.filename,record.ext)+'" '+
                                'onerror="program.panel.replaceImage404(this)">'+
                            '</a>'+
                        '</div>'+
                        '<input type="range" value="'+record.rating+'" step="0.25" id="rating_'+record.image_id+'" style="display: none">'+
                        '<div title="Like this image? Rate it!" class="rateit ratingContainer" data-bis-id="'+record.image_id+'" data-rateit-backingfld="#rating_'+record.image_id+'" data-rateit-resetable="false"  data-rateit-ispreset="true">'+
                        '</div>'+
                    '</div>';
                html.push( subHtml );
            }
            this.el.append( html.join('') );
            $('div.rateit').rateit({step:1});
            $('div.rateit').bind('rated', function() {
                $.ajax({
                    url: me.parent.webportal + 'resources/api/api.php?cmd=setImageRating&rating='+$(this).rateit('value')+'&image_id='+$(this).attr('data-bis-id'),
                    dataType: 'jsonp'
                });
            });
        } else {
            this.el.html( '<span style="padding: 10px">There are no images for '+ collection.name +'.' );
        }
    }

    // init
	if ( config ) $.extend( this, config );
	var me = this;

    $(".fancybox-thumb").fancybox({
        helpers	: {
            title	: {
                type: 'inside'
            },
            overlay	: {
                opacity : 0.8,
                css : {
                    'background-color' : '#000'
                }
            },
            thumbs	: {
                width	: 50,
                height	: 50
            }
        }
    });
}

var observer = new EventTarget();
BIS.ImageViewer.prototype = observer;
BIS.ImageDataView.prototype = observer;
