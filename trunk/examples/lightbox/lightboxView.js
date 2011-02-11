/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/
Ext.namespace('ImagePortal');

ImagePortal.LightboxView = function(config) {
	this.proxy = new Ext.data.HttpProxy({
				url: Config.baseUrl + 'resources/api/api.php'
			});
	
	this.store =  new Ext.data.GroupingStore({
			proxy:this.proxy 
		,	baseParams: { 
					cmd: 'images'
				,	filters:''
				,	code:''
			}
		,	reader: new Ext.data.JsonReader({
				root: 'data'
			,	totalProperty: 'totalCount'
			, 	fields:[
	            	{name: 'image_id'}
        	    ,	{name: 'filename'}
            	,	{name: 'timestamp_modified'}
            	,	{name: 'barcode'}								
	            ,	{name: 'Family'}
	            ,	{name: 'Genus'}
	            ,	{name: 'SpecificEpithet'}
	            ,	{name: 'flickr_PlantID'}
	            ,	{name: 'flickr_modified'}
	            ,	{name: 'picassa_PlantID'}
	            ,	{name: 'picassa_modified'}
	            ,	{name: 'gTileProcessed'}
	            ,	{name: 'zoomEnabled'}
	            ,	{name: 'processed'}
	            ,	{name: 'path'}
				,	{name: 'server'}
	            ,	{name: 'farm'}
			]
        })
		,	remoteSort: true
		,	sortInfo: 'login'
		,	groupField: ''
	//	,	autoLoad: true
	});
	
	
	this.tpl = new Ext.XTemplate(
		'<tpl for=".">',
			'<div class="thumb-wrap" id="{filename}">',
			'<div class="thumbnail">',
			'<a href="{path}{barcode}_m.jpg" rel="lightbox" title="Barcode : {barcode}<br/>',
			'<tpl if="Family != 0">'+
					'<span>Family : {Family}</span><br>'+
			'</tpl>',
			'<tpl if="Genus != 0">'+
					'<span>Genus : {Genus}</span><br>'+
			'</tpl>',
			'<tpl if="SpecificEpithet != 0">'+
					'<span>SpecificEpithet : {SpecificEpithet}</span><br>'+
			'</tpl>',
			'"><img src="{path}{barcode}_s.jpg" title="{filename}"></a></div>',
			'</div>',
		'</tpl>',
		'</div>'
	);
	

	Ext.apply(this,config,{
//	var panel =  new Ext.DataView({
			id:'images-view'
		,	store: this.store
		,	width:900
		,	height:500
		,	autoScroll: true
		,	tpl: this.tpl
		,	multiSelect: true
		,	overClass:'x-view-over'
		,	padding: '20px'
		,	itemSelector:'div.thumb-wrap'
		,	emptyText: 'No images to display'
		,	listeners:{
				beforerender:function(){
					this.store.load();	
					
				}
			}
		,	bbar: new Ext.PagingToolbar({
					pageSize: 10
				,	store: this.store
				,	scope:this
				,	emptyMsg: 'No images available.'
				,	displayInfo: true
				,	displayMsg: 'Displaying Specimen Images {0} - {1} of {2}' 
				,	ref:'../pgtoolbar'
			})
	});
	//panel.render(document.body);
	ImagePortal.LightboxView.superclass.constructor.call(this, config);
}

Ext.extend(ImagePortal.LightboxView, Ext.DataView, {

});
	
	