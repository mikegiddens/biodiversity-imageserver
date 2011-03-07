Ext.namespace('ImagePortal');
	ImagePortal.ProgressOfCollectionRemote=function(config){
		var config2={};
		Ext.apply(config2,config,{
			border:true
		,	width:700
		,	height:420
		,	store:new Ext.data.JsonStore({
				proxy:new Ext.data.ScriptTagProxy({
					url:Config.baseUrl+'resources/api/api.php'
				})
			,	fields:['collection','imaged','notimaged']
			,	root:'data'
			,	baseParams:{
					cmd:'sizeOfCollection'
				}
			,	autoLoad:true
			})
		});
	ImagePortal.ProgressOfCollectionRemote.superclass.constructor.call(this,config2);
};
Ext.extend(ImagePortal.ProgressOfCollectionRemote,ImagePortal.ProgressOfCollection,{});
