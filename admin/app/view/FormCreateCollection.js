Ext.define('BIS.view.FormCreateCollection', {
    extend: 'Ext.panel.Panel',
    alias: ['widget.formcreatecollection'],

    id: 'createCollectionPanel',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'form',
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
                    text: 'Create',
                    handler: this.submit
                }
            ]
        });

        me.callParent(arguments);
    },
    submit: function() {
        var values = Ext.getCmp('formCreateCollection').getValues();
        Ext.Ajax.request({
            method: 'POST',
            url: Config.baseUrl + 'addCollection',
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
