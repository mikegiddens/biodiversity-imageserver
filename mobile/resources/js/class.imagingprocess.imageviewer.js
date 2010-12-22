
/**
 * @copyright SilverBiology, LLC
 * @author SilverBiology, LLC
 * @website http://www.silverbiology.com
*/
Ext.ns('ImagingProgress');

ImagingProgress.ImageViewer = function(config){
	
	var store = new Ext.data.Store({
	            model: 'CFLAImages'
            ,	proxy: {
	                	type: 'ajax'
	                ,	url: ''   
					,	reader: {
					            type: 'json'
					         ,  root: 'data'
							 ,	totalProperty: 'totalCount'
					        }
				}
			,	listeners:{
						dataChanged:function(data,a){
							//	console.log("DataChanged",data,a);
						}
				}	
        });
		
	Ext.apply(this, config, { 
				store : store
			,	tpl : new Ext.XTemplate(
				            '<tpl for=".">',
					            '<div class="thumb-wrap" id="{imageID}">',
					           	'<div class="thumb"><img onerror="this.src=\'http://images.cyberfloralouisiana.com/resources/images/no-image.gif\'" src="{path}{barcode}_s.jpg" class="thumbsmall"></div>',
				            '</tpl>'
				        )
			,	overClass : 'x-view-over'
			,	itemSelector : 'div.thumb-wrap'
			,	emptyText : 'No images to display'
			,	style:'overflow:auto'
			,	draggable:false
			,	loadingText:'Loading....'
			,	listeners: {
							activate:this.loadStore
						,	"itemTap":this.showImage
						}
						
			,	dockedItems: [{
						xtype: 'toolbar'
					,	dock: 'top'
					,	title: 'Images 1 to 100'
					,	ui:'light'
					,	items: [{
								text: 'Back'
							,	ui: 'back'
							,	handler: function(){
										var code = Ext.isEmpty(this.ownerCt.ownerCt.code);
										if(code)
											CFLABUS.fireEvent('ChangeMainMenu',0,false,this);
										else
											CFLABUS.fireEvent('ChangeMainMenu',0,true,this);	
									}
							},{
								xtype:'spacer'
							},{
								text: 'pre'
							,	ui: 'back'
							,	handler: function(){
										this.ownerCt.ownerCt.loadPrvImages();
									//	CFLABUS.fireEvent('ChangeMainMenu',0,true,this);
									}
							},{
								text: 'nxt'
							,	ui: 'next'
							,	handler: function(){
										this.ownerCt.ownerCt.loadNextImages();
										//CFLABUS.fireEvent('ChangeMainMenu',0,true,this);
									}
							}]
				}]
		});
		
		ImagingProgress.ImageViewer.superclass.constructor.call(this,config);
};
Ext.extend(ImagingProgress.ImageViewer , Ext.DataView, {
		showImage:function(dataview, index, item, evt){
					//console.log(dataview, index, item, evt);
					var data = this.store.getAt(index).data;
					if(data.gTileProcessed!=1)
							CFLABUS.fireEvent('ChangeMainMenu',3,data,this);
					else
							CFLABUS.fireEvent('ChangeMainMenu',4,data,this);		
							
			}
	,	loadStore:function(){
						if(!this.storeUrl){
								return;		
							}
							this.start = this.getUrlParam('start');
							this.limit = this.getUrlParam('limit');
							this.code = this.getUrlParam('code');
							this.setToolbarTitle();
							this.store.proxy.url = this.storeUrl;
							this.store.load();
			}
	
	,	getUrlParam : function(param) {
				var params = Ext.urlDecode(this.storeUrl);
				return param ? params[param] : params;
			}
	,	loadNextImages:function(){
					this.start = this.limit;
					this.limit = parseInt(this.limit) + 100; 
					this.storeUrl='http://images.cyberfloralouisiana.com/resources/api/api.php?cmd=images&code'+this.code+'&dir=ASC&filter=&limit='+this.limit+'&sort&start='+this.start
					this.loadStore();
			}
	,	loadPrvImages:function(){
					if(this.start==0){
							return;
					}
					this.start = parseInt(this.start) - 100; 
					this.limit = parseInt(this.limit) - 100;
					this.storeUrl='http://images.cyberfloralouisiana.com/resources/api/api.php?cmd=images&code'+this.code+'&dir=ASC&filter=&limit='+this.limit+'&sort&start='+this.start
					this.loadStore();
			}	
	,	setToolbarTitle:function(){
					var tempToolbar = this.dockedItems.items[0];
					tempToolbar.setTitle('Images '+ (parseInt(this.start)+ 1) +' to '+ this.limit );
					tempToolbar.doLayout();
			}			
							
});	
	