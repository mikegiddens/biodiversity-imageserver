Ext.define('BIS.view.EventTreePanel', {
	extend: 'Ext.tree.TreePanel',
	alias: ['widget.eventtreepanel'],
	requires: [
	],
	id: 'eventTreePanel',
    rootVisible: false,
	initComponent: function() {
		var me = this;
		Ext.applyIf(me, {
			store: 'EventTreeStore',
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
            scope: this,
			listeners: {
				show: function( el, opts ) {
                    if ( opts && opts.isAttribute ) delete opts.isEvent;
                    this.getStore().getProxy().extraParams = {
                        cmd: 'eventTypeList'
                    };
                    this.getStore().load();
					Ext.getCmp('viewsPagingTitle').setText('Events');
				},
                itemappend: function( thisNode, newChildNode, index, eOpts ) {
                    if ( eOpts && eOpts.isEvent ) {
                        //eOpts.isEvent = false;
                        newChildNode.set('modelClass', 'event');
                        newChildNode.set('leaf', true);
                                                   
                        //newChildNode.set('icon', newChildNode.get('profile_image_url'));
                        //newChildNode.set('cls', 'demo-userNode');
                        //newChildNode.set('iconCls', 'demo-userNodeIcon');
                    }
                },
				beforeitemexpand: function( record, opts ) {
                    opts.isEvent = true;
					this.getStore().getProxy().extraParams.cmd = 'eventList';
					this.getStore().getProxy().extraParams.eventTypeId = record.data.eventTypeId;
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
		var tmpWindow = Ext.create('Ext.window.Window', {
			title: 'Create Event Type',
			iconCls: 'icon_newEventType',
			modal: true,
			height: 100,
			width: 350,
			layout: 'fit',
			items: [{
				xtype: 'formcreateeventtype',
                border: false,
				mode: 'add'
			}]
		}).show();
        tmpWindow.on('eventTypeAdded',function(data){
            tmpWindow.close();
            Ext.getCmp('eventTreePanel').getStore().load();
        });
        tmpWindow.on('cancel',function(data){
            tmpWindow.close();
        });
	}
});
