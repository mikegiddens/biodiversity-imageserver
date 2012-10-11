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
                    xtype: 'hiddenfield',
                    name: 'eventTypeId'
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
                            id: 'eventTypeSubmitButton',
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
            Ext.getCmp('eventTypeSubmitButton').setText('Working...').disable();
			form.submit({
				success: function(form, action) {
                    var res = Ext.decode( action.response.responseText );
                    var parentId;
                    if ( me.record ) parentId = me.record.get('eventTypeId');
                    me.ownerCt.fireEvent('eventTypeAdded', {
                        eventTypeId: parentId
                    });
				},
				failure: function( form, action ) {
                    var res = Ext.decode( action.response.responseText );
                    Ext.getCmp('eventTypeSubmitButton').setText( ( me.mode == 'add' ) ? 'Add' : 'Update' ).enable();
			        Ext.Msg.alert( 'Failed', res.error.msg );
				}
			});
		}
	},
    cancel: function() {
        this.ownerCt.fireEvent('cancel');
    }
});
