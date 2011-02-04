/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/

Ext.namespace('ImagingProgress');

ImagePortal.ByCollection = function(config) {


	this.coldaystore = new Ext.data.JsonStore({
				url:'resources/api/api.php'	
			,	fields: ['coll','l1']		
			,	root: 'data'
			,	baseParams: {
					cmd: 'collection_report'
				,	date: ''			
				,	collection_id: ''							
            	,	report_type: 'day'
			}							
    	});
    
     this.coldaychart =  new Ext.Panel({
        		width:'99.50%'
        	,	height:230
			,	title: 'Day: '
        	,	layout:'fit'
			,	scope:this
			,	style: 'padding:2px'
        	,	items: {
            			xtype: 'columnchart'
            		,	store:this.coldaystore 
					,	ref:'../coldaychart'						
            		,	xField: 'coll'
					,   xAxis: new Ext.chart.CategoryAxis({
	        		        title: ''
            			})	
					,   yAxis: new Ext.chart.NumericAxis({
	        		        title: 'Number Imaged'
            			})							
             		,	series: [{
                				type: 'line'
                			,	displayName: 'L1'
                			,	yField: 'l1'
                			,	style: {
                    				color: 0x15228B
								, 	size:6								
                				}							
            			}]
					,	extraStyle: {
                 		   yAxis: {
                    		    titleRotation: -90
                    		}
                		}
	                ,	listeners: {
    	                	itemclick: function(o){
								var tday = this.store.baseParams.start_date.split('-');
								tday = tday[0] + '/' + tday[1] + '/' + tday[2];							
								CollectionBUS.fireEvent('collweek', tday , this);
                    		}
                		}						
					}						
				,	listeners: {
		           		render: function(){
		           			//CollectionBUS.on('collmonth', this.monthChange, this);
				            CollectionBUS.on('collday', this.dayChange, this);
		           		}
		           	}
				,   dayChange: function(day,collid){
						this.setTitle('Day: ' + day);
						this.items.items[0].store.baseParams.date = day;
						this.items.items[0].store.baseParams.date2 = day;									
						this.items.items[0].store.baseParams.collection_id = collid;						
						this.items.items[0].store.load();
					}
   			});
			
	  this.colweekstore = new Ext.data.JsonStore({
				url:'resources/api/api.php'	
			,	fields: ['coll','l1']		
			,	root: 'data'
			,	baseParams: {
					cmd: 'collection_report'
		     	,	date:''
        		,	date2:''
				,	collection_id:''							
            	,	report_type: 'week'
			}
    	});
    
     this.colweekchart =  new Ext.Panel({
        		width:'99.50%'
        	,	height:230
			,	title: 'Week : '
        	,	layout:'fit'
			,	style: 'padding:2px'			
			,	scope:this
        	,	items: {
            			xtype: 'columnchart'
            		,	store:this.colweekstore 
					,	ref:'../colweekchart'						
            		,	xField: 'coll'
					,   xAxis: new Ext.chart.CategoryAxis({
	        		        title: ''
            			})	
					,   yAxis: new Ext.chart.NumericAxis({
	        		        title: 'Number Imaged'
            			})							
             		,	series: [{
                				type: 'line'
                			,	displayName: 'L1'
                			,	yField: 'l1'
                			,	style: {
                    				color: 0x15228B
								, 	size:6								
                				}							
            			}]
					,	extraStyle: {
                 		   yAxis: {
                    		    titleRotation: -90
                    		}
                		}
					,	listeners: {
		                	itemclick: function(o){
								var day = this.getFirstDayOfWeek(this.store.baseParams.date);
								var day = day.add(Date.DAY, o.index);
	                        	CollectionBUS.fireEvent('collday', day.format('Y-m-d'),this.store.baseParams.collection_id , this);
	                    		CollectionBUS.fireEvent('scrolldivtoview',800,this);
							}	
	        	    	}
					,	getFirstDayOfWeek:function(day){
								var value = new Date(day);
								var dayOfWeek = value.getDay();
								dayOfWeek = (dayOfWeek + 6) % 7;
								value.setDate(value.getDate() - dayOfWeek);
								return value;
							}		
					}						
				,	listeners: {
		                	render: function(){
					            CollectionBUS.on('collweek', this.weekChange, this);
		    	            }
	        	    }
	          	,	weekChange: function(day,collid){
						var sDate = this.getFirstDayOfWeek(day);
						var eDate = sDate.add(Date.DAY, 6);									
						this.setTitle('Week: ' + sDate.format('Y-m-d') + ' - ' + eDate.format('Y-m-d'));
						this.items.items[0].store.baseParams.date = sDate.format('Y-m-d');
						this.items.items[0].store.baseParams.date2 = eDate.format('Y-m-d');		
						this.items.items[0].store.baseParams.collection_id = collid;											
						this.items.items[0].store.load();
						CollectionBUS.fireEvent('collday', sDate.format('Y-m-d'),collid , this);
					}
					
				,	getFirstDayOfWeek:function(day){
						var value = new Date(day);
						var dayOfWeek = value.getDay();
						dayOfWeek = (dayOfWeek + 6) % 7;
						value.setDate(value.getDate() - dayOfWeek);
						return value;
					}	    			
			});
				
	 this.colmonthstore = new Ext.data.JsonStore({
				url:'resources/api/api.php'	
			,	fields: ['coll','l1']		
			,	root: 'data'
			,	baseParams: {
					cmd: 'collection_report'
				,	collection_id:''							
           		,	report_type: 'month'
				,	month:''
			}							
    	});
    
     this.colmonthchart =  new Ext.Panel({
        		width:'99.50%'
        	,	height:230
			,	title: 'Month : '
        	,	layout:'fit'
			,	style: 'padding:2px'			
			,	scope:this
        	,	items: {
            			xtype: 'columnchart'
            		,	store:this.colmonthstore 
					,	ref:'../colmonthchart'					
            		,	xField: 'coll'
					,   xAxis: new Ext.chart.CategoryAxis({
	        		        title: ''
            			})	
					,   yAxis: new Ext.chart.NumericAxis({
	        		        title: 'Number Imaged'
            			})							
             		,	series: [{
                				type: 'line'
                			,	displayName: 'L1'
                			,	yField: 'l1'
                			,	style: {
                    				color: 0x15228B
								, 	size:6								
                				}							
            			}]
					,	extraStyle: {
                 		   yAxis: {
                    		    titleRotation: -90
                    		}
                		}
                ,	listeners: {
		                    	itemclick: function(o){
										var day = this.store.baseParams.date.split('-');
										var datevar = (o.index)+1;
										var datevar = datevar < 10 ? "0" + datevar : datevar
										var day = day[0] + '-' + day[1] + '-' + datevar ;
										CollectionBUS.fireEvent('collweek', day,this.store.baseParams.collection_id, this);
										CollectionBUS.fireEvent('collday', day,this.store.baseParams.collection_id , this);
										CollectionBUS.fireEvent('scrolldivtoview',500,this);
			                    	}
	                	}
            		}
          	,	monthChange: function(monthIndex, year,collid){
					var dt = new Date(monthIndex + '/1/' + year);
					var sDate = dt.getFirstDateOfMonth().format('Y-m-d');
					var eDate = dt.getLastDateOfMonth().format('Y-m-d');
					var monname = Date.monthNames[monthIndex - 1];
					this.setTitle('Month: ' + monname + ' (' + sDate + ' - ' + eDate + ')');
					this.items.items[0].store.baseParams.date = sDate;
					this.items.items[0].store.baseParams.date2 = eDate;																	
					this.items.items[0].store.baseParams.collection_id = collid;
					this.items.items[0].store.baseParams.month = monthIndex; 					
					this.items.items[0].store.load();
					var day1 = sDate.split('-');
					var d = new Date();
					var day = d.format('Y-m-d').split('-');
					var day = day1[0] + '-' + day1[1] + '-' + day[2] ;
					CollectionBUS.fireEvent('collweek', day,collid, this);
				}
			,	listeners: {
	                    	render: function(){
						            CollectionBUS.on('collmonth', this.monthChange, this);
			                	}
	                	}	
    });							
		
		
	this.colyearstore = new Ext.data.JsonStore({
				url:'resources/api/api.php'	
			,	fields: ['coll','monthInt','l1']		
			,	root: 'data'	
			,	baseParams: {
					cmd: 'collection_report'
				,	year: 2010
				,	collection_id:''
				,	report_type: 'year'
			}						
	});
   
	this.colyearchart =  new Ext.Panel({
			width:'99.50%'
		,	height: 230
		,	title: 'Year: '
		,	style: 'padding:2px'
		,	layout:'fit'
		,	items: {
					xtype: 'columnchart'
				,	store: this.colyearstore 
				,	ref:'../colyearchart'
				,	xField: 'coll'
				,	xAxis: new Ext.chart.CategoryAxis({
							title: ''
					})	
				,	yAxis: new Ext.chart.NumericAxis({
							title: 'Number Imaged'
					})
				,	series: [{
                				type: 'line'
                			,	displayName: 'L1'
                			,	yField: 'l1'
                			,	style: {
                    				color: 0x15228B
								, 	size:6								
                				}							
            			}]
				,	extraStyle: {
						 yAxis: {
								titleRotation: -90
						}
					}
				,	listeners: {
							itemclick: function(o) {
								CollectionBUS.fireEvent('collmonth', this.store.getAt(o.index).get('monthInt'), this.store.baseParams.year,this.store.baseParams.collection_id,this );
								CollectionBUS.fireEvent('scrolldivtoview',200,this);
							}
					}						
			}
        ,	listeners: {
            	render: function(){
			    	CollectionBUS.on('collyear', this.yearChange, this);
                }	
            }			
		,	yearChange: function( lyear, collid) {
				this.setTitle('Year: ' + lyear);
				this.items.items[0].store.baseParams.year = lyear;	
				this.items.items[0].store.baseParams.collection_id = collid;									
				this.items.items[0].store.load();
				var d = new Date();
				var monthvar = (d.getMonth()+1);
				CollectionBUS.fireEvent('collmonth', monthvar, lyear,collid,this );
			}	
	});
					
	this.comboStore = new Ext.data.JsonStore({
			fields: ['collection_id', 'name'] 
		,	url: 'resources/api/api.php'
		,	baseParams:{
				cmd:'collections'
			}
		,	root: 'records'
	});	
	this.yearStore = new Ext.data.SimpleStore({
			fields:['Yr']
		,	data: this.yearStoreData() //[[2010],[2009],[2008],[2007]] 
	});						
				
	this.search_value = new Ext.ux.TwinComboBox({
						fieldLabel: 'Collections'
					, 	name: 'Collections'
					, 	triggerAction: 'all'
					,	store: this.comboStore
					,	ref: '../cbCollections'
					,	valueField: 'collection_id'
					,	displayField: 'name'
					,	emptyText:"Select collection"
					,	hideTrigger2: false
					,	width:250
					,	hideTrigger1: true
					,	editable:false
					,	value:''
					, 	listeners: {
								select: function (combo, record) {
										this.coll_id = record.data.collection_id;
										this.loadStores(record.data.collection_id);									
									}.createDelegate( this )
							,	clear: function() {
										this.loadallStore();									
									}.createDelegate( this )
						}
	});
				
	//this.comboStore.on('load', this.generateSeries, this );
	
	this.yearCombo = new Ext.form.ComboBox({
					selectOnFocus: true
				,	width: 50
				,	ref: '../yrCollections'
				,	triggerAction: 'all'
				,	store: this.yearStore
				,	mode: 'local'
				,	fieldLabel:'Year: '
				,	lazyRender: true
				,	editable: false
				,	value:'2010'
				,	valueField: 'Yr'
				,	displayField: 'Yr'
				,	listeners:{
								select: function (combo, record) {
									this.colyearstore.baseParams.year = record.data.Yr;	
									if(this.coll_id!='')
										this.loadStores(this.coll_id);
								}.createDelegate( this )
							}
		})		
		
		CollectionBUS.on('scrolldivtoview',this.scrolldivtoview,this);
			
	Ext.apply(this,config,{
			autoScroll: true	
		,	title: 'Imaging by Collection'				
		,	coll_id:''
		,	tbar:[
					'Collection: '
				,	this.search_value
				,	'Year: '
				,	this.yearCombo
			] 				
		,	items:[this.colyearchart, this.colmonthchart, this.colweekchart, this.coldaychart]
		,	listeners: {
				render: function() {
						this.loadStores();
				}
			}
		});

		ImagePortal.ByCollection.superclass.constructor.call(this, config);

	};

Ext.extend(ImagePortal.ByCollection, Ext.Panel, {
		
	initComponent: function(){
		extmainpnl.superclass.initComponent.call(this, arguments);
	}
,	yearStoreData: function(){
		var d = new Date();
		var curr_year = d.getFullYear();
		var yearArray = [];
		var lastYear = 2009;
		for (i= curr_year; i>= lastYear ; i--){
			var arr= [];
			arr.push(i)
			yearArray.push(arr);
		}
		return yearArray;
	}		
,	scrolldivtoview:function(srvalue){
			var el = Ext.getDom(this.coldaychart.refOwner.id);
			var scrollValue = srvalue 
			if (el) {
				//var top = (Ext.fly(el).getOffsetsTo(this.body)[1]) + this.body.dom.scrollTop;
				this.body.scrollTo('top',scrollValue, {
					duration: 0.5,
					callback: function(){
					}
				});
			}
		}		
		
	/*
,	generateSeries:function( data ) {
			
			this.series = new Array();
			// Add collections to series
			data.each( function(t) {
				this.series.push({
						type: 'line'
					,	displayName: t.data.data
					,	yField: 'l' + t.data.value
					,	style: {
								color: 0x15228B
								, 	size:6								
						}							
				});
			}, this );

			this.setSeries();
			//this.loadStores('ALL');
		}
		
	,	setSeries:function(){
			this.colyearchart.series = this.series;
			this.colmonthchart.series = this.series;
			this.colweekchart.series = this.series;
			this.coldaychart.series = this.series;									
		}	
			
*/
	,	loadallStore:function(){
					this.collid = '';
					this.yearCombo.setValue(2010);
					this.colyearstore.baseParams.year = 2010;	
					this.colyearstore.baseParams.collection_id= ''	
					this.colyearstore.load();
					
					this.colmonthstore.baseParams.month = '';			
					this.colmonthstore.baseParams.collection_id= ''
					this.colmonthstore.load();
					
					this.colweekstore.baseParams.date = '';		
					this.colweekstore.baseParams.date2 = '';			
					this.colweekstore.baseParams.collection_id= ''
					this.colweekstore.load();
					
					this.coldaystore.baseParams.date = '';			
					this.coldaystore.baseParams.collection_id= ''
					this.coldaystore.load();	
			}
	
	,	loadStores:function(collid){
			var collid = (typeof collid == 'undefined') ? "" : collid;
			var yearvar = this.yearCombo.getValue();
			var delay = new Ext.util.DelayedTask(function(){
				CollectionBUS.fireEvent('collyear', yearvar,collid, this);
			},this);
			delay.delay(800);  // Used to wait until events are registered
										
		}		

});