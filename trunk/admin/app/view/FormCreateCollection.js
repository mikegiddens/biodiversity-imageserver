Ext.define('BIS.view.FormCreateCollection', {
    extend: 'Ext.form.FormPanel',
    alias: ['widget.formcreatecollection'],
    id: 'formCreateCollection',
    border: false,
    bodyPadding: 10,
    initComponent: function() {
        var me = this;
        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'textfield',
                    name: 'name',
                    fieldLabel: 'Name',
                    allowBlank: false,
                    labelAlign: 'right',
                    anchor: '100%'
                },
                {
                    xtype: 'textfield',
                    name: 'code',
                    fieldLabel: 'Code',
                    labelAlign: 'right',
                    anchor: '100%'
                },
                {
                    xtype: 'textfield',
                    name: 'collectionId',
                    fieldLabel: 'Identifier',
                    labelAlign: 'right',
                    anchor: '100%',
                    readOnly: 'true',
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
        me.callParent(arguments);
    },
    listeners: {
        afterrender: function() {
            if ( this.mode != 'add' ) {
                // edit
                Ext.getCmp('formCreateCollection').loadRecord( this.record );
            }
        }
    },
	submitForm: function() {
		var route = 'resources/api/api.php?cmd=';
		if ( this.mode == 'add' ) {
			route += 'collectionAdd';
		} else {
			// edit
			route += 'collectionUpdate';
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
