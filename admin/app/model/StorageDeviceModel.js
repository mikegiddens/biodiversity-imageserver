Ext.define('BIS.model.StorageDeviceModel', {
    extend: 'Ext.data.Model',
    alias: 'model.storageDeviceModel',
    fields: [{
				name: 'storageDeviceId'
		},{
				name: 'name'
		},{
				name: 'description'
		},{
				name: 'type'
		},{
				name: 'baseUrl'
		},{
				name: 'basePath'
		},{
				name: 'userName'
		},{
				name: 'password'
		},{
				name: 'key'
		},{
				name: 'active'
		},{
				name: 'defaultStorage'
		},{
				name: 'method'
		},{
				name: 'referencePath'
		},{
				name: 'extra2'
		}]
});