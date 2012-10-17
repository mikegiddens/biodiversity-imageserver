Ext.define('BIS.model.SavedFilterModel', {
    extend: 'Ext.data.Model',
    alias: 'model.savedFilterModel',
    fields: [
        {
            name: 'name'
        },
        {
            name: 'description'
        },
        {
            name: 'advFilterId'
        },
        {
            name: 'filter'
        },
        {
            name: 'dateCreated'
        },
        {
            name: 'dateModified'
        },
        {
            name: 'lastModifiedBy'
        }
    ]
});
