Ext.define('BIS.view.MainViewport', {
	extend: 'Ext.panel.Panel',
	requires: [
		'BIS.view.CtxMnuAttribute',
		'BIS.view.CtxMnuCategory',
		'BIS.view.CtxMnuCollection',
		'BIS.view.CtxMnuEvent',
		'BIS.view.CtxMnuEventType',
		'BIS.view.CtxMnuTool',
		'BIS.view.FormCreateCategory',
		'BIS.view.FormCreateAttribute',
		'BIS.view.FormCreateCollection',
		'BIS.view.ImagesPanel',
		'BIS.view.SetTreePanel',
		'BIS.view.CategoryTreePanel',
		'BIS.view.GeographyTreePanel',
		'BIS.view.CollectionTreePanel',
		'BIS.view.EventTreePanel',
		'BIS.view.ImageDetailPanel',
        'BIS.view.KeyManagerPanel',
        'BIS.view.EvernoteSettingsPanel'
	],
	layout: {
		type: 'border'
	},
	initComponent: function() {
		var me = this;

		Ext.applyIf(me, {
			items: [{
				xtype: 'imagespanel',
				region: 'center',
                flex: 4,
				border: false
			},{
				xtype: 'imagedetailpanel',
				collapseDirection: 'right',
				collapsed: false,
				collapsible: true,
				region: 'east',
				flex: 1,
				border: false,
				split: true
			},{
				xtype: 'panel',
				id: 'viewsPanel',
				activeItem: 0,
                flex: 1,
				border: false,
				layout: {
					type: 'card'
				},
				defaults: {
					border: false,
					autoScroll: true
				},
				titleCollapse: false,
				region: 'west',
				width: 350,
				split: true,
				dockedItems: [{
					xtype: 'toolbar',
					id: 'viewsPagingToolbar',
					dock: 'top',
					layout: {
						pack: 'start',
						align: 'center',
						type: 'hbox'
					},
					items: [{
						xtype: 'button',
						flex: 1,
						text: '<',
						scope: this,
						handler: this.decrementView
					},{
						xtype: 'label',
						flex: 6,
						style: 'text-align: center',
						cls: 'x-panel-header-text x-panel-header-text-default-framed',
						id: 'viewsPagingTitle',
						text: 'Categories'
					},{
						xtype: 'button',
						flex: 1,
						text: '>',
						scope: this,
						handler: this.incrementView
					}]
				}],
				items: [/*{
					xtype: 'settreepanel'
				},*/{
					xtype: 'categorytreepanel'
				},{
					xtype: 'collectiontreepanel'
				},{
					xtype: 'treepanel',
					id: 'toolPanel',
                    store: 'ToolsTreeStore',
                    viewConfig: {
                        rootVisible: false
                    },
                    scope: me,
					listeners: {
						show: function( el, opts ) {
							Ext.getCmp('viewsPagingTitle').setText('Tools');
						},
                        itemcontextmenu: function(view, record, item, index, e) {
                            e.stopEvent();
                            var ctx;
                            ctx = Ext.create('BIS.view.CtxMnuTool', {record: record});
                            ctx.showAt(e.getXY());
                        }
					},
                    columns: [
                        {
                            xtype: 'treecolumn',
                            text: 'Tool',
                            dataIndex: 'name',
                            flex: 1
                        }
                    ]
				},{
					xtype: 'geographytreepanel'
				},{
					xtype: 'eventtreepanel',
				},{
					xtype: 'gridpanel',
					id: 'queuePanel',
					store: 'QueueStore',
					columns: [{
                        text: 'Image ID',
                        dataIndex: 'imageId',
                        flex: 1
                    },{
						text:'Job',
						dataIndex:'processType',
						flex: 2
					},{
						text:'Notes',
						dataIndex:'extra',
						flex: 2
					}],
					listeners: {
						show: function( el, opts ) {
							Ext.getCmp('viewsPagingTitle').setText('Queue');
						}
					},
                    dockedItems: [
                        {
                            xtype: 'toolbar',
                            dock: 'top',
                            items: [
                                {
                                    text: 'Refresh Queue',
                                    iconCls: 'icon_refresh',
                                    handler: function() {
                                        Ext.getCmp('queuePanel').getStore().load();
                                    }
                                }
                            ]
                        }
                    ]
				}]
			}],

				dockedItems: [{
					xtype: 'toolbar',
					id: 'masterToolbar',
					dock: 'top',
					items: [{
						text: 'View',
						iconCls: 'icon_view',
						menu: {
							id: 'viewMenu',
							defaults: {
								scope: this,
								handler: this.switchView
							},
							items: [/*{
								text: 'Sets',
								iconCls: 'icon_sets',
								panelIndex: 0
							},*/{
								text: 'Metadata',
								iconCls: 'icon_metadata',
								panelIndex: 0
							},{
								text: 'Collections',
								iconCls: 'icon_collections',
								panelIndex: 1
							},{
								text: 'Geography',
								iconCls: 'icon_geography',
								panelIndex: 3
							},{
								text: 'Events',
								iconCls: 'icon_eventTypes',
								panelIndex: 4
							},{
                                xtype: 'menuseparator'
                            },{
								text: 'Tools',
								iconCls: 'icon_toolbar',
								panelIndex: 2
							},{
								text: 'Queue',
								iconCls: 'icon_queue',
								panelIndex: 5
                            }]
						}
					},{
						xtype: 'tbseparator'
					},{
						xtype: 'button',
						text: 'Tools',
						iconCls: 'icon_toolbar',
						menu: {
							xtype: 'menu',
							id: 'toolsMenu',
							defaults: {
								scope: this
							},
							items: [{
								text: 'Storage Settings',
								iconCls: 'icon_devices',
								handler: this.openStorageSettings
							},{
								text: 'User Manager',
								iconCls: 'icon_users',
								handler: this.openUserManager
							},{
                                text: 'Key Manager',
                                iconCls: 'icon_key',
                                handler: this.openKeyManager
                            },{
                                text: 'Evernote Settings',
                                iconCls: 'icon_evernote',
                                handler: this.openEvernoteSettings
                            },{
								text: 'Server Information',
								iconCls: 'icon_info',
								handler: this.openServerInfo
							}]
						}
					},'->',{
						xtype: 'label',
                        id: 'uploadLabel',
						text: 'Drag and drop to upload images.',
					    style: 'margin-right: 5px;'
                    },{
						xtype: 'label',
                        id: 'filesLabel',
					    style: 'margin-right: 20px;'
                    },{
                        text: 'Sign Out',
                        iconCls: 'icon_logout',
					    style: 'margin-right: 20px;',
                        handler: this.logout
					},{
						xtype: 'label',
                        id: 'userLabel',
						style: 'font-weight: bold; margin-right: 20px;',
						text: 'Welcome to Biodiversity Image Server!'
					}]
				}]
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
    switchView: function( menuItem, e ) {
        Ext.getCmp('viewsPanel').getLayout().setActiveItem(menuItem.panelIndex);
    },
    openStorageSettings: function( menuItem, e ) {
			Ext.create('Ext.window.Window', {
				title: 'Storage Settings',
				iconCls: 'icon_devices',
				modal: true,
				height: 250,
				width: 600,
				layout: 'fit',
				bodyBorder: false,
				items: [{ 
					xtype: 'storagesettingspanel' 
				}],
				dockedItems: [{
					xtype: 'toolbar',
					dock: 'bottom',
					ui: 'footer',
					items: [{ 
						xtype: 'component', flex: 1 
					},{
						text: 'Close',
						xtype: 'button',
						width: 80,
						handler: function() {
							this.ownerCt.ownerCt.close();
						}
					}]
				}]
			}).show();
    },
    openUserManager: function( menuItem, e ) {
			Ext.create('Ext.window.Window', {
				title: 'User Management',
				iconCls: 'icon_users',
				modal: true,
				resizeable: false,
				height: 500,
				width: 800,
				layout: 'fit',
				bodyBorder: false,
				items: [{
					xtype: 'usermanagerpanel' 
				}],
				dockedItems: [{
					xtype: 'toolbar',
					dock: 'bottom',
					ui: 'footer',
					items: [{ 
						xtype: 'component', flex: 1 
					},{
						text: 'Close',
						xtype: 'button',
						width: 80,
						handler: function() {
							this.ownerCt.ownerCt.close();
						}
					}]
				}]
			}).show();
    },
    openKeyManager: function( menuItem, e ) {
			Ext.create('Ext.window.Window', {
				title: 'Access Key Management',
				iconCls: 'icon_key',
				modal: true,
				resizeable: false,
				height: 500,
				width: 800,
				layout: 'fit',
				bodyBorder: false,
				items: [{
                    xtype: 'keymanagerpanel',
                    border: false
				}],
				dockedItems: [{
					xtype: 'toolbar',
					dock: 'bottom',
					ui: 'footer',
					items: [{ 
						xtype: 'component', flex: 1 
					},{
						text: 'Close',
						xtype: 'button',
						width: 80,
						handler: function() {
							this.ownerCt.ownerCt.close();
						}
					}]
				}]
			}).show();
    },
    openEvernoteSettings: function( menuItem, e ) {
			Ext.create('Ext.window.Window', {
				title: 'Evernote Account Management',
				iconCls: 'icon_evernote',
				modal: true,
				resizeable: false,
				height: 500,
				width: 800,
				layout: 'fit',
				bodyBorder: false,
				items: [{
                    xtype: 'evernotesettingspanel' 
				}],
				dockedItems: [{
					xtype: 'toolbar',
					dock: 'bottom',
					ui: 'footer',
					items: [{ 
						xtype: 'component', flex: 1 
					},{
						text: 'Close',
						xtype: 'button',
						width: 80,
						handler: function() {
							this.ownerCt.ownerCt.close();
						}
					}]
				}]
			}).show();
    },
    openServerInfo: function( menuItem, e ) {
			Ext.create('Ext.window.Window', {
				title: 'Server Information',
				iconCls: 'icon_info',
				modal: true,
				resizeable: false,
				height: 400,
				width: 600,
				layout: 'fit',
				items: [{
					xtype: 'panel',
					border: false,
					tpl: new Ext.XTemplate('<div>Server Info</div>')
				}],
				dockedItems: [{
					xtype: 'toolbar',
					dock: 'bottom',
					ui: 'footer',
					items: [{ 
						xtype: 'component', flex: 1 
					},{
						text: 'Close',
						xtype: 'button',
						width: 80,
						handler: function() {
							this.ownerCt.ownerCt.close();
						}
					}]
				}]
			}).show();
    },
    logout: function() {
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'userLogout'
            },
            callback: function() {
                window.location.reload();
            }
        });
    }


});
