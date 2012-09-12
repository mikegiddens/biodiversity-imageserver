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
                            dataIndex: 'bool',
                            text: 'Active?',
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
        Ext.Msg.prompt('Generate Access Key', 'Please enter the IP address for the new key.', function( value ) {
            console.log( value );
            Ext.Ajax.request({
                method: 'POST',
                url: Config.baseUrl + 'resouces/api/api.php',
                params: {
                    cmd: 'remoteAccessKeyGenerate',
                    ip: value
                },
                scope: this,
                success: function( resObj ) {
                    var res = Ext.decode( resObj.responseText );
                    console.log( res );
                    if ( res.success ) {
                        Ext.getCmp('keyGrid').getStore().load();
                    }
                }
            });
        });
	}
});
