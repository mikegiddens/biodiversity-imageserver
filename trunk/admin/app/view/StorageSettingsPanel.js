Ext.define('BIS.view.StorageSettingsPanel', {
	extend: 'Ext.panel.Panel',
	alias: ['widget.storagesettingspanel'],
	requires: ['BIS.view.FormCreateDevice'],
	id: 'storageSettingsPanel',
	border: false,
	layout: 'fit',
	initComponent: function() {
		var me = this;	
		Ext.applyIf(me, {
			items: [{
				xtype: 'tabpanel',
				id: 'storageDevicesTabPanel',
				border: false,
				activeTab: 0,
				items: [{
					iconCls: 'icon_devices',
					title: 'Devices',
					xtype: 'gridpanel',
					id: 'storageDevicesGrid',
					bodyBorder: false,
					border: false,
					store: 'StorageDevicesStore',
					defaults: {
						sortable: true,
						flex: 1
					},
                    listeners: {
                        itemdblclick: function( grid, record, el, ind, e, opts ) {
                            var tmpWindow = Ext.create('Ext.window.Window', {
                                title: 'Edit Storage Device',
                                iconCls: 'icon_editDevice',
                                modal: true,
                                height: 500,
                                width: 800,
                                layout: 'fit',
                                items: [{ 
                                    xtype: 'formcreatedevice',
                                    device: record,
                                    mode: 'edit'
                                }]
                            });
                            tmpWindow.on('deviceCreated', function( data ) {
                                Ext.getCmp('storageDevicesGrid').getStore().load();
                                tmpWindow.close();
                            });
                            tmpWindow.show();
                        },
                        itemcontextmenu: function(view, record, item, index, e) {
                            e.stopEvent();
                            var tmpCtx = Ext.create('BIS.view.CtxMnuDevice', {record: record});
                            tmpCtx.on('deviceDeleted', function( data ) {
                                Ext.getCmp('storageDevicesGrid').getStore().load();
                            });
                            tmpCtx.showAt( e.getXY() );
                        }
                    },
					columns: [{
						text: 'Identifier',
						dataIndex: 'storage_id'
					},{
						text: 'Name',
						flex: 2,
						dataIndex: 'name',
					},{
						text: 'Description',
						flex: 2,
						dataIndex: 'description',
					},{
						text: 'Type',
						dataIndex: 'type',
					},{
						text: 'Base URL',
						flex: 2,
						dataIndex: 'baseUrl',
					},{
						text: 'Base Path',
						flex: 2,
						dataIndex: 'basePath',
					},{
						text: 'Username',
						dataIndex: 'user',
					},{
						text: 'Password',
						dataIndex: 'pw',
					},{
						text: 'Active?',
						dataIndex: 'active',
						renderer: function( value ) {
							if ( value ) { return 'Yes' }
							return ' ';
						}
					},{
						text: 'Notes',
						dataIndex: 'extra2',
					}]
                }],
				dockedItems: [{
					xtype: 'toolbar',
					dock: 'top',
					items: [{
						text: 'Add Device',
						iconCls: 'icon_addDevice',
						handler: this.createDevice
					}]
				}]
			}]
		});

		me.callParent(arguments);
	},
	createDevice: function() {
		var tmpWindow = Ext.create('Ext.window.Window', {
			title: 'Add Storage Device',
			iconCls: 'icon_addDevice',
			modal: true,
			height: 350,
			width: 500,
			layout: 'fit',
			resizable: false,
			bodyBorder: false,
			items: [{
                xtype: 'formcreatedevice',
                mode: 'add'
            }]
		});
        tmpWindow.on('deviceCreated', function( data ) {
            Ext.getCmp('storageDevicesGrid').getStore().load();
            tmpWindow.close();
        });
        tmpWindow.show();
	}
});
