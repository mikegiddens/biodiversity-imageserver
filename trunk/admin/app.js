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
        'FormCreateCategory',
        'FormCreateAttribute',
        'FormCreateCollection',
        'FormCreateDevice'
    ],
    autoCreateViewport: true,
    name: 'BIS'
});
