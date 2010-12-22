/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/
Ext.namespace('ImagePortal');

ImagePortal.NewsPanel = function(config) {
  
	var detailsText = '<i>Select a news posting to see more information...</i>';

	var tpl = new Ext.Template(
		'<h2 class="title">{title}</h2>',
		'{details}'
	);
  tpl.compile();


  Ext.apply(this,config,{
			layout: 'border'
		,	width: 500
		,	height: 500
		,	items: [{
					xtype: 'treepanel'
				,	id: 'news-tree-panel'
				,	region: 'center'
				,   title: 'News'
				,	margins: '2 2 0 2'
				,	autoScroll: true
				,	rootVisible: false
				,	root: new Ext.tree.AsyncTreeNode()
				,	dataUrl: 'news.json'
				,	listeners: {
						'render': function(tp){
							tp.getSelectionModel().on('selectionchange', function(tree, node){
								var el = Ext.getCmp('news-details-panel').body;
								if ( node && node.leaf ){
									tpl.overwrite(el, node.attributes);
								} else {
									el.update(detailsText);
								}
							})
						}
	                }
            }, {
						region: 'south'
					,	title: 'Message Details'
					,	id: 'news-details-panel'
					,	autoScroll: true
					,	bodyStyle: 'padding: 10px;'
					,	split: true
					,	margins: '0 2 2 2'
					,	cmargins: '2 2 2 2'
					,	height: 250
					,	html: detailsText
            }]
        });

 	ImagePortal.NewsPanel.superclass.constructor.call(this, config);
   
} 

Ext.extend(ImagePortal.NewsPanel, Ext.Panel, {

}); // end of extend

