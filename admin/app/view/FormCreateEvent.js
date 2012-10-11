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
                    xtype: 'hiddenfield',
                    name: 'eventId'
                },
                {
                    xtype: 'hiddenfield',
                    name: 'eventTypeId',
                    value: this.record.data.eventTypeId
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
                            id: 'eventSubmitButton',
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
            Ext.getCmp('eventSubmitButton').setText('Working...').disable();            
			form.submit({
				success: function( form, action ) {
                    var res = Ext.decode( action.response.responseText );
                    me.ownerCt.fireEvent('eventCreated', {
                        eventTypeId: me.record.get('eventTypeId')
                    });
				},
				failure: function(form, action) {
                    var res = Ext.decode( action.response.responseText );
                    Ext.getCmp('eventSubmitButton').setText(( me.mode == 'add' ) ? 'Add' : 'Update').enable();
                    Ext.Msg.alert('Failed', res.error.msg);
				}
			});
		}
	},
    cancel: function() {
        this.ownerCt.fireEvent('cancel');
    }
});
