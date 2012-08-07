Ext.define('BIS.view.FormCreateDevice', {
    extend: 'Ext.panel.Panel',
    alias: ['widget.formcreatedevice'],

    id: 'createDevicePanel',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'form',
                    id: 'formCreateDevice',
                    bodyPadding: 10,
                    items: [
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Identifier',
                            labelAlign: 'right',
                            anchor: '100%',
                            name: 'storage_id'
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Name',
                            labelAlign: 'right',
                            anchor: '100%',
                            name: 'name'
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Description',
                            labelAlign: 'right',
                            anchor: '100%',
                            name: 'description'
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Type',
                            labelAlign: 'right',
                            anchor: '100%',
                            name: 'type'
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Base URL',
                            labelAlign: 'right',
                            anchor: '100%',
                            name: 'baseUrl'
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Base Path',
                            labelAlign: 'right',
                            anchor: '100%',
                            name: 'basePath'
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Username',
                            labelAlign: 'right',
                            anchor: '100%',
                            name: 'user'
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Password',
                            labelAlign: 'right',
                            anchor: '100%',
                            name: 'pw'
                        },
                        {
                            xtype: 'checkbox',
                            fieldLabel: 'Active?',
                            labelAlign: 'right',
                            anchor: '100%',
                            name: 'active'
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Notes',
                            labelAlign: 'right',
                            anchor: '100%',
                            name: 'extra2'
                        }

                    ]
                },
                {
                    xtype: 'button',
                    text: ( this.device ) ? 'Update Settings' : 'Add Device',
                    handler: this.submit
                }
            ]
        });

        me.callParent(arguments);
    },
    listeners: {
        afterrender: function() {
            if ( this.device ) {
                Ext.getCmp('formCreateDevice').loadRecord( this.device );
            }
        }
    },
    submit: function() {
        // required fields: name, type, baseUrl
        var values = Ext.getCmp('formCreateDevice').getValues();
        var route = ( this.device ) ? 'updateDevice!!!!!!!!' : 'addStorageDevice';
        Ext.Ajax.request({
            method: 'POST',
            url: Config.baseUrl + route,
            params: values,
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                console.log( res );
                if ( res.success ) {
                    
                }
            }
        });
    }
});
