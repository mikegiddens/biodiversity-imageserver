/**
 * @copyright SilverBiology, LLC
 * @author SilverBiology, LLC
 * @website http://www.silverbiology.com
*/
Ext.ns('ImagingProgress');

ImagingProgress.About = function(config){
		
	Ext.apply(this, config, { 
				iconCls: 'info'
			,	draggable:false
			,	html:	'<div style="padding:10px;">'+
						'<h1>Cyberflora Louisiana Image Server</h1>'+
						'<p>About text here.</p>'+
						'</div>'
			,	dockedItems: [{
						xtype: 'toolbar'
					,	dock: 'top'
					,	title: 'About'
					,	ui:'light'
					,	items: [{
								text: 'Back'
							,	ui: 'back'
							,	handler: function(){
										CFLABUS.fireEvent('ChangeMainMenu',0,true,this);
									}
					}]
				}]			
		});
		
		ImagingProgress.About.superclass.constructor.call(this,config);
};
Ext.extend(ImagingProgress.About , Ext.Panel, {
		
});	
