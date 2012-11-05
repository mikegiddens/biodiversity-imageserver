Ext.define('BIS.view.FormCreateGeography', {
	extend: 'Ext.form.FormPanel',
	alias: ['widget.formcreategeography'],

	id: 'formCreateGeography',
	bodyPadding: 10,

	initComponent: function() {
        var me = this;
        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'textfield',
                    name: 'value',
                    id: 'addGeoValueField',
                    focus: true,
                    fieldLabel: 'Name',
                    labelAlign: 'right',
                    allowBlank: false,
                    anchor: '100%',
                    scope: me
                },
                {
                    xtype: ( me.record ) ? 'hiddenfield' : 'textfield',
                    id: 'addGeoIsoField',
                    name: 'iso',
                    focus: true,
                    fieldLabel: 'Country ISO',
                    maxLength: 3,
                    labelAlign: 'right',
                    allowBlank: false,
                    anchor: '100%',
                    scope: me
                }
            ],
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                ui: 'footer',
                items: [
                    {
                        text: ( this.mode == 'add' ) ? 'Add' : 'Update',
                        id: 'geographySubmitButton',
                        scope: this,
                        handler: this.submitForm
                    },
                    '->',
                    {
                        text: 'Cancel',
                        scope: this,
                        handler: this.cancel
                    }
                ]
            }]
        });
        me.callParent(arguments);
	},
    scope: this,
	listeners: {
		afterrender: function() {
            if ( this.record ) {
                Ext.getCmp('formCreateGeography').loadRecord( this.record );
            }
		}
	},
	
	submitForm: function() {
        var me = this;
        Ext.getCmp('geographySubmitButton').setText('Working...').disable();

        var getIso = function( node, callback ) {
            var next = function( nextNode ) {
                if ( nextNode.parentNode.isRoot() ) {
                    callback( nextNode.get('iso') );
                } else {
                    next( nextNode.parentNode );
                }
            }
            next( node );
        }

        var iso;
        if ( me.record ) {
            getIso( me.record, function( val ) { iso = val } );
        } else {
            iso = Ext.getCmp('addGeoIsoField').getValue()
        }
        
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: ( me.mode == 'add' ) ? 'geographyAdd' : 'geographyUpdate',
                // send as normal params. geo object doesn't seem to be working any more
                //geo: JSON.stringify({
                    parentId: ( me.record ) ? me.record.get('geographyId') : 0,
                    name: Ext.getCmp('addGeoValueField').getValue(),
                    iso: iso
                //    })
            },
            callback: function( ajax, success, res ) {
                var data = Ext.decode( res.responseText );
                if ( data.success ) {
                    // emit parent id so extjs can reload the store node
                    me.ownerCt.fireEvent( 'done', ((me.record)?me.record.get('geographyId'):0) );
                } else {
                    Ext.getCmp('geographySubmitButton').setText( ( me.mode == 'add' ) ? 'Add' : 'Update' ).enable();
                    Ext.Msg.alert( 'Failed', data.error.msg );
                }
            }
        });
	},
    cancel: function() {
        this.ownerCt.fireEvent('cancel');
    }

});
