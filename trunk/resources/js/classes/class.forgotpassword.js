
Ext.onReady(function(){

  Ext.BLANK_IMAGE_URL = 'assets/images/s.gif';

  var Cookies = Ext.util.Cookies;
  var lastKnownUser = Cookies.get('emailid') || '';

    var formPanel = new Ext.FormPanel({
            defaultType: 'textfield'
        ,   renderTo:'forgotpassword'
        ,   border:false
        ,   items: [{
                id: 'emailid'
            ,   fieldLabel: 'Email Address'
            ,   name: 'emailid'
            ,   width: 185
            ,   allowBlank: true
            ,   autoCreate: {
                    tag: "input"
                ,   type: "text"
                ,   autocomplete: "on"
                }
            }]
        ,   buttons: [{
                    id:'forgot'
                ,   text: 'Get your password'
                ,   handler: funForgot
                ,   formBind: true
                ,   border:true
            }]
        ,   keys: [{
                key: 13
            ,   fn: funForgot
            }]
  });


 function funForgot(){
    var form = formPanel.getForm();
    var forgot = Ext.getCmp('forgot');
    if (form.isValid()) {
      var o = form.getFieldValues();
      saveCookies(o);	  
      form.callFieldMethod('disable');
      forgot.formBind = false;
      forgot.disable();
			Ext.Ajax.request({
					url: '/silverarchive_engine/silverarchive.php'
				,	params: { 
							task: 'forgot_password' 
						,	email: o.emailid.trim()
					}
				,	scope: this
				,	success: function(r) {
						console.log('success');
						forgot.enable();
						forgot.formBind = true;
						form.callFieldMethod('enable');										
						var data = Ext.decode(r.responseText);
						if ( data.success ) {
								Ext.get("forgot-instructions-ct").slideOut('t', {
								callback: function(){
									Ext.get("forgot-msg-ct").update(data.message).slideIn('t');
								},
								useDisplay: true,
								duration: 0.2
							});							
						} else {
							if(data.error.message != null) {				
							    switch(data.error.code) {
							        case 111:
													Ext.get("forgot-instructions-ct").slideOut('t', {
														callback: function(){
															Ext.get("forgot-msg-ct").update(data.error.message).slideIn('t');
														},
														useDisplay: true,
														duration: 0.2
													});
							            break;
							        default:
													Ext.get("forgot-instructions-ct").slideOut('t', {
														callback: function(){
															Ext.get("forgot-msg-ct").update("Unknown Error").slideIn('t');
														},
														useDisplay: true,
														duration: 0.2
													});
							            break;
							    }
							}
							else
							    Ext.get("forgot-instructions-ct").update('Please provide your email and password to sign in');						
						}
					}
                ,	failure: function(){
											console.log('failure');
		        }   						
            });
        }
    }
	
   function saveCookies(o){
    var date = new Date();
    date.setFullYear(date.getFullYear() + 1);
    Cookies.set('emailid', o.emailid, date);
  }


  if (lastKnownUser) {
    	formPanel.items.get('emailid').setValue(lastKnownUser);
    	formPanel.items.get('emailid').focus(true, true);
  	} else {
    	formPanel.items.get('emailid').focus(true, true);
  	}
	
});
