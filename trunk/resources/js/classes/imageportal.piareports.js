/**
 * @copyright SilverBiology, LLC
 * @author Michael Giddens
 * @website http://www.silverbiology.com
*/
Ext.namespace('ImagePortal');
 

ImagePortal.PieReport = function(config){
	
	var store = new Ext.data.JsonStore({
				url:  'resources/api/api.php'
			,	root: 'results'
			,	baseParams: { 
						 cmd: 'browse'
					}
			,	fields: [
						{ name: 'nodeValue', type: 'string' }
					,	{ name: 'specimenCount', type: 'int' }
					]
		},this);
	
	store.on('beforeload', this.setParams ,this);
	
	store.on('load', this.onLoadCallback ,this);
	
	Ext.apply(this,config,{	
				layout:'fit'
			,	defaults: {
						border: false
					}
			,	store: store
			,	body:''
			,	columns: [
						{ dataIndex: 'nodeValue', label:'Taxanomy' }
					,	{ dataIndex: 'specimenCount',  label:'Specimen Count' }
					]	
			,	visualizationPkg: {'piechart':'PieChart'}
			, 	visualizationCfg: {legend: 'label', pieJoinAngle: 5}
	});
		ImagePortal.PieReport.superclass.constructor.call(this, config);
};

Ext.extend(ImagePortal.PieReport, Ext.ux.GVisualizationPanel, {
	reloadReports:function(){
			this.store.load();
		}
,	setParams:function() {
			var node = this.node;
			this.store.baseParams.nodeApi = node.attributes.nodeApi;
			this.store.baseParams.nodeValue = node.attributes.nodeValue;
			this.store.baseParams.filter = Ext.encode(node.attributes.filter);
		}
});