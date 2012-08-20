Ext.define('BIS.view.CategoryTreePanel', {
	extend: 'Ext.tree.TreePanel',
	alias: ['widget.categorytreepanel'],
	requires: [
	],
	id: 'categoryTreePanel',
	initComponent: function() {
		var me = this;
		Ext.applyIf(me, {
			store: 'CategoryTreeStore',
			rootVisible: false,
			useArrows: true,
			multiSelect: true,
			allowCopy: true,
			viewConfig: {
				plugins: [
					Ext.create('Ext.tree.plugin.TreeViewDragDrop', {
						ddGroup: 'categoryDD',
						enableDrop: true,
						dragText: 'Copy attribute to another category.',
						appendOnly: true
					})
				],
				listeners: {
					beforedrop: function( el, dragobj, targetNode, action, opts ) {
						var record = dragobj.records[0].data;
						var target = targetNode.data;
						if ( action == 'append' ) {
							if ( record.modelClass == 'attribute' && target.modelClass == 'category' ) {
								if ( record.categoryId != target.categoryId ) {
									// send attributeAdd with record info on target categoryId
									return true;
								}
							}
						}
						return false;
					},
					isValidDropPoint: function( a,b,c,d,e ) {
						console.log( a, b, c, d,e  );
					}
				},
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
				show: function( el, opts ) {
					Ext.getCmp('viewsPagingTitle').setText('Categories');
				},
				beforeitemexpand: function( record, opts ) {
					this.getStore().getProxy().extraParams.cmd = 'attributeList';
					this.getStore().getProxy().extraParams.categoryId = record.data.categoryId;
					this.getStore().getProxy().extraParams.showNames = false;
					this.getStore().getProxy().setModel( 'BIS.model.AttributeModel' );
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
		Ext.create('Ext.window.Window', {
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
	}

});