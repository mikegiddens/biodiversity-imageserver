/**
 * @copyright SilverBiology, LLC
 * @author Michael Giddens
 * @website http://www.silverbiology.com
*/
	Ext.namespace('ImagePortal');

	ImagePortal.Help = function(config){
	
		this.treePanel = new Ext.tree.TreePanel({
				id: 'tree-panel'
			,	title: 'Contents'
			,	region:'west'
			,	split: true
			,	width: 300
			,	minSize: 150
			,	autoScroll: true
			,	rootVisible: false
			,	lines: false
			,	useArrows: true
			,	loader: new Ext.tree.TreeLoader({
					dataUrl:config.url
				})
			,	root: new Ext.tree.AsyncTreeNode()
		}); 
		
		//this.treePanel.setRootNode(root);

		this.treePanel.on('click', function(n){
			var sn = this.selModel.selNode || {}; // selNode is null on initial selection
			if(n.leaf && n.id != sn.id){ // ignore clicks on folders and currently selected node
				Ext.getCmp('details-panel').setTitle(n.text);
				if (n.attributes.link != '') {
					Ext.getCmp('details-panel').load({ url: n.attributes.link });
				} else {
					Ext.getCmp('details-panel').load({ url: 'missing.html' });
				}
			}
		}); 

		Ext.apply(this,config,{
				layout: 'border'
			,	border:false
			,	url:''
			, 	items: [this.treePanel,{
	 				id: 'details-panel'
 				, 	title: 'Welcome'
 				, 	region: 'center'
 				, 	bodyStyle: 'padding-bottom:15px;background:#eee;'
 				, 	autoScroll: true			
			}]
		});
	
		ImagePortal.Help.superclass.constructor.call(this, config);
		
	} 
 
	Ext.extend(ImagePortal.Help, Ext.Panel, {

		setWelcomeUrl:function(htmlurl){
/*			Ext.getCmp('details-panel').load({ url: n.attributes.link });*/
		}
		
	});
