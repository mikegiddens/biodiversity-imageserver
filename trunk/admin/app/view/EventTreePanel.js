Ext.define('BIS.view.EventTreePanel', {
	extend: 'Ext.tree.TreePanel',
	alias: ['widget.eventtreepanel'],
	requires: [
	],
	id: 'eventTreePanel',
	initComponent: function() {
		var me = this;
		Ext.applyIf(me, {
			store: 'EventTreeStore',
			rootVisible: false,
			useArrows: true,
			columns: [{
				xtype: 'treecolumn',
				text: 'Title',
				flex: 1,
				dataIndex: 'title',
				sortable: true
			},{
				text: 'Description',
				flex: 1,
				dataIndex: 'description',
				sortable: true
			}],
			viewConfig: {
				plugins: [
					Ext.create('Ext.tree.plugin.TreeViewDragDrop', {
						ddGroup: 'eventDD',
						enableDrop: true
					})
				],
				listeners: {
					beforedrop: function( el, dragobj, targetNode, action, opts ) {
						var record = dragobj.records[0].data;
						var target = targetNode.data;
						console.log( record, target );
						if ( action == 'append' ) {
							if ( record.modelClass == 'event' && target.modelClass == 'eventtype' ) {
								if ( record.eventTypeId != target.eventTypeId ) {
									// send eventAdd with record info on target eventTypeId
									return true;
								}
							}
						}
						return false;
					}
				},
				copy: true,
				loadMask: false
			},
			listeners: {
				show: function( el, opts ) {
					Ext.getCmp('viewsPagingTitle').setText('Events');
				},
				beforeitemexpand: function( record, opts ) {
					this.getStore().getProxy().extraParams.cmd = 'eventList';
					this.getStore().getProxy().extraParams.filter = {eventTypeId: record.data.eventTypeId};
					this.getStore().getProxy().setModel( 'BIS.model.EventModel' );
				},
				itemcontextmenu: function(view, record, item, index, e) {
					e.stopEvent();
					var ctx;
					switch( record.data.modelClass ) {
						case 'eventtype':
							ctx = Ext.create('BIS.view.CtxMnuEventType', {record: record});
							break;
						case 'event':
							ctx = Ext.create('BIS.view.CtxMnuEvent', {record: record});
							break;
					}
					ctx.showAt(e.getXY());
				}
			},
			dockedItems: [{
				xtype: 'toolbar',
				dock: 'top',
				items: [{
					text: 'New Event Type',
					iconCls: 'icon_newEventType',
					scope: this,
					handler: this.createEventType
				}]
			}]
													
		});
		me.callParent(arguments);
	},

	createEventType: function() {
		Ext.create('Ext.window.Window', {
			title: 'Create Event Type',
			iconCls: 'icon_newEventType',
			modal: true,
			height: 500,
			width: 800,
			layout: 'fit',
			items: [{
				xtype: 'formcreateeventtype',
				mode: 'add'
			}]
		}).show();
	}

});