/**
 * @copyright SilverBiology, LLC
 * @author SilverBiology, LLC
 * @website http://www.silverbiology.com
*/
Ext.ns('ImagingProgress');

ImagingProgress.ImageShow = function(config){
	
	Ext.apply(this, config, { 
				iconCls: 'info'
			,	draggable:false
			,	html:''
			,	tpl:new Ext.XTemplate(
				                '<div ><img onerror="this.src=\'resources/images/no-image.gif\'" src="{path}{barcode}_l.jpg" ></div>'
					    )
			,	scroll:'vertical'
			,	dockedItems: [{
						xtype: 'toolbar'
					,	dock: 'top'
					,	title: 'Image Viewer'
					,	ui:'light'
					,	items: [{
								text: 'Back'
							,	ui: 'back'
							,	handler: function(){
										CFLABUS.fireEvent('ChangeMainMenu',2,false,this);
									}
					}]
				}]
			,	listeners:{
							activate:this.updateImage
					}	
		});
		
		ImagingProgress.ImageShow.superclass.constructor.call(this,config);
};
Ext.extend(ImagingProgress.ImageShow , Ext.Panel, {
		updateImage:function(){
					this.update(this.tpl.applyTemplate(this.storeUrl));
			}
		
});	
