Ext.namespace("ImagePortal")

	ImagePortal.CustomeInputPopup = function(config){

/*	var store = new Ext.data.JsonStore({
			url:'jsondata.json'
		,	root:'filetype'
		,	fields:['id','fltype']
	})*/
	var store = new Ext.data.ArrayStore({
			fields:['id','fltype']
	});
	var data = [['jpeg', 'jpeg'],['png','png'],['jpg','jpg']];
	store.loadData(data);
	Ext.apply(config,config,{
			title:'Custom Size'
		,	height:150
		,	width:250
		,	resizable:false
		,	modal: true
		,	buttons:[{
				    text: 'Download'
				,   ref:'../download'
				,	scope: this
				,   border:true
				,	width:50
				,	handler: this.download
			},{
				    text: 'Cancel'
				,   ref:'../cancel'
				,	scope: this
            	,   border:true
				,	width:50
				,	handler: this.cancel
			}]		
		,   items: [{
				xtype: 'form'
			,	border: false
			,	bodyStyle: 'padding:10px'				
			,	items:[{
						fieldLabel: 'Width'
					,	name: 'flwidth'
					,	xtype: 'numberfield'
					,   ref:'../flwidth'
					,   width: 90
					,   height:20
					,	scope:this
				},{       
						fieldLabel: 'Height'
					,	name: 'flheight'
					,	xtype: 'numberfield'
					,   ref:'../flheight'
					,   width: 90
					,   height:20
					,	scope:this
				},{
						xtype:'combo'
					, 	fieldLabel: 'File Type'
				//	, 	name: 'passin'
					, 	store: store
					,	displayField: 'fltype'
					,	valueField: 'fltype'
					,	mode: 'local'
					,	ref:'../fltype'
					,	value:'jpeg'
					,   width: 90
					,   height:20
					,	scope:this
					,	editable:false
					, 	emptyText: 'Select'
					,	forceSelection: true
					,	triggerAction: 'all'
					,	selectOnFocus:true
				/*	, 	listeners: {
							render: function(){
								this.store.load();
							}
       					}*/
				}]
			}]
		});
	
		ImagePortal.CustomeInputPopup.superclass.constructor.call(this,config)
	}

	Ext.extend( ImagePortal.CustomeInputPopup, Ext.Window, {

		download:function(){
				var flwidth = this.flwidth.getValue();
				var flheight = this.flheight.getValue();
				var fltype = this.fltype.getValue();
				if(Ext.isEmpty(flwidth) && Ext.isEmpty(flheight)){
					ImagePortal.Notice.msg("Notice", "At least one dimension is required.")
				}else{
					var url = Config.baseUrl + "resources/api/api.php?cmd=get_image&width="+flwidth+"&height="+flheight+"&image_id="+this.image_id+"&type="+this.fltype.getValue()  	
					window.open(url,'_blank');	
					this.hide();
				}
		}
	,	cancel: function(){
				this.hide();
		}		
	});


