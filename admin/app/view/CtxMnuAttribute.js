Ext.define('BIS.view.CtxMnuAttribute', {
    extend: 'Ext.menu.Menu',
   // requires: [ 'BIS.view.FilterContextMenu', 'BIS.view.ObjectContextMenu' ],
    scope: this,
    listeners: {
        click: function( menu, item ) {
            switch( item.identifier ) {
                case 'query':
                    this.advFilter.children[0].condition = '=';
                    Ext.getCmp('imagesGrid').setAdvancedFilter( this.advFilter );
                    Ext.getCmp('id_clearFilter').enable();
                    Ext.getCmp('id_clearFilter').disabled = false;
                    var store = Ext.StoreManager.lookup('FilterTreeStore');
                    store.setRootNode( this.appendChild );
                    store.getRootNode().expand( true );
                    break;
                case 'queryInverse':
                    this.advFilter.children[0].condition = '!=';
                    Ext.getCmp('imagesGrid').setAdvancedFilter( this.advFilter );
                    Ext.getCmp('id_clearFilter').enable();
                    Ext.getCmp('id_clearFilter').disabled = false;
                    var store = Ext.StoreManager.lookup('FilterTreeStore');
                    store.setRootNode( this.appendChild );
                    store.getRootNode().expand( true );
                    break;
                case 'update':
                    this.update();
                    break;
                case 'delete':
                    Ext.Msg.confirm('Remove ' + this.record.data.title + '?', 'Are you sure you want remove ' + this.record.data.title + '?', function( btn, nothing, item ) {
                        if ( btn == 'yes' ) this.remove();
                    }, this);
                    break;
                case 'appendWithValue':
                    this.advFilter.children[0].condition = '=';
                    testingFilter.push(this.advFilter);
                    appendChildTreeFilter.push(this.appendChild);
                    Ext.getCmp('imagesGrid').setAdvancedFilter( testingFilter);
                    break
                case'appendWithOutValue':
                    this.advFilter.children[0].condition = '!=';
                    testingFilter.push(this.advFilter);
                    appendChildTreeFilter.push(this.appendChild);
                    Ext.getCmp('imagesGrid').setAdvancedFilter( this.advFilter );
                    break
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
                    value: this.record.get('attributeId'),
                    valueText: this.record.get('name'),
                    value2: null,
                    value2Text: '',
                    condition: '='
                }
            ]
        };
        this.appendChild = {
            node: 'group',
            logop: 'and',
            children: [
                {
                    node: 'condition',
                    object: 'attribute',
                    key: this.record.get('categoryId'),
                    keyText: this.record.parentNode.data.title,
                    value: this.record.get('attributeId'),
                    valueText: this.record.get('name'),
                    value2: null,
                    value2Text: '',
                    condition: '='
                }
            ]
        };



        this.addItem_selected = Ext.create('Ext.Action', {
            text: 'Selected',
            identifier: 'selected',
            disabled: true
        });

        this.addItem_filtered = Ext.create('Ext.Action', {
            text: 'Filter Results',
            identifier: 'filtered',
            disabled: true
        });

        this.removeItem_selected = Ext.create('Ext.Action', {
            disabled: true,
            text: 'Selected',
            identifier: 'remove_selected'
        });

        this.removeItem_filtered = Ext.create('Ext.Action', {
            text: 'Filter Results',
            identifier: 'remove_filtered',
            disabled: true
        });


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
               me.appendwithValue,
               me.appendwithOutValue,
                '-',
                {
                    text: 'Add attribute to',
                    iconCls: 'icon_attribute',
                    menu: {
                        xtype: 'menu',
                        scope: me,
                        listeners: {
                            scope: me,
                            click: me.handleAssignment
                        },
                        items: [
                           me.addItem_selected,
                           me.addItem_filtered,
                            {
                                text: 'All Images',
                                iconCls: 'icon_error',
                                identifier: 'all'
                            }
                        ]
                    }
                },
                {
                    text: 'Remove attribute from',
                    iconCls: 'icon_attribute',
                    menu: {
                        xtype: 'menu',
                        scope: me,
                        listeners: {
                            scope: me,
                            click: me.removeAttribute
                        },
                        items: [
                          me.removeItem_selected,
                          me.removeItem_filtered,
                            {
                                text: 'All Images',
                                iconCls: 'icon_error',
                                identifier: 'remove_all'
                            }
                        ]
                    }
                },
                '-',
                {
                    text: 'Edit Attribute',
                    iconCls: 'icon_editAttribute',
                    identifier: 'update'
                },
                {
                    text: ' Remove Attribute',
                    iconCls: 'icon_removeAttribute',
                    identifier: 'delete'
                }
            ]
        });
        me.callParent(arguments);
    },

    handleAssignment: function( menu, item ) {
        var imagesAffected = '(n/a)';
        var filteredImagesId = [];
        var icon = Ext.MessageBox.QUESTION;
        var text_color = 'normal_text';
        var params = {
            cmd: 'imageAddAttribute',
            category: this.record.get('categoryId'),
            attribType: 'attributeId',
            attribute: this.record.get('attributeId')
        }
        switch ( item.identifier ) {
            case 'selected':
                Ext.each( Ext.getCmp('imagesGrid').getSelectionModel().getSelection(), function( image ) { filteredImagesId.push( image.get('imageId') ) });
                imagesAffected = filteredImagesId.length;
                params.imageId = JSON.stringify( filteredImagesId );
                break;
            case 'filtered':
                filteredImagesId  =  Ext.getCmp('imagesGrid').getStore().collect( 'imageId', false, false );
                imagesAffected = filteredImagesId.length;
                params.imageId = JSON.stringify( filteredImagesId );
               /* params.imageId = Ext.encode( Ext.getCmp('imagesGrid').getStore().collect( 'imageId', false, false ) );
                imagesAffected = Ext.getCmp('imagesGrid').getStore().totalCount;*/
                break;
            case 'all':
                params.advFilter = JSON.stringify({ node: "group", logop: "and", children: [] }); // global filter
                imagesAffected = 'all';
                icon = Ext.MessageBox.WARNING;
                text_color = 'delete_all_text';
                break;
        }
        Ext.MessageBox.show({
            title: 'Add Attribute to Images',
            msg:'<span class='+text_color +'> Are you sure you want to add "' + this.record.get('name') + '" to <span style="font-weight:bold">' + imagesAffected + '</span> images? </span>',
            buttons: Ext.MessageBox.YESNO,
            icon: icon ,
            fn:   function( btn, text, opts ) {
                if ( btn == 'yes' ) {
                    Ext.Ajax.request({
                        url: Config.baseUrl + 'resources/api/api.php',
                        params: params,
                        scope: this,
                        success: function( data ) {
                            data = Ext.decode( data.responseText );
                            console.log( data );
                            if ( data.success ) {
                                // reload image details panel
                                var detailsPanel = Ext.getCmp('imageDetailsPanel');
                                detailsPanel.loadImages( detailsPanel.images );
                            }
                        }
                    });
                }
            }
        });
    },

    removeAttribute: function( menu, item ){
        var imagesAffected = '(n/a)';
        var filteredImagesId = [];
        var icon = Ext.MessageBox.QUESTION;
        var text_color = 'normal_text';
        var params = {
            cmd: 'imageDeleteAttribute',
            attribType: 'attributeId',
            attribute: this.record.get('attributeId')
        }
        switch ( item.identifier ) {
            case 'remove_selected':
                Ext.each( Ext.getCmp('imagesGrid').getSelectionModel().getSelection(), function( image ) { filteredImagesId.push( image.get('imageId') ) });
                imagesAffected = filteredImagesId.length;
                params.imageId = JSON.stringify( filteredImagesId );
                break;
            case 'remove_filtered':
                filteredImagesId  =  Ext.getCmp('imagesGrid').getStore().collect( 'imageId', false, false );
                imagesAffected = filteredImagesId.length;
                params.imageId = JSON.stringify( filteredImagesId );
                break;
            case 'remove_all':
                params.advFilter = JSON.stringify({ node: "group", logop: "and", children: [] }); // global filter
                imagesAffected = 'all';
                icon = Ext.MessageBox.WARNING;
                text_color = 'delete_all_text';
                break;
        }
        Ext.MessageBox.show({
            title: 'Delete Attribute from Images',
            msg: '<span class='+text_color +'> Are you sure you want to delete "' + this.record.get('name') + '" from <span style="font-weight:bold">' + imagesAffected + '</span> images?</span>',
            buttons: Ext.MessageBox.YESNO,
            icon: icon ,
            fn:   function( btn, text, opts ) {
                if ( btn == 'yes' ) {
                    Ext.Ajax.request({
                        url: Config.baseUrl + 'resources/api/api.php',
                        params: params,
                        scope: this,
                        success: function( data ) {
                            data = Ext.decode( data.responseText );
                            console.log( data );
                            if ( data.success ) {
                                // reload image details panel
                                var detailsPanel = Ext.getCmp('imageDetailsPanel');
                                detailsPanel.loadImages( detailsPanel.images );
                            }
                        }
                    });
                }
            }
        });
    },


    remove: function() {
        Ext.Ajax.request({
            method: 'POST',
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'attributeDelete',
                attributeId: this.record.data.attributeId
            },
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                if ( res.success ) {
                    /*
                    Ext.getCmp('categoryTreePanel').getStore().load({
                        node: this.record.parentNode
                    });
                    */
                    this.record.remove();
                }
            }
        });
    },
    update: function() {
        var me = this;
        var tmpWindow = Ext.create('Ext.window.Window', {
            title: 'Edit Attribute ' + this.record.data.title,
            iconCls: 'icon_editAttribute',
            modal: true,
            resizable: false,
            height: 100,
            width: 350,
            layout: 'fit',
            items: [
                Ext.create('widget.formcreateattribute', {
                    border: false,
                    record: this.record,
                    mode: 'edit'
                })
            ]
        }).show();
        tmpWindow.on( 'attributeCreated', function( data ) {
            tmpWindow.close();
            Ext.getCmp('categoryTreePanel').getStore().load({
                node: me.record.parentNode
            });
        });
        tmpWindow.on( 'cancel', function( data ) {
            tmpWindow.close();
        });
    }
});
