Ext.define('BIS.view.KeyManagerPanel', {
	extend: 'Ext.panel.Panel',
	alias: ['widget.keymanagerpanel'],
    requires: [ 'BIS.view.CtxMnuKey', 'BIS.view.FormCreateKey' ],
	id: 'keyManagerPanel',
	layout: 'fit',
	border: false,
	bodyBorder: false,
	initComponent: function() {
		var me = this;
		Ext.applyIf(me, {
			items: [{
				xtype: 'tabpanel',
				id: 'keyManagerTabPanel',
                border: false,
                bodyBorder: false,
				activeTab: 0,
				items: [{
					xtype: 'gridpanel',
					iconCls: 'icon_key',
					title: 'Access Keys',
					id: 'keyGrid',
					border: false,
					store: 'KeyStore',
                    viewConfig: {
                        enableTextSelection: true
                    },
					columns: [
                        {
                            dataIndex: 'title',
                            text: 'Title',
                            flex: 2
                        },
                        {
                            dataIndex: 'description',
                            text: 'Description',
                            flex: 3
                        },
                        {
                            dataIndex: 'originalIp',
                            text: 'IP Address',
                            flex: 2
                        },
                        {
                            dataIndex: 'key',
                            text: 'Access Key',
                            flex: 2
                        },
                        {
                            xtype: 'booleancolumn',
                            dataIndex: 'active',
                            text: 'Enabled?',
                            flex: 1,
                            falseText: 'No',
                            trueText: 'Yes',
                            undefinedText: 'n/a'
					    }
                    ],
					listeners: {
						itemcontextmenu: function(view, record, item, index, e) {
							e.stopEvent();
							Ext.create('BIS.view.CtxMnuKey', {record: record}).showAt( e.getXY() );
						}
					},
					dockedItems: [{
						xtype: 'toolbar',
						dock: 'top',
						items: [{
							text: 'Generate Key',
							iconCls: 'icon_addKey',
							scope: this,
							handler: this.createKey
						}]
					}]
				}]
			}]
		});
		me.callParent(arguments);
	},
	createKey: function() {
		var tmpWindow = Ext.create('Ext.window.Window', {
			title: 'Generate Access Key',
			iconCls: 'icon_addKey',
			modal: true,
			height: 200,
			width: 500,
			layout: 'fit',
			resizable: false,
			bodyBorder: false,
			items: [{
                xtype: 'formcreatekey'
            }]
		});
        tmpWindow.on('done', function( data ) {
            Ext.getCmp('keyGrid').getStore().load();
            tmpWindow.close();
        });
        tmpWindow.on('cancel', function( data ) {
            tmpWindow.close();
        });
        tmpWindow.show();
	}

});
