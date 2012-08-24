Ext.define('BIS.view.ImagesGridView', {
	extend: 'Ext.view.View',
	alias: ['widget.imagesgridview'],
	requires: [
		'BIS.view.ImageZoomViewer',
		'BIS.view.CtxMnuImage',
		'BIS.view.CtxMnuAttribute'
	],
	initialTpl: '<div>Loading...</div>',
	itemSelector: '.imageSelector',
	selectedItemCls: 'imageRowSelected',
	stripeRows: true,
	autoScroll: true,
	multiSelect: true,
	listeners: {
		afterrender: function( gridview, e ) {
			gridview.setTpl('both');
		},
		// dd events
		itemclick: function( gridview, record, el, ind, e, opts ) {
			var data = record.data;
			Ext.getCmp('imageDetailsPanel').loadImage( data );
			Ext.getCmp('propertySeachCombo').enable();
		},
		itemdblclick: function( gridview, record, el, ind, e, opts ) {
			Ext.create('Ext.window.Window', {
				title: 'View Image ' + record.data.filename,
				iconCls: 'icon_image',
				modal: true,
				height: 500,
				width: 800,
				layout: 'fit',
				items: [{
					xtype: 'tabpanel',
					border: false,
					activeItem: 0,
					items: [{
						xtype: 'panel',
						title: 'Static Image',
						iconCls: 'icon_image',
                        autoScroll: true,
						html: '<img src="'+record.data.path + record.data.filename.substr( 0, record.data.filename.indexOf('.') ) + '_l.' + record.data.ext+'">'
					},{
						xtype: 'imagezoomviewer',
						title: 'Zooming Image',
						iconCls: 'icon_magnifier',
                        imageId: record.data.imageId
					}]
				}]
			}).show();
		},
		itemcontextmenu: function(view, record, item, index, e) {
			e.stopEvent();
			var ctx = Ext.create('BIS.view.CtxMnuImage', {record: record});
			ctx.showAt(e.getXY());
		}
	},
	constructor: function( config ) {
        this.table = this;
		this.tplBoth = new Ext.XTemplate(
			'<tpl for=".">'+
			'<div class="imageSelector">' +
				'<div style="display:inline-block;width:100px;margin:5px 10px 5px 5px;">'+
                    '<img src="{[this.renderThumbnail(values.path,values.filename,values.ext)]}">'+
                '</div>'+
				'<div style="display:inline-block">'+
                    '<div unselectable="on">{barcode} {family}<br/>{genus} {specificEpithet}<br/>'+
                        '<tpl if="barcode != 0">'+
                            '<span>Barcode: {barcode}</span><br>'+
                        '</tpl>'+
                        '<span>Date Added: {timestamp_modified:this.convDate}</span>'+
                    '</div>'+
				'</div>'+
			'</div>'+
			'<div style="clear:both"></div>'+
			'</tpl>', {
			convDate: function( date ) {
					//console.log( date );
					return date;
			},
			renderThumbnail: function( path, filename, ext ) {
					return path + filename.substr( 0, filename.indexOf('.') ) + '_s.' + ext;
			}
		});
		this.tplSmallIcons = new Ext.XTemplate(
			'<tpl for=".">'+
			'<div class="imageSelector">' +
			'<div style="display:inline-block; width:100px;height:100px"><img ' +
				'<tpl if="family != \'\' || genus != \'\' || specificEpithet != \'\' ">'+
					' ext:qtip="' +
					'<tpl if="Family != \'\' " >{family}<br></tpl>'+
					'<tpl if="Genus != \'\' " >{genus} {specificEpithet}"</tpl>'+
				'</tpl>' +
				'src="{[this.renderThumbnail(values.path,values.filename,values.ext)]}" /></div>'+
			'</div>'+
			'</tpl>', {
			renderThumbnail: function( path, filename, ext ) {
					return path + filename.substr( 0, filename.indexOf('.') ) + '_s.' + ext;
			}
		});
		this.tplTileIcons = new Ext.XTemplate(
			'<tpl for=".">'+
			'<div class="imageSelector">' +
			'<div style="display:inline-block; padding: 5px;">'+
				'<div unselectable="on">{barcode}<br/> {family}<span>{genus} {specificEpithet}</span></div>'+
				'<div style="border-bottom: solid thin #9F9F9F; width: 275px; height: 276px;"><img src="{[this.renderThumbnail(values.path,values.filename,values.ext)]}"></div>'+
			'</div>'+
			'</div>'+
			'</tpl>', {
			renderThumbnail: function( path, filename, ext ) {
				return path + filename.substr( 0, filename.indexOf('.') ) + '_m.' + ext;
			}
        });
		this.callParent( arguments );
	},
	onRowSelect: function( ind ) {
	},
	onRowDeselect: function( ind ) {
	},
	onRowFocus: function( ind ) {
	},
	setTpl: function( mode ) {
		switch( mode ) {
			case 'small':
				this.tpl = this.tplSmallIcons;
				this.refresh();
				break;
			case 'tile':
				this.tpl = this.tplTileIcons;
				this.refresh();
				break;
			case 'both':
				this.tpl = this.tplBoth;
				this.refresh();
				break;
		}
	}
});
