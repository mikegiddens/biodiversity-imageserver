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
        'QueueModel'
    ],
    stores: [
        'SetTreeStore',
        'CategoryTreeStore',
        'EventTreeStore',
        'ImagesStore',
        'UserStore',
        'CollectionsTreeStore',
        'StorageDevicesStore',
        'QueueStore'
    ],
    views: [
        'MainViewport',
        'StorageSettingsPanel',
        'UserManagerPanel',
        'ImagesGridView',
        'CtxMnuCollection',
        'CtxMnuCategory',
        'CtxMnuAttribute',
        'CtxMnuEventType',
        'CtxMnuEvent',
        'CtxMnuDevice',
        'CtxMnuUser',
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
    name: 'BIS'
});
