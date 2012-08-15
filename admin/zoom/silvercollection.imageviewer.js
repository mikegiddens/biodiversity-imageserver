/**
 * @copyright SilverBiology
 * @website http://www.silverbiology.com
*/
Ext.namespace("SilverCollection")

SilverCollection.ImageViewer = function(config){
	var useDefaultTpl = true;
	if (Config.SilverCollection.ImageViewer) {
		if (Config.SilverCollection.ImageViewer.tpl) {
			tpl = Config.SilverCollection.ImageViewer.tpl;
		}
	}
	if ( useDefaultTpl ) {
		var tpl = new Ext.Template(
			'<tpl>'+
				'<div style="padding:5px;"><img onerror="this.src=\'resources/images/no-image.gif\'" src="{path}{filename}_l.{extension}" ></div>'+
			'</tpl>'
		);
	}
	Ext.apply(this, config, {
			border: false
		,	autoScroll: true
		,	tpl: tpl
	});

	SilverCollection.ImageViewer.superclass.constructor.call(this, config);
	
}

Ext.extend(SilverCollection.ImageViewer, Ext.Panel, {

	imageProcess:function(data){
		var data = data;
		this.update({
				path: data.path
			,	filename: data.filename
			,	extension: data.extension
		});
	}

});
