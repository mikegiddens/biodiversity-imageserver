/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/

	Ext.namespace('ImagePortal');

	ImagePortal.Sequences = function(config) {

	this.ds = new Ext.data.GroupingStore({
			proxy: new Ext.data.HttpProxy({
					url: 'resources/api/api.php'
			})
		,	baseParams: { 
					cmd: 'image_sequence_cache'
				,	filter:''
				,	code:''
			}
		,	reader: new Ext.data.JsonReader({
					root: 'records'
				,	totalProperty: 'totalCount'
				, 	fields:[
	        	    	{name: 'startRange'}
        	    	,	{name: 'endRange'}
	            	,	{name: 'prefix'}
	            	,	{name: 'recordCount'}
	            	,	{name: 'exist'}					
				]
       		})
 		,	sortInfo: {
    		  	field:'startRange',
            	direction:'DESC'
       	 	}			
		,	groupField: ''
	});
	
		this.comboStore = new Ext.data.JsonStore({
		fields: ['collection_id', 'name', 'code']
	,	url: 'resources/api/api.php'
 	,	baseParams:{
 		cmd: 'collections'
 		}
	,	root: 'records'
	//,	autoLoad: true
		 });
		  
	Ext.apply(this,config,{
			region: 'center'
		,	title: 'Sequences'		
		,	enableColumnMove: false
		,	enableColumnHide: false
		,	store: this.ds
		,	scope:this	
		,	loadMask: true
		,	tbar: ['Collection: '
				 , {
					xtype:'combo'
				,	selectOnFocus: true
 				,	width: 250
 				,	ref: '../cbCollections'
 				,	triggerAction: 'all'
 				,	store: this.comboStore
 				,	mode: 'local'
				,	lazyRender: true
 				,	valueField: 'collection_id'
 				,	displayField: 'name'
 				,	listeners:{
 					select: function (combo, record) {
 								this.ownerCt.ownerCt.store.baseParams.code = record.data.code;
								this.ownerCt.ownerCt.store.load({params:{start:0, limit:100}});
								}
 							}
 				},'-',{
					xtype: 'checkbox'
				,	boxLabel: 'Include Existing Ranges'
				,	listeners:{
						check: function (e,state) {
							if(this.cbCollections.getValue() != ''){
								this.store.reload();
							}	
						}
					,	scope: this
					}
				}] 	
		,	columns: [{
					header: "Start Range"
				,	dataIndex: 'startRange'
				,	width: 125
				,	sortable: true
			},{
					header: "End Range"
				,	dataIndex: 'endRange'
				,	width: 125				
				,	sortable: true
			},{
					header: "Prefix"
				,	dataIndex: 'prefix'
				,	width: 80				
				,	sortable: true
				,	scope:this	
			},{
					header: "Record Count"
				,	dataIndex: 'recordCount'
				,	width: 100				
				,	sortable: true
				,	scope:this	
			}]
		,	sm: new Ext.grid.RowSelectionModel({singleSelect:true})
		,   loadMask: true
		,	view: new Ext.grid.GroupingView({
					forceFit: false
				,	emptyText: 'No Sequences'
				,	deferEmptyText: false
			})	
		,	bbar: new Ext.PagingToolbar({
					pageSize: 100
				,	store: this.ds
				,	scope:this
				,	emptyMsg: ''
				,	displayInfo: true
				,	displayMsg: 'Displaying Sequences {0} - {1} of {2}' 
				,	ref:'../pgtoolbar'
			})
	    });

		this.getView().getRowClass = function(row, index) {
			if(row.data.exist == 0)
				return 'error-row';
		}
		
		//this.ds.load({params:{start:0, limit:100}});

		ImagePortal.Sequences.superclass.constructor.call(this, config);

	} 
 
	Ext.extend(ImagePortal.Sequences, Ext.grid.GridPanel, {
		
	}); // end of extend