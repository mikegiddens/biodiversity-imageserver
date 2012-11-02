Ext.define('BIS.view.FormCreateGeography', {
	extend: 'Ext.form.FormPanel',
	alias: ['widget.formcreategeography'],
	id: 'formCreateGeography',
	bodyPadding: 10,
	initComponent: function() {
        var me = this;
        Ext.applyIf(me, {
            geo: ( this.record || this.parentRec ) ? this.getGeo() : { condition: '=' },
            items: [
                {
                    xtype: 'textfield',
                    name: 'value',
                    focus: true,
                    fieldLabel: 'Name',
                    labelAlign: 'right',
                    allowBlank: false,
                    anchor: '100%',
                    scope: me,
                    listeners: {
                        change: function( field, newVal, oldVal, opts ) {
                            me.setCurGeo( newVal );
                        }
                    }
                },
                {
                    xtype: ( me.record || me.parentRec ) ? 'hiddenfield' : 'textfield',
                    id: 'addGeoIsoField',
                    name: 'iso',
                    focus: true,
                    fieldLabel: 'Country ISO',
                    maxLength: 3,
                    labelAlign: 'right',
                    allowBlank: false,
                    anchor: '100%',
                    scope: me,
                    listeners: {
                        change: function( field, newVal, oldVal, opts ) {
                            me.geo.ISO = newVal.toUpperCase();
                        }
                    }
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

    setCurGeo: function( val ) {
        var curRank = 0;
        if ( this.record ) {
            curRank = this.record.get('ref').split('_')[1];
        } else {
            if ( this.parentRec ) {
                curRank = ++this.parentRec.get('ref').split('_')[1];
            }
            // no need for else case, since a root level geo would be at rank 0
        }
        this.geo[ 'NAME_' + curRank ] = val;
    },

    getGeo: function() {
        var rec = this.record || this.parentRec;
        var curRank = rec.get('ref').split('_')[1];
        if ( !this.geo ) this.geo = { condition: '=' };
        for ( var i = curRank; i >= 0; i-- ) {
            console.log( 'rank', i );
            this.geo[ rec.get('ref') ] = rec.get('name');
            if ( rec.parentNode ) rec = rec.parentNode;
        }
        console.log( this.geo );
    },
	
	submitForm: function() {
        var me = this;
        Ext.getCmp('geographySubmitButton').setText('Working...').disable();

        var submit = function() {
            Ext.Ajax.request({
                url: Config.baseUrl + 'resources/api/api.php',
                params: {
                    cmd: ( me.mode == 'add' ) ? 'geographyAdd' : 'geographyUpdate',
                    geo: JSON.stringify( me.geo )
                },
                callback: function( ajax, success, res ) {
                    var data = Ext.decode( res.responseText );
                    if ( data.success ) {
                        me.ownerCt.fireEvent( 'done' );
                    } else {
                        Ext.getCmp('geographySubmitButton').setText( ( me.mode == 'add' ) ? 'Add' : 'Update' ).enable();
                        Ext.Msg.alert( 'Failed', data.error.msg );
                    }
                }
            });
        }

        if ( !me.geo.ISO ) {
            Ext.Ajax.request({
                url: Config.baseUrl + 'resources/api/api.php',
                params: {
                    cmd: 'geographyList',
                    advFilter: JSON.stringify({ node: 'condition', object: 'geography', key: 'NAME_0', value: me.geo.NAME_0, condition: '=' })
                },
                scope: me,
                callback: function( ajax, success, res ) {
                    var data = Ext.decode( res.responseText );
                    if ( data.success && data.records.length > 0 ) {
                        me.geo.ISO = data.records[0].ISO;
                        submit();
                    } else {
                        Ext.getCmp('geographySubmitButton').setText( ( me.mode == 'add' ) ? 'Add' : 'Update' ).enable();
                        Ext.Msg.alert( 'Failed', data.error.msg );
                    }
                }
            });
        } else {
            submit();
        }
	},
    cancel: function() {
        this.ownerCt.fireEvent('cancel');
    }

});
