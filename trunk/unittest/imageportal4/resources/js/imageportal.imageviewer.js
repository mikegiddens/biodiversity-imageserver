Ext.define('ImagePortal.ImageViewer', {
		extend: 'Ext.window.Window'	
	,	alias: 'widget.imageviewer'
	,	border: false
	,	title: 'Image Viewer'
	,	width: 800
	,	height: 500
	,	modal: true
	,	autoScroll: true
	,	layout: 'fit'
	,	maximizable: true
	,	imagePath: ''
	,	imageId: ''
	,	imageBarcode: ''
	,	initComponent: function() {
			this.imageinfopanel = Ext.create('ImagePortal.ImageDetailsPanel', {});
			this.imagezoompanel = Ext.create('SilverMeasure.ImageZoomPanel', {
					border: false
				,	rototeControl: false
				,	scaleControl: false
				,	title: 'Specimen Image'
			});
			this.largeimagepanel = Ext.create('ImagePortal.ImageDetailsPanel', {
					title: 'Specimen Image'
			});
			this.imageTabs = Ext.create('Ext.tab.Panel', {
					border: false
				,	activeTab: 0	
				,	items: [this.imagezoompanel, this.largeimagepanel, this.imageinfopanel]	
			});
			this.tools = [{
					type: 'left'
				,	scope: this
				,	handler: function(){
						this.fireEvent('onPrevious', this);
					}	
			}, {
					type: 'right'
				,	scope: this
				,	handler: function(){
						this.fireEvent('onNext', this);
					}
			}]
			this.tbar = [{
					text: 'Download'
				,	menu: [{
						text: 'Small'
					,	scope:this
					,	handler: this.dnldSmallImg
				 },{		
						text: 'Medium'
					,	scope:this
					,	handler: this.dnldMediumImg
				 },{	
						text: 'Large'
					,	scope:this							
					,	handler: this.dnldLargeImg
				 },{
						text: 'Original'
					,	scope:this							
					,	handler: this.dnldOriginalImg	
				},{
						text: 'Custom'
					,	scope:this							
					,	handler: this.dnldCustomImg
				}]
			}]
			this.buttons = [{
					text: 'Close'
				,	scope: this
				,	handler: function() {
						this.close();
					}
			}]
			this.items = [this.imageTabs]
			this.callParent();
		}
		
	,	loadTabs: function(data){
			if(data.gTileProcessed == 1){
				this.hideTab(1);
				this.showTab(0);
				this.imageTabs.setActiveTab(0);
				var myMask = new Ext.LoadMask(this.imagezoompanel.body, {msg:"Please wait..."});
				myMask.show();
				this.imagezoompanel.resetImage();
				Ext.Ajax.request({
						url : 'http://images.cyberfloralouisiana.com/viewer/resources/api/api.php'
					,	params: {
								cmd: 'loadImage'
							,	filename: data.filename
						}
					,	method: 'GET'
					,	scope: this
					,	success: function (response) {
							var o = Ext.decode(response.responseText);
							if(o.success){
								var imageName = "{2}/tile_{3}.jpg"
								this.imagezoompanel.loadImage(o.url + imageName);
								myMask.hide();
							} else {
								Ext.Msg.alert('Error', 'Error in loading image.');
								myMask.hide();
							}
						}
					,	failure: function () {
							Ext.Msg.alert('Error', 'Error in connection.');
							myMask.hide();
						}
				});
			}else {
				this.hideTab(0);
				this.showTab(1);
				this.imageTabs.setActiveTab(1);
			}
			this.imageinfopanel.loadInfo(data);
			this.imagePath = data.path;
			this.imageBarcode = data.barcode;
			this.imageId = data.image_id;
		}
	
	,	hideTab: function(tabId){
			var tab = this.imageTabs.tabBar.items.items[tabId];
			if(Ext.isDefined(tab)){
				if(!tab.hidden){
					tab.hide();
				}
			}
		}
		
	,	showTab: function(tabId){
			var tab = this.imageTabs.tabBar.items.items[tabId];
			if(Ext.isDefined(tab)){
				if(!tab.hidden){
					tab.show();
				}
			}
		}	
	
	,	dnldSmallImg:function(){
			window.open(this.imagePath + this.imageBarcode +'_s.jpg','_blank');
		}
	,	dnldMediumImg:function(){
			window.open(this.imagePath + this.imageBarcode +'_m.jpg','_blank');
		}
	,	dnldLargeImg:function(){
			window.open(this.imagePath + this.imageBarcode +'_l.jpg','_blank');
		}
	,	dnldOriginalImg:function(){
			window.open(this.imagePath + this.imageBarcode +'.jpg','_blank');
		}
	,	dnldCustomImg:function(){
			var customdownload = Ext.create('ImagePortal.CustomDownload', {
					imageId : this.imageId
			}).show();
		}	
});