Ext.namespace('ImagePortal');
ImagePortal.ProgressOfCollection=function(config){
	this.store = config.store || new Ext.data.JsonStore({
		url:Config.baseUrl+'resources/api/api.php'
	,	fields:['collection','imaged','notimaged']
	,	root:'data'
	,	baseParams:{
			cmd:'sizeOfCollection'
		}
	});
	Ext.apply(this,config,{
		width:400
	,	height:400
	,	title:'Progress of Collections'
	,	items:{
			xtype:'stackedbarchart'
		,	store: this.store
		,	yField:'collection'
		,	xAxis:new Ext.chart.NumericAxis({
				stackingEnabled:true
			,	labelRenderer:Ext.util.Format.numberRenderer('0,0')
			})
		,	series:[{
				xField:'imaged'
			,	displayName:'Imaged'
			,	style:config.styleImaged
			}
		,	{	
				xField:'notimaged'
			,	displayName:'Not Imaged'
			,	style:config.styleNotImaged
			}]
		}
	});
	ImagePortal.ProgressOfCollection.superclass.constructor.call(this,config);
};
Ext.extend(ImagePortal.ProgressOfCollection,Ext.Panel,{});
