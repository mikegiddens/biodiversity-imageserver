Ext.define('BIS.view.KeyManagerPanel', {
	extend: 'Ext.panel.Panel',
	alias: ['widget.keymanagerpanel'],
    requires: [ 'BIS.view.CtxMnuKey' ],
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
                            dataIndex: 'ip',
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
        Ext.Msg.prompt('Generate Access Key', 'Please enter the IP address for the new key.', function( btn, val, dialog ) {
            if ( btn == 'ok' ) {
                Ext.Ajax.request({
                    method: 'POST',
                    url: Config.baseUrl + 'resources/api/api.php',
                    params: {
                        cmd: 'remoteAccessKeyGenerate',
                        ip: val
                    },
                    scope: this,
                    success: function( resObj ) {
                        var res = Ext.decode( resObj.responseText );
                        if ( res.success ) {
                            Ext.getCmp('keyGrid').getStore().load();
                        }
                    }
                });
            }
        });
	}
});
