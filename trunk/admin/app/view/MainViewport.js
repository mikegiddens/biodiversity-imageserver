Ext.define('BIS.view.MainViewport', {
    extend: 'Ext.panel.Panel',
    requires: [
        'BIS.view.CtxMnuAttribute',
        'BIS.view.CtxMnuCategory',
        'BIS.view.CtxMnuCollection',
        'BIS.view.CtxMnuEvent',
        'BIS.view.CtxMnuEventType',
        'BIS.view.ImagesGridView',
        'BIS.view.FormCreateCategory',
        'BIS.view.FormCreateAttribute',
        'BIS.view.FormCreateCollection'
    ],
    layout: {
        type: 'border'
    },
    height: window.innerHeight,
    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'panel',
                    id: 'viewsPanel',
                    activeItem: 0,
                    layout: {
                        type: 'card'
                    },
                    titleCollapse: false,
                    region: 'west',
                    flex: 2,
                    items: [
                        {
                            xtype: 'treepanel',
                            id: 'setTreePanel',
                            store: 'SetTreeStore',
                            rootVisible: false,
                            useArrows: true,
                            columns: [
                                {
                                    xtype: 'treecolumn',
                                    text: 'Name',
                                    flex: 1,
                                    dataIndex: 'name',
                                    sortable: true
                                },
                                {
                                    text: 'Description',
                                    flex: 1,
                                    dataIndex: 'description',
                                    sortable: true
                                }
                            ],
                            listeners: {
                                show: function( el, opts ) {
                                    Ext.getCmp('viewsPagingTitle').setText('Sets');
                                }
                            }
                        },
                        {
                            xtype: 'treepanel',
                            border: false,
                            id: 'categoryTreePanel',
                            store: 'CategoryTreeStore',
                            rootVisible: false,
                            useArrows: true,
                            columns: [
                                {
                                    xtype: 'treecolumn',
                                    text: 'Title',
                                    flex: 1,
                                    dataIndex: 'title',
                                    sortable: true
                                }
                            ],
                            scope: this,
                            listeners: {
                                show: function( el, opts ) {
                                    Ext.getCmp('viewsPagingTitle').setText('Categories');
                                },
                                beforeitemexpand: function( record, opts ) {
                                    this.getStore().getProxy().extraParams.cmd = 'list_attributes';
                                    this.getStore().getProxy().extraParams.categoryID = record.data.typeID;
                                    this.getStore().getProxy().setModel( 'BIS.model.AttributeModel' );
                                },
                                itemcontextmenu: function(view, record, item, index, e) {
                                    e.stopEvent();
                                    var ctx;
                                    switch( record.data.modelClass ) {
                                        case 'category':
                                            ctx = Ext.create('BIS.view.CtxMnuCategory', {record: record});
                                            break;
                                        case 'attribute':
                                            ctx = Ext.create('BIS.view.CtxMnuAttribute', {record: record});
                                            break;
                                    }
                                    ctx.showAt(e.getXY());
                                },
                                itemclick: function( tree, record, el, ind, e, opts ) {
                                }
                            },
                            dockedItems: [
                                {
                                    xtype: 'toolbar',
                                    dock: 'top',
                                    items: [
                                        {
                                            text: 'New Category',
                                            iconCls: 'icon_newCategory',
                                            scope: this,
                                            handler: this.createCategory
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                            xtype: 'treepanel',
                            id: 'collectionTreePanel',
                            store: 'CollectionsTreeStore',
                            rootVisible: false,
                            useArrows: true,
                            columns: [
                                {
                                    xtype: 'treecolumn',
                                    text: 'Name',
                                    flex: 3,
                                    dataIndex: 'name',
                                    sortable: true
                                },
                                {
                                    text: 'Code',
                                    flex: 1,
                                    dataIndex: 'code',
                                    sortable: true
                                },
                                {
                                    text: 'Size',
                                    flex: 1,
                                    dataIndex: 'collectionSize',
                                    sortable: true
                                }
                            ],
                            scope: this,
                            listeners: {
                                show: function( el, opts ) {
                                    Ext.getCmp('viewsPagingTitle').setText('Collections');
                                },
                                itemcontextmenu: function(view, record, item, index, e) {
                                    e.stopEvent();
                                    var ctx = Ext.create('BIS.view.CtxMnuCollection', {record: record});
                                    ctx.showAt(e.getXY());
                                },
                                itemclick: function( tree, record, el, ind, e, opts ) {
                                    var store = Ext.data.StoreManager.lookup('ImagesStore');
                                    store.clearFilter();
                                    store.filter('CollectionCode', record.data.code);
                                }
                            },
                            dockedItems: [
                                {
                                    xtype: 'toolbar',
                                    dock: 'top',
                                    items: [
                                        {
                                            text: 'New Collection',
                                            iconCls: 'icon_newCollection',
                                            scope: this,
                                            handler: this.createCollection
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                            xtype: 'panel',
                            id: 'toolPanel',
                            listeners: {
                                show: function( el, opts ) {
                                    Ext.getCmp('viewsPagingTitle').setText('Tools');
                                }
                            }
                        },
                        {
                            xtype: 'gridpanel',
                            id: 'queuePanel',
                            store: 'QueueStore',
                            columns: [
                                {text:'Identifier',dataIndex:'image_id',flex:2},
                                {text:'Compeleted?',dataIndex:'processed', renderer: function( value ) {
                                    if ( value ) { return 'Yes' }
                                    return ' ';
                                    },
                                    flex: 1
                                }
                            ],
                            listeners: {
                                show: function( el, opts ) {
                                    Ext.getCmp('viewsPagingTitle').setText('Queue');
                                }
                            }
                        },
                        {
                            xtype: 'panel',
                            id: 'geographyPanel',
                            listeners: {
                                show: function( el, opts ) {
                                    Ext.getCmp('viewsPagingTitle').setText('Geography');
                                }
                            }
                        },
                        {
                            xtype: 'treepanel',
                            id: 'eventTreePanel',
                            store: 'EventTreeStore',
                            rootVisible: false,
                            useArrows: true,
                            columns: [
                                {
                                    xtype: 'treecolumn',
                                    text: 'Title',
                                    flex: 1,
                                    dataIndex: 'title',
                                    sortable: true
                                },
                                {
                                    text: 'Description',
                                    flex: 1,
                                    dataIndex: 'description',
                                    sortable: true
                                }
                            ],
                            listeners: {
                                show: function( el, opts ) {
                                    Ext.getCmp('viewsPagingTitle').setText('Events');
                                },
                                beforeitemexpand: function( record, opts ) {
                                    this.getStore().getProxy().extraParams.cmd = 'listEvents';
                                    this.getStore().getProxy().extraParams.filter = {eventTypeId: record.data.eventTypeId};
                                    this.getStore().getProxy().setModel( 'BIS.model.EventModel' );
                                },
                                itemcontextmenu: function(view, record, item, index, e) {
                                    e.stopEvent();
                                    var ctx;
                                    switch( record.data.modelClass ) {
                                        case 'eventtype':
                                            ctx = Ext.create('BIS.view.CtxMnuEventType', {record: record});
                                            break;
                                        case 'event':
                                            ctx = Ext.create('BIS.view.CtxMnuEvent', {record: record});
                                            break;
                                    }
                                    ctx.showAt(e.getXY());
                                }
                            }
                        }
                    ],
                    dockedItems: [
                        {
                            xtype: 'toolbar',
                            id: 'viewsPagingToolbar',
                            dock: 'top',
                            layout: {
                                pack: 'center',
                                type: 'hbox'
                            },
                            items: [
                                {
                                    xtype: 'button',
                                    text: '<',
                                    scope: this,
                                    handler: this.decrementView
                                },
                                {
                                    xtype: 'label',
                                    width: 200,
                                    cls: 'x-panel-header-text x-panel-header-text-default-framed',
                                    id: 'viewsPagingTitle',
                                    text: 'Sets'
                                },
                                {
                                    xtype: 'button',
                                    text: '>',
                                    scope: this,
                                    handler: this.incrementView
                                }
                            ]
                        }
                    ]
                },
                {
                    xtype: 'panel',
                    id: 'imagesPanel',
                    layout: {
                        type: 'fit'
                    },
                    region: 'center',
                    flex: 6,
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
                    ]
                },
                {
                    xtype: 'panel',
                    id: 'imageDetailsPanel',
                    layout: {
                        type: 'fit'
                    },
                    collapseDirection: 'right',
                    collapsed: false,
                    collapsible: true,
                    title: 'Image Properties',
                    flex: 2,
                    region: 'east',
                    split: true
                }
            ],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    id: 'masterToolbar',
                    dock: 'top',
                    items: [
                        {
                            xtype: 'button',
                            text: 'View',
                            iconCls: 'icon_view',
                            menu: {
                                xtype: 'menu',
                                id: 'viewMenu',
                                width: 120,
                                items: [
                                    {
                                        xtype: 'menuitem',
                                        text: 'Sets',
                                        iconCls: 'icon_sets',
                                        scope: this,
                                        handler: this.switchViewToSets
                                    },
                                    {
                                        xtype: 'menuitem',
                                        text: 'Metadata',
                                        iconCls: 'icon_metadata',
                                        scope: this,
                                        handler: this.switchViewToMetadata
                                    },
                                    {
                                        xtype: 'menuitem',
                                        text: 'Collections',
                                        iconCls: 'icon_collections',
                                        scope: this,
                                        handler: this.switchViewToCollections
                                    },
                                    {
                                        xtype: 'menuitem',
                                        text: 'Tools',
                                        iconCls: 'icon_tools',
                                        scope: this,
                                        handler: this.switchViewToTools
                                    },
                                    {
                                        xtype: 'menuitem',
                                        text: 'Queue',
                                        iconCls: 'icon_queue',
                                        scope: this,
                                        handler: this.switchViewToQueue
                                    },
                                    {
                                        xtype: 'menuitem',
                                        text: 'Geography',
                                        iconCls: 'icon_geography',
                                        scope: this,
                                        handler: this.switchViewToGeography
                                    },
                                    {
                                        xtype: 'menuitem',
                                        text: 'Events',
                                        iconCls: 'icon_eventTypes',
                                        scope: this,
                                        handler: this.switchViewToEvents
                                    }
                                ]
                            }
                        },
                        {
                            xtype: 'tbseparator'
                        },
                        {
                            xtype: 'button',
                            text: 'Tools',
                            iconCls: 'icon_toolbar',
                            menu: {
                                xtype: 'menu',
                                id: 'toolsMenu',
                                width: 120,
                                items: [
                                    {
                                        xtype: 'menuitem',
                                        text: 'Storage Settings',
                                        iconCls: 'icon_devices',
                                        scope: this,
                                        handler: this.openStorageSettings
                                    },
                                    {
                                        xtype: 'menuitem',
                                        text: 'User Manager',
                                        iconCls: 'icon_users',
                                        scope: this,
                                        handler: this.openUserManager
                                    }
                                ]
                            }
                        },
                        {
                            xtype: 'tbfill'
                        },
                        {
                            xtype: 'label',
                            style: 'font-weight: bold;',
                            text: 'Welcome, Administrator'
                        }
                    ]
                }
            ]
        });

        me.callParent(arguments);
    },
    incrementView: function( btn, e ) {
        var viewCard = Ext.getCmp('viewsPanel').getLayout();
        if ( viewCard.getLayoutItems().indexOf( viewCard.getActiveItem() ) == viewCard.getLayoutItems().length-1 ) {
            viewCard.setActiveItem( 0 );
        } else {
            viewCard.next();
        }
    },
    decrementView: function( btn, e ) {
        var viewCard = Ext.getCmp('viewsPanel').getLayout();
        if ( viewCard.getLayoutItems().indexOf( viewCard.getActiveItem() ) == 0 ) {
            viewCard.setActiveItem( viewCard.getLayoutItems().length-1 );
        } else {
            viewCard.prev();
        }
    },
    switchViewToSets: function( menuItem, e ) {
        Ext.getCmp('viewsPanel').getLayout().setActiveItem(0);
    },
    switchViewToMetadata: function( menuItem, e ) {
        Ext.getCmp('viewsPanel').getLayout().setActiveItem(1);
    },
    switchViewToCollections: function( menuItem, e ) {
        Ext.getCmp('viewsPanel').getLayout().setActiveItem(2);
    },
    switchViewToTools: function( menuItem, e ) {
        Ext.getCmp('viewsPanel').getLayout().setActiveItem(3);
    },
    switchViewToQueue: function( menuItem, e ) {
        Ext.getCmp('viewsPanel').getLayout().setActiveItem(4);
    },
    switchViewToGeography: function( menuItem, e ) {
        Ext.getCmp('viewsPanel').getLayout().setActiveItem(5);
    },
    switchViewToEvents: function( menuItem, e ) {
        Ext.getCmp('viewsPanel').getLayout().setActiveItem(6);
    },
    openStorageSettings: function( menuItem, e ) {
        var loadMask = new Ext.LoadMask(Ext.getBody(),{msg: 'Loading settings...',indicator: true});
        loadMask.show();
        Ext.create('Ext.window.Window', {
            title: 'Storage Settings',
            iconCls: 'icon_devices',
            modal: true,
            height: 500,
            width: 800,
            layout: 'fit',
            items: [
                { xtype: 'storagesettingspanel' } 
            ]
        }).show();
        loadMask.hide();
    },
    openUserManager: function( menuItem, e ) {
        var loadMask = new Ext.LoadMask(Ext.getBody(),{msg: 'Loading users...',indicator: true});
        loadMask.show();
        Ext.create('Ext.window.Window', {
            title: 'User Management',
            iconCls: 'icon_users',
            modal: true,
            height: 500,
            width: 800,
            layout: 'fit',
            items: [
                { xtype: 'usermanagerpanel' } 
            ]
        }).show();
        loadMask.hide();
    },
    createCategory: function() {
        Ext.create('Ext.window.Window', {
            title: 'Create Category',
            iconCls: 'icon_newCategory',
            modal: true,
            height: 500,
            width: 800,
            layout: 'fit',
            items: [
                { xtype: 'formcreatecategory', mode: 'add' } 
            ]
        }).show();
    },
    createCollection: function() {
        Ext.create('Ext.window.Window', {
            title: 'Create Collection',
            iconCls: 'icon_newCollection',
            modal: true,
            height: 500,
            width: 800,
            layout: 'fit',
            items: [
                { xtype: 'formcreatecollection', mode: 'add' } 
            ]
        }).show();
    }
});
