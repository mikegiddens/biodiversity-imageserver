Ext.define('BIS.view.FormCreateCategory', {
	extend: 'Ext.form.FormPanel',
	alias: ['widget.formcreatecategory'],
	id: 'formCreateCategory',
	bodyPadding: 10,
	initComponent: function() {
        var me = this;
        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'textfield',
                    name: 'title',
                    focus: true,
                    fieldLabel: 'Name',
                    labelAlign: 'right',
                    allowBlank: false,
                    anchor: '100%'
                },
                {
                    xtype: 'textfield',
                    name: 'categoryId',
                    fieldLabel: 'Identifier',
                    labelAlign: 'right',
                    anchor: '100%',
                    readOnly: true,
                    fieldCls: 'x-item-disabled',
                    hidden: this.mode == 'add'
                },
                {
                    xtype: 'hiddenfield',
                    name: 'cmd',
                    value: (this.mode == 'add') ? 'categoryAdd' : 'categoryUpdate'
                }
            ],
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                ui: 'footer',
                items: [
                    {
                        text: ( this.mode == 'add' ) ? 'Add' : 'Update',
                        scope: this,
                        handler: this.submitForm
                    },
                    '->',
                    {
                        text: 'Cancel',
                        scope: this,
                        handler: this.cancel
                    }
                ]
            }]
        });
        me.callParent(arguments);
	},
    scope: this,
	listeners: {
		afterrender: function() {
			if ( this.mode != 'add' ) {
				// edit
                Ext.getCmp('formCreateCategory').loadRecord( this.record );
			}
		}
	},
	
	submitForm: function() {
        var me = this;
		var form = Ext.getCmp('formCreateCategory').getForm();
		form.url = Config.baseUrl + 'resources/api/api.php';
		if ( form.isValid() ) {
			form.submit({
                scope: this,
				success: function(form, action, a) {
                    me.ownerCt.fireEvent( 'categoryCreated', Ext.decode(action.response.responseText) );
				},
				failure: function(form, action) {
						Ext.Msg.alert('Failed', 'Request Failed');
				}
			});
		}
	},
    cancel: function() {
        this.ownerCt.fireEvent('cancel');
    }
});
