Ext.define('BIS.view.SetTreePanel', {
	extend: 'Ext.tree.TreePanel',
	alias: ['widget.settreepanel'],
	requires: [
	],
	id: 'setTreePanel',
	initComponent: function() {
		var me = this;
		Ext.applyIf(me, {
			id: 'setTreePanel',
			store: 'SetTreeStore',
			rootVisible: false,
			useArrows: true,
			columns: [{
				xtype: 'treecolumn',
				text: 'Name',
				flex: 1,
				dataIndex: 'name',
				sortable: true
			},{
				text: 'Description',
				flex: 1,
				dataIndex: 'description',
				sortable: true
			}],
			listeners: {
				scope: this,
				show: function( el, opts ) {
					Ext.getCmp('viewsPagingTitle').setText('Sets');
				}
			}
		});
		me.callParent(arguments);
	},
});