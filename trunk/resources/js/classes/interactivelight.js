/*
 * @copyright SilverBiology, LLC
 * @author Mike Giddens
 * @website http://www.silverbiology.com
 * 
 */


Ext.namespace('ImagePortal');ImagePortal.IVIntractive=function(config){Ext.apply(this,config,{zoomLevel:2,id:'map-canvas',border:true,mapConfOpts:['enableScrollWheelZoom','enableDoubleClickZoom','enableDragging'],mapControls:['GSmallMapControl','GMapTypeControl'],setCenter:{lat:30,lng:-90},width:600,height:400,title:'Specimen Image',ismapReady:false});ImagePortal.IVIntractive.superclass.constructor.call(this,config);};Ext.extend(ImagePortal.IVIntractive,Ext.ux.GMapPanel,{drawImage:function(imagePath){var imgTiles=new google.maps.ImageMapType({getTileUrl:function(ll,z){var X=ll.x%(1<<z);var path=imagePath+"google_tiles/"+(5-z)+"/tile_"+(5-z)+"_"+X+"_"+ll.y+".jpg";return path;},tileSize:new google.maps.Size(256,256),isPng:false,maxZoom:5,name:"Image",minZoom:0,alt:"Specimen Sheet Image"});if(!this.ismapReady){this.ismapReady=true;this.on('mapready',function(map){map.getMap().mapTypes.set('image',imgTiles);map.getMap().setMapTypeId('image');map.getMap().unbind(map.getMap().mapTypes.roadmap);});}else{this.getMap().mapTypes.set('image',imgTiles);this.getMap().setMapTypeId('image');}}});Ext.reg('ivinteractive',ImagePortal.IVIntractive);

Ext.namespace('ImagePortal');ImagePortal.IVIntractiveRemote=function(config){var config2={};this.store=new Ext.data.JsonStore({proxy:new Ext.data.ScriptTagProxy({url:Config.baseUrl+'resources/api/api.php'}),fields:['barcode','image_id','path'],root:'data',baseParams:{cmd:'images'},listeners:{load:function(){this.sendIamgeData();},scope:this}});Ext.apply(config2,config,{border:true,height:550,width:400,title:'Image',iconCls:''});ImagePortal.IVIntractiveRemote.superclass.constructor.call(this,config2);};Ext.extend(ImagePortal.IVIntractiveRemote,ImagePortal.IVIntractive,{loadById:function(image_id){this.store.baseParams={cmd:'images',value:image_id,field:'image_id'}
this.store.load();},loadByBarcode:function(barcode){this.store.baseParams={cmd:'images',type:'list',value:barcode,field:'barcode'}
this.store.load();},sendIamgeData:function(){var data=this.store.getAt(0).data;this.drawImage(data.path);}});
