Ext.define('BIS.view.FormCreateEventType', {
    extend: 'Ext.panel.Panel',
    alias: ['widget.formcreateeventtype'],

    id: 'createEventTypePanel',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'form',
                    id: 'formCreateEventType',
                    border: false,
                    bodyPadding: 10,
                    items: [
                        {
                            xtype: 'textfield',
                            name: 'title',
                            fieldLabel: 'Title',
                            labelAlign: 'right',
                            anchor: '100%'
                        },
                        {
                            xtype: 'textarea',
                            name: 'description',
                            fieldLabel: 'Description',
                            labelAlign: 'right',
                            anchor: '100%'
                        },
                        {
                            xtype: 'textfield',
                            name: 'eventTypeId',
                            fieldLabel: 'Identifier',
                            labelAlign: 'right',
                            anchor: '100%',
                            readOnly: true,
                            fieldCls: 'x-item-disabled',
                            hidden: this.mode == 'add'
                        }
                    ]
                },
                {
                    xtype: 'button',
                    text: ( this.mode == 'add' ) ? 'Add' : 'Update',
                    handler: this.submit
                }
            ]
        });

        me.callParent(arguments)
    },
    listeners: {
        afterrender: function() {
            if ( this.mode != 'add' ) {
                // edit
                Ext.getCmp('formCreateEventType').loadRecord( this.record );
            }
        }
    },
    submit: function() {
        var values = Ext.getCmp('formCreateEventType').getValues();
        var route;
        if ( this.mode == 'add' ) {
            route = 'eventTypeAdd';
        } else {
            // edit
            route = 'eventTypeUpdate';
        }
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
