Ext.namespace('ImagePortal')
ImagePortal.gridlight=function(config){
	this.proxy = '';
		if(Config.mode == 'local'){
			this.proxy = new Ext.data.HttpProxy({
				url: Config.baseUrl + 'resources/api/api.php'
			})
		}else{
			this.proxy = new Ext.data.ScriptTagProxy({
					url: Config.baseUrl + 'resources/api/api.php'
			})
		}
		
	this.store = new Ext.data.JsonStore({
			proxy: this.proxy
		,	reader: new Ext.data.JsonReader({
				root: 'data'
			,	totalProperty: 'totalCount'
			, 	fields:[
	            	{name: 'filename'}
            	,	{name: 'path'}
			]
        })
		,	baseParams:{
					cmd: 'images'
				,	filters:''
				,	code:''
				,	"pagesize": 10
			}
		,	autoLoad: true
	});

	var datav=new Ext.DataView({
		store:imagestore	,
		autoHeight:true,
        multiSelect: true,
        overClass:'x-view-over',
        itemSelector:'div.thumb-wrap',
        emptyText: 'No images to display'	
	});
	
	Ext.apply(this,config,{
			border: true
		,	height: 550
		,	width:400
		,	title:'Image'
		,	autoScroll:true
		,	items:[datav]
	});
	ImagePortal.imagePanel.superclass.constructor.call(this,config)
	
};
Ext.extend(ImagePortal.imagePanel,Ext.Panel,{});
