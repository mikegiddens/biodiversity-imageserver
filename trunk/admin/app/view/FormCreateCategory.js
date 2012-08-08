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
    listeners: {
        afterrender: function() {
            if ( this.mode != 'add' ) {
                // edit
                Ext.getCmp('formCreateCategory').loadRecord( this.record );
            }
        }
    },
    submit: function() {
        var values = Ext.getCmp('formCreateCategory').getValues();
        var route;
        if ( this.mode == 'add' ) {
            route = 'add_category';
        } else {
            // edit
            route = 'rename_category';
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
