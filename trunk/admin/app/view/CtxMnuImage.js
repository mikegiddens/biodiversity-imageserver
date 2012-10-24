Ext.define('BIS.view.CtxMnuImage', {
    extend: 'Ext.menu.Menu',
    requires: [
        'BIS.view.ImageTabPanel'
    ],
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'open':
                    Ext.create('Ext.window.Window', {
                        title: 'View Image ' + this.record.data.filename,
                        iconCls: 'icon_image',
                        bodyCls: 'x-docked-noborder-top x-docked-noborder-bottom x-docked-noborder-right x-docked-noborder-left',
                        modal: true,
                        height: 500,
                        width: 800,
                        layout: 'fit',
                        maximizable: true,
                        items: [{
                            xtype: 'imagetabpanel',
                            record: this.record
                        }]
                    }).show();
                    break;
                case 'delete':
                    Ext.Msg.confirm('Remove ' + this.record.data.filename + '?', 'Are you sure you want remove ' + this.record.data.filename + '?', function( btn, nothing, item ) {
                        if ( btn == 'yes' ) this.remove();
                    }, this);
                    break;
                case 'cw':
                    this.rotateCW();
                    break;
                case 'ccw':
                    this.rotateCCW();
                    break;
                case 'mirror':
                    this.rotate180();
                    break;
                case 'queue':
                    this.queue();
                    break;
                case 'ocr':
                    this.showOcrData();
                    break;
                case 'evernote':
                    this.showEvernoteData();
                    break;
            }
        }
    },
    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    text: 'View Image',
                    iconCls: 'icon_image',
                    identifier: 'open'
                },
                {
                    text: 'View OCR',
                    iconCls: 'icon_ocr',
                    identifier: 'ocr',
                    disabled: this.record.data.ocrFlag == '0'
                },
                {
                    text: 'View Evernote',
                    iconCls: 'icon_evernote',
                    identifier: 'evernote',
                    disabled: this.record.data.enFlag == '0'
                },
                '-',
                {
                    text: 'Remove Image',
                    iconCls: 'icon_removeImage',
                    identifier: 'delete'
                },
                '-',
                {
                    text: 'Rotate 90&#176; Right',
                    iconCls: 'icon_arrowCW',
                    identifier: 'cw'
                },
                {
                    text: 'Rotate 90&#176; Left',
                    iconCls: 'icon_arrowCCW',
                    identifier: 'ccw'
                },
                {
                    text: 'Rotate 180&#176;',
                    iconCls: 'icon_arrowAlternating',
                    identifier: 'mirror'
                },
                '-',
                {
                    text: 'Use tool',
                    iconCls: 'icon_toolbar',
                    menu: {
                        xtype: 'menu',
                        scope: me,
                        listeners: {
                            scope: me,
                            click: me.handleTool
                        },
                        items: [
                            {
                                text: 'Process',
                                iconCls: 'icon_refresh',
                                identifier: 'process'
                            },
                            {
                                text: 'Tesseract OCR',
                                iconCls: 'icon_ocr',
                                identifier: 'tesseract'
                            },
                            {
                                text: 'Send to Evernote',
                                iconCls: 'icon_evernote',
                                identifier: 'evernote'
                            },
                            {
                                text: 'Measurement Detection',
                                iconCls: 'icon_measure',
                                identifier: 'measure'
                            },
                            {
                                text: 'Taxonomic Recognition and Discovery',
                                iconCls: 'icon_find',
                                identifier: 'discover'
                            }
                        ]
                    }
                }
            ]
        });
        me.callParent(arguments);
    },
    remove: function() {
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'imageDelete',
                imageId: this.record.data.imageId
            },
            scope: this,
            success: function() {
                Ext.getCmp('imagesGrid').getStore().load();
            }
        });
    },
    rotateCW: function() {
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'imageModifyRotate',
                imageId: this.record.data.imageId,
                degree: 90
            },
            scope: this,
            success: function( data ) {
                console.log( data );
                if ( data.success ) {
                } else {
                    Ext.Msg.alert('Unable to Process Request', 'You do not have permission to rotate images.');
                }
            }
        });
    },
    rotateCCW: function() {
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'imageModifyRotate',
                imageId: this.record.data.imageId,
                degree: 270
            },
            scope: this,
            success: function( data ) {
                console.log( data );
                if ( data.success ) {
                } else {
                    Ext.Msg.alert('Unable to Process Request', 'You do not have permission to rotate images.');
                }
            }
        });
    },
    rotate180: function() {
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'imageModifyRotate',
                imageId: this.record.data.imageId,
                degree: 180
            },
            scope: this,
            success: function( data ) {
                console.log( data );
                if ( data.success ) {
                } else {
                    Ext.Msg.alert('Unable to Process Request', 'You do not have permission to rotate images.');
                }
            }
        });
    },
    showOcrData: function() {
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'imageGetOcr',
                imageId: this.record.data.imageId
            },
            scope: this,
            success: function( data ) {
                Ext.create('Ext.window.Window', {
                    title: 'Optical Character Recognition from ' + this.record.data.filename,
                    iconCls: 'icon_ocr',
                    bodyCls: 'x-docked-noborder-top x-docked-noborder-bottom x-docked-noborder-right x-docked-noborder-left',
                    modal: true,
                    height: 500,
                    width: 800,
                    layout: 'fit',
                    maximizable: true,
                    items: [
                        {
                            xtype: 'panel',
                            border: false,
                            title: false,
                            autoScroll: true,
                            html: this.parseLineBreaks( data.responseText )
                        }
                    ]
                }).show();
            }
        });
    },
    showEvernoteData: function() {
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            // process on Evernote
            //Config.baseUrl + 'resources/api/backup_services.php', 'cmd=populateEvernoteProcessQueue&imageId='+this.record.data.imageId
            //Config.baseUrl + 'resources/api/backup_services.php', 'cmd=processEvernoteProcessQueue'
            params: {
                cmd: 'imageRetrieveEvernoteData',
                imageId: this.record.data.imageId
            },
            scope: this,
            success: function( data ) {
                data = Ext.decode( data.responseText );
                Ext.create('Ext.window.Window', {
                    title: 'Evernote Data from ' + this.record.data.filename,
                    iconCls: 'icon_evernote',
                    bodyCls: 'x-docked-noborder-top x-docked-noborder-bottom x-docked-noborder-right x-docked-noborder-left',
                    modal: true,
                    height: 500,
                    width: 800,
                    layout: 'fit',
                    maximizable: true,
                    items: [
                        {
                            xtype: 'panel',
                            border: false,
                            title: false,
                            autoScroll: true,
                            html: data
                        }
                    ]
                }).show();
            }
        });
    },
    parseLineBreaks: function( str ) {
        var tempStr = str.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>$2');
        if ( tempStr.trim() == '' ) tempStr = '( No characters were found within this image. )';
        return tempStr;
    },
    handleTool: function( menu, item ) {
        var module = 'api.php';
        var params = {
            cmd: null,
            imageId: this.record.get('imageId')
        }
        switch ( item.identifier ) {
            case 'process':
                params.cmd = 'imageModifyRechop';
                break;
            case 'tesseract':
                module = 'processor.php';
                params.cmd = 'populateOcrProcessQueue';
                break;
            case 'evernote':
                module = 'processor.php';
                params.cmd = '';
                break;
            case 'measure':
                module = 'processor.php';
                params.cmd = 'populateBoxDetect';
                break;
            case 'discover':
                module = 'processor.php';
                params.cmd = 'populateNameFinderProcessQueue';
                break;
        }
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/' + module,
            params: params,
            scope: this,
            success: function( data ) {
                data = Ext.decode( data.responseText );
                console.log( data );
                if ( data.success ) {
                    // reload image details panel
                    var detailsPanel = Ext.getCmp('imageDetailsPanel');
                    if ( detailsPanel.image ) detailsPanel.loadImage( detailsPanel.image );
                }
            }
        });
    }
});
