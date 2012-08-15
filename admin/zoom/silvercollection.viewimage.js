/**
 * @copyright SilverBiology
 * @website http://www.silverbiology.com
*/
Ext.namespace('SilverCollection');

SilverCollection.ViewImage = function( config ) {
	var useDefaultTpl = true;
	var crTpl = false;
	
	if ( Config.SilverCollection.ImageViewer ) {
		if ( Config.SilverCollection.ImageViewer.copyrightTpl ) {
			this.ccTpl = Config.SilverCollection.ImageViewer.copyrightTpl;
			crTpl = false;
		}
	}
	if ( crTpl ) {
		var me = this;
		this.ccTpl = new Ext.XTemplate(
			'{copyright:this.getSymbol}&nbsp;{Author}',
			{
				getSymbol: function( copyright ) {
					switch( copyright.toLowerCase() ) {
						case 'by':
							return '<a href="http://creativecommons.org/licenses/by/3.0/" target="_blank"><img style="float: left;" src="'+Config.General.Home.URL+'resources/images/cc/by.png"></a>';
							break;
						case 'by-sa':
							return '<a href="http://creativecommons.org/licenses/by/3.0/" target="_blank"><img style="float: left;" src="'+Config.General.Home.URL+'resources/images/cc/by-sa.png"></a>';
							break;
						case 'by-nd':
							return '<a href="http://creativecommons.org/licenses/by/3.0/" target="_blank"><img style="float: left;" src="'+Config.General.Home.URL+'resources/images/cc/by-nd.png"></a>';
							break;
						case 'by-nc':
							return '<a href="http://creativecommons.org/licenses/by/3.0/" target="_blank"><img style="float: left;" src="'+Config.General.Home.URL+'resources/images/cc/by-nc.png"></a>';
							break;
						case 'by-nc-sa':
							return '<a href="http://creativecommons.org/licenses/by/3.0/" target="_blank"><img style="float: left;" src="'+Config.General.Home.URL+'resources/images/cc/by-nc-sa.png"></a>';
							break;
						case 'by-nc-nd':
							return '<a href="http://creativecommons.org/licenses/by/3.0/" target="_blank"><img style="float: left;" src="'+Config.General.Home.URL+'resources/images/cc/by-nc-nd.png"></a>';
							break;
						case 'sampling':
						case 'by-sampling':
							return '<a href="http://creativecommons.org/licenses/by/3.0/" target="_blank"><img style="float: left;" src="'+Config.General.Home.URL+'resources/images/cc/sampling.png"></a>';
							break;
						case 'nc-sampling':
						case 'by-nc-sampling':
							return '<a href="http://creativecommons.org/licenses/by/3.0/" target="_blank"><img style="float: left;" src="'+Config.General.Home.URL+'resources/images/cc/nc-sampling.png"></a>';
							break;
						case '':
						case 'copyright':
						case '&copy;':
						case '&#169;':
							return '&#169;&nbsp;' + this.defaultCopyrightText;
							break;
						default:
							return copyright;
							break;
					}
				}
			}
		);
	}
	
	this.interactiveEnabled = false;
	if ( Config.SilverCollection.InteractiveImages ) this.interactiveEnabled = Config.SilverCollection.InteractiveImages.enabled;
	
	this.imagesheet = new SilverCollection.ImageViewer();
	if ( this.interactiveEnabled ) this.tilesheet = new SilverCollection.ImageZoomPanel();
	
	this.cardItems = [];
	this.cardItems.push( this.imagesheet );
	if ( this.interactiveEnabled ) this.cardItems.push( this.tilesheet );

	this.toggleBtn = new Ext.Button({
		text:'Switch to Interactive View',
		iconClass: 'icon_zoom',
		hidden: !this.interactiveEnabled,
		scope: this,
		handler: this.toggleMode
	});

	this.lastIndex = -1;

	Ext.apply(this,	config, {
			iconCls: 'icon_picture'
		,	items: this.cardItems
		,	border: false
		,	layout: 'card'
		,	ImgCount: 0
		,	isLoaded: false
//		,	listeners: { afterrender: '' }
		,	tbar: {
					style: 'margin:0 auto;'
				,	hidden: true
				,	buttonAlign: 'center'
				,	items: [{
							iconCls: 'icon_previous'
//						,	height:30	
						,	scope:this
						,	buttonAlign: 'center'
						,	handler: function(){
								this.activeModeIndex = 0;
								this.getLayout().setActiveItem( 0 );
								this.ImgCount = this.ImgCount-1;
								this.imageProcess();
							}
					}, {
							xtype:'label'
						,	scope:this
						,	ref:'info'	
					}, {
							iconCls: 'icon_next'
//						,	height: 30	
						,	scope: this
						,	handler: function(){
								this.activeModeIndex = 0;
								this.getLayout().setActiveItem( 0 );
								this.ImgCount = this.ImgCount + 1;
								this.imageProcess();
							}
					}]
			}
		,	bbar: [
				{
					text: this.buttonText,
					ref: '../rawBtn',
					iconCls: 'icon_picture_save',
					scope: this,
					handler: this.viewRaw
				},
				{ xtype: 'tbseparator' },
				this.toggleBtn,
				'->',
				{ xtype: 'tbtext', ref: '../copyField', tpl: this.ccTpl }
			]
		,	calcDim: function( width, height, maxDim ) {
				var dim = 0;
				var invert = 0;
				var ratio_1 = (maxDim / height);
				var ratio_2 = (maxDim / width);		
				if ( ratio_1 <= ratio_2 ) {
					return ( (width * ratio_1) + 32 );
				} else {
					return ( (width * ratio_2) + 32 );
				}
			}
		,	activeModeIndex: 0
	});
			
	SilverCollection.ViewImage.superclass.constructor.call(this, config);
	
} 
 
Ext.extend( SilverCollection.ViewImage, Ext.Panel, {

		loadSpecimenByGuid: function(GUID) {
			this.loadSpecimen({GlobalUniqueIdentifier: GUID});
		}

	,	loadSpecimenByID: function(ID) {
			this.loadSpecimen({id: ID});
		}
	,	loadImageById: function( id ) {
			this.loadSpecimen( {imageID: id} );
		}
	,	loadSpecimen: function(filter) {
			if(this.isLoaded){
				return;
			}
			/*
			var mask = new Ext.LoadMask( this.body, {
					msg: 'Loading...'
				,	removeMask: true
			});
			mask.show();
			*/
			Ext.Ajax.request({
					url: Config.General.Home.URL + 'api/silvercollection.php'
				,	method: 'POST'
				,	scope: this
				,	params: {
							cmd: 'image-list'
						,	filter: Ext.encode(filter)
					}
				,	success: function( responseObject ) {
						//mask.hide();
						var record = Ext.decode(responseObject.responseText);
						if( record.success ) {
							this.isLoaded = true; 
							this.specimenRecord = record;
							if ( this.specimenRecord.total_count > 1 ) {
								this.getTopToolbar().setVisible(true);
								this.setIconClass('icon_pictures');
							} else {
								this.setIconClass('icon_picture');
								this.getTopToolbar().setVisible(false);
							}
							this.activeModeIndex = 0;
							this.getLayout().setActiveItem( 0 );
							this.imageProcess();
							this.fireEvent("imageLoaded", this);
						} else {
							SilverCollection.Notice.msg( this.statusTitle, this.txtStatus);
							this.setIconClass('icon_picture');
							this.getTopToolbar().setVisible(false);
						}
					}
				,	failure: function() {
						//mask.hide();
						SilverCollection.Notice.msg( this.statusTitle, this.statusTitle );
					}
			})
		}
	,	imageProcess: function(){
			var data = this.getImageData();
			if (data) {
				if ( !(data.tileUrl) || data.tileUrl == '' || !(this.interactiveEnabled) ) {
					this.toggleBtn.disable();
					this.toggleBtn.setText( this.btnUnavailableText );
					this.toggleBtn.setIconClass( 'icon_picture_error' );
				} else {
					this.toggleBtn.enable();
					if ( this.activeModeIndex == 1 ) {
						this.toggleBtn.setText( this.btnStaticText );
						this.toggleBtn.setIconClass( 'icon_picture' );
					} else {
						this.toggleBtn.setText( this.btnInteractiveText );
						this.toggleBtn.setIconClass( 'icon_pictures' );
					}
				}
				if ( this.activeModeIndex == 0 ) {
					this.imagesheet.imageProcess( data );
				} else {
					if (this.ImgCount != this.lastIndex) {
						this.tilesheet.loadImage( data );
						this.lastIndex = this.ImgCount;
					}
				}
				this.copyField.update( data );
			} else {
				SilverCollection.Notice.msg(this.statusTitle, this.msgDataNotLoaded);
			}
		}		
	
	,	getImageData: function(){
			if ( this.ImgCount < 0 ) {
				this.ImgCount = this.specimenRecord.total_count-1;
			} else if ( this.ImgCount > this.specimenRecord.total_count-1 ) {
				this.ImgCount = 0;
			}
			this.getTopToolbar().info.update('&nbsp;<strong>' + (this.ImgCount+1) + '</strong>&nbsp;of&nbsp;' + this.specimenRecord.total_count + '&nbsp;');
			return this.specimenRecord.results[this.ImgCount];
		}
		
	,	showImageTpl: function(data) {
			this.getTopToolbar().setVisible(false);
			this.imagesheet.imageProcess(data);
		}
	,	toggleMode: function( btn, e ) {
			var next;
			(this.activeModeIndex == 0) ? next = 1 : next = 0;
			this.activeModeIndex = next;
			this.getLayout().setActiveItem( next );
			this.imageProcess();
		}
	,	viewRaw: function() {
			var data = this.getImageData();
			var tpl = Config.SilverCollection.ImageViewer.originalTpl || new Ext.Template("{path}{filename}.{extension}");
			window.open( tpl.apply({
				path: data.path,
				filename: data.filename,
				extension: data.extension
			}), '_blank' );
		}
});
