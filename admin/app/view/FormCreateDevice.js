Ext.define('BIS.view.FormCreateDevice', {
	extend: 'Ext.form.Panel',
	alias: ['widget.formcreatedevice'],
	id: 'createDevicePanel',
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
	items: [{
		fieldLabel: 'Identifier',
		name: 'storage_id',
		readOnly: true,
		fieldCls: 'x-item-disabled'
	},{
		fieldLabel: 'Name',
		name: 'name'
	},{
		fieldLabel: 'Description',
		name: 'description'
	},{
		fieldLabel: 'Type',
		name: 'type'
	},{
		fieldLabel: 'Base URL',
		name: 'baseUrl'
	},{
		fieldLabel: 'Base Path',
		name: 'basePath'
	},{
		fieldLabel: 'Username',
		name: 'user'
	},{
		fieldLabel: 'Password',
		name: 'pw'
	},{
		xtype: 'checkbox',
		fieldLabel: 'Active?',
		name: 'active'
	},{
		fieldLabel: 'Notes',
		name: 'extra2'
	}],
	dockedItems: [{
		xtype: 'toolbar',
		dock: 'bottom',
		ui: 'footer',
		items: [{ 
			xtype: 'component', flex: 1 
		},{
			width: 80,
			text: ( this.device ) ? 'Update Settings' : 'Add Device',
			xtype: 'button',
			handler: function(btn, e) {
				console.log('h', this, btn, e);
//				this.ownerCt.ownerCt.submitForm();
			}
		}]
	}],	
	listeners: {
		afterrender: function() {
			if ( this.device ) {
				Ext.getCmp('formCreateDevice').loadRecord( this.device );
			}
		}
	},
	submitForm: function() {
		console.log('submit');
		// required fields: name, type, baseUrl
		var values = Ext.getCmp('formCreateDevice').getValues();
		var route = ( this.device ) ? 'updateDevice!!!!!!!!' : 'addStorageDevice';
//			url: Config.baseUrl + route,

		Ext.Ajax.request({
			method: 'POST',
			url: Config.baseUrl + route,
			params: values,
			scope: this,
			success: function( resObj ) {
				var res = Ext.decode( resObj.responseText );
				console.log( res );
				if ( res.success ) {						
					// success
				}
			}
		});
	}
});