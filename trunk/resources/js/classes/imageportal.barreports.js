/**
 * @copyright SilverBiology, LLC
 * @author Michael Giddens
 * @website http://www.silverbiology.com
*/
Ext.namespace('ImagePortal');
 

ImagePortal.BarReport = function(config){
	
	this.store = new Ext.data.JsonStore({
				url:  'resources/api/api.php'
			,	root: 'results'
			,	baseParams: { 
						 cmd: 'getCollectionSpecimenCount'
					}
			,	fields: [
						{ name: 'CollectionCode', type: 'string' }
					,	{ name: 'ct', type: 'int' }
					]
		},this);
	
	this.store.on('beforeload', this.setParams ,this);
	
	this.store.on('load', this.onLoadCallback ,this);
	
	Ext.apply(this,config,{	
			layout:'fit'
		,	defaults: {
					border: false
				}
		,	items: {
					xtype: 'stackedbarchart'
				,	store: this.store
				,	yField: 'CollectionCode'
				,	xAxis: new Ext.chart.NumericAxis({
							stackingEnabled: false
						,	labelRenderer: Ext.util.Format.numberRenderer('0,0')
					})
				,	series: [{
							xField: 'ct'
						,	displayName: 'ct'
					//	,	style: config.styleImaged
					}]
			}
		});
	ImagePortal.BarReport.superclass.constructor.call(this, config);
};

Ext.extend(ImagePortal.BarReport, Ext.Panel, {
	reloadReports:function(){
			this.store.load();
		}
,	setParams:function() {
			var node = this.node;
			this.setText(node);
			this.store.baseParams.nodeApi = node.attributes.nodeApi;
			this.store.baseParams.nodeValue = node.attributes.nodeValue;
			//this.store.baseParams.filter = Ext.encode(node.attributes.filter);
		}
,	setText:function(node){
			this.setTitle(node.attributes.nodeValue);
		}			
});