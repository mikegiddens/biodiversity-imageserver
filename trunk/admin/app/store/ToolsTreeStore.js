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
                    leaf: true
                },
                {
                    name: 'Tesseract OCR',
                    route: 'populateOcrProcessQueue',
                    leaf: true
                },
                {
                    name: 'Measurement Detection',
                    route: 'populateBoxDetect',
                    leaf: true
                },
                {
                    name: 'Taxonomic Recognition and Discovery',
                    route: 'populateNameFinderProcessQueue',
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
