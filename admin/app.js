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
        'FormCreateCategory',
        'FormCreateAttribute',
        'FormCreateCollection',
        'FormCreateDevice',
        'FormCreateEventType',
        'FormCreateEvent'
    ],
    autoCreateViewport: true,
    name: 'BIS'
});
