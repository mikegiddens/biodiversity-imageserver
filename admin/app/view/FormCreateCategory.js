Ext.define('BIS.view.FormCreateCategory', {
	extend: 'Ext.form.FormPanel',
	alias: ['widget.formcreatecategory'],
	id: 'formCreateCategory',
	bodyPadding: 10,
	initComponent: function() {
			var me = this;
			Ext.applyIf(me, {
				items: [{
					xtype: 'textfield',
					name: 'value',
					fieldLabel: 'Name',
					labelAlign: 'right',
					allowBlank: false,
					anchor: '100%'
				}],
				dockedItems: [{
					xtype: 'toolbar',
					dock: 'bottom',
					ui: 'footer',
					items: [{
						text: ( this.mode == 'add' ) ? 'Add' : 'Update',
						handler: this.submitForm
					}]
				}]
			});

			me.callParent(arguments);
	},
	listeners: {
		afterrender: function() {
			if ( this.mode != 'add' ) {
				// edit
				Ext.getCmp('formCreateCategory').getForm().setValues({value:this.record.data.title});
			}
		}
	},
	
	submitForm: function() {
		var route;
		if ( this.mode == 'add' ) {
			route = 'add_category';
		} else {
			// edit
			route = 'rename_category';
		}
		var form = this.up('form').getForm();
		form.url = Config.baseUrl + route;
		if (form.isValid()) {
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
/*
			var values = Ext.getCmp('formCreateCategory').getValues();
			Ext.Ajax.request({
					method: 'POST',
					url: Config.baseUrl + route,
					params: values,
					scope: this,
					success: function( resObj ) {
							var res = Ext.decode( resObj.responseText );
							console.log( res );
							if ( res.success ) {
									
							}
					}
			});
*/
	}
});
