Ext.namespace('ImagePortal');

ImagePortal.ddupload = function(config){
	var fields = ['id', 'name', 'size', 'type','status', 'progress'];
	this.fileRecord = Ext.data.Record.create(fields);
 	
	Ext.apply( this, config, {
			border: true
		,	width: 600
		,	height: 200
		,	title: 'Image Upload'	
		,	maxFileSizeBytes: 3145728 
		,	extraPostData: {}
		,	supressPopups: false
		,	swfUploadItems:[]
		,	doLayout:function(){
				this.fileGrid.getView().refresh();
			}
		,	items:[{},{
					xtype:'grid'
				,	autoScroll: true
				,	ref: 'fileGrid'
				,	layout: 'fit'
				,	border: false
				,	height: 180
				,	store:	new Ext.data.ArrayStore({
						fields: fields
					,	reader: new Ext.data.ArrayReader({idIndex: 0}, this.fileRecord)
				})	
				,	columns:[
						{header:'File Name',dataIndex:'name', width:150}
					,	{header:'Size',dataIndex:'size', width:80, renderer:Ext.util.Format.fileSize}
					,	{header:'Type',dataIndex:'type', width:80}
					,	{header:'Status',dataIndex:'status', width:80}
					,	{header:'Progress',dataIndex:'progress',scope:this, renderer:this.progressBarColumnRenderer}
				]
				,	listeners:{
						render:{
							scope:this
						,	fn:function(){
								this.initUploader();
								this.initDnDUploader();		
						}	
					}
				}
			}]
		,	bbar: [{
				text: 'Clear'
			}]
	});
		
	ImagePortal.ddupload.superclass.constructor.call(this, config);

};

Ext.extend(ImagePortal.ddupload, Ext.Panel, {

	initDnDUploader:function(){
		if(!document.body.BodyDragSinker){
			document.body.BodyDragSinker = true;
			
			var body = Ext.fly(document.body);
			body.on({
				dragenter:function(event){
					return true;
				}
				,dragleave:function(event){
					return true;
				}
				,dragover:function(event){
					event.stopEvent();
					return true;
				}
				,drop:function(event){
					event.stopEvent();
					return true;
				}
			});
		}
		this.el.on({
			dragenter:function(event){
				event.browserEvent.dataTransfer.dropEffect = 'move';
				return true;
			}
			,dragover:function(event){
				event.browserEvent.dataTransfer.dropEffect = 'move';
				event.stopEvent();
				return true;
			}
			,drop:{
				scope:this
				,fn:function(event){
					event.stopEvent();
					var files = event.browserEvent.dataTransfer.files;

					if(files === undefined){
						return true;
					}
					var len = files.length;
					while(--len >= 0){
						this.processDnDFileUpload(files[len]);
					}
				}
			}
		});
		
	}
	,	initUploader:function(){
			var settings = {
				flash_url: 'http://images.cyberfloralouisiana.com/portal/unittest/browse/swfupload.swf'
				,upload_url: 'http://images.cyberfloralouisiana.com/portal/unittest/browse/uploader.php'
				,file_size_limit: this.maxFileSizeBytes + ' B'
				,file_types: '*.*'
				,file_types_description: 'All Files'
				,file_upload_limit: 100
				,file_queue_limit: 0
				,debug: false
				,post_params: this.extraPostData
				,button_image_url: 'http://images.cyberfloralouisiana.com/portal/unittest/browse/images/swfupload_browse_button_trans_56x22.PNG'
				,button_width: '56'
				,button_height: '22'
				,button_window_mode: 'opaque'
				,file_post_name: 'Filedata'
				,button_placeholder: this.items.items[0].body.dom
				,file_queued_handler: this.swfUploadfileQueued.createDelegate(this)
				,file_dialog_complete_handler: this.swfUploadFileDialogComplete.createDelegate(this)
				,upload_start_handler: this.swfUploadUploadStart.createDelegate(this)
				,upload_error_handler: this.swfUploadUploadError.createDelegate(this)
				,upload_progress_handler: this.swfUploadUploadProgress.createDelegate(this)
				,upload_success_handler: this.swfUploadSuccess.createDelegate(this)
				,upload_complete_handler: this.swfUploadComplete.createDelegate(this)
				,file_queue_error_handler: this.swfUploadFileQueError.createDelegate(this)
				,minimum_flash_version: '9.0.28'
				,swfupload_load_failed_handler: this.initStdUpload.createDelegate(this)

			};
			this.swfUploader = new SWFUpload(settings);
		}
	,	initStdUpload:function(param){
			if(this.uploader){
				this.uploader.fileInput = null; 
				Ext.destroy(this.uploader);
			}else{
				Ext.destroy(this.items.items[0]);
			}
			this.uploader = new Ext.ux.form.FileUploadField({
					renderTo:this.body
				,	buttonText:'Browse...'
				,	x:0
				,	y:0
				,	style:'position:absolute;'
				,	buttonOnly:true
				,	name:this.standardUploadFilePostName
				,	listeners:{
						scope:this
					, 	fileselected:this.stdUploadFileSelected
				}
			});	
		}
	,	swfUploadUploadProgress: function(file, bytesComplete, bytesTotal){
			this.updateFile(this.swfUploadItems[file.index], 'progress', Math.round((bytesComplete / bytesTotal)*100));	
		}
	,	swfUploadFileDialogComplete: function(){
			this.swfUploader.startUpload();
		}
	,	swfUploadUploadStart: function(file){
			this.updateFile(this.swfUploadItems[file.index], 'status', 'Sending');
		}
	,	swfUploadComplete: function(file){ 
			this.swfUploader.startUpload(); 
		}
	,	swfUploadUploadError: function(file, errorCode, message){
			this.fileAlert('<BR>'+file.name+'<BR><b>'+message+'</b><BR>');
			this.updateFile(this.swfUploadItems[file.index], 'status', 'Error');
			this.fireEvent('fileupload', this, false, {error:message});
		}
	,	swfUploadSuccess: function(file, serverData){ 
			try{
				var result = Ext.util.JSON.decode(serverData);
			}catch(e){
				Ext.MessageBox.show({
					buttons: Ext.MessageBox.OK
					,icon: Ext.MessageBox.ERROR
					,modal:false
					,title:'Upload Error!'
					,msg:'Invalid JSON Data Returned!<BR><BR>Please refresh the page to try again.'
				});
				this.updateFile(this.swfUploadItems[file.index], 'status', 'Error');
				this.fireEvent('fileupload', this, false, {error:'Invalid JSON returned'});
				return true;
			}
			if( result.success ){
				this.swfUploadItems[file.index].set('progress',100);
				this.swfUploadItems[file.index].set('status', 'Done');
				this.swfUploadItems[file.index].commit();
				this.fireEvent('fileupload', this, true, result);
			}else{
				this.fileAlert('<BR>'+file.name+'<BR><b>'+result.error+'</b><BR>');
				this.updateFile(this.swfUploadItems[file.index], 'status', 'Error');
				this.fireEvent('fileupload', this, false, result);
			}
	}
	,	swfUploadfileQueued: function(file){
			this.swfUploadItems[file.index] = this.addFile({
				name: file.name
				,size: file.size
			});
			return true;
		}
	,	swfUploadFileQueError: function(file, error, message){
			this.swfUploadItems[file.index] = this.addFile({
				name: file.name
				,size: file.size
			});
			this.updateFile(this.swfUploadItems[file.index], 'status', 'Error');
			this.fileAlert('<BR>'+file.name+'<BR><b>'+message+'</b><BR>');
			this.fireEvent('fileselectionerror', message);
		}	
	,	progressBarColumnTemplate: new Ext.XTemplate(
			'<div class="ux-progress-cell-inner ux-progress-cell-inner-center ux-progress-cell-foreground">',
				'<div>{value} %</div>',
			'</div>',
			'<div class="ux-progress-cell-inner ux-progress-cell-inner-center ux-progress-cell-background" style="left:{value}%">',
				'<div style="left:-{value}%">{value} %</div>',
			'</div>'
		)
	,	progressBarColumnRenderer:function(value, meta, record, rowIndex, colIndex, store){
			meta.css += ' x-grid3-td-progress-cell';
			return this.progressBarColumnTemplate.apply({
				value: value
			});
	}
	,	processDnDFileUpload:function(file){
			var fileRec = this.addFile({
					name: file.fileName
				,	size: file.fileSize
				,	type: file.type
			});
			
			if(file.fileSize > this.maxFileSizeBytes){
				this.updateFile(fileRec, 'status', 'Error');
				this.fileAlert('<BR>'+file.fileName+'<BR><b>File size exceeds allowed limit.</b><BR>');
				this.fireEvent('fileselectionerror', 'File size exceeds allowed limit.');
				return true;
			}
		
			var upload = new Ext.ux.XHRUpload({
					url: 'http://images.cyberfloralouisiana.com/portal/unittest/browse/upload.php'
				,	filePostName: 'Filedata'
				,	fileNameHeader: 'X-File-Name'
				,	sendMultiPartFormData: false
				,	file:file
				,	listeners:{
						scope:this
					,	uploadloadstart:function(event){
							this.updateFile(fileRec, 'status', 'Sending');
						}
					,	uploadprogress:function(event){
							this.updateFile(fileRec, 'progress', Math.round((event.loaded / event.total)*100));
						}
					,	loadstart:function(event){
							this.updateFile(fileRec, 'status', 'Sending');
						}
					,	progress:function(event){
							fileRec.set('progress', Math.round((event.loaded / event.total)*100) );
							fileRec.commit();
						}
					,	abort:function(event){
							this.updateFile(fileRec, 'status', 'Aborted');
							this.fireEvent('fileupload', this, false, {error:'XHR upload aborted'});
						}
					,	error:function(event){
							this.updateFile(fileRec, 'status', 'Error');
							this.fireEvent('fileupload', this, false, {error:'XHR upload error'});
						}
					,	load:function(event){	
							try{
								var result = Ext.util.JSON.decode(upload.xhr.responseText);
							}catch(e){
								Ext.MessageBox.show({
										buttons: Ext.MessageBox.OK
									,	icon: Ext.MessageBox.ERROR
									,	modal:false
									,	title:'Upload Error!'
									,	msg:'Invalid JSON Data Returned!<BR><BR>Please refresh the page to try again.'
								});
								this.updateFile(fileRec, 'status', 'Error');
								this.fireEvent('fileupload', this, false, {error:'Invalid JSON returned'});
								return true;
							}
							if( result.success ){
								fileRec.set('progress', 100 );
								fileRec.set('status', 'Done');
								fileRec.commit();						
								this.fireEvent('fileupload', this, true, result);
							}else{
								this.fileAlert('<BR>'+file.name+'<BR><b>'+result.error+'</b><BR>');
								this.updateFile(fileRec, 'status', 'Error');
								this.fireEvent('fileupload', this, false, result);
							}
						}
					}
			});
			upload.send();
	} 
	,	addFile: function(file){
			var fileRec = new this.fileRecord(
				Ext.apply(file,{
						id: ++this.fileId
					,	status: 'Pending'
					,	progress: '0'
					,	complete: '0'
				})
			);
			this.fileGrid.store.add(fileRec);
		return fileRec;
	}
	,	updateFile:function(fileRec, key, value){
			fileRec.set(key, value);
			fileRec.commit();
	}
	,	fileAlert:function(text){
			if(this.supressPopups){
				return true;
			}
			if(this.fileAlertMsg === undefined || !this.fileAlertMsg.isVisible()){
				this.fileAlertMsgText = 'Error uploading:<BR>'+text;
				this.fileAlertMsg = Ext.MessageBox.show({
					title:'Upload Error',
					msg: this.fileAlertMsgText,
					buttons: Ext.Msg.OK,
					modal:false,
					icon: Ext.MessageBox.ERROR
				});
			}else{
					this.fileAlertMsgText += text;
					this.fileAlertMsg.updateText(this.fileAlertMsgText);
					this.fileAlertMsg.getDialog().focus();
			}
		
		}
	,	stdUploadFileSelected: function(fileBrowser, fileName){	
			var lastSlash = fileName.lastIndexOf('/'); 
			if( lastSlash < 0 ){
				lastSlash = fileName.lastIndexOf('\\'); 
			}
			if(lastSlash > 0){
				fileName = fileName.substr(lastSlash+1);
			}
			var file = {
					name: fileName
				,	size:'0'
			};
			
			if(Ext.isDefined(fileBrowser.fileInput.dom.files) ){
				file.size = fileBrowser.fileInput.dom.files[0].size;
				file.type = fileBrowser.fileInput.dom.files[0].type;
			};
			
			var fileRec = this.addFile(file);
			
			if( file.size > this.maxFileSizeBytes){
				this.updateFile(fileRec, 'status', 'Error');
				this.fileAlert('<BR>'+file.name+'<BR><b>File size exceeds allowed limit.</b><BR>');
				this.fireEvent('fileselectionerror', 'File size exceeds allowed limit.');
				return true;
			}
			
			var formEl = document.createElement('form'),
			extraPost;
			for( attr in this.extraPostData){
				extraPost = document.createElement('input'),
				extraPost.type = 'hidden';
				extraPost.name = attr;
				extraPost.value = this.extraPostData[attr];
				formEl.appendChild(extraPost);
			}
			formEl = this.el.appendChild(formEl);
			formEl.fileRec = fileRec;
			fileBrowser.fileInput.addClass('au-hidden');
			formEl.appendChild(fileBrowser.fileInput);
			formEl.addClass('au-hidden');
			var formSubmit = new Ext.form.BasicForm(formEl,{
					method: 'POST'
				,	fileUpload: true
			});
			
			formSubmit.submit({
					url: 'http://images.cyberfloralouisiana.com/portal/unittest/browse/uploader.php'
				,	scope: this
				,	success: this.stdUploadSuccess
				,	failure: this.stdUploadFail
			});
			this.updateFile(fileRec, 'status', 'Sending');
			this.initStdUpload(); 
		}
	,	stdUploadSuccess:function(form, action){
			form.el.fileRec.set('progress',100);
			form.el.fileRec.set('status', 'Done');
			form.el.fileRec.commit();
			this.fireEvent('fileupload', this, true, action.result);
		}
	,	stdUploadFail:function(form, action){
			this.updateFile(form.el.fileRec, 'status', 'Error');
			this.fireEvent('fileupload', this, false, action.result);
			this.fileAlert('<BR>'+form.el.fileRec.get('name')+'<BR><b>'+action.result.error+'</b><BR>');
		}		
})