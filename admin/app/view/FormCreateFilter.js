Ext.define('BIS.view.FormCreateFilter', {
	extend: 'Ext.form.Panel',
	alias: ['widget.formcreatefilter'],
	id: 'createFilterPanel',
	layout: 'anchor',
	bodyPadding: 10,
	labelWidth: 80,
	border: false,
	bodyBody: false,
	defaults: {
		labelAlign: 'right',
		anchor: '100%'
	},
	defaultType: 'textfield',
	initComponent: function() {
		var me = this;	
		Ext.applyIf(me, {
            items: [{
                fieldLabel: 'Name',
                name: 'name'
            },{
                fieldLabel: 'Description',
                name: 'description'
            },{
                xtype: 'hiddenfield',
                name: 'filter',
                value: me.filter
            },{
                xtype: 'hiddenfield',
                name: 'cmd',
                value: 'advFilterAdd'
            }],
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                ui: 'footer',
                items: [
                    {
                        width: 80,
                        text: 'Save Filter',
                        iconCls: 'icon_addFilter',
                        scope: this,
                        handler: this.submitForm
                    },
                    '->',
                    {
                        width: 80,
                        text: 'Cancel',
                        scope: this,
                        handler: this.cancel
                    }
                ]
            }]
        });
		me.callParent(arguments);        
    },
    submitForm: function() {
        var me = this;
		var form = this.getForm();
		form.url = Config.baseUrl + 'resources/api/api.php';
		if ( form.isValid() ) {
			form.submit({
                scope: this,
				success: function(form, action) {
                     me.ownerCt.fireEvent( 'done', Ext.decode(action.response.responseText) );
				},
				failure: function(form, action) {
                    var res = Ext.decode(action.response.responseText);
                    Ext.Msg.alert('Failed', 'Unable to save filter. '+res.error.msg);
				}
			});
		}
	},
	cancel: function() {
        this.ownerCt.fireEvent( 'cancel' );
	}
});
