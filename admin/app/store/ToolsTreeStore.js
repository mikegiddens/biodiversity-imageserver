Ext.define('BIS.store.ToolsTreeStore', {
    extend: 'Ext.data.TreeStore',
    requires: [
        'BIS.model.ToolModel'
    ],
    autoLoad: true,
    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            autoLoad: true,
            storeId: 'toolsTreeStore',
            model: 'BIS.model.ToolModel',
            root: { expanded: true, leaf: false, children: [
                {
                    name: 'Process',
                    route: 'imageModifyRechop',
                    module: 'api.php',
                    iconCls: 'icon_refresh',
                    leaf: true
                },
                {
                    name: 'Tesseract OCR',
                    route: 'populateOcrProcessQueue',
                    module: 'processor.php',
                    iconCls: 'icon_ocr',
                    leaf: true
                },
                {
                    name: 'Send to Evernote',
                    route: 'populateEvernoteProcessQueue',
                    module: 'processor.php',
                    iconCls: 'icon_evernote',
                    leaf: true
                },
                {
                    name: 'Measurement Detection',
                    route: 'populateBoxDetect',
                    module: 'processor.php',
                    iconCls: 'icon_measure',
                    leaf: true
                },
                {
                    name: 'Taxonomic Recognition and Discovery',
                    route: 'populateNameFinderProcessQueue',
                    iconCls: 'icon_find',
                    module: 'processor.php',
                    leaf: true
                }
            ]},
            proxy: {
                type: 'memory',
                reader: {
                    type: 'json'
                }
            }
        }, cfg)]);
    }

});
