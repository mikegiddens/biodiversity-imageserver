/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/
BisLightbox = function(config) {
	this.proxy = new Ext.data.HttpProxy({
				url: Config.baseUrl + 'resources/api/api.php'
			});
	
	this.store =  new Ext.data.GroupingStore({
			proxy:this.proxy 
		,	baseParams: { 
					cmd: 'images'
				,	filters:''
				,	code:''
				,	start: 10
				,	limit: 4
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
		'<div style="padding: 10px">',
		'<tpl for=".">',
			'<div class="ux-carousel-slide" id="{filename}">',
				'<div class="thumbnail">',
					'<a class="lb-flower" href="{path}{barcode}_m.jpg" rel="lightbox" title="Barcode : {barcode}<br/>',
						'<tpl if="Family != 0">'+
								'<span>Family : {Family}</span><br>'+
						'</tpl>',
						'<tpl if="Genus != 0">'+
								'<span>Genus : {Genus}</span><br>'+
						'</tpl>',
						'<tpl if="SpecificEpithet != 0">'+
								'<span>SpecificEpithet : {SpecificEpithet}</span><br>'+
						'</tpl>',
						'"><img src="{path}{barcode}_s.jpg" title="{filename}">',
					'</a>',
				'</div>',
			'</div>',
		'</tpl>',
		'</div>'
	);
	

	Ext.apply(this,config,{
//	var panel =  new Ext.DataView({
			id:'images-view'
		,	store: this.store
		,	width:235
		,	height:450
		,	autoScroll: true
		,	tpl: this.tpl
		,	multiSelect: true
		,	overClass:'ux-carousel-slide'//'x-view-over'
		,	padding: '20px'
		,	itemSelector:'div.thumb-wrap'
		,	emptyText: 'No images to display'
		,	listeners:{
				beforerender:function(){
					this.store.load();	
				}
			}
	});
	//panel.render(document.body);
	//new Ext.ux.Carousel(this.getEl().dom);
	BisLightbox.superclass.constructor.call(this, config);
}

Ext.extend(BisLightbox, Ext.DataView, {

});
	
	