/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/
Ext.namespace('ImagePortal');

ImagePortal.LightboxView = function(config) {
	
	this.store = new Ext.data.JsonStore({
		url: 'lightbox/dataview.json'
	,	root: 'images'
	,	fields: [
				'name'
			, 	'url'
		]
	,	autoload:true
	});
	
	this.tpl = new Ext.XTemplate(
		'<tpl for=".">',
			'<div class="thumb-wrap" id="{name}">',
			'<div class="thumbnail"><img src="{url}" title="{name}"></div>',//class="thumbnail
			'<span class="x-editable">{shortName}</span></div>',
		'</tpl>',
		'</div>'
	);

	Ext.apply(this,config,{
//	var panel =  new Ext.DataView({
			id:'images-view'
		,	store: this.store
		,	width:535
		,	title:'Simple DataView'
		,	autoHeight:true
		,	tpl: this.tpl
		,	autoHeight:true
		,	multiSelect: true
		,	overClass:'x-view-over'
		,	padding: '20px'
		,	itemSelector:'div.thumb-wrap'
		,	emptyText: 'No images to display'
		,	listeners:{
				beforerender:function(){
					this.store.load();	
				}
			,	dblclick:function(dv,i,n,e){
					console.log(dv);//.getSelectedNodes()	.url
					var record = dv.store.data.items[0].data;
					window.open(record.url,'_blank');
				}
			}
	});
	//panel.render(document.body);
	ImagePortal.LightboxView.superclass.constructor.call(this, config);
}

Ext.extend(ImagePortal.LightboxView, Ext.DataView, {
});
	
	