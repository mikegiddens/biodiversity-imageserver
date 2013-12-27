Ext.define('BIS.view.CtxMnuNamespace', {
    extend: 'Ext.menu.Menu',
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
                        title: 'Add Category',
                        iconCls: 'icon_newCategory',
                        modal: true,
                        resizable: false,
                        height: 250,
                        width: 500,
                        layout: 'fit',
                        items: [
                            Ext.create('widget.formcreatecategory', {
                                border: false,
                                initNamespace: me.record.get('title'),
                                mode: 'add'
                            })
                        ]
                    }).show();
                    tmpWindow.on('categoryCreated', function( data ) {
                        tmpWindow.close();
                        var store = Ext.getCmp('categoryTreePanel').getStore();
                        store.load({
                            node: store.getRootNode()
                        });
                    });
                    tmpWindow.on( 'cancel', function( data ) {
                        tmpWindow.close();
                    });
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
                    object: 'attribute',
                    key: this.record.get('categoryId'),
                    keyText: this.record.get('title'),
                    value: null,
                    valueText: '',
                    value2: null,
                    value2Text: '',
                    condition: '='
                }
            ]
        }

        Ext.applyIf(me, {
            items: [
                /*
                {
                    text: 'Find with ' + this.record.get('title'),
                    iconCls: 'icon_find',
                    identifier: 'query'
                },
                {
                    text: 'Find without ' + this.record.get('title'),
                    iconCls: 'icon_find',
                    identifier: 'queryInverse'
                },
                '-',*/
                {
                    text: 'Add Category',
                    iconCls: 'icon_newCategory',
                    identifier: 'create'
                }
            ]
        });
        me.callParent(arguments);
    }

});
