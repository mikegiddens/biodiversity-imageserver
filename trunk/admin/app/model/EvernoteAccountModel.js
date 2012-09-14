Ext.define('BIS.model.EvernoteAccountModel', {
    extend: 'Ext.data.Model',
    alias: 'model.evernoteAccountModel',

    fields: [
        {
            name: 'enAccountId'
        },
        {
            name: 'accountName'
        },
        {
            name: 'username'
        },
        {
            name: 'password'
        },
        {
            name: 'consumerKey'
        },
        {
            name: 'consumerSecret'
        },
        {
            name: 'notebookGuid'
        }
    ]
});
