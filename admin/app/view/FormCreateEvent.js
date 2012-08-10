Ext.define('BIS.view.FormCreateEvent', {
    extend: 'Ext.panel.Panel',
    alias: ['widget.formcreateevent'],

    id: 'createEventPanel',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'form',
                    id: 'formCreateEvent',
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
                Ext.getCmp('formCreateEvent').loadRecord( this.record );
            }
        }
    },
    submit: function() {
        var values = Ext.getCmp('formCreateEvent').getValues();
        var route, params = { title: values.title, description: values.description };
        if ( this.mode == 'add' ) {
            route = 'addEvent';
        } else {
            // edit
            route = 'renameEvent';
        }
        Ext.Ajax.request({
            method: 'POST',
            url: Config.baseUrl + route,
            params: params,
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
