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
						{ name: 'nodeValue', type: 'string' }
					,	{ name: 'specimenCount', type: 'int' }
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
						xtype: 'columnchart'
					,	store: this.store
					,	url: 'resources/ext/resources/charts.swf'
					,	xField: 'name'
					,	yField: 'ds1'
					,	yAxis: new Ext.chart.NumericAxis({
								displayName: 'Visits'
							,	labelRenderer : Ext.util.Format.numberRenderer('0,0')
						})
					,	tipRenderer : function(chart, record){
								return Ext.util.Format.number(record.data.ds1, '0,0') + ' images at hour ' + record.data.name;
						}
					,	extraStyle:{
							xAxis:{
								labelRotation:-90	
							}
						}		
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