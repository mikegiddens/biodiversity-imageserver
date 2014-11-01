Ext.define('BIS.view.FormCreateDevice', {
    extend: 'Ext.form.Panel',
    alias: ['widget.formcreatedevice'],
    id: 'createDevicePanel',
    layout: 'anchor',
    bodyPadding: 10,
    labelWidth: 80,
    border: false,
    bodyBody: false,
    defaults: {
        labelAlign: 'right',
        anchor: '100%'
    },
    defaultType: 'textfield',
    initComponent: function() {
        var me = this;
        Ext.applyIf(me, {
            items: [{
                fieldLabel: 'Identifier',
                name: 'storageDeviceId',
                readOnly: true,
                fieldCls: 'x-item-disabled',
                hidden: (this.mode == 'add')
            },{
                fieldLabel: 'Name',
                name: 'name'
            },{
                fieldLabel: 'Description',
                name: 'description'
            },{
                fieldLabel: 'Type',
                name: 'type'
            },{
                fieldLabel: 'Base URL',
                name: 'baseUrl'
            },{
                fieldLabel: 'Base Path',
                name: 'basePath'
            },{
                fieldLabel: 'Import Method',
                name: 'method'
            },{
                fieldLabel: 'Reference Path',
                name: 'referencePath'
            },{
                fieldLabel: 'Username',
                name: 'userName'
            },{
                fieldLabel: 'Password',
                name: 'password'
            },{
                xtype: 'checkbox',
                fieldLabel: 'Active?',
                name: 'active'
            },{
                fieldLabel: 'Notes',
                name: 'extra2'
            },{
                xtype: 'hiddenfield',
                name: 'cmd',
                value: ( this.mode == 'add' ) ? 'storageDeviceAdd' : 'storageDeviceUpdate'
            }],
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                ui: 'footer',
                items: [{
									width: 80,
									text: ( this.mode == 'add' ) ? 'Add Device' : 'Update Settings',
									iconCls: 'icon_saveDevice',
									scope: this,
									handler: this.submitForm
								}, '->', {
									width: 80,
									text: 'Cancel',
									scope: this,
									handler: this.cancel
								}]
            }],
            listeners: {
                afterrender: function() {
                    if ( this.device ) {
                        this.loadRecord( this.device );
                    }
                }
            }
        });
        me.callParent(arguments);
    },
    submitForm: function() {
        var me = this;
        var form = this.getForm();
        form.url = Config.baseUrl + 'resources/api/api.php';
        if ( form.isValid() ) {
            form.submit({
                scope: this,
                success: function(form, action) {
                    me.ownerCt.fireEvent( 'deviceCreated', Ext.decode(action.response.responseText) );
                },
                failure: function(form, action) {
                    var res = Ext.decode(action.response.responseText);
                    Ext.Msg.alert('Failed', 'Unable to create device. '+res.error.msg);
                }
            });
        }
    },
    cancel: function() {
        this.ownerCt.fireEvent( 'cancel' );
    }
});
