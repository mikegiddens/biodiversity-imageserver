Ext.define('BIS.view.ImageTabPanel', {
	extend: 'Ext.tab.Panel',
	alias: ['widget.imagetabpanel'],
	requires: [
		'BIS.view.ImageZoomViewer',
	],

	id: 'imageTabPanel',
    border: false,
    activeItem: 0,

	initComponent: function() {
		var me = this;
		Ext.applyIf(me, {

            items: [
                {
                    xtype: 'panel',
                    border: false,
                    title: 'Static Image',
                    iconCls: 'icon_image',
                    autoScroll: true,
                    html: '<img src="' + me.record.data.path + me.record.data.filename.substr( 0, me.record.data.filename.lastIndexOf('.') ) + '_l.' + me.record.data.ext+'">'
                },
                {
                    xtype: 'imagezoomviewer',
                    border: false,
                    title: 'Zooming Image',
                    iconCls: 'icon_magnifier',
                    imageId: me.record.data.imageId
                }
            ],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'top',
                    items: [
                        {
                            text: 'View Original',
                            iconCls: 'icon_picture',
                            scope: me,
                            record: me.record.data,
                            handler: this.viewOriginal
                        }
                    ]
                }
            ]

		});
		me.callParent(arguments);
	},

    viewOriginal: function( btn, e ) {
        window.open( btn.record.path + btn.record.filename );
    }

});
