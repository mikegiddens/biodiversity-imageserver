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
                    xtype: 'hiddenfield',
                    name: 'collectionId'
                },
                {
                    xtype: 'hiddenfield',
                    name: 'cmd',
                    value: (this.mode == 'add') ? 'collectionAdd' : 'collectionUpdate'
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
                            id: 'collectionSubmitButton',
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
        var me = this;
		var form = Ext.getCmp('formCreateCollection').getForm();
		form.url = Config.baseUrl + 'resources/api/api.php';
        Ext.getCmp('collectionSubmitButton').setText('Working...').disable();
		if ( form.isValid() ) {
			form.submit({
				success: function(form, action) {
                    me.ownerCt.fireEvent( 'collectionCreated', Ext.decode(action.response.responseText) );
				},
				failure: function(form, action) {
                    var response = Ext.decode(action.response.responseText);
                    Ext.getCmp('collectionSubmitButton').setText(( me.mode == 'add' ) ? 'Add' : 'Update').enable();
				    Ext.Msg.alert('Failed', response.error.msg);
				}
			});
		}
	},
    cancel: function() {
        this.ownerCt.fireEvent('cancel');
    }
});
