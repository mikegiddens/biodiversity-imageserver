/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/

	Ext.namespace('ImagePortal');

	ImagePortal.downloadPanel = function(config){
	
		this.tpl = new Ext.Template('<div id="image_wrapper" style="width: {width}px; height: {height}px; background-image:url("http://images.cyberfloralouisiana.com/images/specimensheets/no/0/0/10/34/");"></div>');

		Ext.apply(this, config, {
			title: 'Download Image',
			id: 'dimg',
			border: false,
			autoScroll:true,
			listeners:{
				activate:function(){
				window.open('http://images.cyberfloralouisiana.com/images/specimensheets/no/0/0/10/34/NO0001034_s.jpg','_blank');
				//this.getDwnlImage();
				}
			}
				
		})
		
		ImagePortal.downloadPanel.superclass.constructor.call(this, config);
	}
	
	Ext.extend(ImagePortal.downloadPanel,Ext.Panel,{
		getDwnlImage: function() {
			
				Ext.Ajax.request({
					url: '../silverarchive_remote/api/silverarchive.php'
				,	params: { 
					cmd:'get_image',
					image_id:'NLU0001804',
					width:900,
					height:900
					}
				,	scope: this
				,	success: function(r) {
				
						var data = Ext.decode(r.responseText);
					
						if ( data.success ) {
                          
							var panel = Ext.getCmp('dimg');							
							this.tpl.overwrite( panel.body, data.data );

						} 
					}
				,	failure:function() {
										
					}
			});
		}
	})
