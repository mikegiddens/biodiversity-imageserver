/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/

Ext.override(Ext.PagingToolbar, {
		updateInfo: function(){
			if(this.displayItem){
				var count = this.store.getCount();
				var msg = count == 0 ?
					this.emptyMsg :
						String.format(
								this.displayMsg
							,	this.cursor + 1
							,	this.cursor + count
							, Ext.util.Format.number(this.store.getTotalCount(), '0,000')
						);
				this.displayItem.setText(msg);
			}
		}
	});


	Ext.namespace('ImagePortal');

	ImagePortal.ImagePortalRemote = function(config){

	var config2 = {};
	
	Ext.apply(config2, config, {
			border: true
		,	width: 700
		,	height: 420
		,	store: new Ext.data.GroupingStore({
						proxy: new Ext.data.ScriptTagProxy({
								url: Config.baseUrl + 'resources/api/api.php'
							})
					,	baseParams: { 
								cmd: 'images'
							,	filter:''
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
				})
 	});

	ImagePortal.ImagePortalRemote.superclass.constructor.call(this, config2);

};

 
	Ext.extend(ImagePortal.ImagePortalRemote, ImagePortal.Image, {
			initLoadMethod:function(){
							if(!Config.showCollection){
									this.getBottomToolbar().insert(13, this.views);
									this.getTopToolbar().setVisible(false);
								}
							this.doLayout();
							this.search_value.setValue(Config.defaultCollection);
							this.search_value.triggers[0].show();
							this.store.baseParams= Config.imageStoreParams;
							this.store.load();
							this.search_value.setDisabled(Config.disableCollection);
							this.search_value.setVisible(Config.showCollection);
				}
		,	rightClickMenu:function(grid,row,e){
					grid.getSelectionModel().selectRow(row);
					var record = grid.getSelectionModel().getSelected().data;
					
					var items = [];
					
					items.push({
							text: "View Image"
						//,	iconCls: 'icon_delete_image'
						,	scope: this
						,	handler: this.viewImage
					});
					
					var menu = new Ext.menu.Menu({
							items: items
						,	record: record
					});
					var xy = e.getXY();
					menu.showAt(xy);
			}		
	}); // end of extend