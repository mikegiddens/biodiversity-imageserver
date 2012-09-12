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
                    name: 'name',
                    fieldLabel: 'Name',
                    labelAlign: 'right',
                    allowBlank: false,
                    anchor: '100%'
                },
                {
                    xtype: 'textfield',
                    name: 'attributeId',
                    fieldLabel: 'Identifier',
                    labelAlign: 'right',
                    anchor: '100%',
                    readOnly: true,
                    fieldCls: 'x-item-disabled',
                    hidden: this.mode == 'add'
                },
                {
                    xtype: 'textfield',
                    name: 'categoryId',
                    value: this.record.data.categoryId,
                    fieldLabel: 'Category',
                    labelAlign: 'right',
                    anchor: '100%',
                    readOnly: true,
                    fieldCls: 'x-item-disabled'
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
                        },
                        '->',
                        {
                            text: 'Cancel',
                            scope: this,
                            handler: this.cancel
                        },
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
        var me = this;
		var form = Ext.getCmp('formCreateAttribute').getForm();
		form.url = Config.baseUrl + 'resources/api/api.php';
		if ( form.isValid() ) {
			form.submit({
				success: function(form, action) {
                     me.ownerCt.fireEvent('attributeCreated', action);
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
