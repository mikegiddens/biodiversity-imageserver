Ext.define('BIS.view.FormCreateCategory', {
    extend: 'Ext.panel.Panel',
    alias: ['widget.formcreatecategory'],

    id: 'createCategoryPanel',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'form',
                    id: 'formCreateCategory',
                    bodyPadding: 10,
                    items: [
                        {
                            xtype: 'textfield',
                            name: 'value',
                            fieldLabel: 'Name',
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
        var values = Ext.getCmp('formCreateCategory').getValues();
        Ext.Ajax.request({
            method: 'POST',
            url: Config.baseUrl + 'add_category',
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
