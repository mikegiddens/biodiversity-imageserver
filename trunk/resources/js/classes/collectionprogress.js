/*
 * @copyright SilverBiology, LLC
 * @author Mike Giddens
 * @website http://www.silverbiology.com
 * 
 */


Ext.namespace('ImagePortal');ImagePortal.ProgressOfCollection=function(config){this.store=new Ext.data.JsonStore({url:Config.baseUrl+'resources/api/api.php',fields:['collection','imaged','notimaged'],root:'data',baseParams:{cmd:'sizeOfCollection'}});Ext.apply(this,config,{width:400,height:400,title:'Progress of Collections',items:{xtype:'stackedbarchart',store:this.store,yField:'collection',xAxis:new Ext.chart.NumericAxis({stackingEnabled:true,labelRenderer:Ext.util.Format.numberRenderer('0,0')}),series:[{xField:'imaged',displayName:'Imaged'},{xField:'notimaged',displayName:'Not Imaged'}]}});ImagePortal.ProgressOfCollection.superclass.constructor.call(this,config);};Ext.extend(ImagePortal.ProgressOfCollection,Ext.Panel,{});

Ext.namespace('ImagePortal');ImagePortal.ProgressOfCollectionRemote=function(config){var config2={};Ext.apply(config2,config,{border:true,width:700,height:420,store:new Ext.data.JsonStore({proxy:new Ext.data.ScriptTagProxy({url:Config.baseUrl+'resources/api/api.php'}),fields:['collection','imaged','notimaged'],root:'data',baseParams:{cmd:'sizeOfCollection'},autoLoad:true})});ImagePortal.ProgressOfCollectionRemote.superclass.constructor.call(this,config2);};Ext.extend(ImagePortal.ProgressOfCollectionRemote,ImagePortal.ProgressOfCollection,{});
