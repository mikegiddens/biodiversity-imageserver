/**
 * @copyright SilverBiology, LLC
 * @author Michael Giddens
 * @website http://www.silverbiology.com
*/
Ext.namespace('ImagePortal');

ImagePortal.ReportsPanel = function(config){

	this.reportTree = new ImagePortal.ReportsTree({		
					region:'west'
				,	width:250
				, 	animCollapse: false
 				, 	collapseMode: 'mini'
		})

	this.reportTree.on('loadChart',this.statsbyregion ,this);
	
	this.pieChart = new ImagePortal.PieReport({
						//node:node
		});
				
	this.barChart = new ImagePortal.BarReport({
			//node:node
		});
	
	Ext.apply(this,config,{	
				width: 750
			, 	height: 490
			,	defaults: {
					border: false
				}
			, 	iconCls: 'icon_reports'
			,	paramsBrowse:''//{"node_type":"Family","node_value":"Callitrichaceae"}
			,	paramsFilter:''//{"Family":"C%"}
			,	layout: 'border'
			, 	items: [this.reportTree
					,{
						region: 'center'
					,	xtype:'panel'
					,	layout: 'card'
					,	ref:'centerPanel'
					,	defaults: {border: false}
					,	activeItem: 0
					,	title:'Charts'
					,	items: [{
							title: '&nbsp;'
						},this.pieChart,this.barChart]
				}]
			,	listeners:{
					 activate:function(){
					 		var list=this.reportTree.getRootNode().childNodes;
								Ext.each(list,function(item){
									if(item.isFirst()){
										item.select();
										item.getOwnerTree().fireEvent('nodeClicked',item,this);
									}
								})
						}	
				}
		});

ImagePortal.ReportsPanel.superclass.constructor.call(this, config);
};

Ext.extend(ImagePortal.ReportsPanel, Ext.Panel, {
		statsbyregion:function(node,activeChart){
			if(activeChart == "0"){
				this.pieChart.node = node;
				this.pieChart.reloadReports();
				this.centerPanel.getLayout().setActiveItem(0);
			}else{
				this.barChart.node = node;
				this.barChart.reloadReports();
				this.centerPanel.getLayout().setActiveItem(1);
			}	
		}
	,	findTab:function(varTitle){
			  var findTab = null
  			  this.centerPanel.items.each(function(rec){
   						if(rec.title == varTitle){
    							findTab = rec
    							}
   						}
   					)
  			return findTab
 		}  				
});