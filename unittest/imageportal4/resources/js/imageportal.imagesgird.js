Ext.define('ImagePortal.ImagesGird', {
		extend: 'Ext.grid.Panel'	
	,	alias: 'widget.imagesgird'
	,	border: false
	,	autoScroll: true
	,	loadMask: true
	,	columnLines: true
	,	enableQuickMode: false
	,	enableColumnHide: false
	,	viewConfig: {
				stripeRows: false
			,	itemSelector: 'div.thumb-wrap'
			,	itemTpl: new Ext.XTemplate(
						'<tpl for=".">'
					,		'<div class="thumb-wrap">'
					,			'<div class="thumb">'
					,				'<img src="{path}{barcode}_s.jpg" onerror="this.src=\'http://images.cyberfloralouisiana.com/portal/resources/images/no-image.gif\'" />'
					,			'</div>'
					,		'</div>'
					,	'</tpl>'
				)
			,	multiSelect: false
			, 	singleSelect: true
			,	deferEmptyText: false
			,	forceFit: true
			,	hideColumns: true
			,	emptyText: '<div style="padding:10px;">No images available.</div>'
		}
	,	initComponent: function() {
			
			this.store = Ext.create('Ext.data.Store', {
					pageSize: 100
				,	autoLoad: true	
				,	proxy: {
							type: 'ajax'
						,	model: 'ImagesModel'
						,	url : 'resources/json/images.json'
						,	reader: {
									type: 'json'
								,	root: 'data'
								,	totalProperty: 'totalCount'
							}
						,	extraParams: {}	
					}	
			});
			
			this.bbar = Ext.create('Ext.toolbar.Paging', {
					store: this.store
				,	scope: this	
				,	displayInfo: true
				,	displayMsg: 'Displaying Specimen Images {0} - {1} of {2}'
				,	emptyMsg: 'No images available.'
            });
			
			this.columns = [
					{header: "Image Id", width: 50, sortable: true, dataIndex: 'image_id', draggable: false }
				,	{header: "Collection", width: 80, dataIndex:"Collection", sortable: true, draggable: false }
				,	{header: "Filename", width: 85, dataIndex:"filename", sortable: true, draggable: false }
				,	{header: "Barcode", width: 80, dataIndex:"barcode", sortable: true, draggable: false }
				,	{header: "Last Modified", width: 120, dataIndex:"timestamp_modified", sortable: true, draggable: false }
				,	{header: "Family", width: 120, dataIndex:"Family", sortable: true, draggable: false }
				,	{header: "Genus", width: 120, dataIndex:"Genus", sortable: true, draggable: false }
				,	{header: "Specific Epithet", width: 120, dataIndex:"SpecificEpithet", sortable: true, draggable: false }
				,	{header: "Flickr Avail", width: 80, dataIndex:"flickr_PlantID", sortable: true, draggable: false }
				,	{header: "Picassa Avail", width: 80, dataIndex:"picassa_PlantID", sortable: true, draggable: false }
				,	{header: "Picassa Modified", width: 120, dataIndex:"picassa_modified", sortable: true, draggable: false }
				,	{header: "Tiled Processed", width: 80, dataIndex:"gTileProcessed", sortable: true, draggable: false }
				,	{header: "Zoom Enabled", width: 80, dataIndex:"zoomEnabled", sortable: true, draggable: false }
				,	{header: "Processed", width: 80, dataIndex:"processed", sortable: true, draggable: false }
			]
			this.callParent();
		}
});