Ext.define('BIS.view.ImagesPanel', {
    extend: 'Ext.panel.Panel',
    alias: ['widget.imagespanel'],
    requires: [
        'BIS.view.ImagesGridView',
        'Ext.ux.form.SearchField'
    ],
    id: 'imagesPanel',
    layout: 'fit',
    region: 'center',
    flex: 6,
    listeners: {
        afterrender: function() {
            this.grid = Ext.getCmp('imagesGrid');
        }
    },
    initComponent: function() {
        var me = this;
        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'gridpanel',
                    border: false,
                    id: 'imagesGrid',
                    autoScroll: true,
                    store: 'ImagesStore',
                    viewType: 'imagesgridview',
                    columns: [
                        {
                            xtype: 'gridcolumn',
                            dataIndex: 'image_id',
                            text: 'Identifier'
                        },
                        {
                            xtype: 'gridcolumn',
                            dataIndex: 'filename',
                            text: 'Filename'
                        },
                        {
                            xtype: 'gridcolumn',
                            dataIndex: 'path',
                            text: 'File Path'
                        },
                        {
                            xtype: 'datecolumn',
                            dataIndex: 'timestamp_modified',
                            text: 'Last Modified'
                        }
                    ],
                    dockedItems: [
                        {
                            xtype: 'pagingtoolbar',
                            displayInfo: true,
                            store: 'ImagesStore',
                            displayMsg: 'Displaying {0} - {1} of {2}',
                            dock: 'bottom'
                        }
                    ]
                }
            ],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    id: 'imagesToolbar',
                    dock: 'top',
                    items: [
                        {
                            xtype: 'button',
                            text: 'Clear Filter',
                            iconCls: 'icon_cancel',
                            scope: this,
                            handler: this.clearFilter
                        },
                        {
                            xtype: 'tbseparator'
                        },
                        {
                            xtype: 'cycle',
                            showText: true,
                            prependText: 'View ',
                            menu: {
                                items: [
                                    {
                                        text: 'Both',
                                        iconCls: 'icon_viewBoth',
                                        type: 'both'
                                    },
                                    {
                                        text: 'Small',
                                        iconCls: 'icon_viewSmall',
                                        type: 'small'
                                    },
                                    {
                                        text: 'Large',
                                        iconCls: 'icon_viewLarge',
                                        type: 'tile'
                                    },
                                    {
                                        text: 'Details',
                                        iconCls: 'icon_viewList',
                                        disabled: true,
                                        type: 'details'
                                    }
                                ]
                            },
                            scope: this,
                            changeHandler: this.changeView
                        },
                        {
                            xtype: 'tbseparator'
                        },
                        {
                            xtype: 'searchfield',
                            name: 'searchval',
                            emptyText: 'Search images',
                            handlerCmp: this,
                            width: 200,
                            scope: this
                        }
                    ]
                }
            ]
        });
        me.callParent(arguments);
    },
    setFilter: function( params, reset ) {
        if ( reset ) this.grid.getStore().clearFilter();
        var parsedParams = [];
        for ( var p in params ) {
            parsedParams.push({property: p, value: params[p]});
        }
        this.grid.getStore().filter( parsedParams );
    },
    clearFilter: function() {
        this.grid.getStore().clearFilter();
    },
    changeView: function( cycleBtn, item ) {
        if ( item.type != 'details' ) {
            Ext.getCmp('imagesGrid').getView().setTpl( item.type );
        }
    },
    search: function( val ) {
        this.grid.getStore().filterBy(function( record, id ) {
            for ( var p in record.data ) {
                if ( String(record.data[p]).indexOf(val) > 0 ) {
                    return true;
                }
            }
        });
    }
});
