/*

This file is part of Ext JS 4

Copyright (c) 2011 Sencha Inc

Contact:  http://www.sencha.com/contact

GNU General Public License Usage
This file may be used under the terms of the GNU General Public License version 3.0 as published by the Free Software Foundation and appearing in the file LICENSE included in the packaging of this file.  Please review the following information to ensure the GNU General Public License version 3.0 requirements will be met: http://www.gnu.org/copyleft/gpl.html.

If you are unsure which license is appropriate for your use, please contact the sales department at http://www.sencha.com/contact.

*/
Ext.define('Ext.ux.form.SearchField', {
    extend: 'Ext.form.field.Trigger',
    alias: 'widget.searchfield',
    
    trigger1Cls: Ext.baseCSSPrefix + 'form-clear-trigger',
    trigger2Cls: Ext.baseCSSPrefix + 'form-search-trigger',
    
    hasSearch : false,
    paramName : 'query',
    
    initComponent: function(){
        this.callParent(arguments);
        this.on('specialkey', function(f, e){
            if(e.getKey() == e.ENTER){
                this.onTrigger2Click();
            }
        }, this);
    },
    
    afterRender: function(){
        this.callParent();
    },
    
    onTrigger1Click : function(){
        var me = this, val;
            
        if (me.hasSearch) {
            me.handlerCmp.clearFilter();
            me.setValue('');
            me.hasSearch = false;
            me.doComponentLayout();
        }
    },

    onTrigger2Click : function(){
        var me = this, value = me.getValue();
        if (value.length < 1) {
            me.onTrigger1Click();
            return;
        }
        me.handlerCmp.search( value );
        me.hasSearch = true;
        me.triggerEl.item(0).setDisplayed('block');
        me.doComponentLayout();
    }
});
