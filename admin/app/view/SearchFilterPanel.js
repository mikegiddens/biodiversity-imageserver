Ext.define('BIS.view.SearchFilterPanel', {
    extend: 'Ext.panel.Panel',

    requires: [
        'BIS.view.ObjectsFormPanel',
        'BIS.view.FilterTreePanel'
    ],

    layout: 'border',
    title: false,

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {

            items: [
                /*
                {
                    xtype: 'form',
                    border: false,
                    flex: 2,
                    region: 'east',
                    items: [
                        {
                            xtype: 'button',
                            text: 'Export Filter Graph as JSON',
                            handler: function() {
                                console.log( 'Filter Graph JSON Output:' );
                                console.log( Ext.getCmp('filterTreePanel').exportFilterGraph() );
                                Ext.getCmp('dataoutput').setValue( JSON.stringify( Ext.getCmp('filterTreePanel').exportFilterGraph() ) );
                            }
                        },
                        {
                            xtype: 'textarea',
                            id: 'dataoutput',
                            width: '100%',
                            height: 550
                        }
                    ]
                },
                */
                {
                    xtype: 'panel',
                    flex: 5,
                    region: 'center',
                    border: false,
                    layout: 'border',
                    items: [
                        Ext.create( 'BIS.view.ObjectsFormPanel', {
                            flex: 1,
                            region: 'north'
                        }),
                        Ext.create( 'BIS.view.FilterTreePanel', {
                            flex: 2,
                            region: 'center'
                        })
                    ]
                }
            ],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    ui: 'footer',
                    items: [
                        '->',
                        {
                            text: 'Cancel',
                            scope: this,
                            handler: this.cancel
                        },
                        {
                            text: 'Apply Filter',
                            id: 'advFilterSubmitButton',
                            iconCls: 'icon_magnifier',
                            scope: this,
                            handler: this.activateFilter
                        }
                    ]
                }
            ]
        });

        me.callParent(arguments);
    },

	activateFilter: function() {
        var me = this;
        Ext.getCmp('imagesGrid').setAdvancedFilter( Ext.getCmp('filterTreePanel').exportFilterGraph(), function( success ) {
            if ( success ) me.ownerCt.fireEvent('done');
        });
    },
    cancel: function() {
        this.ownerCt.fireEvent('cancel');
    }
});
