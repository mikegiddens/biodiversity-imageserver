/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/


Ext.namespace('ImagePortal');

ImagePortal.Queue = function(config) {

	this.ds = new Ext.data.GroupingStore({
			proxy: new Ext.data.HttpProxy({
				url: 'resources/api/api.php'					
			})
		,	baseParams: { 
					cmd: 'list_process_queue'
			}
		,	reader: new Ext.data.JsonReader({
				root: 'data'
			,	totalProperty: 'totalCount'
			, 	fields:[
	            	/*{name: 'queue_id'}
    	        ,	*/{name: 'image_id'}
            	,	{name: 'process_type'}
	            ,	{name: 'date_added'}
			]
        })
		,	remoteSort: true
		,	sortInfo: 'image_id'
		,	groupField: ''
	});

	
	
	var encode = false;
	var filters = new Ext.ux.grid.GridFilters({
        	encode: encode 
	    ,	filters: [{
            		type: 'string'
	            ,	dataIndex: 'process_type'
    	    },{
            		type: 'numeric'
	            ,	dataIndex: 'image_id'
    	    },{
            		type: 'date'
	            ,	dataIndex: 'date_added'
    	    }]
    	});    


	Ext.apply(this,config,{
			region: 'center'
		,	title: 'Queue'		
		,	enableColumnMove: false
		,	enableColumnHide: false
		,	store: this.ds
		,	plugins: [filters]	
		,	columns: [/*
{
					header: "Queue Id"
				,	dataIndex: 'queue_id'
				,	width: 80
				,	sortable: true
			},
*/{
					header: "Image Id"
				,	dataIndex: 'image_id'
				,	width: 80
				,	sortable: true
			},{
					header: "Process Type"
				,	dataIndex: 'process_type'
				,	width: 80				
				,	sortable: true
			},{
					header: "Date Added"
				,	dataIndex: 'date_added'
				,	width: 120
				,	scope:this				
				,	sortable: true
				,	renderer:function(a){
						return(this.rendererDatehandling(a));
					}			
			}]
		,	sm: new Ext.grid.RowSelectionModel({singleSelect:true})
		,   loadMask: true
		,	view: new Ext.grid.GroupingView({
					forceFit: false
				,	emptyText: 'No Images in Queue'
				,	deferEmptyText: false
			})
		,	bbar: new Ext.PagingToolbar({
					pageSize: 100
				,	store: this.ds
				,	displayInfo: true
				,	displayMsg: 'Display Queue Items {0} - {1} of {2}' 
				,	emptyMsg: ''
				,	scope:this	
			})
	    });
	
	//	this.ds.load({params:{start:0, limit:100}});

	ImagePortal.Queue.superclass.constructor.call(this, config);

	} 
 
	Ext.extend(ImagePortal.Queue, Ext.grid.GridPanel, {
		
		rendererDatehandling:function(value){
			if(value == '0000-00-00 00:00:00')
			 	return String.format('');
			else
				return value;
		}		
		
}); // end of extend