Ext.define('BIS.view.UserManagerPanel', {
    extend: 'Ext.panel.Panel',
    alias: ['widget.usermanagerpanel'],

    id: 'userManagerPanel',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'tabpanel',
                    id: 'userManagerTabPanel',
                    activeTab: 1,
                    items: [
                        {
                            xtype: 'panel',
                            title: 'Add User',
                            items: [
                                {
                                    xtype: 'form',
                                    id: 'addUserForm',
                                    bodyPadding: 10,
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            name: 'deviceName',
                                            fieldLabel: 'Device',
                                            labelAlign: 'right',
                                            anchor: '100%'
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                            xtype: 'panel',
                            title: 'Users',
                            items: [
                                {
                                    xtype: 'gridpanel',
                                    id: 'usersGrid',
                                    store: 'UserStore',
                                    columns: [
                                        {
                                            xtype: 'booleancolumn',
                                            dataIndex: 'bool',
                                            text: 'First Name',
                                            falseText: 'No',
                                            trueText: 'Yes',
                                            undefinedText: 'n/a'
                                        }
                                    ],
                                    viewConfig: {

                                    }
                                }
                            ]
                        }
                    ]
                }
            ]
        });

        me.callParent(arguments);
    }

});
