/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/

Ext.namespace('ImagePortal');

ImagePortal.Contactus = function(config) {

	  Ext.apply(this,config,{
			    labelWidth: 75// label settings here cascade unless overridden
			,   border:true
			,   title: 'Contact SilverBiology'
		    ,	bodyStyle: 'padding:20px'
		    ,   margins:'2 2 2 2'								        			
			,   width:300
			,   height:200
			,   items: [{
			                html:'<div id="info-box" style="border-bottom:1px solid #eee;width:400px; height:30px">Please feel free to Contact Us.<div id="msg-ct" style="width:300px; height:30px;color:Maroon;"></div><div id="instructions-ct"></div>'
                        ,   border:false			                
			        }, {       
						    fieldLabel: 'Title'
					    ,	name: 'title'
					    ,   id:'title'
					    ,	xtype: 'textfield'
					    ,   ref:'../txtTitle'
					    ,   width: 400
					    ,   height:20
					    ,   autoScroll:true
			        },{       
						    fieldLabel: 'Comment'
					    ,	name: 'comment'
					    ,   id:'comment'
					    ,	xtype: 'textarea'
					    ,   ref:'../txtComment'
					    ,   width: 400
					    ,   height:100
					    ,   autoScroll:true
			        },{
			                xtype:'button'
					    ,   text: 'Send'
					    ,   ref:'../submitComment'
				        ,	scope: this
                        ,   border:true
						,	width:50
                        ,   buttonAlign:'left'
				        ,	handler: this.submitComment
			        }]
	    });
		
		ImagePortal.Contactus.superclass.constructor.call(this, config);

	};

	Ext.extend(ImagePortal.Contactus, Ext.FormPanel, {
					 
		submitComment:function(){
      		this.body.mask('Submiting Comment.....') 
      		var form = this.getForm();
      		var submit = this.submitComment; 
      		var o = form.getFieldValues();       
     		if (form.isValid()) {
        		var o = form.getFieldValues();
        			form.callFieldMethod('disable');
        			submit.formBind = false;
						Ext.Ajax.request({
								url: 'resources/api/api.php'
							,	params: { 
									cmd: '' 
								,	title: o.title.trim()
								,	comment: o.comment.trim()
								}
							,	scope: this
							,	success: function(r) {
	                        			this.body.unmask( true );
										var data = Ext.decode(r.responseText);
										if ( data.success ) {
	                                			form.reset();
								    			Ext.get("instructions-ct").slideOut('t', {
														    		callback: function(){
															    	Ext.get("msg-ct").update('Your comment submited successfully.').slideIn('t');
														    	}
														    ,	useDisplay: true
														    ,	duration: 0.2
												    });		
				                            	form.callFieldMethod('enable');
				                            	submit.formBind = true;
										} else {
												form.callFieldMethod('enable');
					                            submit.formBind = true;
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
	});