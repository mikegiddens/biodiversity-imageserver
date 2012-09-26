Ext.Loader.setConfig({
    enabled: true
});
Ext.application({
    models: [
        'SetModel',
        'CategoryModel',
        'AttributeModel',
        'CattributeModel',
        'EventModel',
        'EventTypeModel',
        'ImageModel',
        'UserModel',
        'CollectionModel',
        'StorageDeviceModel',
        'EvernoteAccountModel',
        'QueueModel',
        'KeyModel'
    ],
    stores: [
        'SetTreeStore',
        'CategoryTreeStore',
        'PropertiesStore',
        'EventTreeStore',
        'ImagesStore',
        'UserStore',
        'CollectionsTreeStore',
        'StorageDevicesStore',
        'EvernoteAccountsStore',
        'QueueStore',
        'KeyStore'
    ],
    views: [
        'MainViewport',
        'StorageSettingsPanel',
        'EvernoteSettingsPanel',
        'UserManagerPanel',
        'KeyManagerPanel',
        'ImagesGridView',
        'CtxMnuCollection',
        'CtxMnuCategory',
        'CtxMnuAttribute',
        'CtxMnuEventType',
        'CtxMnuEvent',
        'CtxMnuDevice',
        'CtxMnuEvernote',
        'CtxMnuUser',
        'CtxMnuKey',
        'FormCreateCategory',
        'FormCreateAttribute',
        'FormCreateCollection',
        'FormCreateDevice',
        'FormCreateEvernoteAccount',
        'FormCreateUser',
        'FormCreateEventType',
        'FormCreateEvent',
        'ImagesPanel'
    ],
    autoCreateViewport: true,
    name: 'BIS',
    launch: function() {
        // Remove Loading Div
        Ext.get('loading').remove();
        Ext.get('loading-mask').fadeOut({remove:true});
    }
});
