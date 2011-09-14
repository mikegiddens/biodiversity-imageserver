Ext.define('ImagePortal.TwinComboBox', {
		extend: 'Ext.form.field.ComboBox'
	,	alias: 'widget.xtwincombo'
	,	trigger1Cls: Ext.baseCSSPrefix + 'form-clear-trigger'
	,	trigger2Cls: Ext.baseCSSPrefix + 'form-arrow-trigger'
	
	,	afterRender: function(){
			this.callParent();
			this.triggerEl.item(0).setDisplayed('none');  
		}
		
	,	onListSelectionChange: function(list, selectedRecords) {
			var me = this,
				isMulti = me.multiSelect,
				hasRecords = selectedRecords.length > 0;
			if (!me.ignoreSelection && me.isExpanded) {
				if (!isMulti) {
					Ext.defer(me.collapse, 1, me);
				}
				if (isMulti || hasRecords) {
					me.setValue(selectedRecords, false);
					me.triggerEl.item(0).setDisplayed('block');
					me.doComponentLayout();
				}
				if (hasRecords) {
					me.fireEvent('select', me, selectedRecords);
				}
				me.inputEl.focus();
			}
		}
		
	,	onTrigger1Click : function(){
			this.reset();
			this.triggerEl.item(0).setDisplayed('none');
			this.doComponentLayout();
			this.fireEvent('clear', this);
		}
		
	,	onTrigger2Click : function(){
			this.onTriggerClick();
		}
})