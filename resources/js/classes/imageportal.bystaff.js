/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/

Ext.namespace('ImagePortal');

ImagePortal.ByStaff = function(config) {

	this.coldaystore = new Ext.data.JsonStore({
				url:'resources/api/api.php'	
			,	fields: ['coll','l1'] 	
			,	root: 'data'
			,	baseParams: {
					cmd: 'graph_report_user'
		      	,	station_id:''
				,	user_id:''
				,	date:''
        		,	date2:''	
           		,	report_type: 'day'
				,	sc:'CFLA1'		
				}
    	});
    
     this.coldaychart =  new Ext.Panel({
        		width:'99.50%'
        	,	height:230
			,	title: 'Day : '
        	,	layout:'fit'
			,	scope:this
			,	style: 'padding:2px'		
        	,	items: {
            			xtype: 'columnchart'
            		,	store:this.coldaystore 
            		,	xField: 'coll'
					,   xAxis: new Ext.chart.CategoryAxis({
	        		        title: ''
            			})	
					,   yAxis: new Ext.chart.NumericAxis({
	        		        title: 'Numbers'
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
								tday = tday[0] + '-' + tday[1] + '-' + tday[2];							
								CollectionBUS.fireEvent('week', tday , this);
                    		}
                		}						
					}						
				,	listeners: {
		           		render: function(){
		           			CollectionBUS.on('month', this.monthChange, this);
				            CollectionBUS.on('day', this.dayChange, this);
		           		}
		           	}
				,   monthChange: function(month){
		        	}
				,   dayChange: function(day,uid){
						this.setTitle('Day: ' + day);
						this.items.items[0].store.baseParams.date = day;
						this.items.items[0].store.baseParams.date2 = day;	
						this.items.items[0].store.baseParams.user_id = uid;	
						if(uid > 0 && uid <=5)
							this.items.items[0].store.baseParams.station_id = 1	;
						else if(uid > 5 && uid <=10)
							this.items.items[0].store.baseParams.station_id = 2	;								
						this.items.items[0].store.load();					
					}
   			}); //eo day chart
			
	 
	  this.colweekstore = new Ext.data.JsonStore({
				url:'resources/api/api.php'	
	 		,	fields: ['coll','l1']	
			,	root: 'data'
			,	baseParams: {
					cmd: 'graph_report_user'
		      	,	station_id:''
				,	user_id:''
				,	date:''
        		,	date2:''	
           		,	report_type: 'week'
				,	sc:'CFLA1'
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
            		,	xField: 'coll'
					,   xAxis: new Ext.chart.CategoryAxis({
	        		        title: ''
            			})	
					,   yAxis: new Ext.chart.NumericAxis({
	        		        title: 'Numbers'
            			})							
             		,	series: [{            //for 1 line on graph
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
									CollectionBUS.fireEvent('day', day.format('Y-m-d'), this.store.baseParams.user_id, this);
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
				            CollectionBUS.on('week', this.weekChange, this);
	    	            }
	        	    }
	          	,	weekChange: function(day,uid){
						var sDate = this.getFirstDayOfWeek(new Date(day));
						var eDate = sDate.add(Date.DAY, 5);	
						this.setTitle('Week : ' + sDate.format('Y-m-d') + ' - ' + eDate.format('Y-m-d'));	
						this.items.items[0].store.baseParams.date = sDate.format('Y-m-d');
						this.items.items[0].store.baseParams.date2 = eDate.format('Y-m-d');
						this.items.items[0].store.baseParams.user_id = uid;
						if(uid > 0 && uid <=5)
							this.items.items[0].store.baseParams.station_id = 1	;
						else if(uid > 5 && uid <=10)
							this.items.items[0].store.baseParams.station_id = 2	;	
						this.items.items[0].store.load();
						//For day chart
						CollectionBUS.fireEvent('day', sDate.format('Y-m-d'),uid , this);
					}
					
				,	getFirstDayOfWeek:function(tdate){
						var value = tdate;
						var dayOfWeek = value.getDay();
						dayOfWeek = (dayOfWeek + 6) % 7;
						value.setDate(value.getDate() - dayOfWeek);
						return value;
					}	    			
			});//eo week chart
				
	 this.colmonthstore = new Ext.data.JsonStore({
				url:'resources/api/api.php'	
			,	fields: ['coll','l1']	
			,	root: 'data'
			,	baseParams: {
					cmd: 'graph_report_user'
		      	,	station_id:''
				,	user_id:''
    	    	,	month:''
				,	date:''
        		,	date2:''	
           		,	report_type: 'month'
				,	sc:'CFLA1'
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
            		,	xField: 'coll'
					,   xAxis: new Ext.chart.CategoryAxis({
	        		        title: ''
            			})	
					,   yAxis: new Ext.chart.NumericAxis({
	        		        title: 'Numbers'
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
                        		CollectionBUS.fireEvent('week',day,this.store.baseParams.user_id, this);
                    	}
                	}
            	}
            ,	listeners: {
                	render: function(){
			            CollectionBUS.on('month', this.monthChange, this);
                	}	
            	}
          	,	monthChange: function(monthIndex, year, uid){
					var dt = new Date(monthIndex + '/1/' + year); //creats the date 1 for monthindex
					var sDate = dt.getFirstDateOfMonth().format('Y-m-d');
					var eDate = dt.getLastDateOfMonth().format('Y-m-d');
					var monname = Date.monthNames[monthIndex - 1];
					this.setTitle('Month : ' + monname + ' (' + sDate + ' - ' + eDate + ')');
					this.items.items[0].store.baseParams.month = monthIndex;
					this.items.items[0].store.baseParams.date = sDate;
					this.items.items[0].store.baseParams.date2 = eDate;		
					this.items.items[0].store.baseParams.user_id = uid;	
					if(uid > 0 && uid <=5)
						this.items.items[0].store.baseParams.station_id = 1	;
					else if(uid > 5 && uid <=10)
						this.items.items[0].store.baseParams.station_id = 2	;	
					this.items.items[0].store.load();
					//Date for week chart.
					var day1 = sDate.split('-');
					var d = new Date();
					var day = d.format('Y-m-d').split('-');
					var day = day1[0] + '-' + day1[1] + '-' + day[2] ;
                	CollectionBUS.fireEvent('week',day,uid, this);
				}
    		}); //eo month
			
				
	this.colyearstore = new Ext.data.JsonStore({
				url:'resources/api/api.php'	
			,	fields: ['coll','monthInt','l1']	
			,	root: 'data'	
			,	baseParams: {
					cmd: 'graph_report_user'
		      	,	year: ''
				,	station_id:''
				,	user_id: ''
				,	report_type: 'year'
				,	sc:'CFLA1'
			}
						
    	});
    
     this.colyearchart =  new Ext.Panel({
        		width:'99.50%'
        	,	height:230
			,	title: 'Year : '
			,	style: 'padding:2px'		
        	,	layout:'fit'
			,	scope:this
        	,	items: {
            			xtype: 'columnchart'
            		,	store:this.colyearstore 
            		,	xField: 'coll'
					,   xAxis: new Ext.chart.CategoryAxis({
	        		        title: ''
            			})	
					,   yAxis: new Ext.chart.NumericAxis({
	        		        title: 'Numbers'
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
                    		itemclick: function(o,a,b){
								CollectionBUS.fireEvent('month', this.store.getAt(o.index).get('monthInt'), this.store.baseParams.year);								
							}
						}						
					}
				,	listeners: {
							render: function() {
								CollectionBUS.on('year', this.yearChange, this);
							}
					}			
		   		,	yearChange: function( lyear, uid) {
						this.setTitle('Year: ' + lyear);
						this.items.items[0].store.baseParams.year = lyear;	
						this.items.items[0].store.baseParams.user_id = uid;	
						if(uid > 0 && uid <=5)
							this.items.items[0].store.baseParams.station_id = 1	;
						else if(uid > 5 && uid <=10)
							this.items.items[0].store.baseParams.station_id = 2	;							
						this.items.items[0].store.load();
						// data for month load.
						var d = new Date();
						var monthvar = (d.getMonth()+1);
						CollectionBUS.fireEvent('month', monthvar, lyear,uid,this);
						
					}	
    		});//eo year chart
					
		this.northPanel = new Ext.Panel({
				title:'Details'
			,	height:125
			,	width:'99.50%'
			,	scope:this
			,	style: 'padding:2px'		
			,	ref:'../northpanel'
		})					
		
		
		this.list = new Ext.ux.TreeCombo({
					emptyText : 'Select User'
		});
		//this.list.getTree('http://images.cyberfloralouisiana.com/portal/resources/api/api.php');
		this.list.getTree('cmbotree.json');
		this.list.on('nodeId',function(value,text,com){
			this.userInfo(value,text); 
		},this);
	
		this.yearStore = new Ext.data.SimpleStore({
					fields:['Yr']
				,	data:[[2010],[2009],[2008],[2007]]
			});	
	
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
								if(this.listId != '')
									this.loadChart(this.listId);
						}.createDelegate( this )
					}
		})
	
		Ext.apply(this,config,{
			scope:this
		,	autoScroll: true	
		,	title:'Imaging by Station/User'		
		,	listId:''
		,	tbar:[
					this.list
				,	'Year: '
				,	this.yearCombo
				] 				
		,	items:[this.northPanel,this.colyearchart, this.colmonthchart, this.colweekchart, this.coldaychart]	
		});

		ImagePortal.ByStaff.superclass.constructor.call(this, config);

	};

	extmainpnl = Ext.extend(ImagePortal.ByStaff, Ext.Panel, {
		
		initComponent: function(){
			extmainpnl.superclass.initComponent.call(this, arguments);
		}
		
	,	userInfo:function(value,text){
			this.items.items[0].body.update("Selected id: " + value + " and text: " + text);
			this.listId = value;
			this.loadChart(value);
		}
	
	,	loadChart:function( id ){  //id=1,2..
	    	/*var d = new Date();
			var startDatevar = d.getFirstDateOfMonth();
   			var endDatevar = d.getLastDateOfMonth();	
	    	var monthvar = Date.monthNames[ d.getMonth() ];
			var weekvar = d.getWeekOfYear();*/
			var yearvar = this.yearCombo.getValue();		
			var delay = new Ext.util.DelayedTask(function(){
				CollectionBUS.fireEvent('year', yearvar,id, this);
				//CollectionBUS.fireEvent('month', d.getMonth()+1, d.getFullYear(),id, this);
				//CollectionBUS.fireEvent('day', d.format('Y-m-d'), id, this);	
				//CollectionBUS.fireEvent('week', d.format('Y-m-d'), id, this);				
			});
	    	delay.delay(800);  // Used to wait until events are registered    	
		}

	})
	
