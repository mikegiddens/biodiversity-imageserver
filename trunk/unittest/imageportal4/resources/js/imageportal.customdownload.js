Ext.define('ImagePortal.CustomDownload', {
		extend: 'Ext.window.Window'	
	,	alias: 'widget.customdownload'
	,	border: false
	,	title: 'Custom Size'
	,	width: 200
	,	height: 150
	,	modal: true
	,	resizable: false
	,	autoScroll: true
	,	layout: 'fit'
	,	imageId: ''
	,	initComponent: function() {
			this.buttons = [{
				    text: 'Download'
				,	scope: this
				,	width: 50
				,	handler: this.downloadImage
			},{
				    text: 'Cancel'
				,	width: 50
				,	scope: this
				,	handler: this.close
			}]
			var ftStore = Ext.create('Ext.data.SimpleStore', {
					fields: ['id','fltype']
				,	data: [['jpeg', 'jpeg'],['png','png'],['jpg','jpg']]
			});
			
			this.downloadForm = Ext.create('Ext.form.Panel', {
					border: false
				,	defaultType: 'numberfield'
				,	bodyPadding: 10
				,	defaults: {
							labelWidth: 60
						,	anchor: '100%'	
					}
				,	plain: true
				,	items: [{
							fieldLabel: 'Width'
						,	name: 'flwidth'
					}, {
							fieldLabel: 'Height'
						,	name: 'flheight'
					}, {
							xtype:'combo'
						, 	fieldLabel: 'File Type'
						,	store: ftStore
						,	displayField: 'fltype'
						,	valueField: 'fltype'
						,	mode: 'local'
						,	value: 'jpeg'
						,	name: 'fltype'
						,	editable: false
						, 	emptyText: 'Select'
						,	forceSelection: true
						,	triggerAction: 'all'
						,	selectOnFocus: true
					}]
			
			});
			this.items = [this.downloadForm]
			this.callParent();
		}
	,	downloadImage: function(){
			var values = this.downloadForm.getForm().getValues();
			if(Ext.isEmpty(values.flwidth) && Ext.isEmpty(values.flheight)){
				Ext.Msg.alert("Notice", "At least one dimension is required.")
			}else{
				var url = Config.portalUrl + "resources/api/api.php?cmd=get_image&width="+values.flwidth+"&height="+values.flheight+"&image_id="+this.imageId+"&type="+values.fltype  	
				window.open(url,'_blank');	
				this.close();
			}
		}
		
});