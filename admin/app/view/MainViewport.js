Ext.define('BIS.view.MainViewport', {
    extend: 'Ext.panel.Panel',
    requires: [
        'BIS.view.CtxMnuAttribute',
        'BIS.view.CtxMnuCategory',
        'BIS.view.CtxMnuCollection',
        'BIS.view.CtxMnuEvent',
        'BIS.view.CtxMnuEventType',
        'BIS.view.FormCreateCategory',
        'BIS.view.FormCreateAttribute',
        'BIS.view.FormCreateCollection',
        'BIS.view.ImagesPanel'
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
                    defaults: {
                        border: false,
                        autoScroll: true,
                        maxHeight: 600
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
                                scope: this,
                                show: function( el, opts ) {
                                    Ext.getCmp('viewsPagingTitle').setText('Sets');
                                }
                            }
                        },
                        {
                            xtype: 'treepanel',
                            id: 'categoryTreePanel',
                            store: 'CategoryTreeStore',
                            rootVisible: false,
                            useArrows: true,
                            multiSelect: true,
                            allowCopy: true,
                            viewConfig: {
                                plugins: [
                                    Ext.create('Ext.tree.plugin.TreeViewDragDrop', {
                                        ddGroup: 'categoryDD',
                                        enableDrop: true,
                                        dragText: 'Copy attribute to another category.',
                                        appendOnly: true
                                    })
                                ],
                                listeners: {
                                    beforedrop: function( el, dragobj, targetNode, action, opts ) {
                                        var record = dragobj.records[0].data;
                                        var target = targetNode.data;
                                        if ( action == 'append' ) {
                                            if ( record.modelClass == 'attribute' && target.modelClass == 'category' ) {
                                                if ( record.categoryId != target.categoryId ) {
                                                    // send attributeAdd with record info on target categoryId
                                                    return true;
                                                }
                                            }
                                        }
                                        return false;
                                    },
                                    isValidDropPoint: function( a,b,c,d,e ) {
                                        console.log( a, b, c, d,e  );
                                    }
                                },
                                copy: true,
                                loadMask: false
                            },
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
                                    this.getStore().getProxy().extraParams.cmd = 'attributeList';
                                    this.getStore().getProxy().extraParams.categoryId = record.data.categoryId;
                                    this.getStore().getProxy().extraParams.showNames = false;
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
                                    sortable: true,
                                    renderer: function( value ) {
                                        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                    }
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
                                    Ext.getCmp('imagesPanel').setFilter({CollectionCode: record.data.code}, true);
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
                            viewConfig: {
                                plugins: [
                                    Ext.create('Ext.tree.plugin.TreeViewDragDrop', {
                                        ddGroup: 'eventDD',
                                        enableDrop: true
                                    })
                                ],
                                listeners: {
                                    beforedrop: function( el, dragobj, targetNode, action, opts ) {
                                        var record = dragobj.records[0].data;
                                        var target = targetNode.data;
                                        console.log( record, target );
                                        if ( action == 'append' ) {
                                            if ( record.modelClass == 'event' && target.modelClass == 'eventtype' ) {
                                                if ( record.eventTypeId != target.eventTypeId ) {
                                                    // send eventAdd with record info on target eventTypeId
                                                    return true;
                                                }
                                            }
                                        }
                                        return false;
                                    }
                                },
                                copy: true,
                                loadMask: false
                            },
                            listeners: {
                                show: function( el, opts ) {
                                    Ext.getCmp('viewsPagingTitle').setText('Events');
                                },
                                beforeitemexpand: function( record, opts ) {
                                    this.getStore().getProxy().extraParams.cmd = 'eventList';
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
                            },
                            dockedItems: [
                                {
                                    xtype: 'toolbar',
                                    dock: 'top',
                                    items: [
                                        {
                                            text: 'New Event Type',
                                            iconCls: 'icon_newEventType',
                                            scope: this,
                                            handler: this.createEventType
                                        }
                                    ]
                                }
                            ]
                        }
                    ],
                    dockedItems: [
                        {
                            xtype: 'toolbar',
                            id: 'viewsPagingToolbar',
                            dock: 'top',
                            layout: {
                                pack: 'start',
                                align: 'center',
                                type: 'hbox'
                            },
                            items: [
                                {
                                    xtype: 'button',
                                    flex: 1,
                                    text: '<',
                                    scope: this,
                                    handler: this.decrementView
                                },
                                {
                                    xtype: 'label',
                                    flex: 6,
                                    style: 'text-align: center',
                                    cls: 'x-panel-header-text x-panel-header-text-default-framed',
                                    id: 'viewsPagingTitle',
                                    text: 'Sets'
                                },
                                {
                                    xtype: 'button',
                                    flex: 1,
                                    text: '>',
                                    scope: this,
                                    handler: this.incrementView
                                }
                            ]
                        }
                    ]
                },
                {
                    xtype: 'imagespanel'
                },
                {
                    xtype: 'panel',
                    id: 'imageDetailsPanel',
                    layout: 'fit',
                    autoScroll: true,
                    tpl: new Ext.XTemplate('<tpl>'+
                        '{message}'+
                        '<div class="imagePropertyGroupHeader">Metadata</div>'+
                            '<div class="imagePropertyGroupContainer">'+
                                '<tpl for="metadata">'+
                                    '<span class="imgmetadata imagePropertyPill">'+
                                        '<span class="imagePropertyPillText">{.}</span>'+
                                        '<span catdata="metadata" pilldata="{.}" class="del imagePropertyPillRemove"></span>'+
                                    '</span>'+
                                '</tpl>'+
                            '</div>'+
                        '<div class="imagePropertyGroupHeader">Events</div>'+
                            '<div class="imagePropertyGroupContainer">'+
                                '<tpl for="events">'+
                                    '<span class="imgevents imagePropertyPill">'+
                                        '<span class="imagePropertyPillText">{.}</span>'+
                                        '<span catdata="events" pilldata="{.}" class="del imagePropertyPillRemove"></span>'+
                                    '</span>'+
                                '</tpl>'+
                            '</div>'+
                        '<div class="imagePropertyGroupHeader">Geography</div>'+
                            '<div class="imagePropertyGroupContainer">'+
                                '<tpl for="geography">'+
                                    '<span class="imggeography imagePropertyPill">'+
                                        '<span class="imagePropertyPillText">{.}</span>'+
                                        '<span catdata="geography" pilldata="{.}" class="del imagePropertyPillRemove"></span>'+
                                    '</span>'+
                                '</tpl>'+
                            '</div>'+
                        '<div class="imagePropertyGroupHeader">Sets</div>'+
                            '<div class="imagePropertyGroupContainer">'+
                                '<tpl for="sets">'+
                                    '<span class="imgsets imagePropertyPill">'+
                                        '<span class="imagePropertyPillText">{.}</span>'+
                                        '<span catdata="sets" pilldata="{.}" class="del imagePropertyPillRemove"></span>'+
                                    '</span>'+
                                '</tpl>'+
                            '</div>'+
                    '</tpl>'),
                    listeners: {
                        scope: this,
                        render: function ( panel ) {
                            var dropTarget = new Ext.dd.DropTarget(panel.el, {
                                ddGroup: 'categoryDD',
                                copy: false,
                                notifyDrop: function (dragSource, e, data) {
                                    var record = data.records[0].data;
                                    console.log( record );
                                    console.log( this );
                                }
                            });
                        },
                        afterrender: function() {
                            Ext.getCmp('imageDetailsPanel').update({message:'<div style="padding: 10px">Click an image to view it\'s properties.</div>'});
                        }
                    },
                    collapseDirection: 'right',
                    collapsed: false,
                    collapsible: true,
                    title: 'Image Properties',
                    flex: 2,
                    region: 'east',
                    split: true,
                    loadImage: function( record ) {
                        var properties = [];
                        for ( var p in record ) {
                            properties.push( p );
                        }
                        this.addProperties({
                            metadata: properties,
                            events: properties,
                            geography: properties,
                            sets: properties
                        });
                    },
                    addProperties: function( data ) {
                        Ext.getCmp('imageDetailsPanel').update( data );
                        for ( var category in data ) {
                            Ext.select('span.img'+category).select('.del').on('click', function( e, el, opts ) {
                                Ext.getCmp('imageDetailsPanel').removeProperty( el.getAttribute('catdata'), el.getAttribute('pilldata') );
                            });
                        }
                    },
                    removeProperty: function( type, id ) {
                        console.log( 'removing', type, id );
                    },
                    dockedItems: [
                        {
                            xtype: 'container',
                            dock: 'top',
                            style: 'padding: 5px',
                            layout: 'hbox',
                            items: [
                                {
                                    xtype: 'combo',
                                    id: 'propertySeachCombo',
                                    disabled: true,
                                    emptyText: 'Type to search attributes or add a new one.',
                                    store: 'ImagesStore',
                                    displayField: 'fileName',
                                    typeAhead: false,
                                    hideLabel: true,
                                    hideTrigger: true,
                                    flex: 1,
                                    listConfig: {
                                        loadingText: 'Looking for properties...',
                                        emptyText: 'No matching properties found.',
                                        getInnerTpl: function() {
                                            return '<div class="propertySearchItem">'+
                                                '<h3><span>{fileName}</h3>'+
                                                '{path}'+
                                            '</div>';
                                        }
                                    },
                                    pageSize: 5,
                                    listeners: {
                                        select: function(combo, selection) {
                                            var property = selection[0];
                                            if ( property ) {
                                                console.log( 'selected', property );
                                            }
                                        }
                                    }
                                }
                            ]
                        }
                    ]
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
                                width: 150,
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
                                    },
                                    {
                                        xtype: 'menuitem',
                                        text: 'Server Information',
                                        iconCls: 'icon_info',
                                        scope: this,
                                        handler: this.openServerInfo
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
    openServerInfo: function( menuItem, e ) {
        var loadMask = new Ext.LoadMask(Ext.getBody(),{msg: 'Loading info...',indicator: true});
        loadMask.show();
        Ext.create('Ext.window.Window', {
            title: 'Server Information',
            iconCls: 'icon_info',
            modal: true,
            height: 500,
            width: 800,
            layout: 'fit',
            items: [
                {
                    xtype: 'panel',
                    border: false,
                    tpl: new Ext.XTemplate(
                        '<div>Server Info</div>'
                    )
                }
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
    },
    createEventType: function() {
        Ext.create('Ext.window.Window', {
            title: 'Create Event Type',
            iconCls: 'icon_newEventType',
            modal: true,
            height: 500,
            width: 800,
            layout: 'fit',
            items: [
                { xtype: 'formcreateeventtype', mode: 'add' }
            ]
        }).show();
    }
});
