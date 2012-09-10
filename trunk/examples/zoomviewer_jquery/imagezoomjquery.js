/* ========================
 *  THIRD PARTY LIBRARIES
========================= */

/*! Copyright (c) 2011 Brandon Aaron (http://brandonaaron.net)
 * Licensed under the MIT License (LICENSE.txt).
 *
 * Thanks to: http://adomas.org/javascript-mouse-wheel/ for some pointers.
 * Thanks to: Mathias Bank(http://www.mathias-bank.de) for a scope bug fix.
 * Thanks to: Seamus Leahy for adding deltaX and deltaY
 *
 * Version: 3.0.6
 * 
 * Requires: 1.2.2+
 */

(function($) {

var types = ['DOMMouseScroll', 'mousewheel'];

if ($.event.fixHooks) {
    for ( var i=types.length; i; ) {
        $.event.fixHooks[ types[--i] ] = $.event.mouseHooks;
    }
}

$.event.special.mousewheel = {
    setup: function() {
        if ( this.addEventListener ) {
            for ( var i=types.length; i; ) {
                this.addEventListener( types[--i], handler, false );
            }
        } else {
            this.onmousewheel = handler;
        }
    },
    
    teardown: function() {
        if ( this.removeEventListener ) {
            for ( var i=types.length; i; ) {
                this.removeEventListener( types[--i], handler, false );
            }
        } else {
            this.onmousewheel = null;
        }
    }
};

$.fn.extend({
    mousewheel: function(fn) {
        return fn ? this.bind("mousewheel", fn) : this.trigger("mousewheel");
    },
    
    unmousewheel: function(fn) {
        return this.unbind("mousewheel", fn);
    }
});


function handler(event) {
    var orgEvent = event || window.event, args = [].slice.call( arguments, 1 ), delta = 0, returnValue = true, deltaX = 0, deltaY = 0;
    event = $.event.fix(orgEvent);
    event.type = "mousewheel";
    
    // Old school scrollwheel delta
    if ( orgEvent.wheelDelta ) { delta = orgEvent.wheelDelta/120; }
    if ( orgEvent.detail     ) { delta = -orgEvent.detail/3; }
    
    // New school multidimensional scroll (touchpads) deltas
    deltaY = delta;
    
    // Gecko
    if ( orgEvent.axis !== undefined && orgEvent.axis === orgEvent.HORIZONTAL_AXIS ) {
        deltaY = 0;
        deltaX = -1*delta;
    }
    
    // Webkit
    if ( orgEvent.wheelDeltaY !== undefined ) { deltaY = orgEvent.wheelDeltaY/120; }
    if ( orgEvent.wheelDeltaX !== undefined ) { deltaX = -1*orgEvent.wheelDeltaX/120; }
    
    // Add event and delta to the front of the arguments
    args.unshift(event, delta, deltaX, deltaY);
    
    return ($.event.dispatch || $.event.handle).apply(this, args);
}

})(jQuery);





/*! 
 * jquery.event.drag - v 2.2
 * Copyright (c) 2010 Three Dub Media - http://threedubmedia.com
 * Open Source MIT License - http://threedubmedia.com/code/license
 */
// Created: 2008-06-04 
// Updated: 2012-05-21
// REQUIRES: jquery 1.7.x

;(function( $ ){

// add the jquery instance method
$.fn.drag = function( str, arg, opts ){
	// figure out the event type
	var type = typeof str == "string" ? str : "",
	// figure out the event handler...
	fn = $.isFunction( str ) ? str : $.isFunction( arg ) ? arg : null;
	// fix the event type
	if ( type.indexOf("drag") !== 0 ) 
		type = "drag"+ type;
	// were options passed
	opts = ( str == fn ? arg : opts ) || {};
	// trigger or bind event handler
	return fn ? this.bind( type, opts, fn ) : this.trigger( type );
};

// local refs (increase compression)
var $event = $.event, 
$special = $event.special,
// configure the drag special event 
drag = $special.drag = {
	
	// these are the default settings
	defaults: {
		which: 1, // mouse button pressed to start drag sequence
		distance: 0, // distance dragged before dragstart
		not: ':input', // selector to suppress dragging on target elements
		handle: null, // selector to match handle target elements
		relative: false, // true to use "position", false to use "offset"
		drop: true, // false to suppress drop events, true or selector to allow
		click: false // false to suppress click events after dragend (no proxy)
	},
	
	// the key name for stored drag data
	datakey: "dragdata",
	
	// prevent bubbling for better performance
	noBubble: true,
	
	// count bound related events
	add: function( obj ){ 
		// read the interaction data
		var data = $.data( this, drag.datakey ),
		// read any passed options 
		opts = obj.data || {};
		// count another realted event
		data.related += 1;
		// extend data options bound with this event
		// don't iterate "opts" in case it is a node 
		$.each( drag.defaults, function( key, def ){
			if ( opts[ key ] !== undefined )
				data[ key ] = opts[ key ];
		});
	},
	
	// forget unbound related events
	remove: function(){
		$.data( this, drag.datakey ).related -= 1;
	},
	
	// configure interaction, capture settings
	setup: function(){
		// check for related events
		if ( $.data( this, drag.datakey ) ) 
			return;
		// initialize the drag data with copied defaults
		var data = $.extend({ related:0 }, drag.defaults );
		// store the interaction data
		$.data( this, drag.datakey, data );
		// bind the mousedown event, which starts drag interactions
		$event.add( this, "touchstart mousedown", drag.init, data );
		// prevent image dragging in IE...
		if ( this.attachEvent ) 
			this.attachEvent("ondragstart", drag.dontstart ); 
	},
	
	// destroy configured interaction
	teardown: function(){
		var data = $.data( this, drag.datakey ) || {};
		// check for related events
		if ( data.related ) 
			return;
		// remove the stored data
		$.removeData( this, drag.datakey );
		// remove the mousedown event
		$event.remove( this, "touchstart mousedown", drag.init );
		// enable text selection
		drag.textselect( true ); 
		// un-prevent image dragging in IE...
		if ( this.detachEvent ) 
			this.detachEvent("ondragstart", drag.dontstart ); 
	},
		
	// initialize the interaction
	init: function( event ){ 
		// sorry, only one touch at a time
		if ( drag.touched ) 
			return;
		// the drag/drop interaction data
		var dd = event.data, results;
		// check the which directive
		if ( event.which != 0 && dd.which > 0 && event.which != dd.which ) 
			return; 
		// check for suppressed selector
		if ( $( event.target ).is( dd.not ) ) 
			return;
		// check for handle selector
		if ( dd.handle && !$( event.target ).closest( dd.handle, event.currentTarget ).length ) 
			return;

		drag.touched = event.type == 'touchstart' ? this : null;
		dd.propagates = 1;
		dd.mousedown = this;
		dd.interactions = [ drag.interaction( this, dd ) ];
		dd.target = event.target;
		dd.pageX = event.pageX;
		dd.pageY = event.pageY;
		dd.dragging = null;
		// handle draginit event... 
		results = drag.hijack( event, "draginit", dd );
		// early cancel
		if ( !dd.propagates )
			return;
		// flatten the result set
		results = drag.flatten( results );
		// insert new interaction elements
		if ( results && results.length ){
			dd.interactions = [];
			$.each( results, function(){
				dd.interactions.push( drag.interaction( this, dd ) );
			});
		}
		// remember how many interactions are propagating
		dd.propagates = dd.interactions.length;
		// locate and init the drop targets
		if ( dd.drop !== false && $special.drop ) 
			$special.drop.handler( event, dd );
		// disable text selection
		drag.textselect( false ); 
		// bind additional events...
		if ( drag.touched )
			$event.add( drag.touched, "touchmove touchend", drag.handler, dd );
		else 
			$event.add( document, "mousemove mouseup", drag.handler, dd );
		// helps prevent text selection or scrolling
		if ( !drag.touched || dd.live )
			return false;
	},	
	
	// returns an interaction object
	interaction: function( elem, dd ){
		var offset = $( elem )[ dd.relative ? "position" : "offset" ]() || { top:0, left:0 };
		return {
			drag: elem, 
			callback: new drag.callback(), 
			droppable: [],
			offset: offset
		};
	},
	
	// handle drag-releatd DOM events
	handler: function( event ){ 
		// read the data before hijacking anything
		var dd = event.data;	
		// handle various events
		switch ( event.type ){
			// mousemove, check distance, start dragging
			case !dd.dragging && 'touchmove': 
				event.preventDefault();
			case !dd.dragging && 'mousemove':
				//  drag tolerance, x² + y² = distance²
				if ( Math.pow(  event.pageX-dd.pageX, 2 ) + Math.pow(  event.pageY-dd.pageY, 2 ) < Math.pow( dd.distance, 2 ) ) 
					break; // distance tolerance not reached
				event.target = dd.target; // force target from "mousedown" event (fix distance issue)
				drag.hijack( event, "dragstart", dd ); // trigger "dragstart"
				if ( dd.propagates ) // "dragstart" not rejected
					dd.dragging = true; // activate interaction
			// mousemove, dragging
			case 'touchmove':
				event.preventDefault();
			case 'mousemove':
				if ( dd.dragging ){
					// trigger "drag"		
					drag.hijack( event, "drag", dd );
					if ( dd.propagates ){
						// manage drop events
						if ( dd.drop !== false && $special.drop )
							$special.drop.handler( event, dd ); // "dropstart", "dropend"							
						break; // "drag" not rejected, stop		
					}
					event.type = "mouseup"; // helps "drop" handler behave
				}
			// mouseup, stop dragging
			case 'touchend': 
			case 'mouseup': 
			default:
				if ( drag.touched )
					$event.remove( drag.touched, "touchmove touchend", drag.handler ); // remove touch events
				else 
					$event.remove( document, "mousemove mouseup", drag.handler ); // remove page events	
				if ( dd.dragging ){
					if ( dd.drop !== false && $special.drop )
						$special.drop.handler( event, dd ); // "drop"
					drag.hijack( event, "dragend", dd ); // trigger "dragend"	
				}
				drag.textselect( true ); // enable text selection
				// if suppressing click events...
				if ( dd.click === false && dd.dragging )
					$.data( dd.mousedown, "suppress.click", new Date().getTime() + 5 );
				dd.dragging = drag.touched = false; // deactivate element	
				break;
		}
	},
		
	// re-use event object for custom events
	hijack: function( event, type, dd, x, elem ){
		// not configured
		if ( !dd ) 
			return;
		// remember the original event and type
		var orig = { event:event.originalEvent, type:event.type },
		// is the event drag related or drog related?
		mode = type.indexOf("drop") ? "drag" : "drop",
		// iteration vars
		result, i = x || 0, ia, $elems, callback,
		len = !isNaN( x ) ? x : dd.interactions.length;
		// modify the event type
		event.type = type;
		// remove the original event
		event.originalEvent = null;
		// initialize the results
		dd.results = [];
		// handle each interacted element
		do if ( ia = dd.interactions[ i ] ){
			// validate the interaction
			if ( type !== "dragend" && ia.cancelled )
				continue;
			// set the dragdrop properties on the event object
			callback = drag.properties( event, dd, ia );
			// prepare for more results
			ia.results = [];
			// handle each element
			$( elem || ia[ mode ] || dd.droppable ).each(function( p, subject ){
				// identify drag or drop targets individually
				callback.target = subject;
				// force propagtion of the custom event
				event.isPropagationStopped = function(){ return false; };
				// handle the event	
				result = subject ? $event.dispatch.call( subject, event, callback ) : null;
				// stop the drag interaction for this element
				if ( result === false ){
					if ( mode == "drag" ){
						ia.cancelled = true;
						dd.propagates -= 1;
					}
					if ( type == "drop" ){
						ia[ mode ][p] = null;
					}
				}
				// assign any dropinit elements
				else if ( type == "dropinit" )
					ia.droppable.push( drag.element( result ) || subject );
				// accept a returned proxy element 
				if ( type == "dragstart" )
					ia.proxy = $( drag.element( result ) || ia.drag )[0];
				// remember this result	
				ia.results.push( result );
				// forget the event result, for recycling
				delete event.result;
				// break on cancelled handler
				if ( type !== "dropinit" )
					return result;
			});	
			// flatten the results	
			dd.results[ i ] = drag.flatten( ia.results );	
			// accept a set of valid drop targets
			if ( type == "dropinit" )
				ia.droppable = drag.flatten( ia.droppable );
			// locate drop targets
			if ( type == "dragstart" && !ia.cancelled )
				callback.update(); 
		}
		while ( ++i < len )
		// restore the original event & type
		event.type = orig.type;
		event.originalEvent = orig.event;
		// return all handler results
		return drag.flatten( dd.results );
	},
		
	// extend the callback object with drag/drop properties...
	properties: function( event, dd, ia ){		
		var obj = ia.callback;
		// elements
		obj.drag = ia.drag;
		obj.proxy = ia.proxy || ia.drag;
		// starting mouse position
		obj.startX = dd.pageX;
		obj.startY = dd.pageY;
		// current distance dragged
		obj.deltaX = event.pageX - dd.pageX;
		obj.deltaY = event.pageY - dd.pageY;
		// original element position
		obj.originalX = ia.offset.left;
		obj.originalY = ia.offset.top;
		// adjusted element position
		obj.offsetX = obj.originalX + obj.deltaX; 
		obj.offsetY = obj.originalY + obj.deltaY;
		// assign the drop targets information
		obj.drop = drag.flatten( ( ia.drop || [] ).slice() );
		obj.available = drag.flatten( ( ia.droppable || [] ).slice() );
		return obj;	
	},
	
	// determine is the argument is an element or jquery instance
	element: function( arg ){
		if ( arg && ( arg.jquery || arg.nodeType == 1 ) )
			return arg;
	},
	
	// flatten nested jquery objects and arrays into a single dimension array
	flatten: function( arr ){
		return $.map( arr, function( member ){
			return member && member.jquery ? $.makeArray( member ) : 
				member && member.length ? drag.flatten( member ) : member;
		});
	},
	
	// toggles text selection attributes ON (true) or OFF (false)
	textselect: function( bool ){ 
		$( document )[ bool ? "unbind" : "bind" ]("selectstart", drag.dontstart )
			.css("MozUserSelect", bool ? "" : "none" );
		// .attr("unselectable", bool ? "off" : "on" )
		document.unselectable = bool ? "off" : "on"; 
	},
	
	// suppress "selectstart" and "ondragstart" events
	dontstart: function(){ 
		return false; 
	},
	
	// a callback instance contructor
	callback: function(){}
	
};

// callback methods
drag.callback.prototype = {
	update: function(){
		if ( $special.drop && this.available.length )
			$.each( this.available, function( i ){
				$special.drop.locate( this, i );
			});
	}
};

// patch $.event.$dispatch to allow suppressing clicks
var $dispatch = $event.dispatch;
$event.dispatch = function( event ){
	if ( $.data( this, "suppress."+ event.type ) - new Date().getTime() > 0 ){
		$.removeData( this, "suppress."+ event.type );
		return;
	}
	return $dispatch.apply( this, arguments );
};

// event fix hooks for touch events...
var touchHooks = 
$event.fixHooks.touchstart = 
$event.fixHooks.touchmove = 
$event.fixHooks.touchend =
$event.fixHooks.touchcancel = {
	props: "clientX clientY pageX pageY screenX screenY".split( " " ),
	filter: function( event, orig ) {
		if ( orig ){
			var touched = ( orig.touches && orig.touches[0] )
				|| ( orig.changedTouches && orig.changedTouches[0] )
				|| null; 
			// iOS webkit: touchstart, touchmove, touchend
			if ( touched ) 
				$.each( touchHooks.props, function( i, prop ){
					event[ prop ] = touched[ prop ];
				});
		}
		return event;
	}
};

// share the same special event configuration with related events...
$special.draginit = $special.dragstart = $special.dragend = drag;

})( jQuery );





//Copyright (c) 2010 Nicholas C. Zakas. All rights reserved.
//MIT License

function EventTarget(){
    this._listeners = {};
		this.on = this.addListener;  // alias for addListener
		this.un = this.removeListener;
}

EventTarget.prototype = {

    constructor: EventTarget,

    addListener: function(type, listener){
        if (typeof this._listeners[type] == "undefined"){
            this._listeners[type] = [];
        }

        this._listeners[type].push(listener);
    },

    fire: function(event, data, extra) {
				data = data || {};
        if (typeof event == "string"){
            event = { type: event };
        }
        if (!event.target){
            event.target = this;
        }

        if (!event.type){  //falsy
            throw new Error("Event object missing 'type' property.");
        }

        if (this._listeners[event.type] instanceof Array){
            var listeners = this._listeners[event.type];
            for (var i=0, len=listeners.length; i < len; i++){
                listeners[i].call(this, event, data, extra);
            }
        }
    },

    removeListener: function(type, listener){
        if (this._listeners[type] instanceof Array){
            var listeners = this._listeners[type];
            for (var i=0, len=listeners.length; i < len; i++){
                if (listeners[i] === listener){
                    listeners.splice(i, 1);
                    break;
                }
            }
        }
    }
};













var ImageZoom = function( config ) {
    var me = this;
    // config
    this.el = config.container;
    this.el ? this.el = $('#'+this.el) : this.el = $('body');

    $("<style type='text/css'>body{height:100%}.overlay{position:fixed;top:0;left:0;width:100%;height:100%;background-color:#000;opacity:.5;z-index:10000}.overlayMessage{position:fixed;top:50%;left:50%;z-index:10001;padding:10px;border-radius:10px;background-color:#fff}img{border:0;border-width:0;background-color:transparent}.imagezoom-tiles{width:256px;height:256px;border:thin solid #999;text-align:center;float:left}.tile{float:left;overflow:hidden;position:relative}.hidden{display:none}.imagezoom-opencursor{cursor:url(../images/icons/openhand_8_8.cur),pointer}.imagezoom-closecursor{cursor:url(../images/icons/closedhand_8_8.cur),pointer}.imagezoom-copyright{bottom:7px;color:grey;font-size:12px;position:absolute;right:8px;z-index:10000}.imagezoom-zoombox{border:medium none;-moz-user-select:none;z-index:10000;position:absolute;width:111px;height:74px;left:0;top:0}.imagezoom-rototecontrol{bottom:4px;left:40%;position:absolute;z-index:10000}.imagezoom-pancontrol{cursor:pointer;width:59px;height:59px;left:15px;position:absolute;top:20px;z-index:10000}.imagezoom-scaleBar{-moz-user-select:none;bottom:4px;color:grey;font-family:Arial,sans-serif;font-size:11px;height:26px;left:15px;position:absolute;width:114px;z-index:10000}.imagezoom-zoomcontrol{left:33px;position:absolute;top:100px;z-index:10000}.imagezoom-zoomplus{padding-bottom:5px;padding-left:2px;cursor:pointer}.imagezoom-zoomminus{padding-left:2px;cursor:pointer}</style>").appendTo('head');
    if ( config.input ) {
        this.el.append('<div style="position:absolute;top:0;left:0;border:1px solid black;padding: 5px;background-color:gray;z-index:900001;width: 100%;"><input id="imageviewerInput" style="width:50%" type="text"><input id="imageviewerUrlInput" style="width: 50%" type="text"></div>');
        $('#imageviewerInput').bind('change', function() {
            me.loadFromBarcode( $('#imageviewerUrlInput').val(), $(this).val() );
        });
    }
    
    // init
    this.style               =   'height: 100%; width: 100%';
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
    this.showOverlay = function( message ) {
        $('body').append( '<div class="overlay"></div>' );
        if ( message ) {
            $('body').append( '<div class="overlayMessage">' + message + '</div>' );
        }
    }
    this.hideOverlay = function() {
        $('.overlay').remove();
        $('.overlayMessage').remove();
    }
    this.loadImage = function( url, params ) {
        var me = this;
        this.showOverlay( 'Loading...' );
        this.fire('beforeImageLoad', this);
        this.resetImage();
        $.ajax({
            url: url,
            dataType: 'jsonp',
            success: function( data ) {
                if ( data.success ) {
                    me.loadTemplate( data );
                    me.hideOverlay();
                } else {
                    $('.overlayMessage').html( 'Unable to load image from<br>' + url );
                }
            },
            error: function() {
                me.hideOverlay();
            }
        });
    },
    this.loadFromBarcode = function( url, barcode ) {
        var me = this;
        this.showOverlay( 'Loading...' );
        this.fire('beforeImageLoad', this);
        this.resetImage();
        $.ajax({
            url: url + '?barcode=' + barcode,
            dataType: 'jsonp',
            success: function( data ) {
                if ( data.success ) {
                    me.loadTemplate( data );
                    me.hideOverlay();
                } else {
                    $('.overlayMessage').html( 'Unable to load ' + barcode );
                }
            },
            error: function() {
                me.hideOverlay();
            }
        });
    },
    this.loadTemplate = function( o ) {
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
            this.tileContainerEl.prepend( '<img src="data:image/gif;base64,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" class="imagezoom-pancontrol" usemap="#panmap">' );
            this.pancontrol = this.tileContainerEl.children('img:first');
        }
    },
    this.loadZoomControl = function() {
        var me = this;
        if ( this.zoomControl && this.isImageLoaded ) {
            var plusImage = 'resources/img/plus.png';
            this.zoomControlContainer.prepend( '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAYAAABWzo5XAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9wGHRAQL3kugG4AAAAidEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVAgb24gYSBNYWOHqHdDAAABuUlEQVQ4y4WTTW/TQBCGn9ldO1RQmipVwkcO8Av4D0icy5+CAwf+UVWpJ46oF9RbhKoSpcFtQ+t8eodDvGbjOGWlle2R95l33pkVgG/ff7xT1Q9AzysAeO9R1WoXfv0E4vhcRM6uRtmplJAvwHuvuPhwAHplKwZQrLMOvfefTankv5CwahBU9QVwbMpydkKMgDWCqjZBwu67WHYdkiaOt/0eaeK4HI65zu62IOHd1INxOdYI7efPODzY50krbYQElS50p8lY/8+aatUh4T/XVI41glfYayUVIHG2+i68kk9n63NlMhdnNwJvXnc52H+KiABrsKryqndE7+gQgNl8wfnFgD8P00qlCxBVBRHSxNFKk41SAJw1OGsAMMZgjWyU6orIiFXh+flrzPhmUql52e2QOMvo9y03kwe89yxXBff5bKPbrj5s2e2E6xK+10rodtokzpLd3TO4HFae1EfGPDKxW53aBQFwuyDhos4XS0SExXLVCKk83AUByKczzi8GWCNbnsSQ4NG8LjlWGbe4CVKu3IjIGTBsmtiGy9kEAThxV6PstNtpf1LVj6rar2CPGBvBcuCk8Pr1L1gTIr+yYTZGAAAAAElFTkSuQmCC" class="imagezoom-zoomplus" title="Zoom In"><br>' );
            this.zoomPlus = this.zoomControlContainer.children('img:first');
            // insert zoom slider here (zoomContainer)
            this.zoomControlContainer.append( '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAYAAABWzo5XAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9wGHRAQG1iadNsAAAAidEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVAgb24gYSBNYWOHqHdDAAABSklEQVQ4y62Tv04CQRCHv1nWP6EimqCJFL6B72BirS+lhYVvREioLOnorAwhaAhBOSByMxZ3ey4HAhI3uexlMvPN7zdzJwDPne6Vmd0AZ2oAoKqYWfGkmt1AHJ+LSLs3GLYkhzwC12r4uDgA1ViJAaRZ176qPrhcyVZIOCUIZnYO3Prczj5KYosNFyfvAwnvrhzc0c5KAx+2s4+SUKtGNpsYcnjgqThZAZrZEjSZzrK6XHgxZDVwApcXdaq1CSIOyLOKK4fOTuh0X/iYTIsmPkDMDETw3nN8VGXTmTOm4mTJqg/eARap8tp/pzo6/LET1EQWvxYpn8lsadu+vJ3haMzblsGWviGAbP27rvg3CID/y4rXQQpF/wEJM5qXE3exE9sCEicibaC/Tsman3MdBKDpe4Nhq35auzezOzNrFLANg41gCdBM1Z6+AY0iPlLdXBoyAAAAAElFTkSuQmCC" class="imagezoom-zoomplus" title="Zoom Out">' );
            this.zoomMinus = this.zoomControlContainer.children('img:last');
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

    this.el.append( '<div style="overflow: hidden; width: 100%; height: 100%; position: relative; left: 0px; top: 0px;"><div class="imagezoom-zoomcontrol"></div><div style="position: relative; left: 0px; top: 0px; z-index: 0;"></div></div>' );
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
