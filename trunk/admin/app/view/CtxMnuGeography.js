Ext.define('BIS.view.CtxMnuGeography', {
    extend: 'Ext.menu.Menu',
    requires: [ 'BIS.view.FormCreateGeography' ],
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'query':
                    this.advFilter.children[0].condition = '=';
                    Ext.getCmp('imagesGrid').setAdvancedFilter( this.advFilter );
                    break;
                case 'queryInverse':
                    this.advFilter.children[0].condition = '!=';
                    Ext.getCmp('imagesGrid').setAdvancedFilter( this.advFilter );
                    break;
                case 'create':
                    var me = this;
                    var tmpWindow = Ext.create('Ext.window.Window', {
                        title: 'Add Geography to ' + this.record.get('name'),
                        iconCls: 'icon_newGeography',
                        modal: true,
                        resizable: false,
                        height: 175,
                        width: 350,
                        layout: 'fit',
                        items: [{
                            xtype: 'formcreategeography',
                            border: false,
                            mode: 'add',
                            record: this.record
                        }]
                    }).show();
                    tmpWindow.on('done', function( data ) {
                        tmpWindow.close();
                        var store = Ext.getCmp('geographyTreePanel').getStore();
                        store.getProxy().extraParams.parentId = data;
                        store.load({
                            node: me.record,
                            callback: function() { if ( !me.record.isExpanded() ) me.record.expand() }
                        });
                    });
                    tmpWindow.on( 'cancel', function( data ) {
                        tmpWindow.close();
                    });
                    break;
                case 'delete':
                    Ext.Msg.confirm('Remove Geography', 'Are you sure you want remove "' + this.record.get('name') + '"?', function( btn, nothing, item ) {
                        if ( btn == 'yes' ) this.remove();
                    }, this);
                    break;
            }
        }
    },
    initComponent: function() {
        var me = this;

        this.advFilter = {
            node: 'group',
            logop: 'and',
            children: [
                {
                    node: 'condition',
                    object: 'geography',
                    key: 'geographyId',
                    keyText: 'Geography Id',
                    value: this.record.get('geographyId'),
                    valueText: this.record.get('name'),
                    value2: null,
                    value2Text: '',
                    condition: '='
                }
            ]
        }

        Ext.applyIf(me, {
            items: [
                {
                    text: 'Find with ' + this.record.get('name'),
                    iconCls: 'icon_find',
                    identifier: 'query'
                },
                {
                    text: 'Find without ' + this.record.get('name'),
                    iconCls: 'icon_find',
                    identifier: 'queryInverse'
                },
                '-',
                {
                    text: 'Add Geography to ' + this.record.get('name'),
                    iconCls: 'icon_newGeography',
                    identifier: 'create'
                },
                {
                    text: 'Remove ' + this.record.get('name'),
                    iconCls: 'icon_removeGeography',
                    disabled: this.record.get('source') != 'user',
                    identifier: 'delete'
                }
            ]
        });
        me.callParent(arguments);
    },
    remove: function() {
        var me = this;
        Ext.Ajax.request({
            method: 'POST',
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'geographyDelete',
                geographyId: this.record.get('geographyId')
            },
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                if ( res.success ) {
                    me.record.remove();
                }
            },
            failure: function( form, action ) {
                Ext.Msg.alert('Failed', 'Request failed.');
            }
        });
    }

});
