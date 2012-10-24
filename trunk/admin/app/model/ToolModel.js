Ext.define('BIS.model.ToolModel', {
    extend: 'Ext.data.Model',

    fields: [
        {
            name: 'name'
        },
        {
            name: 'route'
        },
        {
            name: 'module'
        },
        {
            name: 'iconCls'
        },
        {
            name: 'leaf',
            defaultValue: true
        },
        {
            name: 'children'
        }
    ]
});
