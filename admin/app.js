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
        'KeyModel',
        'FilterModel',
        'GeographyModel',
        'ObjectModel',
        'SavedFilterModel',
        'ToolModel'
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
        'KeyStore',
        'AttributesStore',
        'CategoriesStore',
        'CollectionsStore',
        'EventsStore',
        'EventTypesStore',
        'FilterTreeStore',
        'GeographyStore',
        'GeographyTreeStore',
        'ObjectsTreeStore',
        'SavedFilterStore',
        'ClientStationsStore',
        'ToolsTreeStore'
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
        'CtxMnuGeography',
        'CtxMnuNamespace',
        'CtxMnuAttribute',
        'CtxMnuEventType',
        'CtxMnuEvent',
        'CtxMnuDevice',
        'CtxMnuEvernote',
        'CtxMnuUser',
        'CtxMnuKey',
        'CtxMnuTool',
        'FormCreateCategory',
        'FormCreateGeography',
        'FormCreateAttribute',
        'FormCreateCollection',
        'FormCreateDevice',
        'FormCreateEvernoteAccount',
        'FormCreateUser',
        'FormCreateEventType',
        'FormCreateEvent',
        'FormCreateKey',
        'GeographyTreePanel',
        'ImagesPanel',
        'FilterContextMenu',
        'ObjectContextMenu',
        'FilterTreePanel',
        'ObjectsFormPanel',
        'ObjectsTreePanel',
        'SearchFilterPanel',
        'ImageTabPanel',
        'FormCreateFilter'
    ],
    autoCreateViewport: true,
    name: 'BIS',
    launch: function() {
        // get user info
        Ext.Ajax.request({
            url: Config.baseUrl + 'resources/api/api.php',
            params: {
                cmd: 'userInfo'
            },
            success: function( res, e ) {
                var data = Ext.decode( res.responseText );
                Config.user = data.records;
                Ext.getCmp('userLabel').update( 'Welcome, ' + (data.records.userRealName || data.records.user) );
            }
        });

        // Remove Loading Div
        Ext.get('loading').remove();
        Ext.get('loading-mask').fadeOut({remove:true});

        // init drag and drop uploading
        var dropbox;
        var filesUploaded = 0;
        var totalFiles = 0;
        var files = [];
        var running = false;
        dropbox = Ext.getBody().dom;

        function upload( file ) {
            var xhr = new XMLHttpRequest();
            xhr.open( 'POST', Config.baseUrl + 'resources/api/api.php', true );
            xhr.onreadystatechange = function() {
                if ( xhr.readyState == 4 ) {
                    if ( xhr.status == 200 ) {
                        Ext.getCmp('uploadLabel').update( 'Upload complete!' );
                        running = false;
                        initializeNextFile();
                    } else {
                        console.log( 'error', xhr, xhr.status );
                    }
                }
            }
            xhr.onerror = function () {
                console.log( 'error', xhr, xhr.status );
            }
            xhr.upload.abort = function( e ) {
                if ( e.lengthComputable ) {
                    console.log( 'abort! abort!' );
                }
            }
            xhr.upload.loadstart = function( e ) {
                if ( e.lengthComputable ) {
                    Ext.getCmp('uploadLabel').update( 'Uploading: 0%' );
                }
            }
            xhr.upload.onprogress = function( e ) {
                if ( e.lengthComputable ) {
                    Ext.getCmp('uploadLabel').update( 'Uploading: ' + Math.ceil(( e.loaded / e.total ) * 100) + '%' );
                }
            }
            var reader = new FileReader();
            reader.bisFileName = file.name;
            reader.onloadend = function( ev ) {
                var stream = ev.target.result;
                stream = stream.substr( stream.indexOf(',') + 1 );
                var formData = new FormData();
                formData.append( 'cmd', 'imageAddFromDnd' );
                formData.append( 'filename', this.bisFileName );
                formData.append( 'stream', stream );
                xhr.send( formData );
            }
            reader.readAsDataURL( file );
        }

        function initializeNextFile() {
            if ( !running ) {
                if ( files.length > 0 && typeof files[0] != 'undefined' ) {
                    running = true;
                    var nextFile = files.shift();
                    Ext.getCmp('filesLabel').update( ++filesUploaded + '/' + totalFiles );
                    upload( nextFile );
                } else {
                    // no more files
                    setTimeout( function() {
                        files = [];
                        totalFiles = 0;
                        filesUploaded = 0;
                        Ext.getCmp('filesLabel').update('');
                        Ext.getCmp('uploadLabel').update('Drag and drop to upload images.');
                    }, 5000 );
                }
            }
        }

        function dragenter( e ) {
            e.stopPropagation();
            e.preventDefault();
        }

        function dragleave( e ) {
            e.stopPropagation();
            e.preventDefault();
        }

        function dragover( e ) {
            e.stopPropagation();
            e.preventDefault();
        }

        function drop( e ) {
            e.stopPropagation();
            e.preventDefault();

            var dt = e.dataTransfer;
            if ( dt.files.length > 0 ) {
                for ( var f = 0; f < dt.files.length; f++ ) {
                    var flag = true;
                    var file = dt.files[ f ]
                    var parts = file.name.split('.');
                    if ( Config.extensions.indexOf( parts[1].toLowerCase() ) < 0 ) flag = false;
                    if ( flag ) {
                        totalFiles++;
                        files.push( file );
                    } else {
                        console.log( 'File', file.name, 'is not valid.' );
                    }
                }
                Ext.getCmp('filesLabel').update( filesUploaded + '/' + totalFiles );
                initializeNextFile();
            } else {
                var url = e.dataTransfer.getData('url'); // works in ff, chrome, and safari
                if ( url ) {
                    var parts = url.split('.');
                    if ( Config.extensions.indexOf( parts[parts.length-1].toLowerCase() ) < 0 ) {
                        console.log( 'Url ' + url + ' is not valid.' );
                    } else {
                        Ext.getCmp('uploadLabel').update( 'Uploading from url...' );
                        Ext.Ajax.request({
                            url: Config.baseUrl + 'resources/api/api.php',
                            params: {
                                cmd: 'imageAddFromUrl',
                                url: url
                            },
                            success: function( res ) {
                                if ( res ) {
                                    var data = Ext.decode( res.responseText );
                                    if ( data.success ) {
                                        Ext.getCmp('uploadLabel').update( 'Upload complete!' );
                                        setTimeout( function() {
                                            files = [];
                                            totalFiles = 0;
                                            filesUploaded = 0;
                                            Ext.getCmp('filesLabel').update('');
                                            Ext.getCmp('uploadLabel').update('Drag and drop to upload images.');
                                        }, 5000 );
                                    } else {
                                        Ext.getCmp('uploadLabel').update( 'Upload failed!' );
                                        setTimeout( function() {
                                            files = [];
                                            totalFiles = 0;
                                            filesUploaded = 0;
                                            Ext.getCmp('filesLabel').update('');
                                            Ext.getCmp('uploadLabel').update('Drag and drop to upload images.');
                                        }, 5000 );
                                    }
                                }
                            },
                            failure: function( res ) {
                                Ext.getCmp('uploadLabel').update( 'Upload failed!' );
                                setTimeout( function() {
                                    files = [];
                                    totalFiles = 0;
                                    filesUploaded = 0;
                                    Ext.getCmp('filesLabel').update('');
                                    Ext.getCmp('uploadLabel').update('Drag and drop to upload images.');
                                }, 5000 );
                            }
                        });
                    }
                } else {
                    console.log( 'There is no valid url.' );
                }
            }
        }

        dropbox.addEventListener( 'dragenter', dragenter );
        dropbox.addEventListener( 'dragleave', dragleave, false );
        dropbox.addEventListener( 'dragover', dragover );
        dropbox.addEventListener( 'drop', drop );
    }

});
