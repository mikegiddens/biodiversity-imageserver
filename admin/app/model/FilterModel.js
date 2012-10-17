Ext.define('BIS.model.FilterModel', {
    extend: 'Ext.data.Model',

    fields: [
        {
            name: 'node'
        },
        {
            name: 'logop'
        },
        {
            name: 'object'
        },
        {
            name: 'key'
        },
        {
            name: 'keyText'
        },
        {
            name: 'value'
        },
        {
            name: 'valueText'
        },
        {
            name: 'value2'
        },
        {
            name: 'value2Text'
        },
        {
            name: 'condition'
        },
        {
            name: 'conditionText'
        }
    ],
    hasMany: {
        model: 'BIS.model.FilterModel',
        name: 'children'
    }
});
