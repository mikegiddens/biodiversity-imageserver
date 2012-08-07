Ext.define('BIS.view.Viewport', {
    extend: 'BIS.view.MainViewport',
    renderTo: Ext.getBody(),
    requires: [
        'BIS.view.MainViewport',
        'BIS.view.StorageSettingsPanel',
        'BIS.view.UserManagerPanel'
    ]
});
