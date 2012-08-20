// Disable browser right click
Ext.fly(document.body).on('contextmenu', function(e, target) {
	e.preventDefault();
});	

Ext.define('BIS.view.Viewport', {
	extend: 'Ext.container.Viewport',
	requires: [
		'BIS.view.MainViewport',
		'BIS.view.StorageSettingsPanel',
		'BIS.view.UserManagerPanel'
	],
	layout: 'fit',
	items: [Ext.create('BIS.view.MainViewport')]
});