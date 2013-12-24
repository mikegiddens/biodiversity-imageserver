Ext.define('BIS.view.StorageSettingsPanel', {
    extend: 'Ext.panel.Panel',
    alias: ['widget.storagesettingspanel'],
    requires: ['BIS.view.FormCreateDevice'],
    id: 'storageSettingsPanel',
    border: false,
    layout: 'fit',
    initComponent: function() {
        var me = this;
        Ext.applyIf(me, {
            items: [{
                xtype: 'tabpanel',
                id: 'storageDevicesTabPanel',
                border: false,
                activeTab: 0,
                items: [{
                    iconCls: 'icon_devices',
                    title: 'Devices',
                    xtype: 'gridpanel',
                    id: 'storageDevicesGrid',
                    bodyBorder: false,
                    border: false,
                    store: 'StorageDevicesStore',
                    defaults: {
                        sortable: true,
                        flex: 1
                    },
                    listeners: {
                        itemdblclick: function( grid, record, el, ind, e, opts ) {
                            var tmpWindow = Ext.create('Ext.window.Window', {
                                title: 'Edit Storage Device',
                                iconCls: 'icon_editDevice',
                                modal: true,
                                height: 340,
                                width: 700,
                                layout: 'fit',
                                items: [{
                                    xtype: 'formcreatedevice',
                                    device: record,
                                    mode: 'edit'
                                }]
                            });
                            tmpWindow.on('deviceCreated', function( data ) {
                                Ext.getCmp('storageDevicesGrid').getStore().load();
                                tmpWindow.close();
                            });
                            tmpWindow.on('cancel', function( data ) {
                                tmpWindow.close();
                            });
                            tmpWindow.show();
                        },
                        itemcontextmenu: function(view, record, item, index, e) {
                            e.stopEvent();
                            var tmpCtx = Ext.create('BIS.view.CtxMnuDevice', {record: record});
                            tmpCtx.showAt( e.getXY() );
                        }
                    },
                    columns: [{
                        text: 'Name',
                        //flex: 2,
                        dataIndex: 'name',
                        width: 100
                    },{
                        text: 'Type',
                        dataIndex: 'type',
                        width: 80
                        // flex: 2
                    },{
                        text: 'Base URL',
                        //flex: 3,
                        width: 350,
                        dataIndex: 'baseUrl'
                    },{
                        text: 'Base Path',
                        //	flex: 3,
                        width: 350,
                        dataIndex: 'basePath'
                    },{
                        text: 'Username',
                        dataIndex: 'userName',
                        width: 100
                        //   flex: 2
                    },{
                        text: 'Password',
                        dataIndex: 'password',
                        width: 100
                        //  flex: 2
                    },{
                        text: 'Active?',
                        width: 100,
                        dataIndex: 'active',
                        renderer: function( value ) {
                            if ( value ) { return 'Yes' }
                            return ' ';
                        }
                        //  flex: 1
                    },{
                        text: 'Default?',
                        width: 100,
                        dataIndex: 'defaultStorage',
                        renderer: function( value ) {
                            if ( value == '1' ) { return 'Yes' }
                            return ' ';
                        }
                        //   flex: 1
                    }]
                }],
                dockedItems: [{
                    xtype: 'toolbar',
                    dock: 'top',
                    items: [{
                        text: 'Add Device',
                        iconCls: 'icon_addDevice',
                        handler: this.createDevice
                    }]
                }]
            }]
        });

        me.callParent(arguments);
    },
    createDevice: function() {
        var tmpWindow = Ext.create('Ext.window.Window', {
            title: 'Add Storage Device',
            iconCls: 'icon_addDevice',
            modal: true,
            height: 320,
            width: 700,
            layout: 'fit',
            resizable: false,
            bodyBorder: false,
            items: [{
                xtype: 'formcreatedevice',
                mode: 'add'
            }]
        });
        tmpWindow.on('deviceCreated', function( data ) {
            Ext.getCmp('storageDevicesGrid').getStore().load();
            tmpWindow.close();
        });
        tmpWindow.on('cancel', function( data ) {
            tmpWindow.close();
        });
        tmpWindow.show();
    }
});
