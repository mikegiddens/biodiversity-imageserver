/**
 * @copyright SilverBiology, LLC
 * @author Shashank Shekhar Rahul
 * @website http://www.silverbiology.com
*/

Ext.namespace('ImagePortal');

ImagePortal.MonthRangeChart = function(config) {

	 this.store =new Ext.data.JsonStore({
	 			//url:'../../gui/api/api.php'
				url:'resources/api/api.php'
			,	fields: ['time','col1']	
			//,	root: 'data'
			,	baseParams:{  //for testing 
						cmd:'report_by_date_range'
					,	date:'2009-12-01'
					,	date2:'2009-12-30'
					,	station_id:1
					,	sc:'CFLA1'
					,	user_id:3
				}
    	});
		
		Ext.apply(this,config,{
				title:'Title'			
			,	width:797
        	,	height:400
        	,	autoScroll: true	
			,	border:true
			,	layout:'fit'
			,	style: 'padding:2px'
        	,	items: {
            			xtype: 'stackedcolumnchart'
            		,	store:new Ext.data.JsonStore({
							 			//url:'../../gui/api/api.php'
										url:'resources/api/api.php'
									,	fields: ['time','col1']	
									,	root: 'data'
									,	baseParams:{  //for testing 
												cmd:'report_by_date_range'
											,	date:'2010-12-01'
											,	date2:'2009-12-30'
											,	station_id:1
											,	sc:'CFLA1'
											,	user_id:3
										}
											
						    	})
					,	scope:this 
					,	ref:'../rangechart'			
            		,	xField: 'time'
					,	orientation: 'vertical'
					,   xAxis: new Ext.chart.CategoryAxis({		
	        		       	stackingEnabled: true				
	        		    ,	title: 'Range Between Dates'
            			})	
					,   yAxis: new Ext.chart.NumericAxis({
                    		stackingEnabled: true				
	        		    ,	title: 'Numbers'
            			})							
					/*,	series:[{
							yField: 'col1'
						,	xField: 'time'
					}]*/
	                ,	series:[]
					,	extraStyle: {
                 		   yAxis: {
                    		    titleRotation: -90
                    		}
  						,	xAxis: {
								labelRotation: -90
							}
                		}						
					}	
		});

		ImagePortal.MonthRangeChart.superclass.constructor.call(this, config);

	};
	
	Ext.extend(ImagePortal.MonthRangeChart, Ext.Panel, {
		
		setSeriesSize:function(nostack){
			this.nostack = nostack;	
		}

	,	chartTitle:function(title){
			this.setTitle(title);
		}
		
	,	setMonthRange:function(startDate,endDate){
			this.startDate = startDate;
			this.endDate = endDate;
		}
		
	,	generateSeries:function(){

			this.series = new Array();
			for(var i=1;i<=this.nostack;i++){
				this.series.push({
						yField:'col'+i
					,	xField:'time'	
				//	yField:'coll'
				});							
			}
			this.items.items[0].series = this.series;
			this.getMonthRange();
 		}	
	
	,	getMonthRange:function(){
			
			var data = new Array();
			var startDate = new Date(this.startDate);
			var endDate = new Date(this.endDate);	
			
			while(startDate < endDate){
					
				var smonth = startDate.getMonth();
				var syear = startDate.getFullYear();
				var emonth = endDate.getMonth();
				var eyear = endDate.getFullYear();

				var nos = new Array();				
				nos.push((smonth+1)+'-'+syear);
				for(var j=1;j<=this.nostack;j++)
					nos.push(Ext.ux.getRandomInt(0, 200));
				data.push(nos);
				
				startDate = startDate.add(Date.MONTH,1);
					
			}
			this.store.loadData(data);
		}
	});
	