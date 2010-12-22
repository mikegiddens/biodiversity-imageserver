/**
 * Makes a ComboBox have a twin trigger that is used to clear the value from the field.
 * User listener 'clear' to do something.
 *
 * @author Michael Giddens & help from Animal
 * http://extjs.com/forum/showthread.php?p=76130
 *
 * @history 2007-10-21 jvs
 * Combobox Mod for Ext 2.0
 */
Ext.ux.TwinComboBox = Ext.extend(Ext.form.ComboBox, {
		initComponent: Ext.form.TwinTriggerField.prototype.initComponent
	,	afterRender: Ext.form.TwinTriggerField.prototype.afterRender
	,	getTrigger: Ext.form.TwinTriggerField.prototype.getTrigger
	, getTriggerWidth: Ext.form.TwinTriggerField.prototype.getTriggerWidth
	,	initTrigger: Ext.form.TwinTriggerField.prototype.initTrigger
	,	trigger1Class: 'x-form-clear-trigger'
	,	hideTrigger1: true
	,	hideTrigger2: true
//	,	hideTrigger: true
	,	typeAhead: false
	,	minChars: 2
	,	value: ''
	,	displayField: 'name'
	,	queryParam: 'filter'		
	,	reset : Ext.form.ComboBox.prototype.reset.createSequence(function(){
	  	this.triggers[0].hide();
		})
	,	onViewClick :	Ext.form.ComboBox.prototype.onViewClick.createSequence(function(){
			this.triggers[0].show(); // Added to show trigger
		})
	,	onTrigger2Click : function(){
			this.onTriggerClick();
		}
	,	onTrigger1Click : function(){
			this.clearValue();
			this.triggers[0].hide();
			this.fireEvent('clear', this);
		}
});

// register xtype
Ext.reg('xtwincombo', Ext.ux.TwinComboBox);