/**
 * @copyright SilverBiology
 * @author Michael Giddens
 * @website http://www.silverbiology.com
*/
	Ext.namespace("SilverCollection")

	SilverCollection.GTilePanel = function(config){

	Ext.apply( this, config, {
			mapConfOpts:['enableScrollWheelZoom','enableDoubleClicZoom','enableDragging']
		,	mapControls:['GSmallControl','NonExistantControl','GLargeMapControl']
		,	iconCls: 'icon_picture'
		,	border:false
		,	autoScroll: true
		,	ImgCount:0
		,	style:'background-color:black'
		,	tbar: {
				style:'margin:0 auto;'
			,	hidden:true
			,	buttonAlign: 'center'
			,	items:[{
						iconCls: 'icon_previous'
					,	height:30	
					,	scope:this
					,	handler: function(){
							this.ImgCount = this.ImgCount-1;
							this.drawImage();
						}
					},{
							xtype:'label'
						,	scope:this
						,	ref:'info'	
					},{
						iconCls: 'icon_next'
					,	height:30	
					,	scope:this
					,	handler: function(){
							this.ImgCount = this.ImgCount + 1;
							this.drawImage();
						}
					}]
				}	
	});
	SilverCollection.GTilePanel.superclass.constructor.call(this, config);
	
}

Ext.extend(SilverCollection.GTilePanel, Ext.ux.GMapPanel, {


		loadSpecimen:function(id){
				var status = new Ext.Button({
									ref: '../gmapStatus'
								,	text:'Export Specimen Sheet Image'
								,	listeners: {
											click: {
													scope: this
												,	fn: function(){
														this.lanchExport()
													}
												}
										}		
							});
				
					this.getBottomToolbar().insert(this.getBottomToolbar().items.getCount() + 1, '->', status);
				
				
				this.mask = new Ext.LoadMask( this.body, {
						msg: this.msgText
					,	removeMask: true
				});
				var params = {
						cmd: 'search'
					,	field_type: 'full'
					,	filters: Ext.encode({ GlobalUniqueIdentifier: id })			
				}
				Ext.Ajax.request({
					//	url: Config.General.Home.URL+'api/silvercollection.php' 
						url:Config.General.Home.URL+'temCollection.json'
					,	method: 'POST'
					,	scope: this
					,	params: params
					,	success: function(responseObject) {
							var record = Ext.decode(responseObject.responseText);
							if (record.success && (record.totalCount >= 0) ) {
								this.specimenRecord = record; //this is what I done for json file
								//this.specimenRecord = record.records[0];
								if(this.specimenRecord.totalCount > 1){
										this.getTopToolbar().setVisible(true);
										this.setIconClass('icon_pictures');
								}else{
									this.setIconClass('icon_picture');
									this.getTopToolbar().setVisible(false);
								}
							this.drawImage();
							}else{
								SilverCollection.Notice.msg("Error","Image Not Found");
							}
						}					
					,	failure: function() {
							SilverCollection.Notice.msg("Error","Image Not Found");
						}
				});
				
			this.doLayout();	
			}
	,	varMap: function(){
			var map = this.map
			return this.map;
		}

	,	CustomGetTileUrl: function( a, b ) {
			var path = this.path + "google_tiles/" + (5 - b) + "/tile_"+ 1 + "_" + a.x + "_" + a.y + ".jpg";
			return path;
		}

	,		lanchExport:function(){
				var	exportWindow = new SilverCollection.SSIExport({
							width: 400
    					,	height: 250
					})													
				exportWindow.show();
			}
	
	,	drawImage: function(){
		 
		var path = this.getImagePath();
		
		if (this.path == path) {
			return;
		}
		else {
			this.path = path;
			// ====== Create a copyright entry =====
			var copyright = new GCopyright(1, new GLatLngBounds(new GLatLng(-90, -180), new GLatLng(90, 180)), 0, this.copyright);
			
			// ====== Create a copyright collection =====
			// ====== and add the copyright to it   =====
			var copyrightCollection = new GCopyrightCollection('(Specimen: ...)');
			copyrightCollection.addCopyright(copyright);
			
			// == Write our own getTileUrl function ========
			// In this case the tiles are names like  8053_5274_3.jpg      
			
			// ===== Create the GTileLayer =====
			// ===== adn apply the CustomGetTileUrl to it
			var tileLayers = [new GTileLayer(copyrightCollection, 1,5)];
			tileLayers[0].getTileUrl = this.CustomGetTileUrl.createDelegate(this);
			//var gmaptype = new GMapTypeControl();
			// this.map.addControl(gmaptype);
			
			// ===== Create the GMapType =====
			// ===== and add it to the map =====
			var map = this.getMap();
			var mapTypes = map.getMapTypes();
			
			Ext.each(mapTypes, function(item){
					map.removeMapType(item);
			});
			map.removeMapType(G_SATELLITE_MAP);
			map.removeMapType(G_HYBRID_MAP);
			map.removeMapType(G_NORMAL_MAP);

			var custommap = new GMapType(tileLayers, new GMercatorProjection(18), "Images");
			map.addMapType(custommap);

			var centerLat = 0, centerLong = 0, initialZoom = 1;
			map.setCenter(new GLatLng(centerLat, centerLong), initialZoom, custommap);
			map.setZoom(parseInt(initialZoom));
			}
		}
	,	getImagePath:function(){
			if(this.ImgCount < 0){
				this.ImgCount = this.specimenRecord.totalCount-1;
			}else if(this.ImgCount > this.specimenRecord.totalCount-1){
				this.ImgCount = 0;
			}
				this.getTopToolbar().info.setText((this.ImgCount+1) + ' OF ' + this.specimenRecord.totalCount);
				return (this.specimenRecord.images[this.ImgCount].path);
		}	
});

Ext.reg('GTilePanel', SilverCollection.GTilePanel );







		
		/*
		Ext.Ajax.request({
						url: Config.General.Home.URL + 'api/silvercollection.php'
					,	method: 'POST'
					,	scope: this
					,	params: params
					,	success: function(responseObject) {
							this.mask.hide();
							var record = Ext.decode(responseObject.responseText);
							if (record.success && (record.totalCount >= 1) ) {
								this.specimenRecord = record.records[0];
								if(this.specimenRecord.specimen_sheet_image > 1){
										this.getTopToolbar().setVisible(true);
								}else{
										this.getTopToolbar().setVisible(false);
								}
								this.specimenRecord.ImageUrl=('http://images.cyberfloralouisiana.com/images/specimensheets/nlu/0/0/11/13/');
							this.drawImage();
							}else{
								SilverCollection.Notice.msg("Error","Image Not Found");
							}
						}					
					,	failure: function() {
							this.mask.hide();
							SilverCollection.Notice.msg(this.statusTitle, this.statusText);
						}
				});
*/		