Ext.define('BIS.view.SearchFilterPanel', {
    extend: 'Ext.panel.Panel',

    height: 600,
    width: 1000,
    margin: 50,
    layout: 'border',
    title: 'Search Filter',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {

            items: [
                /*
                Ext.create( 'BIS.view.ObjectsTreePanel', {
                    flex: 1,
                    region: 'west'
                }),
                */
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
            ]
        });

        me.callParent(arguments);
    }

});
