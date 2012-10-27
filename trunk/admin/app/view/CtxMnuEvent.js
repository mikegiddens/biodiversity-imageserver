Ext.define('BIS.view.CtxMnuEvent', {
    extend: 'Ext.menu.Menu',
    requires: ['BIS.view.FormCreateEvent'],
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
                case 'update':
                    this.update();
                    break;
                case 'delete':
                    Ext.Msg.confirm('Remove ' + this.record.data.title + '?', 'Are you sure you want remove ' + this.record.data.title + '?', function( btn, nothing, item ) {
                        if( btn == 'yes' ) this.remove();
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
                    object: 'event',
                    key: this.record.get('eventTypeId'),
                    keyText: this.record.get('eventTypeId'), // as of now, there is no eventTypeTitle field in event objects
                    value: this.record.get('eventId'),
                    valueText: this.record.get('title'),
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
                    text: 'Edit Event',
                    iconCls: 'icon_editEvent',
                    identifier: 'update'
                },
                {
                    text: 'Remove Event',
                    iconCls: 'icon_removeEvent',
                    identifier: 'delete'
                }
            ]
        });
        me.callParent(arguments);
    },
    handleAssignment: function( menu, item ) {
        var imagesAffected = '(n/a)';
        var params = {
            cmd: 'imageAddToEvent',
            eventId: this.record.get('eventId')
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
                Ext.getCmp('imagesGrid').getStore().totalCount;
                break;
            case 'all':
                params.advFilter = JSON.stringify({ node: "group", logop: "and", children: [] }); // global filter
                imagesAffected = 'all';
                break;
        }
        Ext.Msg.confirm( 'Associate Event with Images', 'Are you sure you want to associate "' + this.record.get('title') + '" with <span style="font-weight:bold;">' + imagesAffected + '</span> images?', function( btn, text, opts ) {
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
        });
    },
    remove: function() {
        var me = this;
        Ext.Ajax.request({
            method: 'POST',
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'eventDelete',
                eventId: this.record.get('eventId'),
                eventTypeId: this.record.get('eventTypeId')
            },
            scope: this,
            success: function( resObj ) {
                var res = Ext.decode( resObj.responseText );
                if ( res.success ) {
                    me.record.remove();
                }
            },
            failure: function( form, action ) {
                var res = Ext.decode( action.response.responseText );
                Ext.Msg.alert('Failed', res.error.msg);
            }
        });
    },
    update: function() {
        var me = this;
        var tmpWindow = Ext.create('Ext.window.Window', {
            title: 'Edit Event ' + this.record.data.title,
            iconCls: 'icon_editEvent',
            modal: true,
            height: 225,
            width: 350,
            layout: 'fit',
            items: [
                Ext.create('widget.formcreateevent', {
                    record: this.record,
                    mode: 'edit'
                })
            ]
        }).show();
        tmpWindow.on( 'eventCreated', function( data ) {
            tmpWindow.close();
            var store = Ext.getCmp('eventTreePanel').getStore();
            var node = store.getRootNode().findChild( 'eventTypeId', Number( me.record.get('eventTypeId') ), true );
            if ( node ) {
                store.load({
                    node: node
                });
            }
        });
        tmpWindow.on( 'cancel', function( data ) {
            tmpWindow.close();
        });
    }
});
