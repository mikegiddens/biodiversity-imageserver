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
                        $('.collectionBox').click( me.selectCollection );
                        $('.collectionBox[bis-data-code="'+firstCollection.code+'"]').addClass('glow');
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
                            if ( (!($.isEmptyObject(data.data))) ) {
                                var image = data.data[0];
                                me.imageCollectionViewEl.append('<div class="collectionBox" bis-data-code="'+this.col.code+'"><img class="squished" src="'+me.panel.composeThumbnailPath(image.path,image.filename,image.ext)+'"><div class="collectionBoxMask">'+this.col.name+'</div></div>');
                            }
                            incr( this.col, !($.isEmptyObject(data.data)) );
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
        if ( collection ) {
            this.currentCollection = collection;
        }
        var route = this.webportal + 'resources/api/api.php?cmd=images&collectionCode='+me.currentCollection.code+'&useRating=true&start='+this.pageStart+'&stop='+this.pageStop+'&limit='+this.pageLimit;
        if ( values ) {
            me.cacheValues = JSON.stringify(values.split(',').join('","'));
            route += '&characters=['+me.cacheValues+']';
        } else {
            if ( me.cacheValues ) {
                route += '&characters=['+me.cacheValues+']';
            }
        }
        $.ajax({
            url: route,
            dataType: 'jsonp',
            success: function( data ) {
                var returnedCount = data.data.length;
                me.pageCount = returnedCount;
                me.totalCount = data.totalCount;
                $('.pagingLabel').html('Viewing '+me.pageStart+' to '+((me.totalCount < me.pageStop) ? me.totalCount : me.pageStop)+' of '+me.totalCount+' images.');
                if ( me.pageStop >= me.totalCount ) { $('.pageNext').addClass('disabled') }
                if ( me.pageStart > 0 ) { $('.pagePrev').removeClass('disabled') }
                if ( me.pageStart <= 0 ) { $('.pagePrev').addClass('disabled') }
                if ( me.pageStop < me.totalCount ) { $('.pageNext').removeClass('disabled') }
                me.panel.loadImages( me.currentCollection, data.data );
            }
        });
    }
    this.submitSearch = function( e ) {
        me.pageStart = 0;
        me.pageLimit = 20;
        me.pageStop = me.pageLimit;
        me.pageCount = 0;
        me.totalCount = 0;
        me.loadImageView( me.currentCollection, $('#search').attr('value') );
    }
    this.submitSearchRemove = function( e ) {
        me.cacheValues = null;
        me.submitSearch( e );
    }
    this.nextPage = function() {
        me.pageStart += me.pageLimit;
        me.pageStop += me.pageLimit;
        me.loadImageView();
    }
    this.prevPage = function() {
        me.pageStart -= me.pageLimit;
        me.pageStop -= me.pageLimit;
        me.loadImageView();
    }
    this.selectCollection = function( e ) {
        $('.collectionBox').click(me.selectCollection).removeClass('glow');
        me.currentCollection = me.collectionStore[$(e.delegateTarget).attr('bis-data-code')];
        $(e.delegateTarget).unbind('click').addClass('glow');
        me.pageStart = 0;
        me.pageLimit = 20;
        me.pageStop = me.pageLimit;
        me.pageCount = 0;
        me.totalCount = 0;
        me.loadImageView( me.currentCollection );
    }
    // init
	if ( config ) $.extend( this, config );
	if ( !(this.webportal) || !(this.containerId)) {
		console.log( 'Error instantiating program. A URL must be provided to load images and the id of a container component.' );
	}
    var me = this;
    this.collectionStore = {};
	this.el	= $('#'+this.containerId);
    this.el.html(
            '<div id="imagecollectionview"></div>'+
            '<hr style="width: 80%; margin-top: 10px; margin-bottom: 10px;">'+
            '<div>'+
                '<div style="float: left;">'+
                    '<span class="galleryTitle" style="color: white; font-weight: bold; font-size: 24px;"></span>'+
                    '<br>'+
                    '<span class="pagingLabel" style="color: white; font-size: 16px; padding-top: 5px;"></span>'+
                '</div>'+
                '<span style="float: right"><button id="searchButton" style="float: left; padding-right: 5px;">Search:</button><input id="search" class="searchfield" type="text"></span>'+
            '</div>'+
            '<div id="imagedataview"></div>'+
            '<div class="ui-state-default ui-corner-all pagePrev" style="position: absolute" title="Previous Page"><span class="ui-icon ui-icon-triangle-1-w"></span></div>'+
            '<div class="ui-state-default ui-corner-all pageNext" style="position: absolute" title="Next Page"><span class="ui-icon ui-icon-triangle-1-e"></span></div>');
    var startPos = $('#imagedataview').position();
    $('.pagePrev').offset({left: startPos.left - 17, top: startPos.top + 35});
    $('.pageNext').offset({left: startPos.left + 1051, top: startPos.top + 35});
    $('.pagePrev').hover(function(){$(this).toggleClass('ui-state-hover')}).click( me.prevPage ).toggleClass('disabled');
    $('.pageNext').hover(function(){$(this).toggleClass('ui-state-hover')}).click( me.nextPage );
    $('#search').tagsInput({
        // #autocomplete_url: is the same as #source: in the jquery ui autocomplete plugin
        autocomplete_url: function( request, response ) {
            $.ajax({
                url: me.webportal + 'resources/api/api.php?cmd=listAttributes&searchFormat=left&limit=5',
                dataType: "jsonp",
                data: {
                    value: request.term
                },
                success: function( data ) {
                    response( data.name );
                }
            });
        },
        autocomplete: {
            // options for jQuery autocomplete config
            minLength: 2
        },
        defaultText: 'add term',
        minChars: 2,
        height: 60,
        width: 350,
        onAddTag: this.submitSearch,
        onRemoveTag: this.submitSearchRemove
    });
    this.pageStart = 0;
    this.pageLimit = 20;
    this.pageStop = this.pageLimit;
    this.pageCount = 0;
    this.totalCount = 0;
    this.imageCollectionViewEl = $('#imagecollectionview');
    this.imageDataViewEl = $('#imagedataview');
    $('#searchButton').button().click( this.submitSearch );
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
        return path.replace(filename,'') + filename.substr( 0, filename.indexOf('.') ) + '_m.' + ext;
    }
    this.composeImagePath = function( path, filename, ext ) {
        return path.replace(filename,'') + filename.substr( 0, filename.indexOf('.') ) + '_l.' + ext;
    }
/*
    this.composeThumbnailPath = function( path, filename, ext ) {
        return path + filename.substr( 0, filename.indexOf('.') ) + '_m.' + ext;
    }
    this.composeImagePath = function( path, filename, ext ) {
        return path + filename.substr( 0, filename.indexOf('.') ) + '_l.' + ext;
    }
*/
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
                                'title="<img style=\'display: inline; padding-left: 10px; position: absolute; left: -120px; top: -60px;\' src=\'http://chart.googleapis.com/chart?chs=75x75&cht=qr&chl='+this.composeImagePath(record.path,record.filename,record.ext)+'&chld=L|1&choe=UTF-8\'><span>'+record.filename+'</span>">'+
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
            this.el.html( '<span style="padding: 10px; color: white;">There are no images for '+ collection.name +'.' );
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
