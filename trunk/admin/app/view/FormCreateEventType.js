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
                Ext.getCmp('formCreateEventType').loadRecord( this.record );
            }
        }
    },
	submitForm: function() {
		var route = 'resources/api/api.php?cmd=';
		if ( this.mode == 'add' ) {
			route += 'eventTypeAdd';
		} else {
			// edit
			route += 'eventTypeUpdate';
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
