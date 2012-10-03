Ext.define('BIS.view.CategoryTreePanel', {
	extend: 'Ext.tree.TreePanel',
	alias: ['widget.categorytreepanel'],
	requires: [
        'Ext.tree.plugin.TreeViewDragDrop'
	],
    uses: [
        'Ext.tree.ViewDropZone'
    ],
	id: 'categoryTreePanel',
    rootVisible: false,
    viewConfig: {
       plugins: [{
           ddGroup: 'imageDD',
           ptype: 'treeviewdragdrop'
       }]
    },
	initComponent: function() {
		var me = this;

		Ext.applyIf(me, {
			store: 'CategoryTreeStore',
			useArrows: true,
			multiSelect: true,
			allowCopy: true,
			viewConfig: {
				plugins: [
					Ext.create('Ext.tree.plugin.TreeViewDragDrop', {
						ddGroup: 'imageDD',
						dropGroup: 'imageDD',
						enableDrop: true,
						appendOnly: true,
                        listeners: {
                            drop: function( node, data, overModel, dropPos, e ) {
                                console.log( 'drop', node, data, overModel, dropPos, e );
                            }
                        }
					})
				],
				copy: true,
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
                drop: function( node, data, overModel, dropPos, e ) {
                    console.log( 'drop2', node, data, overModel, dropPos, e );
                },
                beforedrop: function( node, data, overModel, dropPos, e ) {
                    console.log( 'beforedrop2', node, data, overModel, dropPos, e );
                },
				show: function( el, opts ) {
                    if ( opts && opts.isAttribute ) delete opts.isAttribute;
                    this.getStore().getProxy().extraParams = {
                        cmd: 'categoryList'
                    };
                    this.getStore().load();
					Ext.getCmp('viewsPagingTitle').setText('Categories');
				},
                itemappend: function( thisNode, newChildNode, index, eOpts ) {
                    if ( eOpts && eOpts.isAttribute ) {
                        newChildNode.set('modelClass', 'attribute');
                        newChildNode.set('leaf', true);
                        newChildNode.set('title', newChildNode.get('name'));
                                                   
                        //newChildNode.set('icon', newChildNode.get('profile_image_url'));
                        //newChildNode.set('cls', 'demo-userNode');
                        //newChildNode.set('iconCls', 'demo-userNodeIcon');
                    }
                },
				beforeitemexpand: function( record, opts ) {
                    opts.isAttribute = true;
                    // http://stackoverflow.com/questions/11413724/different-node-types-in-a-extjs-4-1-treestore
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
			modal: true,
			height: 100,
			width: 350,
			layout: 'fit',
			items: [{
				xtype: 'formcreatecategory',
				mode: 'add',
				border: false
			}]
		}).show();
        tmpWindow.on( 'categoryCreated', function( data ) {
            tmpWindow.close();
            me.getStore().load();
        });
        tmpWindow.on( 'cancel', function( data ) {
            tmpWindow.close();
        });
	}

});
