/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/


	Ext.namespace('ImagePortal');
	ImagePortal.Image = function(config) {
		this.proxy = '';
		if(Config.mode == 'local'){
			this.proxy = new Ext.data.HttpProxy({
				url: Config.baseUrl + 'resources/api/api.php'
			})
		}else{
			this.proxy = new Ext.data.ScriptTagProxy({
					url: Config.baseUrl + 'resources/api/api.php'
			})
		}
		
		/*this.proxy = new Ext.data.HttpProxy({
			//proxy: new Ext.data.ScriptTagProxy({
					url: Config.baseUrl + 'resources/api/api.php'
			})
*/
	this.store =  new Ext.data.GroupingStore({
			proxy:this.proxy 
		,	baseParams: { 
					cmd: 'images'
				,	filters:''
				,	code:''
			}
		,	reader: new Ext.data.JsonReader({
				root: 'data'
			,	totalProperty: 'totalCount'
			, 	fields:[
	            	{name: 'image_id'}
        	    ,	{name: 'filename'}
            	,	{name: 'timestamp_modified'}
            	,	{name: 'barcode'}								
	            ,	{name: 'Family'}
	            ,	{name: 'Genus'}
	            ,	{name: 'SpecificEpithet'}
	            ,	{name: 'flickr_PlantID'}
	            ,	{name: 'flickr_modified'}
	            ,	{name: 'picassa_PlantID'}
	            ,	{name: 'picassa_modified'}
	            ,	{name: 'gTileProcessed'}
	            ,	{name: 'zoomEnabled'}
	            ,	{name: 'processed'}
	            ,	{name: 'path'}
				,	{name: 'server'}
	            ,	{name: 'farm'}
			]
        })
		,	remoteSort: true
		,	sortInfo: 'login'
		,	groupField: ''
	});

	
	var encode = false;
	var filters = new Ext.ux.grid.GridFilters({
        	encode: encode 
		
	    ,	filters: [{
            		type: 'string'
	            ,	dataIndex: 'filename'
    	    },{
            		type: 'string'
	            ,	dataIndex: 'barcode'
    	    },{
            		type: 'string'
	            ,	dataIndex: 'Family'
    	    },{
            		type: 'string'
	            ,	dataIndex: 'Genus'
    	    },{
            		type: 'string'
	            ,	dataIndex: 'SpecificEpithet'
    	    },{
            		type: 'date'
	            ,	dataIndex: 'timestamp_modified'
    	    },{
            		type: 'date'
	            ,	dataIndex: 'picassa_modified'
				,	format:'mm-dd-yyyy'
    	    },{
            		type: 'string'
	            ,	dataIndex: 'flickr_PlantID'
				//,	options: ['0', '1']
    	    },{
            		type: 'numeric'
	            ,	dataIndex: 'picassa_PlantID'
				,	options: ['0', '1']	
    	    },{
            		type: 'numeric'
	            ,	dataIndex: 'gTileProcessed'
    	    	,	options: ['0', '1']
			},{
            		type: 'numeric'
	            ,	dataIndex: 'processed'
				,	options: ['0', '1']
    	    }]
    	});    

		this.comboStore = new Ext.data.JsonStore({
			fields: ['collection_id', 'name','code'] 
		,	proxy: this.proxy
		,	baseParams:{
					cmd: 'collections'
				}
		,	root: 'records'
		,	autoLoad: false
	});	
	

/*
 * Editing Start
 */

	this.search_value = new Ext.ux.TwinComboBox({
						fieldLabel: 'Collections'
					, 	name: 'Collections'
					, 	triggerAction: 'all'
					,	store: this.comboStore
					,	displayField: 'name'
					,	typeAhead: false
					,	hideTrigger2: false
					,	hideTrigger1: true
					,	editable:false
					,	value:''
					,	width:250
					, 	listeners: {
							'select': function(combo, record) {
									Ext.getCmp('imageGrid').store.baseParams.code = record.data.code;
									Ext.getCmp('imageGrid').store.load({params:{start:0, limit:100}});
								}
						,	'clear': function() {
									Ext.getCmp('imageGrid').store.baseParams.code = '';
									Ext.getCmp('imageGrid').store.load({params:{start:0, limit:100}});
								}
						}
	});
	
	
	this.both = new Ext.XTemplate(
		'<div class="x-grid3-row ux-explorerview-item ux-explorerview-mixed-item">'+
			'<div class="ux-explorerview-icon"><img onerror="this.src=\'http://images.cyberfloralouisiana.com/portal/resources/images/no-image.gif\'" src="{path}{barcode}_s.jpg"></div>'+
				'<div class="ux-explorerview-text"><div class="x-grid3-cell x-grid3-td-name" unselectable="on">{barcode} {Family}<br/>{Genus} {SpecificEpithet}<br/>'+
				'<tpl if="barcode != 0">'+
					'<span>Barcode: {barcode}</span><br>'+
				'</tpl>'+
				'<span>Date Added: {timestamp_modified:this.convDate}</span></div>'+
			'</div>'+
		'</div>',
		{
			convDate:function(value){
								if (value == '0000-00-00 00:00:00') 
									return String.format('');
								else {
									var dt = Date.parseDate(value, "Y-m-d H:i:s", true);
									var dt1 = new Date(dt);
									var dt2= dt1.format('d-M-Y');
									return dt2;
							}
					}
		}
	);
	
	this.smallIcons = new Ext.XTemplate(
		'<div class="x-grid3-row ux-explorerview-item ux-explorerview-small-item">'+
		'<div class="ux-explorerview-icon"><img onerror="this.src=\'http://images.cyberfloralouisiana.com/portal/resources/images/no-image.gif\'" ' +
		  	'<tpl if="Family != \'\' || Genus != \'\' || SpecificEpithet != \'\' ">'+
				' qtip="' +
				'<tpl if="Family != \'\' " >{Family}<br></tpl>'+
				'<tpl if="Genus != \'\' " >{Genus} {SpecificEpithet}"</tpl>'+
			'</tpl>' +
			'src="{path}{barcode}_s.jpg"></div>' +
		'</div>'
	);

	
	this.tileIcons = new Ext.Template(
		'<div class="x-grid3-row ux-explorerview-item ux-explorerview-tiles-item">'+
		'<div class="ux-explorerview-icon"><img onerror="this.src=\'http://images.cyberfloralouisiana.com/portal/resources/images/no-image.gif\'" src="{path}{barcode}_m.jpg"></div>'+
		'<div class="ux-explorerview-text"><div class="x-grid3-cell x-grid3-td-name" unselectable="on">{barcode}<br/> {Family}<span>{Genus} {SpecificEpithet}</span></div></div></div>'
	);
	
	this.rotatedImages=[];
	
	this.views = new Ext.CycleButton({
			showText: true
		,	width: 150
		,	scope: this
		,	prependText: 'View as '
		,	items: [{
					text:'Large' //this.largeText
				,	value: 'large'
				,	iconCls:'icon_cycleImages'
			},{
					text:'Small' //this.smallText
				,	value: 'small'
				,	checked:true
				,	iconCls:'icon_cycleImages'
			},{
					text:'Both'  //this.mixedText
				,	value: 'both'
				,	iconCls:'icon_cycleImages'
			},{
					text:'Details' //this.detailsText
				,	value: 'details'
				,	iconCls:'icon_cycleImages'
			}]
		,	changeHandler: this.changeView
	});
	
	Ext.apply(this,config,{
			title: 'Images'		
		,	enableColumnMove: false
		,	enableColumnHide: false
		,	store: this.store
		,	scope:	this
		,	plugins: [filters]	
		,	loadMask: true
		,	id:'imageGrid'
		,	width:700
		,	height:400
		,	audit: []
		,	tbar: [ 
					'Collection: '
				, ' ', this.search_value
				, ' ',	this.views
				/*,	{
							text:"Save Image Changes"
						,	iconCls:'icon_saveImageChanges'
						,	scope:this
						,	handler:this.sendRotateRequest
					}*/
			]		
		,	columns: [{
					header: "Image Id"
				,	dataIndex: 'image_id'
				,	width: 50
				,	sortable: true
				,	hidden: Config.image_id || false
			},{
					header: "Collection"
				,	dataIndex: ''
				,	width: 80
				,	sortable: true
			},{
					header: "Filename"
				,	dataIndex: 'filename'
				,	width: 85				
				,	sortable: true
				,	hidden: true
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
			},{
					header: "Family"
				,	dataIndex: 'Family'
				,	width: 120
				,	scope:this				
				,	sortable: true
			},{
					header: "Genus"
				,	dataIndex: 'Genus'
				,	width: 120
				,	scope:this				
				,	sortable: true
			},{
					header: "Specific Epithet"
				,	dataIndex: 'SpecificEpithet'
				,	width: 120
				,	scope:this				
				,	sortable: true
			},{
					header: "Flickr Avail"
				,	dataIndex: 'flickr_PlantID'
				,	width: 80
				,	scope:this				
				,	filterable:true
				,	filter: {type: 'string'}
				,	hidden: Config.flickr_PlantID || false
				,	sortable: true
				,	renderer:function(a){
						return(this.rendererPlantID(a));
					}
			},{
					header: "Picassa Avail"
				,	dataIndex: 'picassa_PlantID'
				,	width: 80
				,	scope:this	
				,	filterable:true
				,	hidden: Config.picassa_PlantID || false
				,	filter: {type: 'numeric'}				
				,	sortable: true
				,	renderer:function(a){
						return(this.rendererPlantID(a));
					}
			},{
					header: "Picassa Modified"
				,	dataIndex: 'picassa_modified'
				,	width: 120
				,	sortable: true
				,	scope:this	
				,	hidden: true
				,	renderer:function(a){
						return(this.rendererDatehandling(a));
					}					
			},{
					header: "Tiled Processed"
				,	dataIndex: 'gTileProcessed'
				,	width: 80
				,	scope:this	
				,	filterable:true
				,	filter: {type: 'numeric'}				
				,	sortable: true
				,	hidden: Config.gTileProcessed || false
				,	renderer:function(a){
						return(this.renderergTileProcess(a));
					}
			},{
					header: "Zoom Enabled"
				,	dataIndex: 'zoomEnabled'
				,	width: 80
				,	hidden: true
				,	scope:this				
				,	sortable: true
			},{
					header: "Processed"
				,	dataIndex: 'processed'
				,	width: 80
				,	scope:this	
				,	filterable:true
				,	filter: {type: 'numeric'}			
				,	sortable: true
				,	hidden: Config.processed || false
				,	renderer:function(a){
						return(this.renderergTileProcess(a));
					}
			}]
		,	sm: new Ext.grid.RowSelectionModel({singleSelect:true})
		/*,	view: new Ext.grid.GroupingView({
					forceFit: false
				,	emptyText: 'No Image'
				,	deferEmptyText: false
			//	,	rowTemplate: this.smallIcons
			})*/		
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
				,	store: this.store
				,	scope:this
				,	emptyMsg: 'No images available.'
				,	displayInfo: true
				,	displayMsg: 'Displaying Specimen Images {0} - {1} of {2}' 
				,	ref:'../pgtoolbar'
				,	items:['',{
						xtype:'button'
					,	text:'View Image'   
					,	scope:this 
					,	handler: this.viewImage
					}]
			})
		,	listeners:{
					 rowcontextmenu: this.rightClickMenu
			   ,	'rowdblclick': function(grid, index, e) {
							var imv = this.launchImage(index)
							imv.show();
							var barcode = grid.getStore().getAt(index).get('barcode');
							var image_id = grid.getStore().getAt(index).get('image_id');
							var path = grid.getStore().getAt(index).get('path');
							var data = grid.getSelectionModel().getSelected(index);
							var fId = data.get('flickr_PlantID');
							imv.hideInteractiveTab(data.get('gTileProcessed'),data.data.path,data.data.filename);
							imv.hideFlickerTab(fId,data);
							imv.setBarcode(barcode,image_id);								
						//	imv.showImage(path);
							imv.showInfoData(data);
						}
				}			
	    })

	//	this.store.load({params:{start:0, limit:100}});

		ImagePortal.Image.superclass.constructor.call(this, config);

	} 
 
	Ext.extend(ImagePortal.Image, Ext.grid.GridPanel, {
		
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
	
	,	rightClickMenu:function(grid,row,e){
					grid.getSelectionModel().selectRow(row);
					var record = grid.getSelectionModel().getSelected().data;
					
					var items = [];
					
					items.push({
							text: "Rotate 90' Right"
						,	iconCls: 'icon_rotate_right'
						,	scope: this
						,	handler: function() {
									//this.sendRotateRequest(grid, row, "right",90);
									this.rotateImageGUI(grid, row, 90);
							}
					}, {
							text: "Rotate 90' Left"
						,	iconCls: 'icon_rotate_left'
						,	scope: this
						,	handler: function() {
								//this.sendRotateRequest(grid, row, "left",270);
								this.rotateImageGUI(grid, row, 270);
							}
					}, {
							text: "Rotate 180'"
						,	iconCls: 'icon_rotate_image'
						,	scope: this
						,	handler: function() {
								//this.sendRotateRequest(grid, row, null,180);
								this.rotateImageGUI(grid, row,180);
							}
					}, {
							text: "Audit"
						,	scope: this
						,	handler: function() {
								this.audit.push(record.filename);
								var fname= grid.getStore().getAt(row).data
								Ext.Ajax.request({
										scope: this
									,	url: 'resources/api/api.php'
									,	params: {
											cmd : 'audit'
										,	filenames : Ext.encode(this.audit)
										,	autoProcess: Ext.encode({"small":true,"medium":true,"large":true})
											}
									,	success: function(response){
											var response = Ext.decode(response.responseText);
											console.log("Success",response);
										}
									,	failure: function(result){
											console.log("Fail",result)
										}
								});
							}
					}, {
							text: "Process OCR"
						,	scope: this
						,	handler: function() {
								var fname= grid.getStore().getAt(row).data
								Ext.Ajax.request({
										scope: this
									,	url: 'resources/api/api.php'
									,	params: {
											cmd : 'processOCR'
										}
									,	success: function(response){
											var response = Ext.decode(response.responseText);
											console.log("Success",response);
										}
									,	failure: function(result){
											console.log("Fail",result)
										}
								});
							}
					}/*,{
							text: "Reset Image"
						,	iconCls: 'icon_reset_image'
						,	scope: this
						,	handler: function() {
								//this.sendRotateRequest(grid, row, null,0);
								this.rotateImageGUI(grid, row, nul,0);
							}
					}*/,'-',{
							text: "Delete Record"
						,	iconCls: 'icon_delete_image'
						,	scope: this
						,	handler: function() {
								this.sendDeleteRequest(grid, row, null);
							}
					});
					
					var menu = new Ext.menu.Menu({
							items: items
						,	record: record
					});
					var xy = e.getXY();
					menu.showAt(xy);
			}
	
	,	sendDeleteRequest: function(grid, index,column){
						var items = grid.getStore().getAt(index).data;
						function process(btn, text){
							if (btn === 'yes') {	
								var params = {};
									Ext.apply(params, {
												cmd:'delete-image'
											,	imageId: items.image_id
									});
									Ext.Ajax.request({
										url: Config.baseUrl + 'resources/api/api.php'
									,	scope: this
									,	params:params
									,	success: function(responseObject){
												var o = Ext.decode(responseObject.responseText);
												if(o.success){
													this.store.reload();
												}else{
													Ext.MessageBox.alert('Error: '+o.error.code, o.error.message);
												}
											}
									});
							}
						};		
						Ext.MessageBox.confirm('Delete Image','The selected image will be deleted.<br>Are you sure you wish to delete this image?', process);
			}
	
	,	rotateImageGUI:function(grid, row, degree){
					  	var data = this.getSelectionModel().getSelections()[0].data;	
					  	var params = {};
						Ext.apply(params, {
									cmd:'rotate-images'
								,	image_id: data.image_id
								,	degree:degree
						});
						Ext.Ajax.request({
							url: Config.baseUrl + 'resources/api/api.php'
						,	scope: this
						,	params:params
						,	success: function(responseObject){
									var o = Ext.decode(responseObject.responseText);
										if(o.success){
											this.store.reload();
										}else{
											Ext.MessageBox.alert('Error: '+o.error.code, o.error.message)
										}	
								}
						});
			}
	
	
	,	reloadtheStore:function(){
						this.store.reload();
				}

	
	,	changeView: function(item, checked) {
			var tpl;
			switch ( item.activeItem.value ) {
				case 'large':
					tpl = this.tileIcons;
					break;
				case 'small':
					tpl = this.smallIcons;
					break;
				case 'both':
					tpl = this.both;
					break;
				default:
					tmp = null;
			}
			this.getView().changeTemplate(tpl);
		}
	
	,	renderergTileProcess:function(value){
			if (value == 1) return String.format('Yes');
			else return String.format('');
		}	
		
	,	rendererPlantID:function(value){
			if (value != 0 && value > 0) return String.format('Yes');
			else return String.format('');
		}		
	
	,	viewImage:function(){
			if (this.getSelectionModel().getSelections() != '') {
				var index = this.getStore().indexOfId(this.getSelectionModel().getSelected().id);
				var imv = this.launchImage(index)
				var data = this.getStore().getAt(index);
				imv.show();
				imv.hideInteractiveTab(data.data.gTileProcessed,data.data.path,data.data.filename);
				imv.hideFlickerTab(data.data.flickr_PlantID,data);
				var barcode = data.data.barcode;
				imv.setBarcode(barcode,data.data.image_id);
				imv.showInfoData(data);
			}
		}		
	
	,	launchImage:function(index){
			var rowindex = index; 
			var imv = new ImagePortal.ImageViewer({	
						scope:this	
					,	dwnpath:this.store.getAt(rowindex).get('path')			
					,	tools:[{
							id:'left'
						,	qtip: 'Go to previous image'
						,	scope:this
						,	handler: function(event, toolEl, panel){
							rowindex = rowindex - 1;
							//For privious page,when clicks <<,not getting the rowindex.
							if (rowindex < 0) {
								var tb = this.getBottomToolbar();
								if ((tb.items.items[0].enable())) {
									Ext.override(Ext.PagingToolbar, {
										movePrevious: function(){
											this.doLoad(Math.max(0, this.cursor - this.pageSize));
											tb.on('change', function(){
												rowindex = 99;
												var barcode = this.store.getAt(rowindex).get('barcode');
												var path = this.store.getAt(rowindex).get('path');
												var interact = this.store.getAt(rowindex).get('gTileProcessed');
												var fileName = this.store.getAt(rowindex).get('filename');
												imv.dwnpath = path;
												var data = this.store.getAt(rowindex);
												imv.setBarcode(barcode,data.data.image_id, path);
												imv.hideInteractiveTab(interact,path,fileName);
												imv.showInfoData(data);
												this.ownerCt.getSelectionModel().selectRow(99);
												panel.setTitle(fileName);
												
												var fId = this.store.getAt(rowindex).get('flickr_PlantID');
												imv.hideFlickerTab(fId,data);
												
											}, this);
										}
								}, this);
								this.getBottomToolbar().movePrevious();
								}
							}
							else {
								if (rowindex > -1) {
									var barcode = this.getStore().getAt(rowindex).get('barcode');
									var path = this.getStore().getAt(rowindex).get('path');
									imv.dwnpath = path
									var data = this.getStore().getAt(rowindex);
									var interact = this.store.getAt(rowindex).get('gTileProcessed');
									var fileName = this.store.getAt(rowindex).get('filename');
									imv.setBarcode(barcode,data.data.image_id, path);
									imv.hideInteractiveTab(interact,path,fileName);
									imv.showInfoData(data);
									var fId = this.store.getAt(rowindex).get('flickr_PlantID');
									imv.hideFlickerTab(fId,data);
									
									panel.setTitle(fileName);
									this.getSelectionModel().selectRow(rowindex);
								}
								
								else 
									rowindex = 0;
							}
						}
						},{
				    		id:'right'
				    	,	qtip: 'Go to next image'
						,	scope:this									
				    	,	handler: function(event, toolEl, panel){
									rowindex = rowindex + 1;
									var max = this.getStore().getTotalCount();
									if (rowindex < max) { //For next page,when clicks >>,not getting the rowindex. for new page store 0-99
										var tb = this.getBottomToolbar();
										if (rowindex > 99) {
											if ((tb.items.items[7].enable())) {
												Ext.override(Ext.PagingToolbar, {
													moveNext: function(){
														this.doLoad(this.cursor + this.pageSize);
														tb.on('change', function(){
															rowindex = 0;
															var barcode = this.store.getAt(rowindex).get('barcode');
															var path = this.store.getAt(rowindex).get('path');
															imv.dwnpath = path;
															var fId = this.store.getAt(rowindex).get('flickr_PlantID');
															var data = this.store.getAt(rowindex);
															var interact = this.store.getAt(rowindex).get('gTileProcessed');
															var fileName = this.store.getAt(rowindex).get('filename');
															imv.hideFlickerTab(fId,data);
															imv.showInfoData(data);
															imv.setBarcode(barcode,data.data.image_id, path);
															imv.hideInteractiveTab(interact,path,fileName);
															panel.setTitle(fileName);
															this.ownerCt.getSelectionModel().selectRow(rowindex);
														}, this);
													}
												}, this);
												this.getBottomToolbar().moveNext();
											}
										}
										else {
											var barcode = this.getStore().getAt(rowindex).get('image_id');
											var path = this.getStore().getAt(rowindex).get('path');
											imv.dwnpath = path;
											var fId = this.getStore().getAt(rowindex).get('flickr_PlantID');
											var data = this.getStore().getAt(rowindex);	
											var interact = this.store.getAt(rowindex).get('gTileProcessed');
											var fileName = this.store.getAt(rowindex).get('filename');
											imv.hideFlickerTab(fId,data);		
											imv.showInfoData(data);
											imv.setBarcode(barcode,data.data.image_id, path);
											imv.hideInteractiveTab(interact,path,fileName);	
											panel.setTitle(fileName);
											this.getSelectionModel().selectRow(rowindex);
										}
									}
									else 
										rowindex = max - 1;
					    	}
						}]
					});
			return imv;
		}	
	}); // end of extend