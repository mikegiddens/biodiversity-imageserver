Ext.define('ImagePortal.Images', {
		extend: 'Ext.panel.Panel'	
	,	alias: 'widget.images'
	,	border: false
	,	id: 'images'
	,	layout: 'card'
	,	activeItem: 1
	,	currentView: 'small'
	,	requestType: 'ajax'
	,	initComponent: function() {
			if (Config.mode != 'local') {
				this.requestType = 'jsonp';
			}
			this.store = Ext.create('Ext.data.Store', {
					pageSize: 100
				,	autoLoad: true
				,	remoteSort: true
				,	model: 'ImagesModel'
				,	proxy: {
							type: this.requestType
						,	url : Config.baseUrl + 'resources/api/api.php'
						,	reader: {
									type: 'json'
								,	root: 'data'
								,	totalProperty: 'totalCount'
							}
						,	extraParams: {
									cmd: 'images'
								,	filters: ''
								,	code: ''
							}
						,	simpleSortMode: true	
					}	
			});
			this.views =  Ext.create('Ext.button.Cycle', {
					showText: true
				,	prependText: 'View as '
				,	scope: this
				,	menu: {
						items: [{
								text: 'Small'
							,	value: 'small'
							,	checked: true
							,	iconCls: 'icon_cycleImages'
						},{
								text: 'Large'
							,	value: 'large'
							,	iconCls: 'icon_cycleImages'
						},{
								text: 'Both'
							,	value: 'both'
							,	iconCls: 'icon_cycleImages'
						},{
								text: 'Details'
							,	value: 'details'
							,	iconCls: 'icon_cycleImages'
						}]
					}
				,	changeHandler: this.changeView
			});
			var collectionStore = Ext.create('Ext.data.Store', {
					proxy: {
							type: 'ajax'
						,	model: 'CollectionsModel'
						,	url : Config.baseUrl + 'resources/api/api.php'
						,	reader: {
									type: 'json'
								,	root: 'records'
								,	totalProperty: 'totalCount'
							}
						,	scriptTag: this.scriptTag
						,	extraParams: {
									cmd: 'collections'
								,	filters: ''
								,	code: ''
							}	
					}
			});
			
			this.search_evernote = Ext.create('Ext.ux.form.SearchField', {
					width: 250
				,	fieldLabel: 'Search'
				,	labelWidth: 60
				,	hideLabel: true
				,	xtype: 'searchfield'
				,	store: this.store
				,	paramName: 'value'
				,	onTrigger1Click : function(){
						var me = this,
							store = me.store,
							proxy = store.getProxy(),
							val;
							
						if (me.hasSearch) {
							me.setValue('');
							proxy.extraParams = proxy.extraParams || {};
							proxy.extraParams.cmd = 'images';
							proxy.extraParams[me.paramName] = '';
							proxy.extraParams.start = 0;
							store.load();
							me.hasSearch = false;
							me.triggerEl.item(0).setDisplayed('none');
							me.doComponentLayout();
						}
					}
				,	onTrigger2Click : function(){
						var me = this,
						store = me.store,
						proxy = store.getProxy(),
						value = me.getValue();
						if (value.length < 1) {
							me.onTrigger1Click();
							return;
						}
						proxy.extraParams = proxy.extraParams || {};
						proxy.extraParams.cmd = 'searchEnLabels';
						proxy.extraParams[me.paramName] = value;
						proxy.extraParams.start = 0;
						store.load();
						me.hasSearch = true;
						me.triggerEl.item(0).setDisplayed('block');
						me.doComponentLayout();
					}
			});
			
			this.collectionCombo = Ext.create('ImagePortal.TwinComboBox', {
					store: collectionStore
				,	width: 200
				,   mode: 'local'
				,   displayField: 'name'
				,   typeAhead: false
				,   triggerAction: 'all'
				,   editable: false
				, 	listeners: {
						select: function(combo, record) {
							this.store.getProxy().extraParams.code = record[0].data.code;
							this.store.load();
						}
					,	clear: function() {
							this.store.getProxy().extraParams.code = '';
							this.store.load();
						}
					,	scope: this	
				}
			});
			
			this.viewImage =  Ext.create('Ext.button.Button', {
						text: 'View Image'
					,	iconCls: 'icon_image'
					,	scope: this	
					,	handler: function(me) {
							var currentActive = this.getLayout().getActiveItem().currentActive;
							var selected = [];
							if(currentActive == 'imageGrid')
								selected = this.imagesgird.getSelectionModel().getSelection();
							if(currentActive == 'imageView')
								selected = this.imagesview.getSelectionModel().getSelection();
							if(Ext.isEmpty(selected)){
								Ext.Msg.alert('Notice', 'Please select record');
							}else {
								this.showWindow(selected[0].data);
							}
						}
			});
			
			
			
			this.tbar = ['Collection: ', this.collectionCombo, 'Search: ', this.search_evernote
				, this.views
				,'->' , {
						iconCls: 'icon-rss'
					,	hidden: Config.hideRss
					,	scope: this	
					,	handler: function(){ 
							window.open(Config.baseUrl + 'resources/api/api.php?cmd=images&code=&dir=ASC&filters=&output=rss', '_blank');
						}
			}];
			this.bbar = Ext.create('Ext.toolbar.Paging', {
					store: this.store
				,	scope: this	
				,	displayInfo: true
				,	displayMsg: 'Displaying Specimen Images {0} - {1} of {2}'
				,	emptyMsg: 'No images available.'
				,	items: [this.viewImage]
            });
			
			this.imagesgird = Ext.create('ImagePortal.ImagesGird', {
					currentActive: 'imageGrid'
				,	store: this.store
				,	scope: this
			});
			
			this.imagesview = Ext.create('ImagePortal.ImagesView', {
					store: this.store
				,	scope: this
				,	currentView: this.currentView
			});
			
			this.imagesgird.on({
					scope: this
				,	'itemdblclick': this.imageDblClick
				,	'itemcontextmenu': this.showContextMenu
			});
			
			this.imagesview.on({
					scope: this
				,	'itemdblclick': this.imageDblClick
				,	'itemcontextmenu': this.showContextMenu
			});
			
			this.imagesviewPanel = Ext.create('Ext.panel.Panel', {
					currentActive: 'imageView'
				,	border: false
				,	layout: 'fit'
				,	scope: this
				,	items: [this.imagesview]
			});
			
			this.items = [this.imagesgird, this.imagesviewPanel];
			
			this.callParent();
		}
	
	,	showContextMenu: function(view, record, item, index, e, opt){
			var items = [];
			items.push({
					text: 'View Image'
				,	iconCls: 'icon_image'
				,	scope: this	
				,	handler: function(me) {
						this.showWindow(record.data);
					}  
			});
			var menu = Ext.create('Ext.menu.Menu', {
				items: items
			});
			var xy = e.getXY();
			menu.showAt(xy);
		}
	
	,	imageDblClick: function(view, record, item, index, e, opt){
			this.showWindow(record.data);
		}
		
	,	showWindow: function(data){
			var imageviewer = Ext.create('ImagePortal.ImageViewer', {});
			imageviewer.show();
			imageviewer.on('onPrevious', this.previousClick, this)
			imageviewer.on('onNext', this.nextClick, this)
			imageviewer.loadTabs(data);
		}
		
	,	nextClick: function(imageviewer){
			var currentActive = this.getLayout().getActiveItem().currentActive;
				var pageLimit = this.store.pageSize-1;
				if(currentActive == 'imageView'){
					var selected = this.imagesview.getSelectionModel().getSelection();
					if(!Ext.isEmpty(selected)){
						var currentIndex = this.store.indexOf(selected[0]);
						if(currentIndex == pageLimit){
							Ext.Msg.alert('Notice', 'This last record');
						}else {
							++currentIndex;
							var rec = this.store.getAt(currentIndex);
							this.imagesview.getSelectionModel().doSelect(currentIndex);
							imageviewer.loadTabs(rec.data);
						}
					}
				}
				if(currentActive == 'imageGrid'){
					var selected = this.imagesgird.getSelectionModel().getSelection();
					if(!Ext.isEmpty(selected)){
						var currentIndex = this.store.indexOf(selected[0]);
						if(currentIndex == pageLimit){
							Ext.Msg.alert('Notice', 'This first record');
						}else {
							++currentIndex;
							var rec = this.store.getAt(currentIndex);
							this.imagesgird.getSelectionModel().doSelect(currentIndex);
							imageviewer.loadTabs(rec.data);
						}
					}
				}
		}
			
	,	previousClick: function(imageviewer){
				var currentActive = this.getLayout().getActiveItem().currentActive;
				var pageStart = 0;
				if(currentActive == 'imageView'){
					var selected = this.imagesview.getSelectionModel().getSelection();
					if(!Ext.isEmpty(selected)){
						var currentIndex = this.store.indexOf(selected[0]);
						if(currentIndex == pageStart){
							Ext.Msg.alert('Notice', 'This first record');
						}else {
							--currentIndex;
							var rec = this.store.getAt(currentIndex);
							this.imagesview.getSelectionModel().doSelect(currentIndex);
							imageviewer.loadTabs(rec.data);
						}
					}
				}
				if(currentActive == 'imageGrid'){
					var selected = this.imagesgird.getSelectionModel().getSelection();
					if(!Ext.isEmpty(selected)){
						var currentIndex = this.store.indexOf(selected[0]);
						if(currentIndex == pageStart){
							Ext.Msg.alert('Notice', 'This first record');
						}else {
							--currentIndex;
							var rec = this.store.getAt(currentIndex);
							this.imagesgird.getSelectionModel().doSelect(currentIndex);
							imageviewer.loadTabs(rec.data);
						}
					}
				}
		}
		
	,	changeView: function(cycleButton, activeItem) {
			var currentActive = this.getLayout().getActiveItem().currentActive;
			switch ( activeItem.value ) {
				case 'small':
					if(currentActive != 'imageView'){
						this.changeLayout(1);
					}
					this.imagesview.tpl.view = 'small';
					this.imagesview.refresh();
					break;
				case 'large':
					if(currentActive != 'imageView'){
						this.changeLayout(1);
					}
					this.imagesview.tpl.view = 'large';
					this.imagesview.refresh();
					break;
				case 'both':
					if(currentActive != 'imageView'){
						this.changeLayout(1);
					}
					this.imagesview.tpl.view = 'both';
					this.imagesview.refresh();
					break;
				case 'details':
					this.changeLayout(0);
				default:
					break;
			}
		}
		
	,	changeLayout: function(id){
				var l = this.getLayout();
				l.setActiveItem(id);
				this.doLayout();
		}		
});