Ext.define('BIS.view.FormCreateCategory', {
	extend: 'Ext.form.FormPanel',
	alias: ['widget.formcreatecategory'],
	id: 'formCreateCategory',
	bodyPadding: 10,
	initComponent: function() {
        var me = this;
        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'textfield',
                    name: 'title',
                    focus: true,
                    fieldLabel: 'Name',
                    labelAlign: 'right',
                    allowBlank: false,
                    anchor: '100%'
                },
                {
                    xtype: 'textfield',
                    name: 'elementSet',
                    value: ( me.initNamespace ) ? me.initNamespace : '',
                    fieldLabel: 'Namespace',
                    labelAlign: 'right',
                    anchor: '100%'
                },
                {
                    xtype: 'textfield',
                    name: 'term',
                    fieldLabel: 'Term',
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
                    name: 'categoryId'
                },
                {
                    xtype: 'hiddenfield',
                    name: 'cmd',
                    value: (this.mode == 'add') ? 'categoryAdd' : 'categoryUpdate'
                }
            ],
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                ui: 'footer',
                items: [
                    {
                        text: ( this.mode == 'add' ) ? 'Add' : 'Update',
                        id: 'categorySubmitButton',
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
            }]
        });
        me.callParent(arguments);
	},
    scope: this,
	listeners: {
		afterrender: function() {
			if ( this.mode != 'add' ) {
				// edit
                console.log( this.record );
                Ext.getCmp('formCreateCategory').loadRecord( this.record );
			}
		}
	},
	
	submitForm: function() {
        var me = this;
		var form = Ext.getCmp('formCreateCategory').getForm();
		form.url = Config.baseUrl + 'resources/api/api.php';
		if ( form.isValid() ) {
            Ext.getCmp('categorySubmitButton').setText('Working...').disable();
			form.submit({
                scope: this,
				success: function(form, action, a) {
                    var res = Ext.decode( action.response.responseText );
                    var parentId;
                    if ( me.mode != 'add' ) {
                        // update
                        parentId = me.record.get('categoryId');
                    }
                    me.ownerCt.fireEvent( 'categoryCreated', {
                        categoryId: parentId
                    });
				},
				failure: function(form, action) {
                    var res = Ext.decode( action.response.responseText );
                    Ext.getCmp('categorySubmitButton').setText(( me.mode == 'add' ) ? 'Add' : 'Update').enable();
                    Ext.Msg.alert('Failed', res.error.msg);
				}
			});
		}
	},
    cancel: function() {
        this.ownerCt.fireEvent('cancel');
    }
});
