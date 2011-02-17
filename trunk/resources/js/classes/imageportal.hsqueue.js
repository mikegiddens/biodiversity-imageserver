Ext.namespace('ImagePortal');

ImagePortal.hsQueue = function(config) {

	this.ds = new Ext.data.GroupingStore({
			proxy: new Ext.data.HttpProxy({
				url: 'resources/api/api.php'					
			})
		,	baseParams: { 
					cmd: 'listHSQueue'
			}
		,	reader: new Ext.data.JsonReader({
				root: 'data'
			,	totalProperty: 'totalCount'
			, 	fields:[
	            	{name: 'image_id'}
        	    ,	{name: 'filename'}
            	,	{name: 'timestamp_modified'}
            	,	{name: 'barcode'}								
			]
        })
		,	remoteSort: true
		,	sortInfo: 'image_id'
		,	groupField: ''
	});
	Ext.apply(this,config,{
			region: 'center'
		,	title: 'HelpingScience Queue'		
		,	enableColumnMove: false
		,	enableColumnHide: false
		,	store: this.ds
		,	columns: [{
					header: "Image Id"
				,	dataIndex: 'image_id'
				,	width: 50
				,	sortable: true
				,	hidden: Config.image_id || false
			},{
					header: "Filename"
				,	dataIndex: 'filename'
				,	width: 85				
				,	sortable: true
			},{
					header: "Barcode"
				,	dataIndex: 'barcode'
				,	width: 80				
				,	filterable:true				
				,	sortable: true
			},{
					header: "Last Modified"
				,	dataIndex: 'timestamp_modified'
				,	width: 120				
				,	sortable: true
				,	scope:this	
				,	hidden: Config.lastModified || false
				,	renderer:function(a){
						return(this.rendererDatehandling(a));
					}					
			}]
		,	sm: new Ext.grid.RowSelectionModel({singleSelect: false})
		,   loadMask: true
		,	viewConfig: {
					rowTemplate: this.smallIcons
				,	multiSelect: false
				, 	singleSelect: true	
				,	emptyText: 'No images available.'
				,	deferEmptyText: false
				,	forceFit: true
				,	hideColumns: true
			}	
		,	bbar: new Ext.PagingToolbar({
					pageSize: 100
				,	store: this.ds
				,	displayInfo: true
				,	displayMsg: 'Display HelpingScience Queue Items {0} - {1} of {2}' 
				,	emptyMsg: ''
				,	scope:this	
			})
		
	    });
	
	ImagePortal.hsQueue.superclass.constructor.call(this, config);

	} 
 
	Ext.extend(ImagePortal.hsQueue, Ext.grid.GridPanel, {
		rendererDatehandling:function(value){
			if (value == '0000-00-00 00:00:00') 
				return String.format('');
			else {
				var dt = Date.parseDate(value, "Y-m-d H:i:s", true);
				var dt1 = new Date(dt);
				var dt2= dt1.format('m-d-Y');
				return dt2;
			}
		}	
	}); 