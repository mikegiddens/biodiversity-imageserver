Ext.define('BIS.view.ImageTabPanel', {
	extend: 'Ext.tab.Panel',
	alias: ['widget.imagetabpanel'],
	requires: [
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
                    html: '<img src="'+record.data.path + record.data.filename.substr( 0, record.data.filename.lastIndexOf('.') ) + '_l.' + record.data.ext+'">'
                },
                {
                    xtype: 'imagezoomviewer',
                    border: false,
                    title: 'Zooming Image',
                    iconCls: 'icon_magnifier',
                    imageId: record.data.imageId
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
                            record: record.data,
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
