var LavamapMain = function( config ) {

	if ( config ) $.extend( this, config );
	if ( !(this.mapId) || !(this.filterId) || !(this.panelId) ) { 
		console.log( 'mapId, filterId and panelId must be specified to instantiate the Lavamap program.' ) 
	}
	this.el	=	$('#'+this.mapId);
	this.filterEl	=	$('#'+this.filterId);
	this.panelEl =	$('#'+this.panelId);

	if (typeof this.cartoTableName == 'undefined') this.cartoTableName = 'content_type_listing';
	if (typeof this.lotsTableName == 'undefined') this.lotsTableName = 'jam_mappluto';
	if (typeof this.cartoUsername == 'undefined') this.cartoUsername = 'silverbiology';
	if (typeof this.cartoUrl == 'undefined') this.cartoUrl = 'http://silverbiology.cartodb.com/api/v2/sql?q=';
	if (typeof this.centerStart == 'undefined') this.centerStart = new L.LatLng(40.71, -74);
	if (typeof this.zoomStart == 'undefined') this.zoomStart = 13;
	if (typeof this.rangeType == 'undefined' || this.rangeType == '') this.rangeType = 'box';
	if (typeof this.featuredListings == 'undefined') this.featuredListings = true;
	if (typeof this.minLotLevel == 'undefined') this.minLotLevel = 15;
	if (typeof this.maxLotLevel == 'undefined') this.maxLotLevel = 18;
		
	this.map = new L.Map(this.mapId, {center: this.centerStart, zoom: this.zoomStart});
	
	if (typeof this.baseLayer == 'undefined') {
		this.baseLayer = new L.TileLayer('http://{s}.tile.cloudmade.com/881446b8dc394665afdd328c34768080/997/256/{z}/{x}/{y}.png', {
			attribution: 'Map data © <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>',
			maxZoom: 18
		});
	}
									
	var me = this;
	
	this.lotsMouseClick = function(ev, latlng, pos, data) {
		me.fire('updatePanel', data, {type : 'lots'});
	}
	
	this.lotsMouseOver = function( ev, latlng, pos, data ) {
		document.body.style.cursor = 'pointer';
	}
	
	this.lotsMouseOut = function() {
		document.body.style.cursor = 'default';
	}
	
	this.featureMouseClick = function(ev, latlng, pos, data) {
		me.fire('updatePanel', data, {type : 'listing', latlng : latlng});
	}
	
	this.featureMouseOver = function( ev, latlng, pos, data ) {
		me.showInfo(data);
		document.body.style.cursor = 'pointer';
/*
		if ( data.cartodb_id != me.circleMarker.cartodb_id ) {
			me.circleMarker = new L.CircleMarker( new L.LatLng( latlng.lat, latlng.lng ), me.hoverStyle );
			me.circleMarker.cartodb_id = data.cartodb_id;
			me.map.addLayer( me.circleMarker );
		}
*/
	}
	
	this.featureMouseOut = function() {
		document.body.style.cursor = 'default';
		$.hideCursorMessage(0);
		// me.hideTooltip();
		// me.circleMarker.cartodb_id = null;
		// me.map.removeLayer( me.circleMarker );
	}
	
	this.cartodbLayer	=	this.cartodbLayer	|| new L.CartoDBLayer({
		map: this.map,
		user_name: this.cartoUsername,
		table_name: this.cartoTableName,
		query: 'SELECT * FROM {{table_name}}',
		// tile_style: '#{{table_name}}{[status="lease"]{marker-fill:yellow}[status="sale"]{marker-fill:red}[status="leaseorsale"]{marker-fill:orange}}',
		tile_style: '#{{table_name}}{marker-fill:gray;[field_listing_types_value=\'9\']{marker-fill: yellow;}[field_listing_types_value=\'6\']{marker-fill: red;}[field_listing_types_value=\'4\']{marker-fill: blue;}}',
		interactivity: 'cartodb_id,field_listing_address_value,field_listing_broker_type_value,field_listing_type_value,field_listing_sqft_value,field_listing_price_value,field_description_value,field_listing_city_value,field_listing_state_value,field_listing_zip_value,field_listing_types_value,field_listing_property_use_value, field_listing_crossstreet_value, field_listing_floor_value, field_listing_age_value, field_listing_broker_type_value, field_listing_pricetype_value',
		featureClick: this.featureMouseClick,
		featureOver: this.featureMouseOver,
		featureOut: this.featureMouseOut,
		auto_bound: false
	});

	this.hoverStyle	=	this.hoverStyle	|| { radius:6, color:"#333", weight:2, opacity:1, fillColor: "#0033CC", fillOpacity:1, clickable:false };

	this.lotsLayer = this.lotsLayer	|| new L.CartoDBLayer({
		map: this.map,
		user_name: this.cartoUsername,
		table_name: this.lotsTableName,
		query: 'SELECT * FROM {{table_name}}',
		// tile_style: '#{{table_name}}{marker-fill:red}',
		interactivity: 'cartodb_id,address,block,lot,numbldgs,numfloors,unitstotal,yearbuilt,bldgfront,bldgdepth,bldgarea,builtfar,lotfront,lotdepth,lotarea,zipcode',
		featureClick: this.lotsMouseClick,
		featureOver: this.lotsMouseOver,
		featureOut: this.lotsMouseOut,
		auto_bound: false
	});
		
	// this.circleMarker	=	this.circleMarker	|| new L.CircleMarker( null );
	// this.popup = new L.CartoDBPopup();
	
/*	
	this.createPopup = function(latlng,data) {
		me.map.closePopup(me.popup);
	
		var html = '';
		html += '<p class="popHeading">'+data.field_listing_address_value+'</p>';
		html += '<hr>';
		html += 'Type : '+ data.field_listing_broker_type_value;
		html += '<br>Square Feet : '+ this.numberWithCommas( data.field_listing_sqft_value );
		html += '<br>Price : '+ this.numberWithCommas( data.field_listing_price_value );
		html += '<br>Description : '+ data.field_description_value;
	
		me.popup.setContent(html);
		me.popup.setLatLng(latlng);
		me.map.openPopup(me.popup);
	}
*/	
	
	this.hideLots = function(){
		if(me.lotsLayer.isVisible()) {
			me.lotsLayer.hide();
		}
	}
	
	this.showLots = function(){
		if(!me.lotsLayer.isVisible()) {
			me.lotsLayer.show();
		}
	}
	
	this.showFeaturedListings = function(latlng,limit,featured,callback) {
		if(latlng == '') return callback(false);
		limit = ('undefined' == typeof limit || limit == '') ? 2 : limit;
		featured = (false == featured || true == featured) ? featured : true;
		featured  = false;
		var query = 'select *, ST_AsGeoJSON(the_geom) as latlng from '+me.cartoTableName;
		query += (featured) ? ' WHERE featured = true ' : '';
		query += " ORDER BY the_geom <-> st_setsrid(st_makepoint("+ latlng.lng +","+ latlng.lat +"),4326) LIMIT " + limit;
		query = encodeURIComponent( query );
		
		$.get( me.cartoUrl + query, function( data ) {
			data = JSON.parse(data);
			// console.log(data);
			return callback(data.rows);
			});
	}
	
	this.hideListing = function() {
		$("#listingDiv").hide();
	}
	
	this.showInfo = function(data) {
		var tipMessage = '';
		var tipMessageAr = new Array();
		tipMessage += (data.field_listing_address_value != '') ? data.field_listing_address_value + '<br>' : '';
		tipMessage += (data.field_listing_city_value != '') ? data.field_listing_city_value + ' ' : '';
		tipMessage += (data.field_listing_state_value != '') ? data.field_listing_state_value + ' ' : '';
		tipMessage += (data.field_listing_zip_value != '' && data.field_listing_zip_value != 0) ? data.field_listing_zip_value : '';
		
		tipMessageAr.push(tipMessage);
		
		var type = '';
		switch(data.field_listing_types_value) {
			case '9':
				type = 'For Lease';
				break;
			case '6':
				type = 'For Sale';
				break;
			default:
				type = 'For Lease / Sale';
				break;
		}
		tipMessageAr.push(' Type: ' + type);
		(data.field_listing_type_value != '') ? tipMessageAr.push(' Type: ' + data.field_listing_type_value) : '';
		(data.field_listing_sqft_value != '') ? tipMessageAr.push(' Sq.Ft: ' + data.field_listing_sqft_value) : '';
		(data.field_listing_price_value != '') ? tipMessageAr.push(' Price: ' + data.field_listing_price_value) : '';
		(data.field_description_value != '') ? tipMessageAr.push(' Description: ' + data.field_description_value) : '';
		$.cursorMessage(tipMessageAr.join('<br>'), {
			hideTimeout: 0,
			offsetX: 15,
			offsetY: 15,
			speed: 0
		});
	}

	this.searchByBBL = function(bbl) {
		if(bbl == '') return false;
		var bblQuery = 'select *, ST_AsGeoJSON(the_geom) as latlng from '+me.cartoTableName+" where field_listing_bbl_value = '"+bbl+"'";
		bblQuery = encodeURIComponent( bblQuery );
		$.get( me.cartoUrl + bblQuery, function( data ) {
			data = JSON.parse(data);
//			console.log(data);
			
			var obj = $.parseJSON(data.rows[0].latlng);
			var tmpPoint = new L.LatLng(obj.coordinates[1],obj.coordinates[0]);
			me.map.panTo(tmpPoint);
			me.map.setZoom(18);
			me.fire( 'updatePanel', data.rows[0], {type : 'listing', latlng : tmpPoint});
		});
	
	}
	
	this.showTooltip = function( data, point ) {
		var html = '';
		html += '<label>Type</label><p>'+ data.field_listing_broker_type_value +'</p>';
		html += '<br><label>Square Feet</label><p>'+ this.numberWithCommas( data.field_listing_sqft_value ) +'</p>';
		html += '<br><label>Price</label><p>$'+ this.numberWithCommas( data.field_listing_price_value ) +'</p>';
		html += '<br><label>Description</label><p>'+ data.field_description_value +'</p>';
		
		this.tooltip.html( html );
		this.tooltip.css( {left: (point.x + 15) + 'px', top: point.y - ($('#tooltip').height()) + 100 + 'px'} );
	}
	
	this.hideTooltip = function() {
		this.tooltip.hide();
	}
	
	this.numberWithCommas = function( x ) {
	    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}
	
	this.filter = new LavamapFilter({
		el: this.filterEl,
		parent: this
	});

	if(this.featuredListings) {
		this.showFeaturedListings(this.centerStart,2,false,function(rows){
			if(false !== rows) {
				me.fire( 'updatePanel', rows, {type : 'listings'} );
			}
		});
	}
	
	this.panel = new LavamapPanel({
		el: me.panelEl,
		parent: me
	});
	
	this.map.on('zoomend',function(e){
//		console.log(e.target.getZoom());
		if(e.target.getZoom() >= me.minLotLevel && e.target.getZoom() <= me.maxLotLevel) {
			me.showLots();
		} else {
			me.hideLots();
		}
	});
	
	this.map.on('dragend',function(e){
		if(me.featuredListings) {
			me.showFeaturedListings(me.map.getCenter(),2,false,function(rows){
				if(false !== rows) {
					me.fire( 'updatePanel', rows, {type : 'listings'} );
				}
			});
		}
	});
	
	this.on('updateFilter', function( sender, formData, me ) {
		var preConvertObj = { select: '*', from: me.cartoTableName, where: [], rejectEmpty: true };
		formData.forEach( function( kvp ) {
			var valType = kvp.name.substring( 0, kvp.name.indexOf('.') );
			var valName;
			if ( kvp.name.indexOf('.') == -1 ) {
				valName = kvp.name;
			} else {
				valName = kvp.name.substring( kvp.name.indexOf('.') + 1 );
			}
			// var whereobj = { filter: valName, value: kvp.value };
			var whereobj = { filter: valName };
			// var whereobj = { filter: valName, value: (kvp.value != '') ? kvp.value + '::text' : kvp.value };
			switch ( valType ) {
				case 'min':
					whereobj.operator = '>';
					break;
				case 'max':
					whereobj.operator = '<';
					break;
				case 'mineq':
					whereobj.operator = '>=';
					break;
				case 'maxeq':
					whereobj.operator = '<=';
					break;
				default:
					whereobj.operator = '=';
					break;
			}
			if(whereobj.operator != '=' && kvp.value != '') {
				whereobj.value = kvp.value + '::text' ;
			} else {
				whereobj.value = kvp.value;
			}
			
			preConvertObj.where.push( whereobj );
		});
		var output = jsonToSql( preConvertObj );
		output = output.replace(/"/g,"'");
		count = output.replace( '*', 'count(*)' );
		count = 'select count(*) as total from '+me.cartoTableName+' union '+count;
		count = encodeURIComponent( count );

		$.get( me.cartoUrl + count, function( data ) {
			data = JSON.parse(data);
			if('undefined' == typeof data.rows[1]) {
				data.rows[1] = data.rows[0];
			}
			me.cartodbLayer.setQuery( output );
			$('.filterMode1').children().first().children().first().html( '( Showing ' + data.rows[0].total + ' of '+data.rows[1].total+' records. )<hr>' );
			$('#lavamapSearchSubmit').removeClass('disabled').addClass('btn-primary').html( 'Search' );
			me.hideListing();
		});
	});

	this.on('zoomToStreetLevel', function(sender, latlng, parent) {
		me.map.panTo(latlng);
		me.map.setZoom(18);
	});
	
	this.map.setView( this.centerStart, this.zoomStart );
	this.map.addLayer( this.baseLayer );
	this.map.addLayer( this.lotsLayer );
	this.map.addLayer( this.cartodbLayer );

	if(!(this.zoomStart >= this.minLotLevel && this.zoomStart <= this.maxLotLevel)) {
		this.hideLots();
	}

}

LavamapFilter = function( config ) {
	if ( config ) $.extend( this, config );

	var me = this;
	
	this.mode	=	config.mode	|| 0;
	this.components	=	new Array();
	this.lastMode	=	1;
	this.el.append( '<div class="filterMode0"><span class=""><a href="#" onclick=\'$(".filterMode0").hide(0,function(){$(".filterMode1").show()})\'>Maximize Filter</a></div>' );

	var form = "" +
		"<form id='frm'>" +
			"<div></div>" +
			"<div style='position: absolute; bottom: 25px; right: 25px'><a href='#' onclick='$(\".filterMode1\").hide(0,function(){$(\".filterMode0\").show()})'>Minimize Filter</a></div>" +
			"<div class='row'>" +
				"<div class='span3'>" +
					"<label>Filter</label>" +
					"<select name='field_listing_types_value'>" +
						"<option value=''>For Lease or Sale</option>" +
						"<option value='9'>For Lease</option>" +
						"<option value='6'>For Sale</option>" +
					"</select>" +
					"<label>Property Type</label>" +
					"<input type='checkbox' name='field_listing_property_use_value' value='Retail'>" + "<span class='padUltraSlim'>Retail</span>" + "<br>" + 
					"<input type='checkbox' name='field_listing_property_use_value' value='Industrial'>" + "<span class='padUltraSlim'>Industrial</span>" +  "<br>" + 
					"<input type='checkbox' name='field_listing_property_use_value' value='Office'>" + "<span class='padUltraSlim'>Office</span>" + "<br>" + 
					"<input type='checkbox' name='field_listing_property_use_value' value='Multi-Family'>" + "<span class='padUltraSlim'>Multi-Family</span>" +  "<br>" + 
					"<input type='checkbox' name='field_listing_property_use_value' value='Vacant'>" + "<span class='padUltraSlim'>Vacant</span>" + "<br>" + 

				"</div>"+
				"<div class='span0 vr'></div>" + 
				"<div class='span5'>";
				
				if(this.parent.rangeType == 'slider') {
					form += "<label>Square Feet: <span id='sq-range' style='color: #0088CC;'></span></label>"+ 
					"<div id='sq-slider' class ='span4'></div>"+
					"<input id='minsq' type='hidden' name='mineq.field_listing_sqft_value'>"+
					"<input id='maxsq' type='hidden' name='maxeq.field_listing_sqft_value'>"+
					"<br>"+
					"<label>Price: <span id='price-range' style='color: #0088CC;'></span></label>"+
					"<div id='price-slider' class ='span4'></div><br>"+
					"<input id='minprice' type='hidden' name='mineq.field_listing_price_value'>"+
					"<input id='maxprice' type='hidden' name='maxeq.field_listing_price_value'>"+
					"<br>";
				
				} else {
					form += "<label>Square Feet: <span id='range'></span></label>"+
					"<input type='text' name='mineq.field_listing_sqft_value' placeholder='min' class='input-small'>"+"<span class='dash'> - </span>"+
					"<input type='text' name='maxeq.field_listing_sqft_value' placeholder='max' class='input-small'>"+
					"<label>Price:</label>"+
					"<input type='text' name='mineq.field_listing_price_value' placeholder='min' class='input-small'>"+"<span class='icon'> - </span>"+
					"<input type='text' name='maxeq.field_listing_price_value' placeholder='max' class='input-small'>"+
					"<br><br>";
				}
				
				form += "<button id='lavamapSearchSubmit' class='btn btn-primary btn-small' onclick=\"\">Search</button> "+
				"<input type='button' id='resetFilter' class='btn btn-primary btn-small' onclick='this.form.reset();' value='Reset'>"+
				"</div>"+
			"</div>"+
		"</form>";
	this.el.append( '<div class="filterMode1">'+form+'</div>' );
	this.form = this.el.find('form').first();

	if(this.parent.rangeType == 'slider') {
		$('#resetFilter').click(function(e){
			$( "#sq-slider" ).slider( "values", 0, 0 );
			$( "#sq-slider" ).slider( "values", 1, 4000 );
			$("#sq-range" ).html( me.parent.numberWithCommas($( "#sq-slider" ).slider( "values", 0 )) + " sq.ft. - " + me.parent.numberWithCommas($( "#sq-slider" ).slider( "values", 1 )) + ' sq.ft.' );
			$('#minsq').val($( "#sq-slider" ).slider( "values", 0 ));
			$('#maxsq').val($( "#sq-slider" ).slider( "values", 1 ));

			$( "#price-slider" ).slider( "values", 0, 0 );
			$( "#price-slider" ).slider( "values", 1, 10000 );
			$("#price-range" ).html( '$' + me.parent.numberWithCommas($( "#price-slider" ).slider( "values", 0 )) +	" - $" + me.parent.numberWithCommas($( "#price-slider" ).slider( "values", 1 )) );
			$('#minprice').val($( "#price-slider" ).slider( "values", 0 ));
			$('#maxprice').val($( "#price-slider" ).slider( "values", 1 ));
			
			$('#frm').submit();
		});
	}
	
	$( "#sq-slider" ).slider({
		range: true,
		min: 0,
		max: 4000,
		values: [ 0, 4000 ],
		slide: function( event, ui ) {
			$("#sq-range" ).html( me.parent.numberWithCommas(ui.values[ 0 ]) + " sq.ft. - " + me.parent.numberWithCommas(ui.values[ 1 ]) + ' sq.ft.' );
			$('#minsq').val(ui.values[ 0 ]);
			$('#maxsq').val(ui.values[ 1 ]);
		}
	});
	$("#sq-range").html( this.parent.numberWithCommas($( "#sq-slider" ).slider( "values", 0 )) + " sq.ft. - " + this.parent.numberWithCommas($( "#sq-slider" ).slider( "values", 1 )) + ' sq.ft.' );
	$('#minsq').val($( "#sq-slider" ).slider( "values", 0 ));
	$('#maxsq').val($( "#sq-slider" ).slider( "values", 1 ));
	
	$("#price-slider").slider({
			range: true,
			min: 0,
			max: 10000,
			values: [ 0, 10000 ],
			slide: function( event, ui ) {
				$( "#price-range" ).html( '$' + me.parent.numberWithCommas(ui.values[ 0 ]) + " - $" + me.parent.numberWithCommas(ui.values[ 1 ]) );
				$('#minprice').val(ui.values[ 0 ]);
				$('#maxprice').val(ui.values[ 1 ]);
			}
		});
	$("#price-range" ).html( '$' + this.parent.numberWithCommas($( "#price-slider" ).slider( "values", 0 )) +	" - $" + this.parent.numberWithCommas($( "#price-slider" ).slider( "values", 1 )) );
	$('#minprice').val($( "#price-slider" ).slider( "values", 0 ));
	$('#maxprice').val($( "#price-slider" ).slider( "values", 1 ));
	
	this.search = function() {
		this.fire( 'updateFilter', this.form.serializeArray(), this.parent );
	}
	var me = this;
	this.form.submit( function( e ) {
		e.preventDefault();
		me.search();
	});
	for ( i=this.lastMode; i>this.mode; i-- ) {
		$('.filterMode'+i).hide();
	}
}

LavamapPanel = function( config ) {

	if ( config ) $.extend( this, config );
	var me = this;
	var listingsData;
	var divHTML = '<div id="listingsDiv">';
	var listingDivHTML = '<div id="listingDiv" class="lm-listing-detail">';
	
	this.on( 'updatePanel', function( sender, data, obj ) {
		if('undefined' == typeof obj.type) obj.type = 'listing';
		switch(obj.type) {
			case 'listings':
				var listing = ($('#listingDiv').html() && $('#listingDiv').css('display') == 'block') ? listingDivHTML + $('#listingDiv').html() + '</div>' : '';

				var panelEl = this.panelEl;
				listingsData = data;
				var html = [divHTML];
				for(var i=0;i<data.length;i++) {
					var tmpFeatHtml = ''
					if (data[i].hasOwnProperty('field_listing_address_value')) {
						tmpFeatHtml += data[i]['field_listing_address_value'] + '<br>';
					}
					if (data[i].hasOwnProperty('field_listing_city_value')) {
						tmpFeatHtml += data[i]['field_listing_city_value'] + ' ';
					}
					if (data[i].hasOwnProperty('field_listing_state_value')) {
						tmpFeatHtml += data[i]['field_listing_state_value'] + ' ';
					}
					if (data[i].hasOwnProperty('field_listing_zip_value')) { 
						tmpFeatHtml += data[i]['field_listing_zip_value'];
					}
					var dvClass = (i == 0) ? '<div class="lm-listing-featured lm-listing-featured-top">' : '<div class="lm-listing-featured">';
					html.push(dvClass +  
						'<div style="position:relative;float:right;" class="featured">Featured</div><div><a href="#" id="lst_'+i+'">' + tmpFeatHtml + '</a></div>' +
					'</div>');					
				}
				html.push('</div>');
				
				this.panelEl.html( listing + html.join(''));
				addEvent(this.panelEl);
				break;

			case 'lots':
				showLotsDetail(data,this.panelEl,obj.latlng);
				break;

			case 'listing':
			default:
				showDetail(data,this.panelEl,obj.latlng);
				break;
		}
	});
	
	var addEvent = function(panelEl) {
		$('#listingsDiv a').click(function(e){
			var lstng = listingsData[e.target.id.replace('lst_','')];
			var obj = $.parseJSON(lstng.latlng);
			var tmpPoint = new L.LatLng(obj.coordinates[1], obj.coordinates[0]);
			showDetail(lstng,panelEl,tmpPoint);
		});
	}
	
	var showDetail = function(data, panelEl, latlng) {
		var listings = ($('#listingsDiv').html()) ? divHTML + $('#listingsDiv').html() + '</div>' : '';
	
		var html = [listingDivHTML];
		html.push( "<div class='lm-listing-close' title='Close Listing'><a href='#' onclick=\"$(this).closest(\'.lm-listing-detail\').hide()\">X</a></div>" );

		var header = new Array();
		header += (data.hasOwnProperty('field_listing_address_value') && data['field_listing_address_value'] != '') ? data['field_listing_address_value'] + '<br>' : '';
		header += (data.hasOwnProperty('field_listing_city_value') && data['field_listing_city_value'] != '') ? data['field_listing_city_value'] + ' ' : '';
		header += (data.hasOwnProperty('field_listing_state_value') && data['field_listing_state_value'] != '') ? data['field_listing_state_value'] + ' ' : '';
		header += (data.hasOwnProperty('field_listing_zip_value') && data['field_listing_zip_value'] != 0 && data['field_listing_zip_value'] != '') ? data['field_listing_zip_value'] : '';
		
		html.push('<h5>' + header + '</h5>');
		html.push('<div class="pad1"><a href="#" id="streetLevelA"><span class="smallText">Zoom to street level</span></a></div>');
		html.push('<table>');
		
		var properties = {field_listing_type_value : 'Type', field_listing_price_value : 'Price', field_listing_sqft_value : 'Square Footage', field_listing_property_use_value : 'Use', field_listing_crossstreet_value : 'Cross street', field_listing_floor_value : 'Level', field_listing_age_value : 'Age', field_listing_broker_type_value : 'Broker', field_description_value : 'desc'};
		for (var key in properties) {
			 if (properties.hasOwnProperty(key)) {
				if(data.hasOwnProperty(key) && data[key] != '') {
					if(key == 'field_listing_price_value' && data.hasOwnProperty('field_listing_pricetype_value') && data['field_listing_pricetype_value'] != '') {
						html.push('<tr><td valign="top" width="110"><h7>'+properties[key]+':</h7> </td><td>'+data[key]+' '+data['field_listing_pricetype_value']+'</td></tr>' );
					} else if(key == 'field_description_value' && data.hasOwnProperty('field_description_value') && data['field_description_value'] != '') {
						html.push('<tr><td valign="top" colspan="2">'+data[key]+'</td></tr>' );
					} else {
						html.push('<tr><td valign="top" width="110"><h7>'+properties[key]+':</h7> </td><td>'+((data.hasOwnProperty(key)) ? data[key] : ' ')+'</td></tr>' );
					}
				}
			 }
		}
		
		html.push('</table>');
//		html.push('<hr>');
		html.push('</div>');
		panelEl.html(html.join('') + listings);
		addEvent(panelEl);
		$('#streetLevelA').click(function(){
			me.fire('zoomToStreetLevel', latlng, this.parent);
		});
	};
	
	var showLotsDetail = function(data,panelEl) {
		var listings = ($('#listingsDiv').html()) ? divHTML + $('#listingsDiv').html() + '</div>' : '';
		var html = [listingDivHTML];
		html.push( "<div class='lm-listing-close' title='Close Lot'><a href='#' onclick=\"$(this).closest(\'.lm-listing-detail\').hide()\">X</a></div>" );

		var header = '';
		header += (data.hasOwnProperty('address') && data['address'] != '') ? data['address'] : '';
		header += (data.hasOwnProperty('zipcode') && data['zipcode'] != '' && data['zipcode'] != 0 ) ? ' ' + data['zipcode'] : '';
		
		html.push('<h5>' + header + '</h5>');
		html.push('<table>');
		var properties = {block: 'Block', lot: 'Lot', numbldgs: 'Number of Buildings', numfloors: 'Building Class', unitstotal: 'Total # of Units', yearbuilt: 'Year Built', bldgfront: 'Building Frontage', bldgdepth: 'Building Depth', bldgarea: 'Building Area', builtfar: 'F.A.R as built', lotfront: 'LOT Frontage', lotdepth: 'Lot Depth', lotarea: 'Lot Area'};
		for (var key in properties) {
		   if (properties.hasOwnProperty(key)) {
				if(data.hasOwnProperty(key) && data[key] != '') {
					html.push( '<tr><td class="" width="200"><h7>' + properties[key] + ':</h7> </td><td>'+((data.hasOwnProperty(key)) ? data[key] : ' ') + '</td></tr>');
				}
		   }
		}
		
		html.push('</table>');
		html.push('</div>');
		panelEl.html( html.join('') + listings);
	};
	
}

var observer = new EventTarget();
LavamapMain.prototype = observer;
LavamapFilter.prototype = observer;
LavamapPanel.prototype = observer;