/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/

	Ext.namespace('ImagePortal');

	ImagePortal.IVFlickr = function(config){

        this.flicTpl = new Ext.XTemplate(
					'<tpl>'+
					'<a href="http://www.flickr.com/photos/cyberfloralouisiana/{server}/" title="{barcode} by cyberfloralouisiana, on Flickr"><img src="http://farm4.static.flickr.com/{server}/{flickr_PlantID}_{secret}.jpg" width="859" height="1024" alt={barcode} /></a>'+
					'</tpl>'
					)

		Ext.apply(this,config,{
		 	title: 'Flickr'
		,	id: 'fid'
		,	border: false
		,	autoScroll: true
	//	,	tpl: this.flicTpl
		,	listeners:{
				//activate:this.loadFilckerImage
		}
		})
		
		ImagePortal.IVFlickr.superclass.constructor.apply(this, arguments);

	};

	Ext.extend(ImagePortal.IVFlickr, Ext.Panel, {
			loadFilckerImage:function(data){
						this.flicTpl.overwrite( this.body, data.data );
				}
	});
	
	Ext.reg('ivflickr', ImagePortal.IVFlickr );
