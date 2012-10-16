Ext.Loader.setConfig({
    enabled: true
});

Ext.application({
    models: [
        'AttributeModel',
        'CategoryModel',
        'CollectionModel',
        'EventModel',
        'EventTypeModel',
        'GeographyModel',
        'FilterModel',
        'ObjectModel'
    ],
    stores: [
        'AttributesStore',
        'CategoriesStore',
        'CollectionsStore',
        'EventsStore',
        'EventTypesStore',
        'GeographyStore',
        'FilterTreeStore',
        'ObjectsTreeStore'
    ],
    views: [
        'ObjectsTreePanel',
        'ObjectsFormPanel',
        'FilterTreePanel',
        'SearchFilterPanel',
        'FilterContextMenu'
    ],
    autoCreateViewport: true,
    name: 'BIS'
});
