Ext.define('BIS.view.CollectionTreePanel', {
	extend: 'Ext.tree.TreePanel',
	alias: ['widget.collectiontreepanel'],
	id: 'collectionTreePanel',
    rootVisible: false,
	initComponent: function() {
		var me = this;
		Ext.applyIf(me, {
			store: 'CollectionsTreeStore',
			useArrows: true,
			columns: [{
				xtype: 'treecolumn',
				text: 'Name',
				flex: 3,
				dataIndex: 'name',
				sortable: true
			},{
				text: 'Code',
				flex: 1,
				dataIndex: 'code',
				sortable: true
			},{
				text: 'Images',
				flex: 1,
				dataIndex: 'collectionSize',
				sortable: true,
				renderer: function( value ) {
					return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
				}
			}],
			scope: this,
			listeners: {
				show: function( el, opts ) {
                    this.getStore().load();
					Ext.getCmp('viewsPagingTitle').setText('Collections');
				},
				itemcontextmenu: function(view, record, item, index, e) {
					e.stopEvent();
					var ctx = Ext.create('BIS.view.CtxMnuCollection', {record: record});
					ctx.showAt(e.getXY());
				}
			},
			dockedItems: [{
				xtype: 'toolbar',
				dock: 'top',
				items: [{
					text: 'New Collection',
					iconCls: 'icon_newCollection',
					scope: this,
					handler: this.createCollection
				}]
			}]
		});
		me.callParent(arguments);
	},

	createCollection: function() {
        var me = this;
        var tmpWindow = Ext.create('Ext.window.Window', {
			title: 'Create Collection',
			iconCls: 'icon_newCollection',
			modal: true,
			height: 150,
			width: 350,
			layout: 'fit',
			items: [{
				xtype: 'formcreatecollection',
				mode: 'add'
			}]
		}).show();
        tmpWindow.on( 'collectionCreated', function( data ) {
            tmpWindow.close();
            me.getStore().load();
        });
        tmpWindow.on( 'cancel', function( data ) {
            tmpWindow.close();
        });
	}
});
