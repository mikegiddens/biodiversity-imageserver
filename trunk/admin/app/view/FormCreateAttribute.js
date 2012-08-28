Ext.define('BIS.view.FormCreateAttribute', {
    extend: 'Ext.form.FormPanel',
    alias: ['widget.formcreateattribute'],
    id: 'formCreateAttribute',
    bodyPadding: 10,
    initComponent: function() {
        var me = this;
        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'textfield',
                    name: 'value',
                    fieldLabel: 'Name',
                    labelAlign: 'right',
                    allowBlank: false,
                    anchor: '100%'
                },
                {
                    xtype: 'textfield',
                    name: 'identifier',
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
                    value: (this.mode == 'add') ? 'attributeAdd' : 'attributeUpdate'
                }
            ],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    ui: 'footer',
                    items: [
                        {
                            text: ( this.mode == 'add' ) ? 'Add' : 'Update',
                            scope: this,
                            handler: this.submitForm
                        }
                    ]
                }
            ]
        });

        me.callParent(arguments);
    },
    listeners: {
        afterrender: function() {
            if ( this.mode != 'add' ) {
                // edit
                Ext.getCmp('formCreateAttribute').loadRecord( this.record );
            }
        }
    },

	submitForm: function() {
		var form = this.up('form').getForm();
		form.url = Config.baseUrl + 'resources/api/api.php';
		if ( form.isValid() ) {
			form.submit({
				success: function(form, action) {
                     this.fireEvent('attributeCreated', action);
				},
				failure: function(form, action) {
						Ext.Msg.alert('Failed', 'Request Failed');
				}
			});
		}
	}    

});
