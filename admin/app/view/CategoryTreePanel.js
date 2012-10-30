Ext.define('BIS.view.CategoryTreePanel', {
	extend: 'Ext.tree.TreePanel',
	alias: ['widget.categorytreepanel'],
	id: 'categoryTreePanel',

    rootVisible: false,
	initComponent: function() {
		var me = this;

		Ext.applyIf(me, {
			store: 'CategoryTreeStore',
			useArrows: true,
			multiSelect: true,
			viewConfig: {
				loadMask: false
			},
			columns: [{
				xtype: 'treecolumn',
				text: 'Title',
				flex: 1,
				dataIndex: 'title',
				sortable: true
			}],
			scope: this,
			listeners: {
				show: function( el, opts ) {
                    if ( opts && opts.isAttribute ) delete opts.isAttribute;
                    this.getStore().getProxy().extraParams = {
                        cmd: 'categoryList'
                    };
                    this.getStore().load();
					Ext.getCmp('viewsPagingTitle').setText('Categories');
				},
                itemappend: function( thisNode, newChildNode, index, eOpts ) {
                    /*
                    var rootNode = this.getRootNode();
                    var namespaceNode = rootNode.findChild( 'title', newChildNode.get('elementSet') );
                    if ( namespaceNode ) {
                        if ( !namespaceNode.findChild( 'categoryId', newChildNode.get('categoryId') ) ) {
                            namespaceNode.appendChild( newChildNode );
                        }
                    } else {
                        rootNode.appendChild({ title: newChildNode.get('elementSet'), leaf: false, expanded: true }).appendChild( newChildNode );
                    }
                    */
                    /*
                    if ( eOpts && eOpts.isAttribute ) {
                        newChildNode.set('modelClass', 'attribute');
                        newChildNode.set('leaf', true);
                        newChildNode.set('iconCls', 'icon_attribute');
                        newChildNode.set('title', newChildNode.get('name'));
                    }
                    if ( eOpts && eOpts.isCategory ) {
                        newChildNode.set('modelClass', 'category');
                        newChildNode.set('leaf', false);
                        newChildNode.set('iconCls', 'icon_category');
                    }
                    */
                    return false;
                },
				beforeitemexpand: function( record, opts ) {
                    opts.isAttribute = true;
					this.getStore().getProxy().extraParams.cmd = 'attributeList';
					this.getStore().getProxy().extraParams.categoryId = record.data.categoryId;
					this.getStore().getProxy().extraParams.showNames = false;
				},
				itemcontextmenu: function(view, record, item, index, e) {
					e.stopEvent();
					var ctx;
					switch( record.data.modelClass ) {
						case 'category':
							ctx = Ext.create('BIS.view.CtxMnuCategory', {record: record});
							break;
						case 'attribute':
							ctx = Ext.create('BIS.view.CtxMnuAttribute', {record: record});
							break;
					}
					ctx.showAt(e.getXY());
				},
				itemclick: function( tree, record, el, ind, e, opts ) {
				}
			},
			dockedItems: [{
				xtype: 'toolbar',
				dock: 'top',
				items: [{
					text: 'New Category',
					iconCls: 'icon_newCategory',
					scope: this,
					handler: this.createCategory
				}]
			}]
		});
		me.callParent(arguments);
	},

	createCategory: function() {
        var me = this;
		var tmpWindow = Ext.create('Ext.window.Window', {
			title: 'Create Category',
			iconCls: 'icon_newCategory',
            resizable: false,
			modal: true,
			height: 250,
			width: 500,
			layout: 'fit',
			items: [{
				xtype: 'formcreatecategory',
				mode: 'add',
				border: false
			}]
		}).show();
        tmpWindow.on( 'categoryCreated', function( data ) {
            tmpWindow.close();
            var store = me.getStore();
            store.load({
                node: store.getRootNode()
            });
        });
        tmpWindow.on( 'cancel', function( data ) {
            tmpWindow.close();
        });

	}

});
