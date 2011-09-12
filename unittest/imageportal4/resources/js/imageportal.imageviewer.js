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
			this.imagezoompanel = Ext.create('SilverMeasure.ImageZoomPanel', {
					border: false
				,	rototeControl: false
				,	scaleControl: false
			});
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
			this.items = [this.imagezoompanel]
			this.callParent();
		}
	,	loadImage: function(url){
			this.imagezoompanel.loadImage(url);
		}
	,	resetImage: function(){
			this.imagezoompanel.resetImage();
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