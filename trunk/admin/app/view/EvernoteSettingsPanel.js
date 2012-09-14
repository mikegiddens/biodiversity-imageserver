Ext.define('BIS.view.EvernoteSettingsPanel', {
	extend: 'Ext.panel.Panel',
	alias: ['widget.evernotesettingspanel'],
	requires: ['BIS.view.FormCreateEvernoteAccount'],
	id: 'evernoteSettingsPanel',
	border: false,
	layout: 'fit',
	initComponent: function() {
		var me = this;	
		Ext.applyIf(me, {
			items: [{
				xtype: 'tabpanel',
				id: 'evernoteSettingsTabPanel',
				border: false,
				activeTab: 0,
				items: [
                    {
                        iconCls: 'icon_evernote',
                        title: 'Accounts',
                        xtype: 'gridpanel',
                        id: 'evernoteSettingsGrid',
                        bodyBorder: false,
                        border: false,
                        store: 'EvernoteAccountsStore',
                        defaults: {
                            sortable: true,
                            flex: 1
                        },
                        listeners: {
                            itemdblclick: function( grid, record, el, ind, e, opts ) {
                                var tmpWindow = Ext.create('Ext.window.Window', {
                                    title: 'Edit Evernote Account',
                                    iconCls: 'icon_evernote',
                                    modal: true,
                                    height: 500,
                                    width: 800,
                                    layout: 'fit',
                                    items: [{ 
                                        xtype: 'formcreateevernoteaccount',
                                        device: record,
                                        mode: 'edit'
                                    }]
                                });
                                tmpWindow.on('accountCreated', function( data ) {
                                    Ext.getCmp('evernoteSettingsGrid').getStore().load();
                                    tmpWindow.close();
                                });
                                tmpWindow.on('cancel', function( data ) {
                                    tmpWindow.close();
                                });
                                tmpWindow.show();
                            },
                            itemcontextmenu: function(view, record, item, index, e) {
                                e.stopEvent();
                                var tmpCtx = Ext.create('BIS.view.CtxMnuEvernote', {record: record});
                                tmpCtx.showAt( e.getXY() );
                            }
                        },
                        columns: [{
                            text: 'Name',
                            flex: 2,
                            dataIndex: 'accountName',
                        },{
                            text: 'Notebook Identifier',
                            dataIndex: 'notebookGuid',
                            flex: 2
                        },{
                            text: 'Username',
                            flex: 2,
                            dataIndex: 'username',
                        },{
                            text: 'Password',
                            flex: 2,
                            dataIndex: 'password',
                        },{
                            text: 'Key',
                            dataIndex: 'consumerKey',
                            flex: 2
                        },{
                            text: 'Secret',
                            dataIndex: 'consumerSecret',
                            flex: 2
                        }]
                    }
                ],
				dockedItems: [{
					xtype: 'toolbar',
					dock: 'top',
					items: [{
						text: 'Add Account',
						iconCls: 'icon_evernote',
						handler: this.createDevice
					}]
				}]
			}]
		});

		me.callParent(arguments);
	},
	createDevice: function() {
		var tmpWindow = Ext.create('Ext.window.Window', {
			title: 'Add Evernote Account',
			iconCls: 'icon_addDevice',
			modal: true,
			height: 350,
			width: 500,
			layout: 'fit',
			resizable: false,
			bodyBorder: false,
			items: [{
                xtype: 'formcreateevernoteaccount',
                mode: 'add'
            }]
		});
        tmpWindow.on('accountCreated', function( data ) {
            Ext.getCmp('evernoteSettingsGrid').getStore().load();
            tmpWindow.close();
        });
        tmpWindow.on('cancel', function( data ) {
            tmpWindow.close();
        });
        tmpWindow.show();
	}
});
