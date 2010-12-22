
Ext.onReady(function(){

  Ext.BLANK_IMAGE_URL = 'assets/images/s.gif';

  var Cookies = Ext.util.Cookies;
  var lastKnownUser = Cookies.get('log') || '';

    var formPanel = new Ext.FormPanel({
            defaultType: 'textfield'
        ,   renderTo:'formPanel'
        ,   border:false
        ,   items: [{
                id: 'userName'
            ,   fieldLabel: 'Username'
            ,   name: 'userName'
            ,   width: 185
            ,   allowBlank: true
            ,   autoCreate: {
                    tag: "input"
                ,   type: "text"
                ,   autocomplete: "on"
                }
            }, {
                id: 'password'
            ,   fieldLabel: 'Password'
            ,   name: 'password'
            ,   width: 185
            ,   allowBlank: true
            ,   inputType: 'password'
            }]
        ,   buttons: [{
                    id:'signin'
                ,   text: 'Sign In'
//                ,   disabled: true
                ,   handler: login
                ,   formBind: true
                ,   border:true
            }]
        ,   keys: [{
                key: 13
            ,   fn: login
            }]
  });


 function login(){
    var form = formPanel.getForm();
    var signin = Ext.getCmp('signin');
    if (form.isValid()) {
      var o = form.getFieldValues();
      saveCookies(o);	  
      form.callFieldMethod('disable');
      signin.formBind = false;
      signin.disable();
			Ext.Ajax.request({
					url: '/silverarchive_engine/silverarchive.php'
				,	params: { 
							task: 'sign_in' 
						,	username: o.userName.trim()
						,	password: o.password
					}
				,	scope: this
				,	success: function(r) {
						var data = Ext.decode(r.responseText);
						if ( data.success ) {
							window.location.href = '/account.php';
						} else {
							signin.enable();
							signin.formBind = true;
							form.callFieldMethod('enable');					
							if(data.error.message != null) {				
							    switch(data.error.code) {
							        case 111:
													Ext.get("instructions-ct").slideOut('t', {
														callback: function(){
															Ext.get("msg-ct").update(data.error.message).slideIn('t');
														},
														useDisplay: true,
														duration: 0.2
													});
							            break;
							        default:
													Ext.get("instructions-ct").slideOut('t', {
														callback: function(){
															Ext.get("msg-ct").update("Unknown Error").slideIn('t');
														},
														useDisplay: true,
														duration: 0.2
													});
							            break;
							    }
							}
							else
							    Ext.get("instructions-ct").update('Please provide your email and password to sign in');						
						}
					}
                ,	failure: {
		        }   						
            });
        }
    }
	
   function saveCookies(o){
    var date = new Date();
    date.setFullYear(date.getFullYear() + 1);
    Cookies.set('log', o.userName, date);
  }


  if (lastKnownUser) {
    	formPanel.items.get('userName').setValue(lastKnownUser);
    	formPanel.items.get('password').focus(true, true);
  	} else {
    	formPanel.items.get('userName').focus(true, true);
  	}
});
