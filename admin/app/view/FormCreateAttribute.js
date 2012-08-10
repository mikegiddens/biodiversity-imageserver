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
                            fieldCls: 'x-item-disabled'
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
            if ( this.mode == 'add' ) {
                form.setValues( {identifier:this.record.data.typeID} );
            } else {
                // edit
                form.setValues( {value:this.record.data.title,identifier:this.record.data.valueID} );
            }
        }
    },
    submit: function() {
        var values = Ext.getCmp('formCreateAttribute').getValues();
        var route, params = { value: values.value };
        if ( this.mode == 'add' ) {
            params.categoryID = values.identifier;
            route = 'add_attribute';
        } else {
            // edit
            params.valueID = values.identifier;
            route = 'rename_attribute';
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
