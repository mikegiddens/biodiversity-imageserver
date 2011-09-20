Ext.define('ImagePortal.ImagePanelRemote', {
		extend: 'ImagePortal.Images'	
	,	alias: 'widget.imagepanelremote'
	,	listeners: {
				render: function(){
					this.getDockedItems()[1].insert(12, this.views);
					this.doComponentLayout();
				}
		}
})