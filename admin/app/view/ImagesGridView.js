Ext.define('BIS.view.ImagesGridView', {
	extend: 'Ext.view.View',
	alias: ['widget.imagesgridview'],
	requires: [
        'BIS.view.ImageTabPanel',
		'BIS.view.CtxMnuImage',
		'BIS.view.CtxMnuAttribute'
	],
	initialTpl: '<div>Loading...</div>',
	itemSelector: 'div.imageSelector',
	selectedItemCls: 'imageRowSelected',
    overItemCls: 'highlight',
    trackOver: true,
    style: '-moz-user-select: none; -webkit-user-select: none; -ms-user-select: none; -o-user-select: none; user-select: none;',
    scope: this,
	listeners: {
		afterrender: function( gridview, e ) {
			gridview.setTpl('both');
		},
		itemclick: function( gridview, record, el, ind, e, opts ) {
			Ext.getCmp('imageDetailsPanel').fireEvent( 'selectionchange', this.getSelectionModel().getSelection() );
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

			Ext.getCmp('imageDetailsPanel').fireEvent( 'selectionchange', this.getSelectionModel().getSelection() );
			Ext.getCmp('propertySeachCombo').enable();

			var ctx = Ext.create('BIS.view.CtxMnuImage', {record: record});
			ctx.showAt(e.getXY());
		}
	},
    initComponent: function() {
        var me = this;

        this.getSelectionModel().setSelectionMode( 'MULTI' );
        this.getSelectionModel().mode = 'MULTI';

        this.callParent( arguments );
    },
	constructor: function( config ) {
        this.table = this;
		this.tplBoth = new Ext.XTemplate(
			'<tpl for=".">'+
			'<div class="imageSelector" style="width: 100%; position: relative;">' +
				'<div style="width: 100px; margin: 5px 10px 5px 5px; display: inline-block;">'+
                    '<img src="{[this.renderThumbnail(values.path,values.filename,values.ext)]}" onerror="this.onerror=\'\'; this.src=\'./resources/img/noimg_100.png\'; return true;">'+
                '</div>'+
				'<div style="display: inline-block;">'+
                    '<div>'+
                        '<span style="font-weight:bold">{filename}</span><br/>'+
                        '{family}<br/>{genus} {specificEpithet}<br/>'+
                        '<tpl if="barcode != \'\'"><span>Barcode: {barcode}</span><br></tpl>'+
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
			'<div class="imageSelector" data-qtip="{filename}" style="width: 114px; height: 114px; padding: 2px;">' +
                '<div>'+
                    '<img style="display: block; margin: auto;" src="{[this.renderThumbnail(values.path,values.filename,values.ext)]}" onerror="this.onerror=\'\'; this.src=\'./resources/img/noimg_100.png\'; return true;"/>'+
                '</div>'+
			'</div>'+
			'</tpl>', {
			renderThumbnail: function( path, filename, ext ) {
                return path + filename.substr( 0, filename.lastIndexOf('.') ) + '_s.' + ext;
			}
		});
		this.tplTileIcons = new Ext.XTemplate(
			'<tpl for=".">'+
			'<div class="imageSelector" style="padding: 2px;">' +
                '<div>'+
                    '<span style="font-weight:bold">{filename}</span><br/>{barcode} {family}<br/>{genus} {specificEpithet}'+
                '</div>'+
                '<div style="border-bottom: solid thin #9F9F9F; width: 275px; height: 276px;">'+
                    '<img style="display: block; margin: auto;" src="{[this.renderThumbnail(values.path,values.filename,values.ext)]}" onerror="this.onerror=\'\'; this.src=\'./resources/img/noimg_275.png\'; return true;">'+
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
        var record = this.getStore().getAt( ind );
        new Ext.Element( this.getNode( record ) ).addCls( 'imageRowSelected' );
        if ( record && !this.getSelectionModel().isSelected( ind ) ) {
            this.getSelectionModel().select( ind );
        }
	},
	onRowDeselect: function( ind ) {
        var record = this.getStore().getAt( ind );
        new Ext.Element( this.getNode( record ) ).removeCls( 'imageRowSelected' );
        if ( record && this.getSelectionModel().isSelected( ind ) ) {
            this.getSelectionModel().deselect( ind );
        }
	},
	onRowFocus: function( ind ) {
        // this has to be implemented ( abstract method )
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
