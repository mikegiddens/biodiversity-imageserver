Ext.define('ImagePortal.ImagesGird', {
		extend: 'Ext.grid.Panel'	
	,	alias: 'widget.imagesgird'
	,	border: false
	,	autoScroll: true
	,	loadMask: true
	,	columnLines: true
	,	enableQuickMode: false
	,	enableColumnHide: false
	,	forceFit: true
	,	viewConfig: {
				stripeRows: false
			,	emptyText: '<div style="padding:10px;">No images available.</div>'
		}
	,	initComponent: function() {
			var encode = false;
			var filters = {
					ftype: 'filters'
				,	encode: encode 
				,	filters: [{
							type: 'date'
						,	dataIndex: 'timestamp_modified'
					}]
			}
			
			this.features =  [filters];
			this.columns = [
					{header: "Image Id", width: 50, sortable: true, dataIndex: 'image_id', draggable: false, hidden: true }
				,	{header: "Collection", width: 157, dataIndex: "Collection", sortable: true, draggable: false}
				,	{header: "Filename", width: 85, filter: { type: 'string'}, dataIndex:"filename", sortable: true, draggable: false, hidden: true }
				,	{header: "Barcode", width: 160, filter: { type: 'string'}, dataIndex:"barcode", sortable: true, draggable: false}
				,	{header: "Last Modified", width: 120, filter: {type: 'date', format:'mm-dd-yyyy'}, dataIndex:"timestamp_modified", sortable: true, draggable: false, hidden: true }
				,	{header: "Family", width: 160, filter: { type: 'string'}, dataIndex:"Family", sortable: true, draggable: false}
				,	{header: "Genus", width: 160, filter: { type: 'string'}, dataIndex:"Genus", sortable: true, draggable: false }
				,	{header: "Specific Epithet", width: 160, filter: { type: 'string'}, dataIndex:"SpecificEpithet", sortable: true, draggable: false }
				,	{header: "Flickr Avail", width: 80, filter: { type: 'string'}, dataIndex:"flickr_PlantID", sortable: true, draggable: false, hidden: true}
				,	{header: "Picassa Avail", width: 80, filter: { type: 'numeric', options: ['0', '1'] }, dataIndex:"picassa_PlantID", sortable: true, draggable: false, hidden: true}
				,	{header: "Picassa Modified", width: 120, filter: {	type: 'date', format:'mm-dd-yyyy'}, dataIndex:"picassa_modified", sortable: true, draggable: false, hidden: true}
				,	{header: "Tiled Processed", width: 80, filter: { type: 'numeric', options: ['0', '1'] }, dataIndex:"gTileProcessed", sortable: true, draggable: false, hidden: true}
				,	{header: "Zoom Enabled", width: 80, filter: { type: 'numeric', options: ['0', '1'] }, dataIndex:"zoomEnabled", sortable: true, draggable: false, hidden: true}
				,	{header: "Processed", width: 80, filter: { type: 'numeric', options: ['0', '1'] }, dataIndex:"processed", sortable: true, draggable: false, hidden: true}
			]
			this.callParent();
		}
});