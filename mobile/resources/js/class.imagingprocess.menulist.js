/**
 * @copyright SilverBiology, LLC
 * @author SilverBiology, LLC
 * @website http://www.silverbiology.com
*/
Ext.namespace('ImagingProgress');

ImagingProgress.MenuList = function(config){
	
	var store = new Ext.data.TreeStore({
	            model: 'CFLAMenu'
            ,	totalProperty:'totalCount'
			,	proxy: {
	                	type: 'ajax'
	                ,	url: 'getMainMenu.json' //http://dev.silvercollection.silverbiology.com/api/silvercollection.php?cmd=checklist-nodes&collections=&filter=null&nodeApi=HigherGeography&nodeValue='  
					,	reader: {
					            type: 'tree'
					         ,  root: 'records'
					        }
				}
        });

	Ext.apply(this, config, { 
					getDetailCard: this.addDetailCards 
            	,	store: store
				,	getTitleTextTpl: function() {
                			return '{' + this.displayField + '}';
            		}
				,	toolbar: {
								title: 'CyberFlora LA'
							}
				,	disclosure: {
				                scope: this
			                ,	handler: function(record, btn, index) {
				              //      alert('Disclose more info for ' + record.get('text'));
				                }
		            	}
				,	listeners:{
								selectionchange: this.changeStoreUrl
							,	cardswitch : function(panel,newCard,oldCard,index,e){
										if (index == 0) {
											this.toolbar.setTitle("CyberFlora LA");
											this.toolbar.doLayout();
										}
										if (index == 2) {
											this.toolbar.setTitle("Collections");
											this.toolbar.doLayout();
										}	
								}	
					}				
		});
		
		ImagingProgress.MenuList.superclass.constructor.call(this,config);
};
Ext.extend(ImagingProgress.MenuList , Ext.NestedList, {
		addDetailCards:function(record, parentRecord) {
    				var text = record.attributes.record.data.text;
					switch(text) {
						case 'About':
								CFLABUS.fireEvent('ChangeMainMenu',1,false,this);
							break;
						case 'All':
								var url='../resources/api/api.php?cmd=images&code=&dir=ASC&filter=&limit=25&sort=&start=0'
								CFLABUS.fireEvent('ChangeMainMenu',2,url,this);
							break;
						case 'Images':
							//	silvercollectionBUS.fireEvent('ChangeMainMenu',3,this);
							break;
					}	
			}
			
	,	changeStoreUrl:function(list,sel){
					if(list.selected.items.length == 0)
						return;
					
					var data = list.selected.items[0].data;
					if (data.text == 'Collections' || data.text == 'Images' || data.text == 'About' || data.text == 'Browse') 
							this.selectedRoot =  list.selected.items[0].data.text;
					if(Ext.isEmpty(data.text)){
								this.loadCollImages(list);
								return;
						}
					
					switch(this.selectedRoot) {
							case 'Collections':
									this.changeCollectionCMD(list);
								break;
							case 'Checklist':
									this.changeChecklistCMD(list);
								break;
							case 'Images':
									this.changeImageCMD(list);
								break;
							case 'About':
									this.displayField = 'text';
									this.store.proxy.setReader({
									            type: 'tree'
									         ,  root: 'records'
									        });
								break;	
						}
			}
			
	,	changeCollectionCMD:function(list){
					var data =  list.selected.items[0].data;
					this.displayField = 'name';
					this.store.proxy.setReader({
					            type: 'tree'
					         ,  root: 'records'
					        });
					this.store.proxy.url='../resources/api/api.php?cmd=collections'		
			}
			
	,	loadCollImages:function(list){
					var data =  list.selected.items[0].data;
					var url='../resources/api/api.php?cmd=images&code='+data.code+'&dir=ASC&filter=&limit=25&sort&start=0'
					CFLABUS.fireEvent('ChangeMainMenu',2,url,this);
		}
		
	,	adjustBackCard:function(){
					var currList      = this.getActiveItem(),
			            currIdx       = this.items.indexOf(currList);
					    var prevDepth     = currIdx - 1,
			                prevList      = this.items.itemAt(prevDepth),
			                recordNode    = prevList.recordNode,
			                record        = recordNode.getRecord(),
			                parentNode    = recordNode ? recordNode.parentNode : null,
			                backBtn       = this.backButton,
			                backToggleMth = (prevDepth !== 0) ? 'show' : 'hide',
			                backBtnText;
			
			            this.setCard(prevDepth, {
			                type: this.animation,
			                reverse: true,
			                after: function(el, opts) {
			                    this.remove(currList);
			                    if (this.clearSelectionDefer) {
			                        prevList.clearSelections.defer(this.clearSelectionDefer, prevList);
			                    }
			                },
			                scope: this
			            });
			
			            if (backBtn) {
			                backBtn[backToggleMth]();
			                if (parentNode) {
			                    backBtnText = this.useTitleAsBackText ? this.renderTitleText(parentNode) : this.backText;
			                    backBtn.setText(backBtnText);
			                }
			            }
			
			
			            if (this.toolbar && this.updateTitleText) {
			                this.toolbar.setTitle(recordNode && recordNode.getRecord() ? this.renderTitleText(recordNode) : this.title || '');
			                this.toolbar.doLayout();
			            }
						this.doLayout();
			}					
});	
