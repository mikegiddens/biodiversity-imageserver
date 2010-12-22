Ext.setup({
		icon: 'icon.png'
	,	tabletStartupScreen: 'tablet_startup.png'
	,	phoneStartupScreen: 'phone_startup.png'
	,	glossOnIcon: false
	,	onReady: function() {
			
		CFLABUS = new Ext.util.Observable();
		
		Ext.regModel('CFLAMenu', {
					    fields: [
						        	{name: 'text', type: 'string'}
								,	{name: 'leaf', type: 'string'}
								,	{name: 'collection_id'}
				        	    ,	{name: 'name'}
				            	,	{name: 'code'}
						    ]
			});
			
		Ext.regModel('CFLAImages', {
					    fields: [
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
						    ]
			});

		Ext.regModel('SCCollection', {
					    fields: [
					        		{name: 'collection_id'}
				        	    ,	{name: 'name'}
				            	,	{name: 'code'}
						    ]
			});

		var mainmenu = new ImagingProgress.MenuList();
		
		var about = new ImagingProgress.About();
        var imageviewer = new ImagingProgress.ImageViewer();
		var imageShow = new ImagingProgress.ImageShow();
		var zoomPanel = new ImaginProgress.ZoomImage();
		
		CFLABUS.on("ChangeMainMenu",function(index,adjust){
				if(index == 2)
					imageviewer.storeUrl = adjust;
				if(index == 3)
					imageShow.storeUrl = adjust;
				if(index == 4)
					zoomPanel.storeUrl = adjust;		
				cardPanel.setCard(index);
				
				if(adjust == true){
					mainmenu.adjustBackCard();
				}
				cardPanel.doLayout();
		},this);	
		
		var cardPanel = new Ext.Panel({
				fullscreen: true
			,	layout:'card'	
			,	activeItem:0
			,	tabBar: {
						dock: 'bottom'
					,	scroll: 'horizontal'
					,	sortable: true
					,	layout: {
								pack: 'center'
						}
					}
			,	items: [
						mainmenu
					,	about	
					,	imageviewer	
					,	imageShow
					,	zoomPanel
					]
   			});
			
	}
});