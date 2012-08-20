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
                    fieldLabel: 'Type Identifier',
                    labelAlign: 'right',
                    anchor: '100%',
                    readOnly: true,
                    fieldCls: 'x-item-disabled',
                    hidden: this.mode == 'add'
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

        me.callParent(arguments)
    },
    listeners: {
        afterrender: function() {
            if ( this.mode != 'add' ) {
                // edit
                console.log( this.record.data );
                Ext.getCmp('formCreateEvent').loadRecord( this.record );
            }
        }
    },
	submitForm: function() {
		var route = 'resources/api/api.php?cmd=';
        console.log( this, this.mode );
		if ( this.mode == 'add' ) {
			route += 'eventAdd';
		} else {
			// edit
			route += 'eventUpdate';
		}
		var form = this.up('form').getForm();
		form.url = Config.baseUrl + route;
		if ( form.isValid() ) {
			form.submit({
				success: function(form, action) {
					 Ext.Msg.alert('Success', action.result.msg);
					 // fire event, close window
					 // DO NOT refresh any list here it should be done from the list copmonent by listening to the event.
				},
				failure: function(form, action) {
						Ext.Msg.alert('Failed', 'Request Failed');
				}
			});
		}
	}
});
