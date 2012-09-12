Ext.define('BIS.view.FormCreateEvent', {
    extend: 'Ext.form.FormPanel',
    alias: ['widget.formcreateevent'],
    id: 'formCreateEvent',
    bodyPadding: 10,
    initComponent: function() {
        var me = this;
        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'textfield',
                    name: 'title',
                    fieldLabel: 'Title',
                    labelAlign: 'right',
                    allowBlank: false,
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
                    name: 'eventId',
                    fieldLabel: 'Event Identifier',
                    labelAlign: 'right',
                    anchor: '100%',
                    readOnly: true,
                    fieldCls: 'x-item-disabled',
                    hidden: this.mode == 'add'
                },
                {
                    xtype: 'textfield',
                    name: 'eventTypeId',
                    value: this.record.data.eventTypeId,
                    fieldLabel: 'Type Identifier',
                    labelAlign: 'right',
                    anchor: '100%',
                    readOnly: true,
                    fieldCls: 'x-item-disabled'
                },
                {
                    xtype: 'hiddenfield',
                    name: 'cmd',
                    value: (this.mode == 'add') ? 'eventAdd' : 'eventUpdate'
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
                Ext.getCmp('formCreateEvent').loadRecord( this.record );
            }
        }
    },
	submitForm: function() {
        var me = this;
		var form = Ext.getCmp('formCreateEvent').getForm();
		form.url = Config.baseUrl + 'resources/api/api.php';
		if ( form.isValid() ) {
			form.submit({
				success: function(form, action) {
                    me.ownerCt.fireEvent('eventAdded',action);
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
