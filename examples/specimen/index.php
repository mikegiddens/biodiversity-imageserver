<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Specimen Image</title>
<!--Syntax Highlighter Files -->
<link href="../syntaxhighliter/shCore.css" rel="stylesheet" />
<link href="../syntaxhighliter/shThemeDefault.css" rel="stylesheet" />
<script src="../syntaxhighliter/xrepexp.js"></script>
<script src="../syntaxhighliter/shCore.js"></script>
<script type="text/javascript" src="../syntaxhighliter/shBrushJScript.js"></script>


<link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-3.3.0/resources/css/ext-all.css">
<link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-3.3.0/resources/css/xtheme-gray.css">

<link href="../../resources/css/style.css" rel="stylesheet" type="text/css">
<link href="layout.css" rel="stylesheet" type="text/css">
<?php
	//	include("../../config.dynamic.php");
?>
<script type="text/javascript" src="http://extjs.cachefly.net/ext-3.3.0/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="http://extjs.cachefly.net/ext-3.3.0/ext-all.js"></script>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
          google.load("maps", "3",{"other_params":"sensor=false"});
          google.load('visualization', '1', {packages: ['linechart', 'barchart', 'piechart']});
</script> 

	<script type="text/javascript" src="../../resources/js/classes/GVisualizationPanel.js"></script>
 	<script type="text/javascript" src="../../resources/js/classes/Ext.ux.GMapPanel3.js"></script>	
	
	<script type="text/javascript" src="../../resources/js/classes/imageportal.ivintractive.js"></script>	   
  	<script type="text/javascript" src="imageportal.ivintractive-remote.js"></script>
	
<script type="text/javascript">
  Ext.onReady(function(){
    Ext.QuickTips.init();
		Ext.fly(document.body).on('contextmenu', function(e, target) {
			e.preventDefault();
		});

	
	Config = {
			baseUrl: "http://images.cyberfloralouisiana.com/portal/" //"<?php print $config['configWebPath'];?>"
		,	mode: 'local'	
		}

	var imagepanel = new ImagePortal.IVIntractiveRemote({
            title: 'Sample Specimen: Achillea lanulosa (Mountain Yarrow)'
        ,    renderTo: 'imagepanel'
        ,    tbar: [{
                    text: 'Visit USDA Plants Profile Page'
                ,    handler: function() {
                        window.open("http://www.plants.usda.gov/java/profile?symbol=ACMIO", "_blank");
                    }
            }]	
    });
	Ext.Ajax.request({
			scope: this
		,	url: Config.baseUrl + 'resources/api/api.php'
		,	params: {
				cmd:'images'
			}
		,	success: function(response){
				var response = Ext.decode(response.responseText);
				for (var i=0; i<response.data.length; i++){
					if(response.data[i].gTileProcessed != 0){
						imagepanel.loadByBarcode(response.data[i].barcode);
						break;
					}
				}
			}	
		,	failure: function(result){}
	});
});
</script>	 

</head>
<body>
	<div style="float:right; width: 600px">
		<pre type="syntaxhighlighter" class="brush: js">
&lt;link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-3.3.0/resources/css/ext-all.css">
&lt;link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-3.3.0/resources/css/xtheme-gray.css">

&lt;link href="style.css" rel="stylesheet" type="text/css">
&lt;link href="layout.css" rel="stylesheet" type="text/css">
&lt;?php
	//	include("../../config.dynamic.php");
?>
&lt;script type="text/javascript" src="http://extjs.cachefly.net/ext-3.3.0/adapter/ext/ext-base.js">&lt;/script>
&lt;script type="text/javascript" src="http://extjs.cachefly.net/ext-3.3.0/ext-all.js">&lt;/script>
&lt;script type="text/javascript" src="http://www.google.com/jsapi">&lt;/script>
&lt;script type="text/javascript">
    google.load("maps", "3",{"other_params":"sensor=false"});
    google.load('visualization', '1', {packages: ['linechart', 'barchart', 'piechart']});
&lt;/script> 

&lt;script type="text/javascript" src="../../resources/js/classes/GVisualizationPanel.js">&lt;/script>
&lt;script type="text/javascript" src="../../resources/js/classes/Ext.ux.GMapPanel3.js">&lt;/script>	
	
&lt;script type="text/javascript" src="../../resources/js/classes/imageportal.ivintractive.js">&lt;/script>	   
&lt;script type="text/javascript" src="imageportal.ivintractive-remote.js">&lt;/script>
&lt;script type="text/javascript">

Ext.onReady(function(){
    Ext.QuickTips.init();
	Ext.fly(document.body).on('contextmenu', function(e, target) {
		e.preventDefault();
	});
	Config = {
		baseUrl: "http://images.cyberfloralouisiana.com/portal/" 
	,	mode: 'local'	
	}
	var imagepanel = new ImagePortal.IVIntractiveRemote({
		title: 'Sample Specimen: Achillea lanulosa (Mountain Yarrow)'
	,   renderTo: 'imagepanel'
	,   tbar: [{
				text: 'Visit USDA Plants Profile Page'
			,   handler: function() {
					window.open("http://www.plants.usda.gov/java/profile?symbol=ACMIO", "_blank");
				}
		}]	
    });
	Ext.Ajax.request({
		scope: this
	,	url: Config.baseUrl + 'resources/api/api.php'
	,	params: {
			cmd:'images'
		}
	,	success: function(response){
			var response = Ext.decode(response.responseText);
			for (var i=0; i&lt;response.data.length; i++){
				if(response.data[i].gTileProcessed != 0){
					imagepanel.loadByBarcode(response.data[i].barcode);
					break;
				}
			}
		}	
	,	failure: function(result){}
	});
});
&lt;/script>
...
&lt;div id="imagepanel" style="padding: 5px; height: 380px; width: 350px; background-color: #E6E6E0">&lt;/div>
...	</pre>
		</div>
	
	<h3>Biodiveristy Image Server Specimen Example</h3><br>
    <div id="imagepanel" style="padding: 5px; height: 380px; width: 350px; background-color: #E6E6E0"></div><br>
    <p>This example can be placed on any webpage.<br>
		<a href="../">See more examples...</a><br>
		<br>
		<script type="text/javascript">
				 SyntaxHighlighter.all();
		</script>
    </p>
	
</body>
</html>