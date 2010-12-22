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
					,	layout: 'card'
					,	ref:'centerPanel'
					,	defaults: {border: false}
					,	activeItem: 0
					,	items: [{
							title: '&nbsp;'							
						}]
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
		statsbyregion:function(node){
			var pieChart=new ImagePortal.PieReport({
						node:node
			});
			this.centerPanel.add(pieChart);
			this.centerPanel.getLayout().setActiveItem(pieChart);
			pieChart.reloadReports();
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