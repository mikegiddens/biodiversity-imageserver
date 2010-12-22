Ext.ux.TreeCombo = Ext.extend(Ext.form.ComboBox, {
	initList: function() {
		this.addEvents('nodeId');
		
		this.list = new Ext.tree.TreePanel({
			loader: new Ext.tree.TreeLoader({
							dataUrl:this.url
						,	baseParams:{
								cmd:'getStationUsers'
							}	
						,	requestMethod:'GET'	
				}),
			rootVisible: false,
			root: new Ext.tree.AsyncTreeNode({
				expanded: true
			}),		
			floating: true,
			autoHeight: true,
			useArrows:true,
			singleSelect:true,
			listeners: {
					click: function(node, e){
						this.onNodeClick(node, e);
					}
				,	scope: this
			},
			alignTo: function(el, pos) {
				this.setPagePosition(this.el.getAlignToXY(el, pos));
			}
		});
	},

   getTree:function(url){
		this.url = url;
    },
    
   expand: function() {
		if (!this.list.rendered) {
			this.list.render(document.body);
			this.list.setWidth(this.el.getWidth());
			this.innerList = this.list.body;
			this.list.hide();
		}
		this.el.focus();
		Ext.ux.TreeCombo.superclass.expand.apply(this, arguments);
	},

	doQuery: function(q, forceAll) {
		this.expand();
	},

    collapseIf : function(e){
		if(!e.within(this.wrap) && !e.within(this.list.el)){
           this.collapse();
        }
    },

	onNodeClick: function(node, e) {
		this.setRawValue(node.attributes.text);
		if (this.hiddenField) {
			this.hiddenField.value = node.id;
		}
		this.collapse();
		this.fireEvent('nodeId',node.id, node.attributes.text, this)
	}
});
