
Ext.apply(Ext.form.VTypes, {

        funPassword : function(val, field) {   
            if (field.initialPassField) {
                var pwd = Ext.getCmp(field.initialPassField);
                if(!(val == pwd.getValue())){ Ext.get('confirm').addClass('wrongPass'); } 
                else{ Ext.get('confirm').replaceClass('wrongPass',''); }        
                return (val == pwd.getValue());
            }
              return true;
        }
});


Ext.onReady(function(){
//  Ext.Direct.addProvider(Ext.app.REMOTING_API);

  var Cookies = Ext.util.Cookies;
//  Ext.BLANK_IMAGE_URL = 'assets/images/s.gif';
  Ext.form.Field.prototype.msgTarget = 'under';

  var lastKnownUser = Cookies.get('lun') || '';

  var formPanel = new Ext.FormPanel({
        enableOverflow: false
    ,   labelWidth: 125
    ,   monitorValid: true
    ,   baseCls: 'x-plain'
    ,   el: 'form-box'
    ,   labelAlign: 'top'
    ,   autoHeight: true
    ,   defaultType: 'textfield'
    ,   defaults: {
                validationEvent: false
            ,   validateOnBlur: false
        }
    ,   items: [{
                    id: 'userName'
                ,   fieldLabel: 'Name'
                ,   name: 'userName'
                ,   width: 185
                ,   allowBlank: false
            },{
                    id: 'email'
                ,   fieldLabel: 'Email'
                ,   name: 'email'
                ,   vtype: 'email'                
                ,   width: 185
                ,   allowBlank: false
                ,   autoCreate: {
                        tag: "input"
                    ,   type: "text"
                    ,   autocomplete: "on"
                }
            },{
                    id: 'pass'
                ,   fieldLabel: 'Password'
                ,   name: 'pass'
                ,   width: 185
                ,   allowBlank: false
                ,   inputType: 'password'
            },{
                    id: 'confirm'
                ,   fieldLabel: 'Confirm Password'
                ,   name: 'confirm'
                ,   width: 185
                ,   allowBlank: false
                ,   inputType: 'password'
                ,   vtype:'funPassword'
                ,   initialPassField: 'pass'                
            }, {
                    id: 'code'
                ,   fieldLabel: 'Code'
                ,   name: 'code'
                ,   width: 185
                ,   allowBlank: false
                ,   autoCreate: {
                        tag: "input"
                    ,   type: "text"
                    ,   autocomplete: "on"
                }
            }]
    ,   buttons: [{
                id:'createAccount' 
            ,   text: 'Create Account'
            ,   disabled: true
            ,   handler: doCreateAccount
            ,   formBind: true
            }]
    ,   keys: [{
                    key: 13
                ,   fn: doCreateAccount
            }]
    });


  var panel = new Ext.Panel({
            layout:'table'
        ,   layoutConfig:{columns:2}
        ,   frame: true
        ,   el: 'login-box'
        ,   autoHeight: true
        ,   items: [{
                    xtype: 'box'
                ,   el: 'info-box'
                ,   colspan: 2
                ,   cellCls:'info-cell'
        },{
                    xtype: 'box'
                ,   width: 150
                ,   el: 'left-box'
                ,   cellCls:'left-cell'
          }
         ,  formPanel]
    });
    panel.render();

    function doCreateAccount(){
        var form = formPanel.getForm();
        var createAct = Ext.getCmp('createAccount');
        if (form.isValid()) {
//      Ext.getDom('logo').src = 'assets/images/extlogo64-anim.gif';
            var o = form.getFieldValues();
            saveCookies(o);
            form.callFieldMethod('disable');
            createAct.formBind = false;
            createAct.disable();
			Ext.Ajax.request({
					url: '/silverarchive_engine/silverarchive.php'
				,	params: { 
							task: 'create_account' 
						,	login: o.userName.trim()	
						,	email: o.email.trim()										
						,	password: o.pass
						,	confirm: o.confirm
						,	code: o.code.trim()																								
					}
				,	scope: this
				,	success: function(r) {
						var data = Ext.decode(r.responseText);
						if ( data.success ) {
							window.location.href = '/accountcreated.php';
						} else {
		//          Ext.getDom('logo').src = 'assets/images/extlogo64.gif';
							form.callFieldMethod('enable');
							createAct.formBind = true;
							createAct.enable();
							Ext.get("instructions-ct").slideOut('t', {
								callback: function(){
									Ext.get("msg-ct").update(data.error.msg).slideIn('t');
								},
								useDisplay: true,
								duration: 0.2
							});
						}
						
					}
            });
        }
  }

  function getTimeOffset(){
    var date = new Date();
      date.setFullYear(date.getFullYear() + 1);
    return ((new Date().getTimezoneOffset() / 60) * -1) * 1000 * 60 * 60;
  }

  function saveCookies(o){
    var date = new Date();
    date.setFullYear(date.getFullYear() + 1);
    Cookies.set('lun', o.userName, date);
  }

  if (lastKnownUser) {
    formPanel.items.get('userName').setValue(lastKnownUser);
    formPanel.items.get('userName').focus(true, true);
  } else {
    formPanel.items.get('userName').focus(true, true);
  }
});
