Ext.namespace('ImagePortal');

ImagePortal.ProgressOfCollectionRemote = function(config){

	var config2 = {};
	
	Ext.apply(config2, config, {
			border: true
		,	width: 700
		,	height: 420
		,	store: new Ext.data.JsonStore({
					proxy: new Ext.data.ScriptTagProxy({
						url: 'http://ecat-dev.gbif.org/ws/usage/6498097'
					})
				,	fields: [
							'scientificName'
						, 	'accordingTo'
						, 	'canonicalName'
						]
				,	root: 'data'
				
				,	autoLoad: true	
			})
 	});

	ImagePortal.ProgressOfCollectionRemote.superclass.constructor.call(this, config2);

};

Ext.extend(ImagePortal.ProgressOfCollectionRemote, ImagePortal.ProgressOfCollection, {
});