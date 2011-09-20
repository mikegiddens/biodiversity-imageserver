Ext.override(Ext.toolbar.Paging, {
	 updateInfo : function(){
        var me = this,
            displayItem = me.child('#displayItem'),
            store = me.store,
            pageData = me.getPageData(),
            count, msg;
			pageData.fromRecord = Ext.util.Format.number(pageData.fromRecord, '0,0');
			pageData.toRecord = Ext.util.Format.number(pageData.toRecord, '0,0');
			pageData.total = Ext.util.Format.number(pageData.total, '0,0');
        if (displayItem) {
            count = store.getCount();
            if (count === 0) {
                msg = me.emptyMsg;
            } else {
                msg = Ext.String.format(
                    me.displayMsg,
                    pageData.fromRecord,
                    pageData.toRecord,
                    pageData.total
                );
            }
            displayItem.setText(msg);
            me.doComponentLayout();
        }
    }
});