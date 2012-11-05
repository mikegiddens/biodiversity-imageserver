Ext.define('BIS.view.GeographyTreePanel', {
	extend: 'Ext.tree.TreePanel',
	alias: ['widget.geographytreepanel'],
    requires: [
        'BIS.view.CtxMnuGeography',
        'BIS.view.FormCreateGeography'
    ],
	id: 'geographyTreePanel',

    rootVisible: false,
	initComponent: function() {
		var me = this;

		Ext.applyIf(me, {
			store: 'GeographyTreeStore',
			useArrows: true,
			multiSelect: true,
			viewConfig: {
				loadMask: false
			},
			columns: [{
				xtype: 'treecolumn',
				text: 'Region',
				flex: 1,
				dataIndex: 'name',
				sortable: true
			}],
			scope: this,
			listeners: {
				show: function( el, opts ) {
                    Ext.getCmp('viewsPagingTitle').setText('Geography');
				},
                itemappend: function( thisNode, newChildNode, index, eOpts ) {
                    var rank = newChildNode.get('rank');
                    if ( rank > 3 ) {
                        newChildNode.set('leaf', true);
                    }
                    if ( newChildNode.get('source') == 'user' ) newChildNode.set('iconCls', 'icon_map_link');
                },
				beforeitemexpand: function( record, opts ) {
                    var proxy = this.getStore().getProxy();
                    proxy.extraParams.parentId = record.get('geographyId');
				},
				itemcontextmenu: function(view, record, item, index, e) {
					e.stopEvent();
                    var ctx = Ext.create('BIS.view.CtxMnuGeography', {record: record});
                    ctx.showAt(e.getXY());
				},
				itemclick: function( tree, record, el, ind, e, opts ) {
				}
			},
			dockedItems: [{
				xtype: 'toolbar',
				dock: 'top',
				items: [{
					text: 'Add Geography',
					iconCls: 'icon_newGeography',
					scope: this,
					handler: this.createGeography
				}]
			}]
		});
		me.callParent(arguments);
	},

	createGeography: function() {
        var me = this;
		var tmpWindow = Ext.create('Ext.window.Window', {
			title: 'Add Geography',
			iconCls: 'icon_newGeography',
            resizable: false,
			modal: true,
			height: 175,
			width: 350,
			layout: 'fit',
			items: [{
				xtype: 'formcreategeography',
				mode: 'add',
				border: false
			}]
		}).show();
        tmpWindow.on( 'done', function( data ) {
            tmpWindow.close();
            var store = me.getStore();
            store.getProxy().extraParams.parentId = 0;
            store.load();
        });
        tmpWindow.on( 'cancel', function( data ) {
            tmpWindow.close();
        });

	}

});
