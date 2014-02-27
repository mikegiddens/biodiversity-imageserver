Ext.define('BIS.view.CtxMnuCategory', {
    extend: 'Ext.menu.Menu',
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'query':
                    this.advFilter.children[0].condition = '=';
                    Ext.getCmp('imagesGrid').setAdvancedFilter( this.advFilter );
                    Ext.getCmp('id_clearFilter').enable();
                    Ext.getCmp('id_clearFilter').disabled = false;
                    testingFilter.push(this.advFilter);
                   // appendChildTreeFilter.push(this.advFilter);
                    var store = Ext.StoreManager.lookup('FilterTreeStore');
                    store.setRootNode( this.advFilter );
                    store.getRootNode().expand( true );
                    break;
                case 'queryInverse':
                    this.advFilter.children[0].condition = '!=';
                    Ext.getCmp('imagesGrid').setAdvancedFilter( this.advFilter );
                    Ext.getCmp('id_clearFilter').enable();
                    Ext.getCmp('id_clearFilter').disabled = false;
                    testingFilter.push(this.advFilter);
                   // appendChildTreeFilter.push(this.advFilter);
                    var store = Ext.StoreManager.lookup('FilterTreeStore');
                    store.setRootNode( this.advFilter );
                    store.getRootNode().expand( true );
                    break;
                case 'create':
                    var me = this;
                    var tmpWindow = Ext.create('Ext.window.Window', {
                        title: 'Add Attribute to ' + this.record.data.title,
                        iconCls: 'icon_newAttribute',
                        modal: true,
                        resizable: false,
                        height: 100,
                        width: 350,
                        layout: 'fit',
                        items: [
                            Ext.create('widget.formcreateattribute', {
                                border: false,
                                record: this.record,
                                mode: 'add'
                            })
                        ]
                    }).show();
                    tmpWindow.on('attributeCreated', function( data ) {
                        tmpWindow.close();
                        var store = Ext.getCmp('categoryTreePanel').getStore();
                        var node = store.getRootNode().findChild( 'categoryId', Number( data.categoryId ), true );
                        if ( node ) {
                            if ( node.isExpanded() ) {
                                store.load({
                                    node: node
                                });
                            } else {
                                node.expand();
                            }
                        }
                    });
                    tmpWindow.on( 'cancel', function( data ) {
                        tmpWindow.close();
                    });
                    break;
                case 'update':
                    var me = this;
                    var tmpWindow = Ext.create('Ext.window.Window', {
                        title: 'Edit ' + this.record.data.title,
                        iconCls: 'icon_editCategory',
                        resizable: false,
                        modal: true,
                        height: 250,
                        width: 500,
                        layout: 'fit',
                        items: [
                            Ext.create('widget.formcreatecategory', {
                                border: false,
                                record: this.record,
                                mode: 'edit'
                            })
                        ]
                    }).show();
                    tmpWindow.on('categoryCreated', function( data ) {
                        tmpWindow.close();
                        var store = Ext.getCmp('categoryTreePanel').getStore();
                        store.load({
                            node: this.record
                        });
                    });
                    tmpWindow.on( 'cancel', function( data ) {
                        tmpWindow.close();
                    });
                    break;
                case 'delete':
                    Ext.Msg.confirm('Remove Category', 'Are you sure you want remove "' + this.record.data.title + '"?', function( btn, nothing, item ) {
                        if ( btn == 'yes' ) this.remove();
                    }, this);
                    break;
                case 'appendWithValue':
                    this.advFilter.children[0].condition = '=';
                    testingFilter.push(this.advFilter);
                    appendChildTreeFilter.push(this.advFilter);
                    Ext.getCmp('imagesGrid').setAdvancedFilter( testingFilter);
                    break
                case'appendWithOutValue':
                    this.advFilter.children[0].condition = '!=';
                    testingFilter.push(this.advFilter);
                    appendChildTreeFilter.push(this.advFilter);
                    Ext.getCmp('imagesGrid').setAdvancedFilter( this.advFilter );
                    break
            }
        }
    },
    initComponent: function() {
        var me = this;
        this.appendwithValue = Ext.create('Ext.Action', {
            text: 'Append with value',
            iconCls:'icon_appendWith',
            identifier: 'appendWithValue',
            disabled: (testingFilter.length > 0) ? false : true
        });

        this.appendwithOutValue = Ext.create('Ext.Action', {
            text: 'Append without value',
            iconCls:'icon_appendWithOut',
            identifier: 'appendWithOutValue',
            disabled: (testingFilter.length > 0) ? false : true
        });
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
                '-',me.appendwithValue,me.appendwithOutValue,'-',
                {
                    text: 'Add Attribute',
                    iconCls: 'icon_newAttribute',
                    identifier: 'create'
                },
                '-',
                {
                    text: 'Edit Category',
                    iconCls: 'icon_editCategory',
                    identifier: 'update'
                },
                {
                    text: 'Remove Category',
                    iconCls: 'icon_removeCategory',
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
                cmd: 'categoryDelete',
                categoryId: this.record.data.categoryId
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
