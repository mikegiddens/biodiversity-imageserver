Ext.define('ImageZoom', {
    extend: 'Ext.Component',
    alias: ['widget.imagezoom'],
    //requires: ['Ext.dd.DragTracker', 'Ext.util.KeyNav', 'BIS.view.ImageZoomTile'],
    initComponent: function() {
        var me = this;
        Ext.applyIf( me, {
            style: 'height: 100%',
            draggable: true,
            isMouseScroll: true,
            showZoomBox: true,
            currentZoomLevel: 1,
            zoomControl: true,
            rototeControl: false,
            copyRightControl: true,
            copyrightText: '',
            panControl: true,
            rototeMin: -180,
            rototeMax: 180,
            tileLayers: [],
            currentLayer: '',
            currentCircle: '',
            previousLayer: '' ,
            backgroundColor: '#EFEFEF',
            zoomMin: 1,
            zoomMax: 10,
            dblClickZoom: true,
            isImageLoaded: false,
            spaceHotkey: true,
            showMask: true,
            currentShape: '',
            listeners: {
                scope: this,
                'resize': function(){
                    this.getTileByOffset();
                },
                afterrender: function() {
                    this.el.applyStyles({overflow: 'hidden'});
                    this.tileContainerEl = this.el.select('div').item(0);
                    this.dragEl = this.el.select('div').item(2);
                    this.zoomControlContainer = this.el.select('div').item(1);
                    this.addCls('imagezoom-opencursor');
                    this.setBackgroundColor(this.backgroundColor);
                    this.dragEl.applyStyles({overflow: 'hidden'});
                    this.loadDragTracker();
                    this.initMouseEvent();
                },
                destroy: function() {
                    if (this.tracker && Ext.isDefined(this.tracker)) {
                        this.tracker.destroy();
                    }
                    for (var i= 0; i < this.tileLayers.length; i++) {
                        if (Ext.isDefined(this.tileLayers[i])) {
                            this.tileLayers[i].destroy();
                        }
                        this.tileLayers.splice(i,1);
                    }
                }
            },
            html: '<div style="overflow: hidden; width: 100%; height: 100%; position: relative; left: 0px; top: 0px;"><div class="imagezoom-zoomcontrol"></div><div style="position: relative; left: 0px; top: 0px; z-index: 0;"></div></div>'
        });
        me.callParent(arguments);
    },
    enableDblClickZoom: function(){
        this.dragEl.on('dblclick', function(e){
            if (this.currentShape != 'polygon' && this.currentShape != 'polyline') {
                if (this.currentZoomLevel < this.zoomMax) {
                    this.zoom(e, true);
                }
            }
        }, this);
    },
    getImageSize: function() {
        return (this.getTileCount() * this.currentLayer.tileSize);
    },
    getTileCount: function() {
        return Math.pow(this.currentZoomLevel, 2);
    },
    getTileByOffset: function() {
        if (this.isImageLoaded) {
            var offset = this.el.getOffsetsTo(this.dragEl);
            var i = Math.ceil(offset[1]/this.currentLayer.tileSize);
            var j = Math.ceil(offset[0]/this.currentLayer.tileSize);
            var rowTiles = Math.ceil(this.el.getHeight()/this.currentLayer.tileSize);
            var columnTiles = Math.ceil(this.el.getWidth()/this.currentLayer.tileSize);
            var totalTile = Math.floor(this.dragEl.getWidth()/this.currentLayer.tileSize);
            var bottomrightX, bottomrightY;
            var elXY = this.el.getXY();
            var bottomX = this.el.getWidth() + elXY[0];
            var bottomY = this.el.getHeight() + elXY[1];
            var xy = this.dragEl.getXY();
            var btmX = Math.ceil(Math.abs(xy[1]-bottomY)/this.currentLayer.tileSize);
            var btmY = Math.ceil(Math.abs(xy[0]-bottomX)/this.currentLayer.tileSize);
            btmX = (btmX > totalTile) ? totalTile : btmX;
            btmY = (btmY > totalTile) ? totalTile : btmY;
            btmX = (btmX < 1) ? 1 : btmX;
            btmY = (btmY < 1) ? 1 : btmY;
            i = (i <= 0) ? 1: i;
            j = (j <= 0) ? 1: j;
            if((totalTile < rowTiles) && (totalTile < columnTiles)){
                bottomrightX = i;
                bottomrightY = j;
            } else {
                bottomrightX = btmX;
                bottomrightY = btmY;
            }
            this.currentLayer.showTilesByRange({
                    row: i
                ,	column: j
            }, {
                    row: bottomrightX
                ,	column: bottomrightY
            });
        }
    },	
    initMouseEvent: function() {
        if ( this.dblClickZoom ) {
            this.enableDblClickZoom();
        }
        if (this.isMouseScroll) {
            this.mon(this.tileContainerEl, 'mousewheel', this.onMouseWheel, this);
            this.mon(this.tileContainerEl, 'mouseover', function(e) {
                Ext.getBody().applyStyles({overflow: 'hidden'})
            }, this);
            this.mon(this.tileContainerEl, 'mouseout', function(e) {
                Ext.getBody().applyStyles({overflow: 'auto'})
            }, this);
        }
    },
    loadCopyRight: function() {
        if(this.copyRightControl && this.isImageLoaded){
            this.copyrightDiv = Ext.DomHelper.insertFirst(this.tileContainerEl.dom, {tag: 'div', cls: 'imagezoom-copyright'});
            this.updateCopyright(this.copyrightText);
        }
    },
    loadZoomBox: function() {
        if(this.showZoomBox && this.isImageLoaded){
                this.zoomBoxTag = Ext.DomHelper.insertFirst(this.tileContainerEl.dom, {tag: 'div', cls: 'imagezoom-zoombox'});
                var zoomBoxBottomLeft = Ext.DomHelper.insertFirst(this.zoomBoxTag, {
                        tag: 'div'
                    //,	id: 'bottomLeft'
                    ,	cls: 'zoomboxCornor'
                    ,	style: "width: 8px; opacity: 0; filter:(opacity=0); height: 6px; line-height: 1px; font-size: 1px; position: absolute; border-width: 0px 0px 2px 2px; border-style: none none solid solid; border-color: red; left: 0px; top: 67px;"
                });
                var zoomBoxBottomRight = Ext.DomHelper.insertFirst(this.zoomBoxTag, {
                        tag: 'div'
                    //,	id: 'bottomRight'
                    ,	cls: 'zoomboxCornor'
                    ,	style: "width: 8px; opacity: 0; filter:(opacity=0); height: 6px; line-height: 1px; font-size: 1px; position: absolute; border-width: 0px 2px 2px 0px; border-style: none solid solid none; border-color: red; left: 100px; top: 67px;"
                });
                var zoomBoxTopRight = Ext.DomHelper.insertFirst(this.zoomBoxTag, {
                        tag: 'div'
                    //,	id: 'topRight'
                    ,	cls: 'zoomboxCornor'
                    ,	style: "width: 8px; opacity: 0; filter:(opacity=0); height: 6px; line-height: 1px; font-size: 1px; position: absolute; border-width: 2px 2px 0px 0px; border-style: solid solid none none; border-color: red; left: 100px; top: 0px;"
                });
                var zoomBoxTopLeft = Ext.DomHelper.insertFirst(this.zoomBoxTag, {
                        tag: 'div'
                    //,	id: 'topLeft'
                    ,	cls: 'zoomboxCornor'
                    ,	style: "width: 8px; opacity: 0; filter:(opacity=0); height: 6px; line-height: 1px; font-size: 1px; position: absolute; border-width: 2px 0px 0px 2px; border-style: solid none none solid; border-color: red; left: 0px; top: 0px;"
                });
        }
    },
    loadDragTracker: function() {
        this.tracker = new Ext.dd.DragTracker({
                    onStart: this.onDragStart
                ,	onDrag: this.onDrag
                ,	tolerance: 3
                ,	autoStart: 300
                ,	el: this.tileContainerEl
                ,	scope: this
                ,	listeners: {
                            mousedown: function(e) { this.addCls('imagezoom-closecursor'); }
                        ,	mouseup: function(e) { this.removeCls('imagezoom-closecursor'); }
                        ,	scope: this
                    }
            });
    },
    loadImage: function( url, params ) {
        if (this.showMask) {
            this.myMask = new Ext.LoadMask( this.el, {msg: 'Please wait...'} );
            this.myMask.show();
        }
        this.fireEvent('beforeImageLoad', this);
        this.resetImage();
        Ext.data.JsonP.request({
            url: url,
            scope: this,
            success: this.loadAllControls
        });
    },
    loadAllControls: function( o ) {
        if (o.success) {
            if (this.currentZoomLevel > o.maxZoomLevel) this.currentZoomLevel = o.maxZoomLevel;
            if (this.isImageLoaded) {
                this.resetImage();
            }
            this.zoomMax = o.maxZoomLevel;
            this.tileTpl = o.tpl;
            this.tileTpl = this.tileTpl.replace( '{z}', '{2}' ).replace( '{i}', '{3}' );
            //this.imageWidth = o.width;
            //this.imageHeight = o.height;
            this.isImageLoaded = true;
            this.zoomTile(this.currentZoomLevel);
            this.loadZoomControl();
            this.loadPanControl();
            this.loadRototeControl();
            this.loadCopyRight();
            this.updateCopyright(o.copyright);
            this.loadZoomBox();
            this.setCenterTile();
            this.fireEvent('afterImageLoad', this);	
            if (this.showMask) {
                this.myMask.hide();
            }
        } else {
            this.fireEvent( 'loaderror', this );
            if (this.showMask) {
                this.myMask.hide();
            }
        }
    },
    loadPanControl: function() {
        if(this.panControl && this.isImageLoaded){
            this.mapTag = Ext.DomHelper.insertFirst(this.tileContainerEl.dom, {tag: 'map', name: "panmap"});
            this.xtpl = Ext.DomHelper.createTemplate({
                    tag: 'area'
                ,	shape: "rect"
                ,	coords: '{0}'
                ,	title: '{1}'
            });
            this.eastPan = this.xtpl.insertFirst(this.mapTag, ["40,20,55,38", this.panRightText]);
            this.westPan = this.xtpl.insertFirst(this.mapTag, ["5,20,20,38", this.panLeftText]);
            this.centerPan = this.xtpl.insertFirst(this.mapTag, ["18,20,40,38", this.resetPanText]);
            this.northPan = this.xtpl.insertFirst(this.mapTag, ["20,5,38,20", this.panUpText]);
            this.southPan = this.xtpl.insertFirst(this.mapTag, ["20,35,38,55", this.panDownText]);
            Ext.fly(this.eastPan).on('click', function(){
                this.moveDragContainer(50, 0);
            }, this);
            Ext.fly(this.northPan).on('click', function(){
                this.moveDragContainer(0, -50);
            }, this);
            Ext.fly(this.centerPan).on('click', function(){
                this.setCenterTile();
                if(this.rototeControl){
                    this.rototeContainer.setValue(0);
                }
            }, this);
            Ext.fly(this.southPan).on('click', function(){
                this.moveDragContainer(0, 50);
            }, this);
            Ext.fly(this.westPan).on('click', function(){
                this.moveDragContainer(-50, 0);
            }, this);
            var panImage = 'resources/img/pan-control.gif';
            this.pancontrol = Ext.DomHelper.insertFirst(this.tileContainerEl.dom, {tag: 'img', src: panImage, cls: 'imagezoom-pancontrol', usemap:"#panmap"});
        }
    },
    loadRototeControl: function() {
        if(this.rototeControl && this.isImageLoaded){
            this.rototeDiv = Ext.DomHelper.insertFirst(this.tileContainerEl.dom, {tag: 'div', cls: 'imagezoom-rototecontrol'});
            this.rototeContainer = new Ext.slider.SingleSlider({
                    renderTo: this.rototeDiv
                ,	hideLabel: true
                ,	width: 200
                ,	useTips: false
                ,	increment: 10
                ,	minValue: this.rototeMin
                ,	maxValue: this.rototeMax
                ,	value: 0
                ,	listeners: {
                            change: function(slider, newValue, thumb){
                                var value = Ext.util.Format.format('rotate({0}deg)', newValue);
                                this.dragEl.applyStyles({
                                        '-webkit-transform': value
                                    ,	'-moz-transform': value
                                });
                            }
                        ,	scope: this
                    }
            });				
        }
    },
    loadZoomControl: function() {
        if(this.zoomControl && this.isImageLoaded){
            var plusImage = 'resources/img/plus.png';
            this.zoomPlus = Ext.DomHelper.insertFirst(this.zoomControlContainer, {tag: 'img', src: plusImage, cls: 'imagezoom-zoomplus', title: this.zoomPlusText});
            var minusImage = 'resources/img/minus.png';
            this.zoomContainer = new Ext.slider.SingleSlider({
                    renderTo: this.zoomControlContainer
                ,	hideLabel: true
                ,	height: 20 * (this.zoomMax - this.zoomMin)
                ,	vertical: true
                ,	useTips: false
                ,	increment: 1
                ,	minValue: this.zoomMin
                ,	maxValue: this.zoomMax
                ,	value: this.currentZoomLevel
                ,	listeners: {
                            changecomplete: function(slider, newValue, thumb){
                                var isZoomIn = (this.currentZoomLevel < newValue)? true : false;
                                this.currentZoomLevel = newValue;
                                this.zoomCenter(isZoomIn);
                            }
                        ,	scope: this
                    }
            });
            this.zoomMinus = Ext.DomHelper.append(this.zoomControlContainer, {tag: 'img', src: minusImage, cls: 'imagezoom-zoomminus', title: this.zoomMinusText});
            Ext.fly(this.zoomPlus).on('click', function(e, ele){
                if(this.currentZoomLevel < this.zoomMax){
                        this.currentZoomLevel++;
                        if(this.zoomControl){
                            this.zoomContainer.setValue(this.currentZoomLevel);
                            this.zoomCenter(true);
                        }
                }
            }, this);
            Ext.fly(this.zoomMinus).on('click', function(e, ele){
                if(this.currentZoomLevel > this.zoomMin){
                    this.currentZoomLevel--;
                    if(this.zoomControl){
                        this.zoomContainer.setValue(this.currentZoomLevel);
                        this.zoomCenter(false);
                    }
                }
            }, this);
        }
    },
    onDrag: function(e) {
        var xy = e.getXY();
        this.scope.dragEl.setXY([this.cXY[0] - (this.currentPosition[0]-xy[0]), this.cXY[1] - (this.currentPosition[1]-xy[1])]);
        this.scope.getTileByOffset();
    },
    onDragEnd: function(e) {
        this.currentPosition = e.getXY();
    },
    onDragStart: function(e) {
        this.currentPosition = e.getXY();
        this.cXY = this.scope.dragEl.getXY();
    },
    onMouseWheel: function(e, el) {
        var delta = e.getWheelDelta();
        e.preventDefault();
        if (delta > 0) {
            if(this.currentZoomLevel < this.zoomMax)
                this.zoom(e, true);
        } else if (delta < 0) {
            if(this.currentZoomLevel > this.zoomMin)
                this.zoom(e, false);
        }
    },
    resetImage: function() {
        if(this.panControl && Ext.fly(this.pancontrol) != null){
                Ext.fly(this.pancontrol).remove();
                Ext.fly(this.mapTag).remove();
        }
        if(this.showZoomBox && Ext.fly(this.zoomBoxTag) != null){
                Ext.fly(this.zoomBoxTag).remove();
        }
        if(this.zoomControl && Ext.isDefined(this.zoomContainer) && this.zoomContainer.el != null){
            Ext.fly(this.zoomPlus).remove();
            Ext.fly(this.zoomMinus).remove();
            this.zoomContainer.el.remove();
        }
        
        if(this.rototeControl && Ext.fly(this.rototeDiv) != null){
            this.rototeContainer.el.remove();
            Ext.fly(this.rototeDiv).remove();
            this.dragEl.applyStyles({
                    '-webkit-transform': 'rotate(0deg)'
                ,	'-moz-transform': 'rotate(0deg)'
            });
        }
        if(this.copyRightControl && Ext.fly(this.copyrightDiv) != null)
            Ext.fly(this.copyrightDiv).remove();
        
        for(var i=0; i < this.tileLayers.length; i++){

            if(Ext.isDefined(this.tileLayers[i])){
                this.tileLayers[i].removeTiles();
                this.tileLayers[i].el.remove();
            }
            this.tileLayers.splice(i,1);
        }
        
        this.resetObject();
        this.fireEvent('afterResetImage', this);
    },
    resetObject: function() {
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
    setBackgroundColor: function( color ) {
        this.el.applyStyles({backgroundColor: color});
    },
    setCenterTile: function() {
        var x = (this.el.getWidth()/2)-(this.dragEl.getWidth()/2);
        var y = (this.el.getHeight()/2)-(this.dragEl.getHeight()/2);
        this.dragEl.setTop( y );
        this.dragEl.setLeft( x );
        this.getTileByOffset();
    },
    moveDragContainer: function( x, y ) {
        var xy = this.dragEl.getXY();
        this.dragEl.setX(xy[0] - x);
        this.dragEl.setY(xy[1] - y);
    },
    updateCopyright: function(text) {
        if (this.copyRightControl && this.isImageLoaded){
            Ext.fly(this.copyrightDiv).update(text);
            this.copyrightText = text;
        }
    },
    zoomCenter: function(isZoomIn) {
        if(this.isImageLoaded){
            var tileContainerElXY = this.tileContainerEl.getXY();
            var tileContainerHeight = this.tileContainerEl.getHeight();
            var tileContainerWidth = this.tileContainerEl.getWidth();
            var x = parseFloat(tileContainerWidth/2) + tileContainerElXY[0];
            var y = parseFloat(tileContainerHeight/2) + tileContainerElXY[1];
            var xy = [x, y]
            this.zoomBox(xy, isZoomIn);
            var dragXY = this.dragEl.getXY();
            var beforeImageSize = this.imageSize;
            var offsetX = Math.abs(xy[0] - dragXY[0]);
            var offsetY = Math.abs(xy[1] - dragXY[1]);
            this.zoomTile(this.currentZoomLevel);
            var afterImageSize = this.imageSize;
            var newTileCount = afterImageSize / beforeImageSize;
            var newOffsetX = xy[0] - Math.ceil(offsetX * newTileCount);
            var newOffsetY = xy[1] - Math.ceil(offsetY * newTileCount);
            this.dragEl.setXY([newOffsetX, newOffsetY]);
            this.getTileByOffset();
            this.fireEvent('zoomChange', this.currentZoomLevel, this);
        }	
    },
    zoom: function(e, flag) {
			if(this.isImageLoaded){
				var xy = e.getXY();
				var dragXY = this.dragEl.getXY();
				var beforeImageSize = this.imageSize;
				var offsetX = Math.abs(xy[0] - dragXY[0]);
				var offsetY = Math.abs(xy[1] - dragXY[1]);
				if(flag){
						this.zoomIn(e);
				}else {
						this.zoomOut(e);
				}
				var afterImageSize = this.imageSize;
				var newTileCount = afterImageSize / beforeImageSize;
				var newOffsetX = xy[0] - Math.ceil(offsetX * newTileCount);
				var newOffsetY = xy[1] - Math.ceil(offsetY * newTileCount);
				this.dragEl.setXY([newOffsetX, newOffsetY]);
				this.getTileByOffset();
				this.fireEvent('zoomChange', this.currentZoomLevel, this);
			}
    },
    zoomBox: function(xy, flag) {
        if(this.showZoomBox && this.isImageLoaded){
            var zoomboxEl = Ext.fly(this.zoomBoxTag);
            zoomboxEl.setHeight(74);
            zoomboxEl.setWidth(111);
            Ext.each( zoomboxEl.select('div').elements, function( el ) { Ext.get(el).fadeIn().fadeOut() } );
            //Ext.get('topLeft').fadeIn().fadeOut();
            //Ext.get('topRight').fadeIn().fadeOut();
            //Ext.get('bottomLeft').fadeIn().fadeOut();
            //Ext.get('bottomRight').fadeIn().fadeOut();
            var zoomboxX = xy[0]-zoomboxEl.getWidth()/2;
            var zoomboxY = xy[1]-zoomboxEl.getHeight()/2;
            var currentXY = [zoomboxX, zoomboxY];
            Ext.get(this.zoomBoxTag).setXY(currentXY);
            //zoomboxEl.setHeight(0);
            //zoomboxEl.setWidth(0);
        }
    },
    zoomIn: function(e) {
        if(this.isImageLoaded){
            if(this.currentZoomLevel < this.zoomMax){
                this.currentZoomLevel++;
                if(this.zoomControl){
                    this.zoomContainer.setValue(this.currentZoomLevel);
                }
                this.zoomBox(e.getXY(), true);
                this.zoomTile(this.currentZoomLevel);
            }
        }
    },
    zoomOut: function(e) {
        if(this.isImageLoaded){
            if(this.currentZoomLevel > this.zoomMin){
                this.currentZoomLevel--;
                if(this.zoomControl){
                    this.zoomContainer.setValue(this.currentZoomLevel);
                }
                this.zoomBox(e.getXY(), false);
                this.zoomTile(this.currentZoomLevel);
            }
        }
    },
    zoomTile: function(zoomLevel) {
        if(this.isImageLoaded){
            if(!Ext.isDefined(this.tileLayers[zoomLevel-1])){
                this.tileLayers[zoomLevel-1] = Ext.create('ImageZoomLayer',{
                        renderTo: this.dragEl
                    ,	zoomLevel: zoomLevel
                    ,	tileTpl: this.tileTpl
                });
                this.currentLayer = this.tileLayers[zoomLevel-1];
                if(Ext.isDefined(this.previousLayer)){
                    if(this.preivousZoom > zoomLevel){
                        this.currentLayer.el.insertBefore(this.previousLayer.el.dom);
                    } else if(this.preivousZoom < zoomLevel){
                        this.currentLayer.el.insertAfter(this.previousLayer.el.dom);
                    }
                }
            }
            this.previousLayer = this.tileLayers[this.preivousZoom-1];
            this.currentLayer = this.tileLayers[zoomLevel-1];
            if(Ext.isDefined(this.currentLayer)){
                this.fireEvent('layerChanged', this.currentLayer, this);
                this.imageSize = (Math.pow(zoomLevel, 2) * this.currentLayer.tileSize);
            }
            if(this.preivousZoom != zoomLevel){
                if(Ext.isDefined(this.previousLayer)){
                    this.previousLayer.el.addCls('hidden');
                    this.currentLayer.el.removeCls('hidden');
                }
            }
            this.dragEl.setWidth(this.currentLayer.el.getWidth());
            this.getTileByOffset();
            this.preivousZoom = zoomLevel;
        }
    }	
});
