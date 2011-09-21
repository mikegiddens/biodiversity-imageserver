Ext.define('ImagePortal.ImageViewer', {
		extend: 'Ext.window.Window'	
	,	alias: 'widget.imageviewer'
	,	border: false
	,	title: 'Image Viewer'
	,	width: 800
	,	height: 500
	,	modal: true
	,	layout: 'fit'
	,	maximizable: true
	,	imagePath: ''
	,	imageId: ''
	,	imageBarcode: ''
	,	currentData: new Object()
	,	initComponent: function() {
			this.imageinfopanel = Ext.create('ImagePortal.ImageDetailsPanel', {});
			this.imagezoompanel = Ext.create('SilverMeasure.ImageZoomPanel', {
					border: false
				,	rototeControl: false
				,	scaleControl: false
				,	title: 'Specimen Image'
				,	listeners: {
							scope: this
						,	beforeImageLoad: function(zoompanel){
								this.imageinfopanel.resetInfo();
							}
						,	afterImageLoad: function(zoompanel){
								this.imageinfopanel.loadInfo(this.currentData);
							}	
					}
			});
		
		this.imageTabs = Ext.create('Ext.tab.Panel', {
					defaults: {
						border: false
					}
				,	activeTab: 0
				,	items: [this.imagezoompanel, this.imageinfopanel]	
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
			this.currentData = data;
			this.imagezoompanel.loadImage(data.filename);
			this.imagePath = data.path;
			this.imageBarcode = data.barcode;
			this.imageId = data.image_id;
			this.doComponentLayout();
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