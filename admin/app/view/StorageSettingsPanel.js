Ext.define('BIS.view.StorageSettingsPanel', {
    extend: 'Ext.panel.Panel',
    alias: ['widget.storagesettingspanel'],
    requires: ['BIS.view.FormCreateDevice'],
    id: 'storageSettingsPanel',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'tabpanel',
                    id: 'storageDevicesTabPanel',
                    activeTab: 0,
                    items: [
                        {
                            xtype: 'panel',
                            title: 'Devices',
                            items: [
                                {
                                    xtype: 'gridpanel',
                                    id: 'storageDevicesGrid',
                                    store: 'StorageDevicesStore',
                                    bodyPadding: 10,
                                    columns: [
                                        {
                                            text: 'Identifier',
                                            flex: 1,
                                            dataIndex: 'storage_id',
                                            sortable: true
                                        },
                                        {
                                            text: 'Name',
                                            flex: 2,
                                            dataIndex: 'name',
                                            sortable: true
                                        },
                                        {
                                            text: 'Description',
                                            flex: 2,
                                            dataIndex: 'description',
                                            sortable: true
                                        },
                                        {
                                            text: 'Type',
                                            flex: 1,
                                            dataIndex: 'type',
                                            sortable: true
                                        },
                                        {
                                            text: 'Base URL',
                                            flex: 2,
                                            dataIndex: 'baseUrl',
                                            sortable: true
                                        },
                                        {
                                            text: 'Base Path',
                                            flex: 2,
                                            dataIndex: 'basePath',
                                            sortable: true
                                        },
                                        {
                                            text: 'Username',
                                            flex: 1,
                                            dataIndex: 'user',
                                            sortable: true
                                        },
                                        {
                                            text: 'Password',
                                            flex: 1,
                                            dataIndex: 'pw',
                                            sortable: true
                                        },
                                        {
                                            text: 'Active?',
                                            flex: 1,
                                            dataIndex: 'active',
                                            sortable: true,
                                            renderer: function( value ) {
                                                if ( value ) { return 'Yes' }
                                                return ' ';
                                            }
                                        },
                                        {
                                            text: 'Notes',
                                            flex: 1,
                                            dataIndex: 'extra2',
                                            sortable: true
                                        }
                                    ],
                                    listeners: {
                                        itemdblclick: function( grid, record, el, ind, e, opts ) {
                                            Ext.create('Ext.window.Window', {
                                                title: 'Edit Storage Device',
                                                modal: true,
                                                height: 500,
                                                width: 800,
                                                layout: 'fit',
                                                items: [
                                                    { xtype: 'formcreatedevice', device: record }
                                                ]
                                            }).show();
                                        }
                                    }
                                }
                            ]
                        },
                        {
                            xtype: 'panel',
                            title: 'Settings 2'
                        },
                        {
                            xtype: 'panel',
                            title: 'Settings 3'
                        }
                    ],
                    dockedItems: [
                        {
                            xtype: 'toolbar',
                            dock: 'top',
                            items: [
                                {
                                    text: 'Add Device',
                                    scope: this,
                                    handler: this.createDevice
                                }
                            ]
                        }
                    ]
                }
            ]
        });

        me.callParent(arguments);
    },
    createDevice: function() {
        Ext.create('Ext.window.Window', {
            title: 'Add Storage Device',
            modal: true,
            height: 500,
            width: 800,
            layout: 'fit',
            items: [
                { xtype: 'formcreatedevice' } 
            ]
        }).show();
    }

});
