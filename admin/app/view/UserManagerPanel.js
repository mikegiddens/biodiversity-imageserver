Ext.define('BIS.view.UserManagerPanel', {
	extend: 'Ext.panel.Panel',
	alias: ['widget.usermanagerpanel'],
	requires: ['BIS.view.FormCreateUser'],
	id: 'userManagerPanel',
	layout: 'fit',
	border: false,
	bodyBorder: false,
	initComponent: function() {
		var me = this;
		Ext.applyIf(me, {
			items: [{
				xtype: 'tabpanel',
				id: 'userManagerTabPanel',
				activeTab: 0,
				items: [{
					xtype: 'gridpanel',
					iconCls: 'icon_users',
					title: 'Users',
					id: 'usersGrid',
					border: false,
					store: 'UserStore',
					columns: [{
						xtype: 'booleancolumn',
						dataIndex: 'bool',
						text: 'First Name',
						falseText: 'No',
						trueText: 'Yes',
						undefinedText: 'n/a'
					}],
					listeners: {
						itemdblclick: function( grid, record, el, ind, e, opts ) {
							Ext.create('Ext.window.Window', {
								title: 'Edit User',
								iconCls: 'icon_editUser',
								modal: true,
								height: 500,
								width: 800,
								layout: 'fit',
								items: [{
									xtype: 'formcreateuser',
									user: record 
								}]
							}).show();
						},
						itemcontextmenu: function(view, record, item, index, e) {
							e.stopEvent();
							Ext.create('BIS.view.CtxMnuUser', {record: record}).showAt( e.getXY() );
						}
					},
					dockedItems: [{
						xtype: 'toolbar',
						dock: 'top',
						items: [{
							text: 'Add User',
							iconCls: 'icon_addUser',
							scope: this,
							handler: this.createUser
						}]
					}]
				}]
			}]
		});

		me.callParent(arguments);
	},
	createUser: function() {
		Ext.create('Ext.window.Window', {
			title: 'Add New User',
			iconCls: 'icon_addUser',
			modal: true,
			height: 500,
			width: 800,
			layout: 'fit',
			items: [{
				xtype: 'formcreateuser' 
			}]
		}).show();
	}
});