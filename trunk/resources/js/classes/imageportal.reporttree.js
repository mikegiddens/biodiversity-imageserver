/**
 * @copyright SilverBiology, LLC
 * @author Michael Giddens
 * @website http://www.silverbiology.com
*/

Ext.namespace('ImagePortal');
 
// This is an override to fix the issue where no loader can be added into a node
// Check to see if this can be removed in future version > 2.2.1

Ext.override(Ext.tree.TreeLoader, {
	createNode : function(attr){
		if(this.baseAttrs){
			Ext.applyIf(attr, this.baseAttrs);
		}
		if(this.applyLoader !== false && !attr.loader){
			attr.loader = this;
		}
		if(typeof attr.uiProvider == 'string'){
		   attr.uiProvider = this.uiProviders[attr.uiProvider] || eval(attr.uiProvider);
		}
		if(attr.nodeType){
			return new Ext.tree.TreePanel.nodeTypes[attr.nodeType](attr);
		}else{
			return attr.leaf ?
						new Ext.tree.TreeNode(attr) :
						new Ext.tree.AsyncTreeNode(attr);
		}
	}
});

ImagePortal.ReportsTree = function(config){

var chartMode = new Ext.CycleButton({
			showText: true
		,	hidden: false
		,	ref: '../chartMode'
		,	scope: this
		,	items: [{
				text:'Taxonomy' 
			,	value: '0'
			//,	iconCls:'icon-scanner'
			},{
				text:'Collection Distribution' 
			,	value: '1'
			//,	iconCls:'icon-manual'
			}]
		,	changeHandler: this.changeChartMode
	});



Ext.apply(this,config,{	
				autoScroll: true
			,	defaults: {
					border: false
				}
			, 	iconCls: 'icon_reports'
			,	autoScroll: true
			//, 	animCollapse: false
			,	split: true       
			,   useArrows: true
			,   title:'Species'
			,	paramsBrowse:''
			,  	paramsFilter:''
			,  	rootVisible: false
			,	tbar:[chartMode]
			,	root: {
					text:'Root'
				,	expanded: true
				,	draggable: false
				,	expanded: true
					// Custom Fields
				,	nodeApi:	'root'
				,	nodeValue: null
				, 	filter: null	
				,	children: [{
						reportType: 'families'
					,	text: 'Family'
					,	leaf: false
					,	nodeValue:'Family'
					,	nodeApi:'alpha'
					,	filter:{}
					,	qtip: 'Click to load family report.'
					,	iconCls: 'icon_chart_pie'
					},{
						reportType: 'genus'
					,	text: 'Genus'
					,	leaf: false
					,	nodeValue:'Genus'
					,	nodeApi:'alpha'
					,	qtip: 'Click to load Genus report.'
					,	iconCls: 'icon_chart_pie'
					}]
				}
			,	loader: new Ext.tree.TreeLoader({
					dataUrl: Config.baseUrl + 'resources/api/api.php'
				,	root: 'results'
				,	disableCheckbox:true
				,	baseParams: { 
						cmd: 'browse'
					}
				, listeners: {
						loadexception: function(loader, node, response) {
							node.unload();
						} 
					}
				,	processResponse: function(response, node, callback, scope){
						var json = response.responseText;
						try {
							var o = response.responseData || Ext.decode(json);
							if ( o.success ) {
								o = o.results;
								node.beginUpdate();
								for(var i = 0, len = o.length; i < len; i++){
									if(typeof(o[i].checked) != "undefined"){
										if (this.disableCheckbox) {
											delete(o[i].checked);
										}
										o[i].qtip = 'Click to load '+ o[i].nodeValue +' report.'
									}
									o[i].iconCls='icon_chart_pie';
									var n = this.createNode(o[i]);
									if(n){
										node.appendChild(n);
									}
								}
								node.endUpdate();
							} else {
								node.unload();
							}
							this.runCallback(callback, scope || node, [node]);
						} catch(e) {
							node.unload();
						}
					}				
				})
			,	listeners: {
						beforeLoad: this.applyFilters
					,	click: this.logChecked	
					,	contextmenu: function(node) {
							this.showcontextmenu(node);
						}.createDelegate(this)
				}		
		});

ImagePortal.ReportsTree.superclass.constructor.call(this, config);
};
Ext.extend(ImagePortal.ReportsTree, Ext.tree.TreePanel, {
		collapseMenu: function() {
			this.collapse();
		}
	,	changeChartMode:function(){
			//this.getSelectedNode();
			var activeChart = this.chartMode.activeItem.value;
			this.fireEvent('loadChart',node,activeChart,this);
		}
	,	expandMenu: function() {
			this.expand();
		}

	,	hideMenu: function() {
			this.hide();
		}
		
	,	showMenu: function() {
			this.show();
		}
	,	applyFilters: function(node) {
			this.loader.baseParams = Ext.apply(this.loader.baseParams, {
					nodeApi: node.attributes.nodeApi
				, 	nodeValue: node.attributes.nodeValue
				, 	filter: Ext.encode(node.attributes.filter)
			});

			/*if (node.attributes.nodeApi != 'root') {
				var value = node.attributes.nodeValue;
				if (node.attributes.nodeValue == null) {
					value = '';
				}
			}*/
			}
	,	logChecked:function(node){
				//if(node.isLeaf())
				var activeChart = this.chartMode.activeItem.value;
				if(node.attributes.nodeValue == 'Family' || node.attributes.nodeValue == 'Genus'){
						
				}else{
					this.fireEvent('loadChart',node,activeChart,this);
				}	
			}
	,	addBrowseFilter: function(store, opt ) {
			var list = this.browseTree.getChecked();
			var tmp = [];
			Ext.each(list, function(item) {
				tmp.push({
						node_type: item.attributes.nodeApi
					, 	node_value: item.attributes.nodeValue
					, 	filter: item.attributes.filter
				});
			});
			opt.params.browse = Ext.encode(tmp);
		}
	,	showcontextmenu:function(node){
				var menu = new Ext.menu.Menu();
				var menu1 = new Ext.menu.Item({
						text: 'GBIF Checklist Bank'
					, 	handler: function(node){
							window.open('http://ecat-dev.gbif.org/search?q='+node.attributes.nodeValue+'&rkey=1');
						} 
					});
				var menu2 = new Ext.menu.Item({
						text: 'Wikispecies'
					, 	handler:function(node){
							window.open('http://species.wikimedia.org/wiki/Carex_'+node.attributes.nodeValue);
						} 
					});            
				
				menu.show(node.ui.getAnchor());
		}	
})
