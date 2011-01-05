/**
 * @copyright SilverBiology, LLC
 * @author Shashank
 * @website http://www.silverbiology.com
*/



	Ext.namespace('ImagePortal');

	ImagePortal.IVIntractiveRemote = function(config){

	var config2 = {};
	
	Ext.apply(config2, config, {
			border: true
		,	height: 420
		
 	});

	ImagePortal.IVIntractiveRemote.superclass.constructor.call(this, config2);

};

 
	Ext.extend(ImagePortal.IVIntractiveRemote, ImagePortal.IVIntractive, {
			loadById:function(image_id){
				Ext.Ajax.request({
								url:Config.baseUrl + 'resources/api/api.php'
							,	params: { cmd: 'image', image_id: image_id }
							,	success: function(responseObject) {
										var response = Ext.decode(responseObject.responseText);
										console.log(response);
										this.drawImage(response.data.path);
										/*
if (response.success) {
											Ext.MessageBox.alert('Success', 'Connection Tested Successfully.');
										} else {
											Ext.MessageBox.alert('Error', 'Error in Connection.');	
										}
*/
									}
						});		
			}	
		,	loadByGuid:function(GUID){
				var filter = {GlobalUniqueIdentifier:GUID}	
					Ext.Ajax.request({
									url:Config.baseUrl + 'resources/api/api.php'
								,	params: {
								cmd: 'image-list'
							,	filter: Ext.encode(filter)
						}
								,	success: function(responseObject) {
											var response = Ext.decode(responseObject.responseText);
											console.log(response);
											
											/*
if (response.success) {
												console.log(response);
												Ext.MessageBox.alert('Success', 'Connection Tested Successfully.');
											} else {
												Ext.MessageBox.alert('Error', 'Error in Connection.');	
											}
*/
										}
							});	
				}
	}); // end of extend