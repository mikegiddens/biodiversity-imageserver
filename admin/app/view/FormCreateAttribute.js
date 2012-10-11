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
                    xtype: 'hiddenfield',
                    name: 'attributeId'
                },
                {
                    xtype: 'hiddenfield',
                    name: 'categoryId',
                    value: this.record.get('categoryId')
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
                            id: 'attributeSubmitButton',
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
                console.log( this.record );
                Ext.getCmp('formCreateAttribute').loadRecord( this.record );
            }
        }
    },

	submitForm: function() {
        var me = this;
		var form = Ext.getCmp('formCreateAttribute').getForm();
		form.url = Config.baseUrl + 'resources/api/api.php';
		if ( form.isValid() ) {
            Ext.getCmp('attributeSubmitButton').setText('Working...').disable();
			form.submit({
				success: function(form, action) {
                    var res = Ext.decode( action.response.responseText );
                    me.ownerCt.fireEvent('attributeCreated', {
                        // res contains attributeId
                        categoryId: me.record.get('categoryId')
                    });
				},
				failure: function(form, action) {
                    var res = Ext.decode( action.response.responseText );
                    Ext.getCmp('attributeSubmitButton').setText(( me.mode == 'add' ) ? 'Add' : 'Update').enable();
                    Ext.Msg.alert('Failed', res.error.msg);
				}
			});
		}
    },
    cancel: function() {
        this.ownerCt.fireEvent('cancel');
    }
});
