/**
 * @copyright SilverBiology, LLC
 * @website http://www.silverbiology.com
*/
Ext.namespace('ImagePortal');

ImagePortal.ImageViewer = function(config){
	
	this.infoTabPanel =	new ImagePortal.ImageInfoPanel();
	this.largeImagePanel = new Ext.Panel({
		title: 'Large Image'
	,	tpl: new Ext.XTemplate(
			'<div><img src={path}></div>'
		)
	});
	this.intimage = new ImagePortal.IVIntractive();
	this.flicker = new ImagePortal.IVFlickr();
	Ext.apply(this,config,{
			title: 'Image Viewer'
		,	width: 800
		,	height: 500
		,	modal: true
		,	autoScroll: true
		,	layout: 'fit'	
		,	ref: '../imagevp'
		,	maximizable: true	
		,	tbar: [{
					text: 'Download'
				,	menu: [{
						text: 'Small'
					,	scope:this
					,	handler: this.dnldSmallImg
				//	,	handler: this.addDwnImgTab
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
		,	buttons: [{
					text: 'Close'
				,	scope: this
				,	handler: function() {
						this.close();
					}
			}]
		,	items:[{
				xtype: 'tabpanel'
			,	id: 'tabId'
			,	activeTab: 0
			,	deferredRender:false
			//,	defaults:{autoHeight: true}
			,	border: false
			,	items:[
						this.intimage
					,	this.largeImagePanel
					,	this.infoTabPanel
					,	this.flicker
				]
			}]		
	});
			
	ImagePortal.ImageViewer.superclass.constructor.call(this, config);

};

Ext.extend(ImagePortal.ImageViewer, Ext.Window, {
	
		showLargeImage:function(path){
			this.largeImagePanel.update({
				path : path
			});
		}
	
	,	hideInteractiveTab:function(hideunhide,path){
					if(hideunhide == 0 ){
						Ext.getCmp('tabId').hideTabStripItem(this.intimage);
						Ext.getCmp('tabId').unhideTabStripItem(this.largeImagePanel);
						Ext.getCmp('tabId').setActiveTab(1);
						this.showLargeImage(path);	
					}else{
						Ext.getCmp('tabId').hideTabStripItem(this.largeImagePanel);
						Ext.getCmp('tabId').unhideTabStripItem(this.intimage);
						this.showImage(path);
						//Ext.getCmp('tabId').setActiveTab(0);
					}	
			}
			
			
	,	hideFlickerTab:function(fId,data){
					if(fId != 0 && fId > 0 ){
						Ext.getCmp('tabId').unhideTabStripItem(this.flicker);
						this.flicker.loadFilckerImage(data);
					}else{
						Ext.getCmp('tabId').hideTabStripItem(this.flicker);
						if(Ext.getCmp('tabId').getActiveTab().title == 'Flickr');{
								this.hideInteractiveTab(data.data.gTileProcessed,data.data.path);
							}
					}	
			}			
	
	,	dnldSmallImg:function(){
			window.open(this.dwnpath+this.imgbarcode+'_s.jpg','_blank');
		}
		
	,	dnldMediumImg:function(){
			window.open(this.dwnpath+this.imgbarcode+'_m.jpg','_blank');
		}

	,	dnldLargeImg:function(){
			window.open(this.dwnpath+this.imgbarcode+'_l.jpg','_blank');
		}
		
	,	dnldOriginalImg:function(){
			window.open(this.dwnpath+this.imgbarcode+'.jpg','_blank');
		}

	,	setBarcode:function(barcode,image_id){
			this.imgbarcode = barcode;
			this.image_id = image_id;	
		}
	
	,	showImage: function(path){
				this.intimage.drawImage(path);
		}

	,	showInfoData: function(data){
			this.infoTabPanel.showInfoData(data);
		}
	
	,	dnldCustomImg:function(){
			var imageId = this.image_id;
			var popup = new ImagePortal.CustomeInputPopup({
						image_id : imageId
			});
			popup.show();
		}		

/*
,	addDwnImgTab:function(){
		var newtab = null;
		var tab; 
		 if (newtab == null) {
			newtab = new ImagePortal.downloadPanel();
			tab = Ext.getCmp('tabId');
			tab.add(newtab);
			this.doLayout();
		} 

}
*/
	,	disableFlickerTab:function(){
			var filc = this.findTab('Flickr');
			if(filc){
				this.deactiveFlickerTab();
				filc.disable();
			}
		}

	,	enableFlickerTab:function(){
			var filc = this.findTab('Flickr');
			if (filc) {
				filc.enable();
			}
		}

	,	deactiveFlickerTab:function(){
			var tab = Ext.getCmp('tabId');
			var active = tab.getActiveTab();	
			var filc = this.findTab('Flickr');
			if(active == filc) {
				tab.setActiveTab(0);
			}
		}

	,	findTab: function(vartitle){
			var findtab = null;
			Ext.getCmp('tabId').items.each( function(rec){
				if (rec.title == vartitle) {
					findtab = rec;
				}
			});
			return findtab;
		}

});