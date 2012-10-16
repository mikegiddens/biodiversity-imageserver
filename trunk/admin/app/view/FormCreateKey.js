Ext.define('BIS.view.FormCreateKey', {
	extend: 'Ext.form.Panel',
	alias: ['widget.formcreatekey'],
	id: 'createKeyPanel',
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
                fieldLabel: 'Title',
                name: 'title'
            },{
                fieldLabel: 'Description',
                name: 'description'
            },{
                fieldLabel: 'IP Address',
                name: 'ip'
            },{
                xtype: 'hiddenfield',
                name: 'cmd',
                value: 'remoteAccessKeyGenerate'
            }],
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                ui: 'footer',
                items: [
                    {
                        width: 80,
                        text: 'Create Key',
                        iconCls: 'icon_addKey',
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
            }]
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
                     me.ownerCt.fireEvent( 'done', Ext.decode(action.response.responseText) );
				},
				failure: function(form, action) {
                    var res = Ext.decode(action.response.responseText);
                    Ext.Msg.alert('Failed', 'Unable to generate access key. '+res.error.msg);
				}
			});
		}
	},
	cancel: function() {
        this.ownerCt.fireEvent( 'cancel' );
	}
});
