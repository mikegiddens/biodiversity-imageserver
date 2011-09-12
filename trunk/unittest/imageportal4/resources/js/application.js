Ext.Loader.setConfig({enabled: true});
Ext.Loader.setPath('ImagePortal', 'resources/js');
Ext.require(['*']);

Ext.onReady(function(){
	Ext.tip.QuickTipManager.init();
	Ext.fly(document.body).on('contextmenu', function(e, target) {
		e.preventDefault();
	});
	
	
	var images = Ext.create('ImagePortal.Images', {});
	
	var imagepanel = Ext.create('Ext.panel.Panel', {
			height: 500
		,	width: 800
		,	title: 'Images'
		, 	renderTo: 'imagepanel'
		,	layout: 'fit'
		,	items: [images]
	});

});