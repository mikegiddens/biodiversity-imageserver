Ext.define('ImagePortal.Images', {
		extend: 'Ext.panel.Panel'	
	,	alias: 'widget.images'
	,	border: false
	,	id: 'images'
	,	autoScroll: true
	,	layout: 'card'
	,	activeItem: 0
	,	scriptTag: false
	,	initComponent: function() {
			if (Config.mode != 'local') {
				this.scriptTag = true;
			}
		
			this.store = Ext.create('Ext.data.Store', {
					pageSize: 100
				,	autoLoad: true
				,	remoteSort: true
				,	model: 'ImagesModel'
				,	proxy: {
							type: 'ajax'
						,	url : Config.baseUrl + 'resources/api/api.php'
						,	reader: {
									type: 'json'
								,	root: 'data'
								,	totalProperty: 'totalCount'
							}
						,	scriptTag: this.scriptTag
						,	extraParams: {
									cmd: 'images'
								,	filters: ''
								,	code: ''
							}	
					}	
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
			
			this.collectionCombo = Ext.create('Ext.form.field.ComboBox', {
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
			
			this.views =  Ext.create('Ext.button.Cycle', {
					showText: true
				,	prependText: 'View as '
				,	scope: this
				,	menu: {
						items: [{
								text: 'Details'
							,	value: 'details'
							,	checked: true
							,	iconCls: 'icon_cycleImages'
						},{
								text: 'Small'
							,	value: 'small'
							,	iconCls: 'icon_cycleImages'
						},{
								text: 'Both'
							,	value: 'both'
							,	iconCls: 'icon_cycleImages'
						},{
								text: 'Large'
							,	value: 'large'
							,	iconCls: 'icon_cycleImages'
						}]
					}
				,	changeHandler: this.changeView
			});
			
			this.tbar = ['Collection: ', this.collectionCombo, 'Search: ', this.search_evernote, this.viewImage
				, this.views
				,'->' , {
						iconCls: 'icon-rss'
					,	handler: function(){ 
							window.open(Config.baseUrl + 'resources/api/api.php?cmd=images&code=&dir=ASC&filters=&output=rss', '_blank');
						}
				}];
			
			this.imagesgird = Ext.create('ImagePortal.ImagesGird', {
					currentActive: 'imageGrid'
				,	store: this.store
				,	scope: this
			});
			
			this.imagesview = Ext.create('ImagePortal.ImagesView', {
					store: this.store
				,	scope: this	
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
				,	layout: 'fit'
				,	border: false
				,	items: [this.imagesview]	
			});
			
			this.items = [this.imagesgird, this.imagesviewPanel];
			
			this.callParent();
		}
	
	,	showContextMenu: function(view, record, item, index, e, opt){
			var items = [];
			console.log(record, item, index, "Index")
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
			var imageviewer = Ext.create('ImagePortal.ImageViewer', {
					imagePath: data.path
				,	imageBarcode: data.barcode
				,	imageId: data.image_id
			});
			imageviewer.show();
			var url = "http://a1.silverbiology.com/silvermeasure/unittests/imagezoom/sample/{2}/sample_{3}.jpg"
			imageviewer.loadImage(url);
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