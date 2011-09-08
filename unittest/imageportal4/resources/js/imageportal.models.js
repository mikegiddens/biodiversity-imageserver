Ext.define('ImagesModel', {
		extend: 'Ext.data.Model'
	,	fields: [
				'image_id'
			,	'filename'
			,	'timestamp_modified'
			,	'barcode'								
			,	'Family'
			,	'Genus'
			,	'SpecificEpithet'
			,	'flickr_PlantID'
			,	'flickr_modified'
			,	'picassa_PlantID'
			,	'picassa_modified'
			,	'gTileProcessed'
			,	'zoomEnabled'
			,	'processed'
			,	'path'
			,	'server'
			,	'farm'
		]
});