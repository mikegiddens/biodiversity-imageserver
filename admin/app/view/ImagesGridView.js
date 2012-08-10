Ext.define('BIS.view.ImagesGridView', {
    extend: 'Ext.view.View',
    alias: ['widget.imagesgridview'],
    initialTpl: '<div>testing...</div>',
    tpl: '<tpl for="."><div>hello</div></tpl>',
    itemSelector: '.ux-explorerview-item',
    selectedItemCls: 'imageRowSelected',
    stripeRows: true,
    autoScroll: true,
    multiSelect: true,
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
            '<div>' +
                '<div style="float:left;width:100px;margin:5px 10px 5px 5px;"><img src="{[this.renderThumbnail(values.path,values.filename,values.ext)]}"></div>'+
                    '<div style="float:left"><div unselectable="on">{barcode} {Family}<br/>{Genus} {SpecificEpithet}<br/>'+
                    '<tpl if="barcode != 0">'+
                        '<span>Barcode: {barcode}</span><br>'+
                    '</tpl>'+
                    '<span>Date Added: {timestamp_modified:this.convDate}</span></div>'+
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
            }
        );
        this.tplSmallIcons = new Ext.XTemplate(
            '<tpl for=".">'+
            '<div style="float: left; width:100px;height:100px"><img ' +
                '<tpl if="Family != \'\' || Genus != \'\' || SpecificEpithet != \'\' ">'+
                    ' ext:qtip="' +
                    '<tpl if="Family != \'\' " >{Family}<br></tpl>'+
                    '<tpl if="Genus != \'\' " >{Genus} {SpecificEpithet}"</tpl>'+
                '</tpl>' +
                'src="{[this.renderThumbnail(values.path,values.filename,values.ext)]}" /></div>'+
            '</tpl>', {
                renderThumbnail: function( path, filename, ext ) {
                    return path + filename.substr( 0, filename.indexOf('.') ) + '_s.' + ext;
                }
            }
        );
        this.tplTileIcons = new Ext.XTemplate(
            '<tpl for=".">'+
            '<div style="float: left; padding: 5px;">'+
                '<div unselectable="on">{barcode}<br/> {Family}<span>{Genus} {SpecificEpithet}</span></div>'+
                '<div style="border-bottom: solid thin #9F9F9F; width: 275px; height: 276px;"><img src="{[this.renderThumbnail(values.path,values.filename,values.ext)]}"></div>'+
            '</div>'+
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
