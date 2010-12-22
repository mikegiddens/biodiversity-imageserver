/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/



	Ext.namespace('ImagePortal');

	ImagePortal.IVIntractiveRemote = function(config){

	var config2 = {};
	this.store = new Ext.data.JsonStore({
					proxy: new Ext.data.ScriptTagProxy({
						url: Config.baseUrl + 'resources/api/api.php'
					})
				,	fields: ['barcode', 'image_id', 'path']
				,	root: 'data'
				,	baseParams: {	
						cmd:'images'
					}
				,	listeners:{
						load:function(){
							this.sendIamgeData();
						}
					,	scope:this	
					}	
			});
	
	Ext.apply(config2, config, {
			border: true
		,	height: 550
		,	width:400
		,	title:'Image'
		,	iconCls:''
 	});

	ImagePortal.IVIntractiveRemote.superclass.constructor.call(this, config2);

};

 
	Ext.extend(ImagePortal.IVIntractiveRemote, ImagePortal.IVIntractive, {
			loadById:function(image_id){
				this.store.baseParams={ 
									cmd: 'images'
								,	value:image_id
								,	field:'image_id'
								}
				this.store.load();	
				}	
		/*
,	loadByGuid:function(GUID){
				this.store.baseParams= { 
										cmd: 'images'
									,	type:'list'
									,	value:GUID
									,	field:'guid'
									}
				this.store.load();
				}
*/
		,	loadByBarcode:function(barcode){
					this.store.baseParams= { 
										cmd: 'images'
									,	type:'list'
									,	value:barcode
									,	field:'barcode'
									}
					this.store.load();
				}		
		,	sendIamgeData:function(){
						var data = this.store.getAt(0).data;
						this.drawImage(data.path);
						//this.setTitle('Specimen Image: ' + data.barcode);
				}		
	}); // end of extend