Ext.define('BIS.view.FormCreateEvernoteAccount', {
	extend: 'Ext.form.Panel',
	alias: ['widget.formcreateevernoteaccount'],
	id: 'createEvernoteAccountForm',
	layout: 'anchor',
	bodyPadding: 10,
	labelWidth: 80,
	border: false,
	bodyBody: false,
	defaults: {
		labelAlign: 'right',
		anchor: '100%'
	},
	defaultType: 'textfield',
	initComponent: function() {
		var me = this;
		Ext.applyIf(me, {
            items: [{
                fieldLabel: 'Identifier',
                name: 'enAccountId',
                readOnly: true,
                fieldCls: 'x-item-disabled',
                hidden: (this.mode == 'add')
            },{
                fieldLabel: 'Name',
                name: 'accountName'
            },{
                fieldLabel: 'Notebook Identifier',
                name: 'notebookGuid'
            },{
                fieldLabel: 'Username',
                name: 'userName'
            },{
                fieldLabel: 'Password',
                name: 'password'
            },{
                fieldLabel: 'Key',
                name: 'consumerKey'
            },{
                fieldLabel: 'Secret',
                name: 'consumerSecret'
            },{
                xtype: 'hiddenfield',
                name: 'cmd',
                value: ( this.mode == 'add' ) ? 'evernoteAccountAdd' : 'evernoteAccountUpdate'
            }],
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                ui: 'footer',
                items: [
                    {
                        width: 80,
                        text: ( this.mode == 'add' ) ? 'Add Account' : 'Update Account',
                        iconCls: 'icon_evernote',
                        scope: this,
                        handler: this.submitForm
                    },
                    '->',
                    {
                        width: 80,
                        text: 'Cancel',
                        scope: this,
                        handler: this.cancel
                    }
                ]
            }],	
            listeners: {
                afterrender: function() {
                    if ( this.device ) {
                        this.loadRecord( this.device );
                    }
                }
            }
        });
		me.callParent(arguments);        
    },
    submitForm: function() {
        var me = this;
		var form = this.getForm();
		form.url = Config.baseUrl + 'resources/api/api.php';
		if ( form.isValid() ) {
			form.submit({
                scope: this,
				success: function(form, action) {
                     me.ownerCt.fireEvent( 'accountCreated', Ext.decode(action.response.responseText) );
				},
				failure: function(form, action) {
                    var res = Ext.decode(action.response.responseText);
                    Ext.Msg.alert('Failed', 'Unable to create Evernote account. '+res.error.msg);
				}
			});
		}
	},
	cancel: function() {
        this.ownerCt.fireEvent( 'cancel' );
	}
});
