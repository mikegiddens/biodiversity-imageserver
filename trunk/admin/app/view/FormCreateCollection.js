Ext.define('BIS.view.FormCreateCollection', {
    extend: 'Ext.panel.Panel',
    alias: ['widget.formcreatecollection'],

    id: 'createCollectionPanel',
    border: false,
    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'form',
                    border: false,
                    id: 'formCreateCollection',
                    bodyPadding: 10,
                    items: [
                        {
                            xtype: 'textfield',
                            name: 'name',
                            fieldLabel: 'Name',
                            labelAlign: 'right',
                            anchor: '100%'
                        },
                        {
                            xtype: 'textfield',
                            name: 'collectionCode',
                            fieldLabel: 'Code',
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

        me.callParent(arguments);
    },
    listeners: {
        afterrender: function() {
            if ( this.mode != 'add' ) {
                // edit
                Ext.getCmp('formCreateCollection').getForm().setValues({
                    name: this.record.data.name,
                    collectionCode: this.record.data.code
                });
            }
        }
    },
    submit: function() {
        var values = Ext.getCmp('formCreateCollection').getValues();
        var route;
        if ( this.mode == 'add' ) {
            route = 'addCollection';
        } else {
            // edit
            route = 'renameCollection';
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
