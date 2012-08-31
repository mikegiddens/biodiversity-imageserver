var ImageZoom = function( config ) {

    // config
    this.el = config.container;
    this.el ? this.el = $('#'+this.el) : this.el = $('body');
    
    // init
    var me                   =   this;
    this.style               =   'height: 100%';
    this.draggable           =   true;
    this.isMouseScroll       =   true;
    this.showZoomBox         =   true;
    this.currentZoomLevel    =   1;
    this.zoomControl         =   true;
    this.rototeControl       =   false;
    this.copyRightControl    =   true;
    this.copyrightText       =   '';
    this.panControl          =   true;
    this.rototeMin           =   -180;
    this.rototeMax           =   180;
    this.tileLayers          =   [];
    this.currentLayer        =   '';
    this.currentCircle       =   '';
    this.previousLayer       =   '';
    this.backgroundColor     =   '#EFEFEF';
    this.zoomMin             =   1;
    this.zoomMax             =   10;
    this.dblClickZoom        =   true;
    this.isImageLoaded       =   false;
    this.spaceHotkey         =   true;
    this.showMask            =   true;
    this.currentShape        =   '';

    // listeners
    this.on('resize', function() {
        this.getTileByOffset();
    });

    this.on('destroy', function() {
        for (var i= 0; i < this.tileLayers.length; i++) {
            if ( this.tileLayers[i] ) {
                this.tileLayers[i].destroy();
            }
            this.tileLayers.splice(i,1);
        }
    });

    // methods
    this.enableDblClickZoom = function() {
        this.dragEl.dblclick( function(e) {
            if (this.currentShape != 'polygon' && this.currentShape != 'polyline') {
                if (this.currentZoomLevel < this.zoomMax) {
                    this.zoom(e, true);
                }
            }
        });
    },
    this.getImageSize = function() {
        return this.getTileCount() * this.currentLayer.tileSize;
    },
    this.getTileCount = function() {
        return Math.pow(this.currentZoomLevel, 2);
    },
    this.getTileByOffset = function() {
        if ( this.isImageLoaded ) {
            var oX = this.el.position().left - this.dragEl.position().left;
            var oY = this.el.position().top - this.dragEl.position().top;
            var offset = [ oX, oY ];
            var i = Math.ceil(offset[1]/this.currentLayer.tileSize);
            var j = Math.ceil(offset[0]/this.currentLayer.tileSize);
            var rowTiles = Math.ceil(this.el.height()/this.currentLayer.tileSize);
            var columnTiles = Math.ceil(this.el.width()/this.currentLayer.tileSize);
            var totalTile = Math.floor(this.dragEl.width()/this.currentLayer.tileSize);
            var bottomrightX, bottomrightY;
            oX = this.el.position().left;
            oY = this.el.position().top;
            var elXY = [ oX, oY ];
            var bottomX = this.el.width() + elXY[0];
            var bottomY = this.el.height() + elXY[1];
            oX = this.dragEl.position().left;
            oY = this.dragEl.position().top;
            var xy = [ oX, oY ];
            var btmX = Math.ceil(Math.abs(xy[1]-bottomY)/this.currentLayer.tileSize);
            var btmY = Math.ceil(Math.abs(xy[0]-bottomX)/this.currentLayer.tileSize);
            btmX = (btmX > totalTile) ? totalTile : btmX;
            btmY = (btmY > totalTile) ? totalTile : btmY;
            btmX = (btmX < 1) ? 1 : btmX;
            btmY = (btmY < 1) ? 1 : btmY;
            i = (i <= 0) ? 1: i;
            j = (j <= 0) ? 1: j;
            if ((totalTile < rowTiles) && (totalTile < columnTiles)) {
                bottomrightX = i;
                bottomrightY = j;
            } else {
                bottomrightX = btmX;
                bottomrightY = btmY;
            }
            this.currentLayer.showTilesByRange(
                { row: i, column: j },
                { row: bottomrightX, column: bottomrightY }
            );
        }
    },
    this.initMouseEvent = function() {
        if ( this.dblClickZoom ) {
            this.enableDblClickZoom();
        }
        if ( this.isMouseScroll ) {
            this.tileContainerEl.mousewheel( this.onMouseWheel );
            this.tileContainerEl.bind( 'mouseover', function(e) {
                $('body').css('overflow','hidden');
            });
            this.tileContainerEl.bind( 'mouseout', function(e) {
                $('body').css('overflow','auto');
            });
        }
    },
    this.loadCopyRight = function() {
        if ( this.copyRightControl && this.isImageLoaded ) {
            this.tileContainerEl.prepend( '<div class="imagezoom-copyright"></div>' );
            this.copyrightDiv = this.tileContainerEl.children('div:first');
            this.updateCopyright( this.copyrightText );
        }
    },
    this.loadZoomBox = function() {
        if ( this.showZoomBox && this.isImageLoaded ) {
                this.tileContainerEl.prepend( '<div class="imagezoom-zoombox"></div>' );
                this.zoomBoxTag = this.tileContainerEl.children('.imagezoom-zoombox:first');
                this.zoomBoxTag.prepend( '<div class="zoomboxCornor" style="width: 8px; opacity: 0; filter:(opacity=0); height: 6px; line-height: 1px; font-size: 1px; position: absolute; border-width: 0px 0px 2px 2px; border-style: none none solid solid; border-color: red; left: 0px; top: 67px;"></div>' );
                this.zoomBoxBottomLeft = this.zoomBoxTag.children('div:first');
                this.zoomBoxTag.prepend( '<div class="zoomboxCornor" style="width: 8px; opacity: 0; filter:(opacity=0); height: 6px; line-height: 1px; font-size: 1px; position: absolute; border-width: 0px 2px 2px 0px; border-style: none solid solid none; border-color: red; left: 100px; top: 67px;"></div>' );
                this.zoomBoxBottomRight = this.zoomBoxTag.children('div:first');
                this.zoomBoxTag.prepend( '<div class="zoomboxCornor" style="width: 8px; opacity: 0; filter:(opacity=0); height: 6px; line-height: 1px; font-size: 1px; position: absolute; border-width: 2px 2px 0px 0px; border-style: solid solid none none; border-color: red; left: 100px; top: 0px;"></div>' );
                this.zoomBoxTopRight = this.zoomBoxTag.children('div:first');
                this.zoomBoxTag.prepend( '<div class="zoomboxCornor" style="width: 8px; opacity: 0; filter:(opacity=0); height: 6px; line-height: 1px; font-size: 1px; position: absolute; border-width: 2px 0px 0px 2px; border-style: solid none none solid; border-color: red; left: 0px; top: 0px;"></div>' );
                this.zoomBoxTopLeft = this.zoomBoxTag.children('div:first');
        }
    },
    this.loadImage = function( url, params ) {
        // put loading message here
        this.fire('beforeImageLoad', this);
        this.resetImage();
        $.ajax({
            url: url,
            dataType: 'jsonp',
            success: this.loadAllControls
        });
    },
    this.loadAllControls = function( o ) {
        if ( o.success ) {
            if ( me.currentZoomLevel > o.maxZoomLevel ) me.currentZoomLevel = o.maxZoomLevel;
            if ( me.isImageLoaded ) {
                me.resetImage();
            }
            me.zoomMax = o.maxZoomLevel;
            me.tileTpl = o.tpl;
            me.tileTpl = me.tileTpl.replace( '{z}', '{2}' ).replace( '{i}', '{3}' );
            me.isImageLoaded = true;
            me.zoomTile( me.currentZoomLevel );
            me.loadZoomControl();
            me.loadPanControl();
            me.loadCopyRight();
            me.updateCopyright(o.copyright);
            me.loadZoomBox();
            me.setCenterTile();
            me.fire( 'afterImageLoad' );
        } else {
            me.fire( 'loaderror' );
        }
    },
    this.loadPanControl = function() {
        var me = this;
        if ( this.panControl && this.isImageLoaded ) {
            this.tileContainerEl.prepend( '<map name="panmap"></map>' );
            this.mapTag = this.tileContainerEl.children('map:first');
            this.mapTag.prepend( '<area shape="rect" coords="40,20,55,38" title="Pan Right">' );
            this.eastPan = this.mapTag.children('area:first');
            this.mapTag.prepend( '<area shape="rect" coords="5,20,20,38" title="Pan Left">' );
            this.westPan = this.mapTag.children('area:first');
            this.mapTag.prepend( '<area shape="rect" coords="18,20,40,38" title="Center Image">' );
            this.centerPan = this.mapTag.children('area:first');
            this.mapTag.prepend( '<area shape="rect" coords="20,5,38,20" title="Pan Up">' );
            this.northPan = this.mapTag.children('area:first');
            this.mapTag.prepend( '<area shape="rect" coords="20,35,38,55" title="Pan Down">' );
            this.southPan = this.mapTag.children('area:first');

            this.eastPan.click( function() {
                me.moveDragContainer(50, 0);
            });
            this.northPan.click( function() {
                me.moveDragContainer(0, -50);
            });
            this.centerPan.click( function() {
                me.setCenterTile();
            });
            this.southPan.click( function() {
                me.moveDragContainer(0, 50);
            });
            this.westPan.click( function() {
                me.moveDragContainer(-50, 0);
            });
            this.tileContainerEl.prepend( '<img src="resources/img/pan-control.gif" class="imagezoom-pancontrol" usemap="#panmap">' );
            this.pancontrol = this.tileContainerEl.children('img:first');
        }
    },
    this.loadZoomControl = function() {
        var me = this;
        if ( this.zoomControl && this.isImageLoaded ) {
            var plusImage = 'resources/img/plus.png';
            this.zoomControlContainer.prepend( '<img src="resources/img/plus.png" class="imagezoom-zoomplus" title="Zoom In">' );
            this.zoomPlus = this.zoomControlContainer.children('img:first');
            // insert zoom slider here (zoomContainer)
            this.zoomControlContainer.prepend( '<img src="resources/img/minus.png" class="imagezoom-zoomplus" title="Zoom Out">' );
            this.zoomMinus = this.zoomControlContainer.children('img:first');
            this.zoomPlus.click( function(e, ele) {
                if (me.currentZoomLevel < me.zoomMax) {
                        me.currentZoomLevel++;
                        if (me.zoomControl) {
                            //me.zoomContainer.setValue(me.currentZoomLevel);
                            me.zoomCenter(true);
                        }
                }
            });
            this.zoomMinus.click( function(e, ele) {
                if (me.currentZoomLevel > me.zoomMin) {
                    me.currentZoomLevel--;
                    if (me.zoomControl) {
                        //me.zoomContainer.setValue(me.currentZoomLevel);
                        me.zoomCenter(false);
                    }
                }
            });
        }
    },
    this.onMouseWheel = function(e, delta) {
        e.preventDefault();
        if (delta > 0) {
            if(me.currentZoomLevel < me.zoomMax)
                me.zoom(e, true);
        } else if (delta < 0) {
            if(me.currentZoomLevel > me.zoomMin)
                me.zoom(e, false);
        }
    },
    this.resetImage = function() {
        if ( this.panControl && this.pancontrol != null ) {
            this.pancontrol.remove();
            this.mapTag.remove();
        }
        if ( this.showZoomBox && this.zoomBoxTag != null ) {
                this.zoomBoxTag.remove();
        }
        if ( this.zoomControl && this.zoomContainer && this.zoomContainer.el != null ) {
            this.zoomPlus.remove();
            this.zoomMinus.remove();
            this.zoomContainer.remove();
        }
        if ( this.copyRightControl && this.copyrightDiv != null ) {
            this.copyrightDiv.remove();
        }
        for (var i=0; i < this.tileLayers.length; i++) {
            if ( this.tileLayers[i] ) {
                this.tileLayers[i].removeTiles();
                this.tileLayers[i].el.remove();
            }
            this.tileLayers.splice(i,1);
        }
        
        this.resetObject();
        this.fire('afterResetImage', this);
    },
    this.resetObject = function() {
        this.pancontrol = '';
        this.zoomContainer = '';
        this.rototeContainer = '';
        this.copyrightDiv = '';
        this.tileLayers = [];
        this.mapTag = '';
        this.rototeDiv = '';
        this.zoomPlus = '';
        this.zoomMinus = '';
        this.zoomBoxTag = '';
        this.isImageLoaded = false;
    },
    this.setBackgroundColor = function( color ) {
        this.el.css('background-color', color);
    },
    this.setCenterTile = function() {
        var x = (this.el.width()/2)-(this.dragEl.width()/2);
        var y = (this.el.height()/2)-(this.dragEl.height()/2);
        this.dragEl.css('top', y );
        this.dragEl.css('left', x );
        this.getTileByOffset();
    },
    this.moveDragContainer = function( x, y ) {
        var xy = [this.dragEl.position().left,this.dragEl.position().top];
        this.dragEl.css('left',(xy[0] - x));
        this.dragEl.css('top',(xy[1] - y));
    },
    this.updateCopyright = function(text) {
        if (this.copyRightControl && this.isImageLoaded){
            this.copyrightDiv.text( text );
            this.copyrightText = text;
        }
    },
    this.zoomCenter = function(isZoomIn) {
        if(this.isImageLoaded){
            var tileContainerElXY = [ this.tileContainerEl.position().left, this.tileContainerEl.position().top ];
            var tileContainerHeight = this.tileContainerEl.height();
            var tileContainerWidth = this.tileContainerEl.width();
            var x = parseFloat(tileContainerWidth/2) + tileContainerElXY[0];
            var y = parseFloat(tileContainerHeight/2) + tileContainerElXY[1];
            var xy = [x, y]
            this.zoomBox(xy, isZoomIn);
            var dragXY = [ this.dragEl.position().left, this.dragEl.position().top ];
            var beforeImageSize = this.imageSize;
            var offsetX = Math.abs(xy[0] - dragXY[0]);
            var offsetY = Math.abs(xy[1] - dragXY[1]);
            this.zoomTile(this.currentZoomLevel);
            var afterImageSize = this.imageSize;
            var newTileCount = afterImageSize / beforeImageSize;
            var newOffsetX = xy[0] - Math.ceil(offsetX * newTileCount);
            var newOffsetY = xy[1] - Math.ceil(offsetY * newTileCount);
            this.dragEl.css('left',newOffsetX);
            this.dragEl.css('top',newOffsetY);
            this.getTileByOffset();
            this.fire('zoomChange', this.currentZoomLevel, this);
        }	
    },
    this.zoom = function(e, flag) {
        if(this.isImageLoaded){
            var xy = [ e.clientX, e.clientY ];
            var dragXY = [ this.dragEl.position().left, this.dragEl.position().top ];
            var beforeImageSize = this.imageSize;
            var offsetX = Math.abs(xy[0] - dragXY[0]);
            var offsetY = Math.abs(xy[1] - dragXY[1]);
            if ( flag ) {
                this.zoomIn(e);
            } else {
                this.zoomOut(e);
            }
            var afterImageSize = this.imageSize;
            var newTileCount = afterImageSize / beforeImageSize;
            var newOffsetX = xy[0] - Math.ceil(offsetX * newTileCount);
            var newOffsetY = xy[1] - Math.ceil(offsetY * newTileCount);
            this.dragEl.css( 'left', newOffsetX );
            this.dragEl.css( 'top', newOffsetY );
            this.getTileByOffset();
            this.fire('zoomChange', this.currentZoomLevel, this);
        }
    },
    this.zoomBox = function( xy, flag ) {
        var me = this;
        if ( this.showZoomBox && this.isImageLoaded ){
            this.zoomBoxTag.height(74);
            this.zoomBoxTag.width(111);
            this.zoomBoxTag.children('div').fadeIn('fast',function(){ me.zoomBoxTag.children('div').fadeOut('fast'); });
            var zoomboxX = xy[0] - this.zoomBoxTag.width() / 2;
            var zoomboxY = xy[1] - this.zoomBoxTag.height() / 2;
            var currentXY = [zoomboxX, zoomboxY];
            this.zoomBoxTag.css('left', currentXY.x );
            this.zoomBoxTag.css('top', currentXY.y );
        }
    },
    this.zoomIn = function(e) {
        if ( this.isImageLoaded ) {
            if ( this.currentZoomLevel < this.zoomMax ) {
                this.currentZoomLevel++;
                if ( this.zoomControl ) {
                    //this.zoomContainer.setValue( this.currentZoomLevel );
                }
                this.zoomBox( [e.clientX, e.clientY], true );
                this.zoomTile( this.currentZoomLevel );
            }
        }
    },
    this.zoomOut = function(e) {
        if ( this.isImageLoaded ) {
            if ( this.currentZoomLevel > this.zoomMin ) {
                this.currentZoomLevel--;
                if ( this.zoomControl ) {
                    //this.zoomContainer.setValue(this.currentZoomLevel);
                }
                this.zoomBox( [e.clientX, e.clientY], false );
                this.zoomTile( this.currentZoomLevel );
            }
        }
    },
    this.zoomTile = function(zoomLevel) {
        if ( this.isImageLoaded ) {
            if ( !this.tileLayers[zoomLevel-1] ) {
                this.dragEl.append( '<div class="dragtile"></div>' );
                this.tileLayers[zoomLevel-1] =  new ImageZoomLayer({
                    parent: me,
                    el: this.dragEl.children('div:last'),
                    zoomLevel: zoomLevel,
                    tileTpl: this.tileTpl
                });
                this.currentLayer = this.tileLayers[zoomLevel-1];
                if ( this.previousLayer ) {
                    if ( this.preivousZoom > zoomLevel ) {
                        this.currentLayer.el.insertBefore( this.previousLayer.el );
                    } else if ( this.preivousZoom < zoomLevel ) {
                        this.currentLayer.el.insertAfter( this.previousLayer.el );
                    }
                }
            }
            this.previousLayer = this.tileLayers[this.preivousZoom-1];
            this.currentLayer = this.tileLayers[zoomLevel-1];
            if ( this.currentLayer ) {
                this.fire('layerChanged', this.currentLayer, this);
                this.imageSize = (Math.pow(zoomLevel, 2) * this.currentLayer.tileSize);
            }
            if ( this.preivousZoom != zoomLevel ) {
                if ( this.previousLayer ) {
                    this.previousLayer.el.addClass('hidden');
                    this.currentLayer.el.removeClass('hidden');
                }
            }
            this.dragEl.width( this.currentLayer.el.width() );
            this.getTileByOffset();
            this.preivousZoom = zoomLevel;
        }
    }

    this.el.html( '<div style="overflow: hidden; width: 100%; height: 100%; position: relative; left: 0px; top: 0px;"><div class="imagezoom-zoomcontrol"></div><div style="position: relative; left: 0px; top: 0px; z-index: 0;"></div></div>' );
    this.el.css('overflow', 'hidden');
    this.tileContainerEl = this.el.find('div').eq(0);
    this.dragEl = this.el.find('div').eq(2);
    this.zoomControlContainer = this.el.find('div').eq(1);
    this.dragEl.css('overflow', 'hidden');
    this.initMouseEvent();
    this.setBackgroundColor(this.backgroundColor);
    this.el.addClass('imagezoom-opencursor');

}

var ImageZoomLayer = function( config ) {
    // config
    $.extend(this, config);

    // init
    var me           =   this;
    this.tileSize    =   256;
    this.el.css({overflow:'hidden',position:'relative'});
    this.tiles       =   [];

    // dragging logic w/o using jQuery UI
    this.el.drag( function( ev, dd ) {
        $(this).parent().css({
            left: dd.offsetX,
            top: dd.offsetY
        });
        me.parent.getTileByOffset();
    });

    this.createTiles = function( zoomLevel ) {
        this.zoomLevel = zoomLevel;
        var tileCount = Math.pow(zoomLevel, 2);
        for (var i=1; i <= tileCount; i++) {
            this.tiles[i] = [];
            for (var j=1; j <= tileCount; j++) {
                this.el.append( '<img src="data:image/gif;base64,R0lGODlhAQABAID/AMDAwAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" class="tile" style="width:256px; height:256px;">' );
                this.tiles[i][j] = this.el.children('img:last');
            }
        }
        this.el.append( '<div style="clear:both"></div>' );
        this.lastDiv = this.el.children('div:last');
    },
    this.removeTiles = function() {
        var tileCount = Math.pow(this.zoomLevel, 2);
        for (var i=1; i <= tileCount; i++) {
            for (var j=1; j <= tileCount; j++) {
                this.tiles[i][j].remove();
            }
        }
        this.lastDiv.remove();
        this.tiles = [];
    },
    this.getTile = function( x, y ) {
        return this.tiles[x][y];
    },
    this.isBlankTile = function( x, y ) {
        return( (this.tiles[x][y].rendered) ? false : true );
    },
    this.setImage = function( x, y, url ) {
        if ( this.tiles[x] && this.tiles[x][y] && !this.tiles[x][y].rendered ) {
            this.tiles[x][y].attr('src', url);
            this.tiles[x][y].rendered = true;
        }
    },
    this.showTile = function( x, y ) {
        if ( this.tiles[x] && this.tiles[x][y] && !this.tiles[x][y].rendered ) {
            var tileCount = Math.pow(this.zoomLevel, 2);
            var imgName = ((x-1) * (tileCount)) + (y-1);
            var url = String.format(this.tileTpl, x, y, this.zoomLevel, imgName);
            this.setImage(x, y, url);
        }
    },
    this.showTilesByRange = function( topLeft, bottomRight ) {
        for (var x = topLeft.row; x <= bottomRight.row; x++) {
            for (var y = topLeft.column; y <= bottomRight.column; y++) {
                this.showTile(x,y);
            }
        }
    },
    this.showAllTiles = function() {
        var tileCount = Math.pow(this.zoomLevel, 2);
        for (var i=1; i <= tileCount; i++) {
            for (var j=1; j <= tileCount; j++) {
                var imgName = ((i-1) * (tileCount)) + (j-1);
                var url = String.format(this.tileTpl, i, j, this.zoomLevel, imgName);
                this.setImage(i, j, url);
            }
        }
    },
    this.hideTile = function( x, y ) {
        this.setImage(x, y, 'data:image/gif;base64,R0lGODlhAQABAID/AMDAwAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
    }

    this.tiles = [];
    this.createTiles( this.zoomLevel );
    this.el.width( (this.tileSize * (Math.pow(this.zoomLevel, 2))) + 2 );
};

// extensions
String.format = function() {
    var s = arguments[0];
    for (var i = 0; i < arguments.length - 1; i++) {       
        var reg = new RegExp("\\{" + i + "\\}", "gm");             
        s = s.replace(reg, arguments[i + 1]);
    }
    return s;
}

var observer = new EventTarget();
ImageZoom.prototype = observer;
ImageZoomLayer.prototype = observer;
