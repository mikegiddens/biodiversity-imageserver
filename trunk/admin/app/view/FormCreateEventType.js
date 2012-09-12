Ext.define('BIS.view.FormCreateEventType', {
    extend: 'Ext.form.FormPanel',
    alias: ['widget.formcreateeventtype'],
    id: 'formCreateEventType',
    bodyPadding: 10,
    initComponent: function() {
        var me = this;
        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'textfield',
                    name: 'title',
                    fieldLabel: 'Title',
                    allowBlank: false,
                    labelAlign: 'right',
                    anchor: '100%'
                },
                {
                    xtype: 'textarea',
                    name: 'description',
                    fieldLabel: 'Description',
                    labelAlign: 'right',
                    anchor: '100%'
                },
                {
                    xtype: 'textfield',
                    name: 'eventTypeId',
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
                    value: (this.mode == 'add') ? 'eventTypeAdd' : 'eventTypeUpdate'
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
                        }
                    ]
                }
            ]
        });

        me.callParent(arguments)
    },
    listeners: {
        afterrender: function() {
            if ( this.mode != 'add' ) {
                // edit
                Ext.getCmp('formCreateEventType').loadRecord( this.record );
            }
        }
    },
	submitForm: function() {
        var me = this;
		var form = Ext.getCmp('formCreateEventType').getForm();
		form.url = Config.baseUrl + 'resources/api/api.php';
		if ( form.isValid() ) {
			form.submit({
				success: function(form, action) {
                    me.ownerCt.fireEvent('eventTypeAdded',action);
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
