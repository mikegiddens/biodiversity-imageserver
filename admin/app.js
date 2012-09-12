Ext.Loader.setConfig({
    enabled: true
});
Ext.application({
    models: [
        'SetModel',
        'CategoryModel',
        'AttributeModel',
        'EventModel',
        'EventTypeModel',
        'ImageModel',
        'UserModel',
        'CollectionModel',
        'StorageDeviceModel',
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
        'QueueStore',
        'KeyStore'
    ],
    views: [
        'MainViewport',
        'StorageSettingsPanel',
        'UserManagerPanel',
        'KeyManagerPanel',
        'ImagesGridView',
        'CtxMnuCollection',
        'CtxMnuCategory',
        'CtxMnuAttribute',
        'CtxMnuEventType',
        'CtxMnuEvent',
        'CtxMnuDevice',
        'CtxMnuUser',
        'CtxMnuKey',
        'FormCreateCategory',
        'FormCreateAttribute',
        'FormCreateCollection',
        'FormCreateDevice',
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
