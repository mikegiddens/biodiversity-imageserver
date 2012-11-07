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
                    text: 'Add to',
                    iconCls: 'icon_',
                    menu: {
                        xtype: 'menu',
                        scope: me,
                        listeners: {
                            scope: me,
                            click: me.handleAssignment
                        },
                        items: [
                            {
                                text: 'Selected',
                                identifier: 'selected'
                            },
                            {
                                text: 'Filter Results',
                                identifier: 'filtered'
                            },
                            {
                                text: 'All Images',
                                identifier: 'all'
                            }
                        ]
                    }
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

    handleAssignment: function( menu, item ) {
        var me = this;
        // first check the client-side category store and see if the category exists
        // if not, chain two requests generating the category and then creating the attribute
        var catStore = Ext.StoreManager.lookup('CategoriesStore');
        var rankToText = {
            0: 'Country',
            1: 'StateProvince',
            2: 'County',
            3: 'Locality',
            4: 'Sub-Locality'
        };
        var category = catStore.find( 'title', rankToText[ this.record.getDepth() - 1 ] );

        var addAttributeToCategory = function() {
            Ext.Ajax.request({
                url: Config.baseUrl + 'resources/api/api.php',
                params: {
                    cmd: 'attributeAdd',
                    categoryId: category.data.categoryId,
                    name: me.record.get('name')
                },
                scope: this,
                success: function( data ) {
                    data = Ext.decode( data.responseText );
                    if ( data.success ) {
                        assignToImages( data.attributeId );
                    } else {
                        Ext.Msg.alert( 'Error assigning geography', 'Unable to assign geography: ' + data.error.msg );
                    }
                }
            });
        }

        var assignToImages = function( attrId ) {
            var imagesAffected = '(n/a)';
            var params = {
                cmd: 'imageAddAttribute',
                category: category.data.categoryId,
                attribType: 'attributeId',
                attribute: attrId
            }
            switch ( item.identifier ) {
                case 'selected':
                    var images = [];
                    Ext.each( Ext.getCmp('imagesGrid').getSelectionModel().getSelection(), function( image ) { images.push( image.get('imageId') ) });
                    imagesAffected = images.length;
                    params.imageId = JSON.stringify( images );
                    break;
                case 'filtered':
                    params.imageId = Ext.encode( Ext.getCmp('imagesGrid').getStore().collect( 'imageId', false, false ) );
                    imagesAffected = Ext.getCmp('imagesGrid').getStore().totalCount;
                    break;
                case 'all':
                    params.advFilter = JSON.stringify({ node: "group", logop: "and", children: [] }); // global filter
                    imagesAffected = 'all';
                    break;
            }
            Ext.Msg.confirm( 'Add Attribute to Images', 'Are you sure you want to add "' + me.record.get('name') + '" to <span style="font-weight:bold">' + imagesAffected + '</span> images?', function( btn, text, opts ) {
                if ( btn == 'yes' ) {
                    Ext.Ajax.request({
                        url: Config.baseUrl + 'resources/api/api.php',
                        params: params,
                        scope: this,
                        success: function( data ) {
                            data = Ext.decode( data.responseText );
                            if ( data.success ) {
                                // reload image details panel
                                var detailsPanel = Ext.getCmp('imageDetailsPanel');
                                detailsPanel.loadImages( detailsPanel.images );
                            } else {
                                Ext.Msg.alert( 'Error assigning geography', 'Unable to assign geography: ' + data.error.msg );
                            }
                        }
                    });
                }
            });
        }

        if ( category >= 0 ) {
            category = catStore.getAt( category );
            var attrStore = Ext.StoreManager.lookup('AttributesStore');
            var attribute = attrStore.findBy( function( attrRec, attrId ) {
                if ( attrRec.get('categoryId') == category.data.categoryId ) {
                    if ( attrRec.get('name') == me.record.get('name') ) return true;
                }
            });
            if ( attribute >= 0 ) {
                attribute = attrStore.getAt( attribute );
                assignToImages( attribute.get('attributeId') );
            } else { 
                addAttributeToCategory();
            }
        } else {
            var catTitle = rankToText[ this.record.getDepth() - 1 ];
            Ext.Ajax.request({
                url: Config.baseUrl + 'resources/api/api.php',
                params: {
                    cmd: 'categoryAdd',
                    namespace: 'BIS',
                    term: '',
                    description: '',
                    title: catTitle
                },
                scope: this,
                success: function( data ) {
                    data = Ext.decode( data.responseText );
                    if ( data.success ) {
                        category = { data: { title: catTitle, categoryId: data.categoryId } };
                        addAttributeToCategory();
                    } else {
                        Ext.Msg.alert( 'Error assigning geography', 'Unable to assign geography: ' + data.error.msg );
                    }
                }
            });
        
        }

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
