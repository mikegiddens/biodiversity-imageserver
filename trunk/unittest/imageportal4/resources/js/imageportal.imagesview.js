Ext.define('ImagePortal.ImagesView', {
		extend: 'Ext.view.View'
	,	alias: 'widget.imagesview'
	,	id: 'imagesview'
	,	layout: 'fit'
	,	border: false
	,	autoScroll: true
	,	itemSelector: 'div.thumb-wrap'
    ,	multiSelect:false
	,	overItemCls: 'x-item-over'
    ,	singleSelect: true
    ,	cls: 'x-image-view'
	,	currentView: 'small'
	,	deferEmptyText: false
	,	initComponent: function(){
				this.emptyText =  '<div style="padding:10px;">No images available.</div>';
				this.imageTpl = new ImagePortal.XTemplate(
						 '<tpl for=".">'
					,	  '<tpl if="this.isViewChange(this)==\'small\'">'
					,			'<div class="thumb-wrap ux-explorerview-item ux-explorerview-small-item">'
					,				'<tpl if="gTileProcessed == 1">'
					,					'<div class="divZoom smallIconZoomIn"  title="Double click to view large image.">&nbsp;</div>'
					,				'</tpl>'
					,				'<div class="ux-explorerview-icon"><img  ' 
					,				'<tpl if="Family != \'\' || Genus != \'\' || SpecificEpithet != \'\' ">'
					,					' qtip="' 
					,					'<tpl if="Family != \'\' " >{Family}<br></tpl>'
					,					'<tpl if="Genus != \'\' " >{Genus} {SpecificEpithet}"</tpl>'
					,				'</tpl>' 
					,			'src="{path:this.testMirror}{barcode}_s.jpg" onerror="this.src=\'http://images.cyberfloralouisiana.com/portal/resources/images/no-image.gif\'" /></div>'
					,		'</div>'
					,	  '</tpl>'
					,	  '<tpl if="this.isViewChange(this)==\'large\'">'
					,			'<div class="thumb-wrap ux-explorerview-item ux-explorerview-tiles-item">'
					,				'<tpl if="gTileProcessed == 1">'
					,					'<div class="divZoom largeIconZoomIn" title="Double click to view large image.">&nbsp;</div>'
					,				'</tpl>'
					,				'<div class="ux-explorerview-icon"><img onerror="this.src=\'http://images.cyberfloralouisiana.com/portal/resources/images/no-image.gif\'" src="{path:this.testMirror}{barcode}_m.jpg"></div>'
					,			'<div class="ux-explorerview-text"><div class="x-grid3-cell x-grid3-td-name" unselectable="on">{barcode}<br/> {Family}<span>{Genus} {SpecificEpithet}</span></div></div></div>'
					,	  '</tpl>'
					,	  '<tpl if="this.isViewChange(this)==\'both\'">'
					,			'<div class="thumb-wrap x-row ux-explorerview-item ux-explorerview-mixed-item">'
					,				'<tpl if="gTileProcessed == 1">'
					,					'<div class="divZoom bothIconZoomIn"  title="Double click to view large image.">&nbsp;</div>'
					,				'</tpl>'
					,				'<div class="ux-explorerview-icon"><img onerror="this.src=\'http://images.cyberfloralouisiana.com/portal/resources/images/no-image.gif\'" src="{path:this.testMirror}{barcode}_s.jpg"></div>'
					,					'<div class="ux-explorerview-text"><div class="x-grid3-cell x-grid3-td-name" unselectable="on">{barcode} {Family}<br/>{Genus} {SpecificEpithet}<br/>'
					,						'<tpl if="barcode != 0">'
					,							'<span>Barcode: {barcode}</span><br>'
					,						'</tpl>'
					,					'<span>Date Added: {timestamp_modified:this.convDate}</span></div>'
					,				'</div>'
					,			'</div>'
					,	  '</tpl>'
					,	'</tpl>'
				);
				this.imageTpl.setDefaultView(this.currentView);
				this.imageTpl.setMirror(Config.mirrors || [] );
				this.tpl = this.imageTpl;
				this.callParent();	
		}
	
});