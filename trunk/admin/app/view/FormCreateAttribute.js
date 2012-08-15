Ext.define('BIS.view.FormCreateAttribute', {
    extend: 'Ext.panel.Panel',
    alias: ['widget.formcreateattribute'],

    id: 'createAttributePanel',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'form',
                    id: 'formCreateAttribute',
                    bodyPadding: 10,
                    items: [
                        {
                            xtype: 'textfield',
                            name: 'value',
                            fieldLabel: 'Name',
                            labelAlign: 'right',
                            anchor: '100%'
                        },
                        {
                            xtype: 'textfield',
                            name: 'identifier',
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

        me.callParent(arguments);
    },
    listeners: {
        afterrender: function() {
            var form = Ext.getCmp('formCreateAttribute').getForm();
            if ( this.mode != 'add' ) {
                // edit
                form.setValues( {value:this.record.data.title,identifier:this.record.data.categoryId} );
            }
        }
    },
    submit: function() {
        var values = Ext.getCmp('formCreateAttribute').getValues();
        var route, params = { value: values.value };
        if ( this.mode == 'add' ) {
            params.categoryID = values.identifier;
            route = 'attributeAdd';
        } else {
            // edit
            params.valueID = values.identifier;
            route = 'attributeUpdate';
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
