Ext.define('BIS.view.FormCreateGeography', {
	extend: 'Ext.form.FormPanel',
	alias: ['widget.formcreategeography'],
	id: 'formCreateGeography',
	bodyPadding: 10,
	initComponent: function() {
        var me = this;
        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'textfield',
                    name: 'value',
                    focus: true,
                    fieldLabel: 'Name',
                    labelAlign: 'right',
                    allowBlank: false,
                    anchor: '100%'
                },
                { xtype: 'hiddenfield', name: 'geo', value: ( me.record ) ? JSON.stringify( me.record.raw ) : '' },
                { xtype: 'hiddenfield', name: 'rank', value: ( me.record ) ? ++(me.record.get('name').split('_')[1]) : 0 },
                {
                    xtype: 'hiddenfield',
                    name: 'cmd',
                    value: ( this.mode == 'add' ) ? 'geographyAdd' : 'geographyUpdate'
                }
            ],
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                ui: 'footer',
                items: [
                    {
                        text: ( this.mode == 'add' ) ? 'Add' : 'Update',
                        id: 'geographySubmitButton',
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
            Ext.getCmp('formCreateGeography').loadRecord( this.record );
		}
	},
	
	submitForm: function() {
        var me = this;
		var form = Ext.getCmp('formCreateGeography').getForm();
		form.url = Config.baseUrl + 'resources/api/api.php';
		if ( form.isValid() ) {
            Ext.getCmp('geographySubmitButton').setText('Working...').disable();
			form.submit({
                scope: this,
				success: function(form, action, a) {
                    var res = Ext.decode( action.response.responseText );
                    me.ownerCt.fireEvent( 'done' );
				},
				failure: function(form, action) {
                    var res = Ext.decode( action.response.responseText );
                    Ext.getCmp('geographySubmitButton').setText( ( this.mode == 'add' ) ? 'Add' : 'Update' ).enable();
                    Ext.Msg.alert('Failed', res.error.msg);
				}
			});
		}
	},
    cancel: function() {
        this.ownerCt.fireEvent('cancel');
    }
});
