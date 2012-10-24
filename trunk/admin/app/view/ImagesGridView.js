Ext.define('BIS.view.ImagesGridView', {
	extend: 'Ext.view.View',
	alias: ['widget.imagesgridview'],
	requires: [
        'BIS.view.ImageTabPanel',
		'BIS.view.CtxMnuImage',
		'BIS.view.CtxMnuAttribute',
        'Ext.ux.DataView.Draggable'
	],
    mixins: {
        draggable: 'Ext.ux.DataView.Draggable'
    },
	initialTpl: '<div>Loading...</div>',
	itemSelector: 'div.imageSelector',
	selectedItemCls: 'imageRowSelected',
    overItemCls: 'highlight',
    trackOver: true,
    // selectionModel config
    mode: 'MULTI',
    //allowDeselect: true, // this is true by default
    //
    scope: this,
	listeners: {
		afterrender: function( gridview, e ) {
			gridview.setTpl('both');
		},
        /*
        beforeselect: function( a, b, c, d, e ) {
            console.log( 'before',a,b,c,d,e);
        },
        selectionchange: function( a, b, c, d, e ) {
            console.log( 'select',a,b,c,d,e);
        },
        */
		itemclick: function( gridview, record, el, ind, e, opts ) {
            this.getSelectionModel().deselectAll();
            this.getSelectionModel().select( ind );
			var data = record.data;
			Ext.getCmp('imageDetailsPanel').loadImage( data );
			Ext.getCmp('propertySeachCombo').enable();
		},
		itemdblclick: function( gridview, record, el, ind, e, opts ) {
			Ext.create('Ext.window.Window', {
				title: 'View Image ' + record.data.filename,
				iconCls: 'icon_image',
                bodyCls: 'x-docked-noborder-top x-docked-noborder-bottom x-docked-noborder-right x-docked-noborder-left',
				modal: true,
				height: 500,
				width: 800,
				layout: 'fit',
                maximizable: true,
				items: [{
                    xtype: 'imagetabpanel',
                    record: record
				}]
			}).show();
		},
		itemcontextmenu: function(view, record, item, index, e) {
			e.stopEvent();
			var ctx = Ext.create('BIS.view.CtxMnuImage', {record: record});
			ctx.showAt(e.getXY());
		}
	},
    initComponent: function() {
        this.mixins.draggable.init( this, {
            ddConfig: {
                ddGroup: 'imageDD'
            },
            ghostTpl: [ new Ext.XTemplate(
                    '<tpl for=".">',
                        '<img src="{[this.renderThumbnail(values.path,values.filename,values.ext)]}">',
                    '</tpl>',
                    '<div class="count">',
                        '{[values.length]} images selected.',
                    '<div>', {
                        renderThumbnail: function( path, filename, ext ) {
                            return path + filename.substr( 0, filename.lastIndexOf('.') ) + '_s.' + ext;
                        }
                    }
                )
            ]
        });

        this.callParent( arguments );
    },
	constructor: function( config ) {
        this.table = this;
		this.tplBoth = new Ext.XTemplate(
			'<tpl for=".">'+
			'<div class="imageSelector" style="width: 100%; position: relative;">' +
				'<div style="width: 100px; margin: 5px 10px 5px 5px; display: inline-block;">'+
                    '<img src="{[this.renderThumbnail(values.path,values.filename,values.ext)]}">'+
                '</div>'+
				'<div style="display: inline-block;">'+
                    '<div>'+
                        '<span style="font-weight:bold">{filename}</span><br/>{family}<br/>{genus} {specificEpithet}<br/>'+
                        '<span>Barcode: {barcode}</span><br>'+
                        '<span>Date Added: {timestampAdded:this.renderDate}</span><br>'+
                        '<span>Date Modified: {timestampModified:this.renderDate}</span>'+
                    '</div>'+
				'</div>'+
                '<div style="bottom: 5px; right: 25px; position: absolute;">Image identifier: <span style="font-weight:bold">{imageId}</span></div>'+
			'</div><br/>'+
			'</tpl>', {
            renderDate: function( date ) {
                try {
                    return Ext.Date.format( new Date(date), 'j M Y' );
                } catch( err ) {
                    return date;
                }
            },
			renderThumbnail: function( path, filename, ext ) {
                return path + filename.substr( 0, filename.lastIndexOf('.') ) + '_s.' + ext;
			}
		});
		this.tplSmallIcons = new Ext.XTemplate(
			'<tpl for=".">'+
			'<div class="imageSelector" style="width: 100px; height: 100px">' +
                '<div>'+
                    '<img style="display: block; margin: auto;" src="{[this.renderThumbnail(values.path,values.filename,values.ext)]}" />'+
                '</div>'+
			'</div>'+
			'</tpl>', {
			renderThumbnail: function( path, filename, ext ) {
                return path + filename.substr( 0, filename.lastIndexOf('.') ) + '_s.' + ext;
			}
		});
		this.tplTileIcons = new Ext.XTemplate(
			'<tpl for=".">'+
			'<div class="imageSelector">' +
                '<div style="padding: 5px;">'+
                    '<div>'+
                        '<span style="font-weight:bold">{filename}</span><br/>{barcode} {family}<br/>{genus} {specificEpithet}'+
                    '</div>'+
                    '<div style="border-bottom: solid thin #9F9F9F; width: 275px; height: 276px;">'+
                        '<img style="display: block; margin: auto;" src="{[this.renderThumbnail(values.path,values.filename,values.ext)]}">'+
                    '</div>'+
                '</div>'+
			'</div>'+
			'</tpl>', {
			renderThumbnail: function( path, filename, ext ) {
				return path + filename.substr( 0, filename.lastIndexOf('.') ) + '_m.' + ext;
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
	},
    onItemSelect: function(record) {
        console.log( 'calling', record );
        var node = this.getNode(record);
        
        if (node) {
            Ext.fly(node).addCls(this.selectedItemCls);
            console.log( Ext.fly(node).addCls(this.selectedItemCls) );
        }
    }

});
