/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/

Ext.namespace('CollectionBUS');

CollectionBUS = new Ext.util.Observable();
CollectionBUS.addEvents('year','month', 'week', 'day');

Ext.onReady(function() {

	// Disable browser right click
	Ext.fly(document.body).on('contextmenu', function(e, target) {
		e.preventDefault();
	});	
			
	Ext.QuickTips.init();
	
	Ext.override(Ext.PagingToolbar, {
   		updateInfo:function(){
     		if(this.displayItem){
             	 var count = this.store.getCount();
             	 var msg = count == 0 ?
                 this.emptyMsg :
                    String.format(
                        this.displayMsg,
                        this.cursor+1, this.cursor+count, Ext.util.Format.number(this.store.getTotalCount() , '0,000')
                   );
              this.displayItem.setText(msg);
          }
      }
  });
	
	newtext = function(){
			console.log("called");
	} 
	
	var items = [/*{
				mainItem: 0
			,	activeItem:1
			,	items: [{
						title: 'Welcome'
					,	style: 'padding: 10px;'
					,	layout: 'fit'
				}, {
						title: 'News'
					,	iconCls: 'x-icon-templates'
					,	style: 'padding: 10px;'
					,	layout: 'fit'
					,   id:'newsid'
					,	tabTip: 'Current News'							
					,   items: [ new ImagePortal.NewsPanel() ]
				}, {
						title: 'Contact Us'
					,	iconCls: 'x-icon-templates'
					,	style: 'padding: 10px;'
					,	layout: 'fit'
					,	tabTip: ''
					,   items: [ new ImagePortal.Contactus() ]
				}, {
						title: 'Help'
					,	iconCls: 'x-icon-help'
					,	tabTip: ''
					,	style: 'padding: 10px;'
					,	layout: 'fit'								
					,   items: [new ImagePortal.Help({
								url:'../resources/helpfiles/help-data.json'
						})]								
				}]
			},*/{
				 mainItem: 0
			 ,	 items: [{
						title: 'Console'
					,	iconCls: 'x-icon-subscriptions'
					,	tabTip: ''
					,	style: 'padding: 10px;'
					,	layout: 'fit'
				},{
						title: ' Images '
					,	tabTip: ''
					,	iconCls: 'x-icon-templates'								
					,	style: 'padding: 10px;'
					,	layout: 'fit'
					, 	items: [ new ImagePortal.Image() ]
					,	listeners:{
						render:function(){
							this.items.items[0].store.load({params:{start:0, limit:100}})
							//this.items.items[0].comboStore.load()
						}
					}
				},{
						title: ' Queue '
					,	tabTip: ''
					,	iconCls: 'x-icon-templates'								
					,	style: 'padding: 10px;'
					,	layout: 'fit'
					, 	items: [ new ImagePortal.Queue() ] 
					,	listeners:{
						render:function(){
							this.items.items[0].store.load({params:{start:0, limit:100}})
						}
					}
				},{
						title: ' Sequences '
					,	tabTip: ''
					,	iconCls: 'x-icon-templates'								
					,	style: 'padding: 10px;'
					,	layout: 'fit'
					, 	items: [ new ImagePortal.Sequences() ] 
					,	listeners:{
						render:function(){
						//	this.items.items[0].store.load({params:{start:0, limit:100}})
						//	this.items.items[0].comboStore.load()
						this.items.items[0].cbCollections.store.load();
						}
					}
				}]
			}];
	
	if(Config.reports){
			var reports ={
						mainItem: 0
					,	items: [{
								title: 'Reports'
							,	iconCls: 'x-icon-subscriptions'
							,	tabTip: ''
							,	style: 'padding: 10px;'
							,	layout: 'fit'
						},{
								title: 'Progress of Collections'
							,	tabTip: ''
							,	iconCls: 'x-icon-templates'								
							,	style: 'padding: 10px;'
							,	layout: 'fit'
							, 	items: [ new ImagePortal.ProgressOfCollection() ]
							,	listeners: {
									render: function() {
										this.items.items[0].store.load();										}
								}
					 	},{
								title: ' Imaging by Collection'
							,	tabTip: ''
							,	iconCls: 'x-icon-templates'								
							,	style: 'padding: 10px;'
							,	layout: 'fit'
							, 	items: [ new ImagePortal.ByCollection() ]
					 	},{
								title: ' Imaing by Station/User'
							,	tabTip: ''
							,	iconCls: 'x-icon-templates'								
							,	style: 'padding: 10px;'
							,	layout: 'fit'
							, 	items: [ new ImagePortal.ByStaff() ]
							,	listeners: {
									render: function() {
										this.items.items[0].loadChart( '0' );										}
								}							
					 	},{
                title: ' Species Charts'
              , tabTip: ''
              , iconCls: 'x-icon-templates'               
              , style: 'padding: 10px;'
              , layout: 'fit'
              ,   items: [ new ImagePortal.ReportsPanel() ]
              , listeners: {
                  render: function() {
                    //this.items.items[0].loadChart( '0' );                  
                    }
                }             
            }/*,{
								title: ' Imaging by Time'
							,	tabTip: ''
							,	iconCls: 'x-icon-templates'								
							,	style: 'padding: 10px;'
							,	layout: 'fit'
							, 	items: [ new ImagePortal.MonthRangeChart({
											border:true
										,	style: 'padding:0px'		
									}) ]
							,	listeners: {
									render: function() {
										this.items.items[0].chartTitle('Imaging Progress by Time');
										this.items.items[0].setSeriesSize(1);
										this.items.items[0].setMonthRange(new Date().add(Date.YEAR,-1).format('Y-m-d'), new Date().format('Y-m-d'));
										this.items.items[0].generateSeries();	
										this.items.items[0].store.baseParams.date2 = new Date().format('Y-m-d');																														
										this.items.items[0].store.baseParams.date = new Date().add(Date.YEAR,-1).format('Y-m-d');
										this.items.items[0].store.load();																												
									}
								}								
					 	},{
								title: ' Imaging by Station'
							,	tabTip: ''
							,	iconCls: 'x-icon-templates'								
							,	style: 'padding: 10px;'
							,	layout: 'fit'
							, 	items: [ new ImagePortal.MonthRangeChart({
											border:true
										,	style: 'padding:0px'		
									}) ]
							,	listeners: {
									render: function() {
										this.items.items[0].chartTitle('Imaging Progress by Station');
										this.items.items[0].setSeriesSize(1);
										this.items.items[0].setMonthRange(new Date().add(Date.YEAR,-1).format('Y-m-d'), new Date().format('Y-m-d'));
										this.items.items[0].generateSeries();	
										this.items.items[0].store.baseParams.date2 = new Date().format('Y-m-d');																														
										this.items.items[0].store.baseParams.date = new Date().add(Date.YEAR,-1).format('Y-m-d');
										this.items.items[0].store.load();																																																																					
									}
								}								
					 	}*/]
					} ;
			
			items.push(reports);
	}
	
	var Viewport = new Ext.Viewport({
			layout:'border'
		,	id:'mainviewport'			
		,	deferredRender:true
		,	items: [{
					height: 60
				,	region: 'north'
				,	border: false
				,	bodyStyle: 'background-color: #4E78B1; padding: 5px'
				,	html: '<div class="hd-imagingprocesstext">' + Config.title + '</div><div class="rightarea"><div class="hd-user">Welcome: ' + USER + ' </div><div class="signout"><a href="/login.php?task=sign_out" class="a-signout">Signout</a></div></div></div>'
			},{
					xtype: 'grouptabpanel'
				,	region:'center'
				,	layoutOnTabChange: true
				,	deferredRender: false
				,	tabWidth: 175
				,	activeGroup: 0	
				,	items: items
					}]
    		});
	});

								
