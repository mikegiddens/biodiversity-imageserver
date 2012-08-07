Ext.define('BIS.view.ImagesGridView', {
    extend: 'Ext.view.View',
    alias: ['widget.imagesgridview'],
    initialTpl: '<div>testing...</div>',
    tpl: '<tpl for="."><div>hello</div></tpl>',
    itemSelector: '.ux-explorerview-item',
    selectedItemCls: 'imageRowSelected',
    stripeRows: true,
    autoScroll: true,
    listeners: {
        afterrender: function( gridview, e ) {
            gridview.setTpl('both');
        },
        itemclick: function( gridview, record, el, ind, e, opts ) {
            var data = record.data;
            var html = [];
            for ( var p in data ) {
                html.push('<p>'+p+':&nbsp;'+data[p]+'</p>');
            }
            Ext.getCmp('imageDetailsPanel').update( html.join('') );
        }
    },
    constructor: function( config ) {
        this.tplBoth = new Ext.XTemplate(
            '<tpl for=".">'+
            '<div class="x-grid3-row ux-explorerview-item ux-explorerview-mixed-item">' +
                '<tpl if="gTileProcessed == 1">'+
                    '<div class="divZoom bothIconZoomIn"  title="Double click to view large image.">&nbsp;</div>'+
                '</tpl>'+
                '<div class="ux-explorerview-icon"><img src="{[this.renderThumbnail(values.path,values.filename,values.ext)]}"></div>'+
                    '<div class="ux-explorerview-text"><div class="x-grid3-cell x-grid3-td-name" unselectable="on">{barcode} {Family}<br/>{Genus} {SpecificEpithet}<br/>'+
                    '<tpl if="barcode != 0">'+
                        '<span>Barcode: {barcode}</span><br>'+
                    '</tpl>'+
                    '<span>Date Added: {timestamp_modified:this.convDate}</span></div>'+
                '</div>'+
            '</div></tpl>', {
                convDate: function( date ) {
                    //console.log( date );
                    return date;
                },
                renderThumbnail: function( path, filename, ext ) {
                    return path + filename.substr( 0, filename.indexOf('.') ) + '_s.' + ext;
                }
            }
        );
        this.tplSmallIcons = new Ext.XTemplate(
            '<tpl for=".">'+
            '<div class="x-grid3-row ux-explorerview-item ux-explorerview-small-item">'+
            '<tpl if="gTileProcessed == 1">'+
                '<div class="divZoom smallIconZoomIn"  title="Double click to view large image.">&nbsp;</div>'+
            '</tpl>'+	
            '<div class="ux-explorerview-icon"><img  ' +
                '<tpl if="Family != \'\' || Genus != \'\' || SpecificEpithet != \'\' ">'+
                    ' ext:qtip="' +
                    '<tpl if="Family != \'\' " >{Family}<br></tpl>'+
                    '<tpl if="Genus != \'\' " >{Genus} {SpecificEpithet}"</tpl>'+
                '</tpl>' +
                'src="{[this.renderThumbnail(values.path,values.filename,values.ext)]}" /></div>'+
            '</div></tpl>', {
                renderThumbnail: function( path, filename, ext ) {
                    return path + filename.substr( 0, filename.indexOf('.') ) + '_s.' + ext;
                }
            }
        );
        this.tplTileIcons = new Ext.XTemplate(
            '<tpl for=".">'+
            '<div class="x-grid3-row ux-explorerview-item ux-explorerview-tiles-item">'+
            '<tpl if="gTileProcessed == 1">'+
                '<div class="divZoom largeIconZoomIn" title="Double click to view large image.">&nbsp;</div>'+
            '</tpl>'+
            '<div class="ux-explorerview-icon"><img src="{[this.renderThumbnail(values.path,values.filename,values.ext)]}"></div>'+
            '<div class="ux-explorerview-text"><div class="x-grid3-cell x-grid3-td-name" unselectable="on">{barcode}<br/> {Family}<span>{Genus} {SpecificEpithet}</span></div></div></div>'+
            '</tpl>', {
                renderThumbnail: function( path, filename, ext ) {
                    return path + filename.substr( 0, filename.indexOf('.') ) + '_m.' + ext;
                }
            }
        );
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
