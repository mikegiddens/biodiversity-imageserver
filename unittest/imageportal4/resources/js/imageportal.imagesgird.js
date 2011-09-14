Ext.override(Ext.toolbar.Paging, {
	 updateInfo : function(){
        var me = this,
            displayItem = me.child('#displayItem'),
            store = me.store,
            pageData = me.getPageData(),
            count, msg;
			pageData.fromRecord = Ext.util.Format.number(pageData.fromRecord, '0,0');
			pageData.toRecord = Ext.util.Format.number(pageData.toRecord, '0,0');
			pageData.total = Ext.util.Format.number(pageData.total, '0,0');
        if (displayItem) {
            count = store.getCount();
            if (count === 0) {
                msg = me.emptyMsg;
            } else {
                msg = Ext.String.format(
                    me.displayMsg,
                    pageData.fromRecord,
                    pageData.toRecord,
                    pageData.total
                );
            }
            displayItem.setText(msg);
            me.doComponentLayout();
        }
    }
});
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
			,	emptyText: '<div style="padding:10px;">No images available.</div>'
		}
	,	initComponent: function() {			
			this.bbar = Ext.create('Ext.toolbar.Paging', {
					store: this.store
				,	scope: this	
				,	displayInfo: true
				,	displayMsg: 'Displaying Specimen Images {0} - {1} of {2}'
				,	emptyMsg: 'No images available.'
            });
			var encode = false;
			var filters = {
					ftype: 'filters'
				,	encode: encode 
				,	filters: [{
							type: 'date'
						,	dataIndex: 'timestamp_modified'
					},]
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