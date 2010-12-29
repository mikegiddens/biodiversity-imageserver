/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/
Ext.namespace('ImagePortal');

	ImagePortal.ImageInfoPanel = function(config){

	this.filedTpl = new Ext.XTemplate(
								'<div style="visibility: visible; height: 100%; position: relative; width: 100%;" class="dataview" id="dataview3">' +
								'<div style="padding: 1px; height: 22px;border-top:1px solid #AAAAAA;" class="detailrow1">' +
								'<div style="margin-left: 24px; margin-top: 5px;"></div>' +
								
								'<tpl if="image_id != 0">' +
								'<div class="detailrow1"><div class="detaillabel">Image Id at</div><div class="detailtext">{image_id}</div></div>' +
								'</tpl>'+
								
								'<tpl if="filename!= 0">' +
								'<div class="detailrow1"><div class="detaillabel">Filename</div><div class="detailtext">{filename}</div></div>' +
								'</tpl>' +
								
								'<tpl if="barcode != 0">' +
								'<div class="detailrow1"><div class="detaillabel">Barcode</div><div class="detailtext">{barcode}</div></div>' +
								'</tpl>' +
								
								
								'<tpl if="timestamp_modified!= 0">' +
								'<div class="detailrow1"><div class="detaillabel">Last Modified</div><div class="detailtext">{timestamp_modified}</div></div>' +
								'</tpl>' +
								
								
								'<tpl if="Family != 0">' +
								'<div class="detailrow1"><div class="detaillabel">Family</div><div class="detailtext">{Family}</div></div>' +
								'</tpl>' +
								
								
								'<tpl if="Genus != 0">' +
								'<div class="detailrow1"><div class="detaillabel">Genus</div><div class="detailtext">{Genus}</div></div>' +
								'</tpl>' +
								
								'<tpl if="SpecificEpithet!= 0">' +
								'<div class="detailrow1"><div class="detaillabel">Specific Epithet</div><div class="detailtext">{SpecificEpithet}</div></div>' +
								'</tpl>' +
								
								//'<tpl if="flickr_PlantID!= 0">' +
								'<div class="detailrow1"><div class="detaillabel">Flickr PlantID</div><div class="detailtext">{flickr_PlantID}</div></div>' +
								//'</tpl>' +
								//'<tpl if="flickr_PlantID== 0">'+
								//'<div class="detailrow1"><div class="detaillabel">Flickr PlantID</div><div class="detailtext">&nbsp;</div></div>' +
								//'</tpl>' +
								
								//'<tpl if="picassa_PlantID!= 0">' +
								'<div class="detailrow1"><div class="detaillabel">Picassa PlantID</div><div class="detailtext">{picassa_PlantID}</div></div>' +
								//'</tpl>' +
								//'<tpl if="picassa_PlantID== 0">'+
								//'<div class="detailrow1"><div class="detaillabel">Picassa PlantID</div><div class="detailtext">&nbsp;</div></div>' +
								//'</tpl>' +
								
								'<tpl if="picassa_modified!= 0">' +
								'<div class="detailrow1"><div class="detaillabel">Picassa Modified</div><div class="detailtext">{picassa_modified}</div></div>' +
								'</tpl>' +
								'<tpl if="picassa_modified== 0">'+
								'<div class="detailrow1"><div class="detaillabel">Picassa Modified</div><div class="detailtext">&nbsp;</div></div>' +
								'</tpl>' +
								
								'<tpl if="gTileProcessed!= 0">' +
								'<div class="detailrow1"><div class="detaillabel">Tiled Processed</div><div class="detailtext">{gTileProcessed}</div></div>' +
								'</tpl>' +
								'<tpl if="gTileProcessed== 0">'+
								'<div class="detailrow1"><div class="detaillabel">Tiled Processed</div><div class="detailtext">&nbsp;</div></div>' +
								'</tpl>' +
								
								//'<tpl if="zoomEnabled!= 0">' +
								'<div class="detailrow1"><div class="detaillabel">Zoom Enabled</div><div class="detailtext">{zoomEnabled}</div></div>' +
								//'</tpl>' +
								//'<tpl if="zoomEnabled== 0">'+
								//'<div class="detailrow1"><div class="detaillabel">Zoom Enabled</div><div class="detailtext">&nbsp;</div></div>' +
								//'</tpl>' +
							
								'<tpl if="processed!= 0">' +
								'<div class="detailrow1"><div class="detaillabel">Processed</div><div class="detailtext">{processed}</div></div>' +
								'</tpl>' +
								'<tpl if="processed == 0">'+
								'<div class="detailrow1"><div class="detaillabel">Processed</div><div class="detailtext">&nbsp;</div></div>' +
								'</tpl>' +
								'</div>'
				)


		Ext.apply(this,config,{
					title: 'Image Info'
				,	scope:this					
				,	ref:'../infopanel'
	 			,	border: false
				,	autoScroll:true		
				
		})
		
	ImagePortal.ImageInfoPanel.superclass.constructor.call(this, config);

};

	Ext.extend(ImagePortal.ImageInfoPanel, Ext.Panel, {
		showInfoData:function(data){
			this.filedTpl.overwrite( this.body, data.data );
		}
	});
	
	
