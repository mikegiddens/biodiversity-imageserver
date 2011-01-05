Ext.onReady(function() {       

	var map = new FusionMaps("resources/fusionmaps/FCMap_Louisiana.swf", "Map_Id", 600, 500, "0", "0");
	map.setDataURL("data.xml");
	map.render("map");

	var store = new Ext.data.JsonStore({
			fields: ['collection', 'imaged', 'notimaged']
		//,	url: '../../gui/api/api.php'
		,	url:'resources/api/api.php'	
		,	baseParams: {
				cmd: 'sizeOfCollection'
			}
		,	root: 'data'
		,	autoLoad: true
	});

	var tpl = new Ext.Template(
			'<p>Name: {name}</p>'
		,	'<p>Collection Code: {code}</p>'
		,	'<p>Collection Size: {size}</p>'
	);

	this.loadDetails = function(vid) {

		Ext.Ajax.request({
				url: '../../gui/api/api.php'
			,	url:'resources/api/api.php'		
			,	params: { 
						cmd:'details' 
					,	id:vid
				}                    
			,	scope:this
			,	success: function(r) {
					var data = Ext.decode(r.responseText);
					if ( data.success ) {						
						var d = {
								name: data.data[0].name
							,	code: data.data[0].code
							,	size: data.data[0].collectionSize
						}
						tpl.overwrite(this.details.body, d);
						this.details.body.highlight('#AA0055', {block:true});
					}
				}
			,	failure: function() {}
			
		});
				
	}

	this.chart = new Ext.Panel({
			width: 900
		,	height: 400
		,	renderTo: 'stats'
		,	title: 'Size of Collections'
		,	items: {
					xtype: 'stackedbarchart'
				,	store: store
				,	yField: 'collection'
				,	xAxis: new Ext.chart.NumericAxis({
							stackingEnabled: true
						,	labelRenderer: Ext.util.Format.numberRenderer('0,0')
					})
				,	series: [{
							xField: 'imaged'
						,	displayName: 'Imaged'
					},{
							xField: 'notimaged'
						,	displayName: 'Not Imaged'
					}]
				}
			});

	this.details = new Ext.Panel({
			title: 'Collection Details'
		,	width: 300
		,	height: 450
		,	bodyStyle: 'padding: 10px'
		,	html: '<p><i>Click on collection on the map to display details</i></p>'
		,	renderTo: 'details'
	});

	var imgpbtime = new ImagePortal.MonthRangeChart({
			renderTo:'imgbytime'
		,	style: 'margin-top: 10px'
	});
	imgpbtime.chartTitle('Imaging Progress by Time');
	imgpbtime.setSeriesSize(12);
	imgpbtime.generateSeries();	
	imgpbtime.store.baseParams.cmd = 'report_by_date_range';																														
	imgpbtime.store.baseParams.date = new Date().format('Y-m-d');																														
	imgpbtime.store.baseParams.date2 = new Date().add(Date.YEAR,1).format('Y-m-d');
	imgpbtime.store.load();																												
																												

	var imgpbstation = new ImagePortal.MonthRangeChart({
			renderTo:'imgbystation'
		,	style: 'margin-top: 10px'	
	});
	imgpbstation.chartTitle('Imaging Progress by Station');
	imgpbstation.setSeriesSize(5);
	imgpbstation.generateSeries();	
	imgpbstation.store.baseParams.cmd = 'graph_report_station';																														
	imgpbstation.store.baseParams.date = new Date().format('Y-m-d');																														
	imgpbstation.store.baseParams.date2 = new Date().add(Date.YEAR,1).format('Y-m-d');
	imgpbstation.store.load();																												

});