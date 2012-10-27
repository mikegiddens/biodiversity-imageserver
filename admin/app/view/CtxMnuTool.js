Ext.define('BIS.view.CtxMnuTool', {
    extend: 'Ext.menu.Menu',
    scope: this,

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    text: 'Apply to',
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
                }
            ]
        });
        me.callParent(arguments);
    },

    handleAssignment: function( menu, item ) {
        var me = this;
        var imagesAffected = '(n/a)';
        var params = {
            cmd: this.record.get('route')
        }
        switch ( item.identifier ) {
            case 'selected':
                var images = [];
                Ext.each( Ext.getCmp('imagesGrid').getSelectionModel().getSelection(), function( image ) { images.push( image.get('imageId') ) });
                imagesAffected = images.length;
                params.imageId = JSON.stringify( images );
                break;
            case 'filtered':
                params.imageId = Ext.getCmp('imagesGrid').getStore().collect( 'imageId', false, false );
                imagesAffected = Ext.getCmp('imagesGrid').getStore().totalCount;
                break;
            case 'all':
                params.advFilter = JSON.stringify({ node: "group", logop: "and", children: [] }); // global filter
                imagesAffected = 'all';
                break;
        }
        Ext.Msg.confirm( 'Apply Tool to Images', 'Are you sure you want to apply "' + this.record.get('name') + '" to <span style="font-weight:bold">' + imagesAffected + '</span> images?', function( btn, text, opts ) {
            if ( btn == 'yes' ) {
                Ext.Ajax.request({
                    url: Config.baseUrl + 'resources/api/' + me.record.get('module'),
                    params: params,
                    scope: me,
                    success: function( data ) {
                        data = Ext.decode( data.responseText );
                        console.log( data );
                        if ( data.success ) {
                        }
                    }
                });
            }
        });
    }

});
