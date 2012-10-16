Ext.define('BIS.store.ObjectsTreeStore', {
    extend: 'Ext.data.TreeStore',

    requires: [ 'BIS.model.ObjectModel' ],

    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            autoLoad: true,
            storeId: 'objectsTreeStore',
            model: 'BIS.model.ObjectModel',
            root: { expanded: true, children: [
                {
                    text: 'Groups',
                    leaf: false,
                    expanded: true,
                    children: [
                        {
                            text: 'and',
                            node: 'group',
                            type: 'and',
                            leaf: true,
                            children: []
                        },
                        {
                            text: 'or',
                            node: 'group',
                            type: 'or',
                            leaf: true,
                            children: []
                        }
                    ]
                },
                {
                    text: 'Conditions',
                    leaf: false,
                    expanded: true,
                    children: [
                        {
                            text: 'Attribute',
                            node: 'condition',
                            type: 'attribute',
                            leaf: true,
                            children: []
                        },
                        {
                            text: 'Event',
                            node: 'condition',
                            type: 'event',
                            leaf: true,
                            children: []
                        },
                        {
                            text: 'Geography',
                            node: 'condition',
                            type: 'geography',
                            leaf: true,
                            children: []
                        },
                        {
                            text: 'Collection',
                            node: 'condition',
                            type: 'collection',
                            leaf: true,
                            children: []
                        },
                        {
                            text: 'Time',
                            node: 'condition',
                            type: 'time',
                            leaf: true,
                            children: []
                        },
                        {
                            text: 'Client Station',
                            node: 'condition',
                            type: 'clientStation',
                            leaf: true,
                            children: []
                        }
                    ]
                }
            ] },
            proxy: {
                type: 'memory',
                reader: {
                    type: 'json'
                }
            }
        }, cfg)]);
    }
});
