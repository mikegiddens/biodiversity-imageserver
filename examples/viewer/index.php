<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html><head>
<title>Plants Images</title>
<link href="../syntaxhighliter/shCore.css" rel="stylesheet" />
<link href="../syntaxhighliter/shThemeDefault.css" rel="stylesheet" />
<script src="../syntaxhighliter/xrepexp.js"></script>
<script src="../syntaxhighliter/shCore.js"></script>
<script type="text/javascript" src="../syntaxhighliter/shBrushJScript.js"></script>

<link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-3.3.0/resources/css/ext-all.css">
<link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-3.3.0/resources/css/xtheme-gray.css">

<link href="../../resources/css/style.css" rel="stylesheet" type="text/css">
<link href="layout.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="../../resources/css/explorerview.css">
<link rel="stylesheet" type="text/css" href="../../resources/css/tpl.css">   
<link rel="stylesheet" type="text/css" href="GridFilters.css">
<script type="text/javascript" src="http://extjs.cachefly.net/ext-3.3.0/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="http://extjs.cachefly.net/ext-3.3.0/ext-all.js"></script>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
          google.load("maps", "3",{"other_params":"sensor=false"});
          google.load('visualization', '1', {packages: ['linechart', 'barchart', 'piechart']});
</script> 
<?php
	//	include("../../config.dynamic.php");
?>

<script type="text/javascript" src="../../resources/js/classes/imageportal-light.js"></script>
<script type="text/javascript">
  Ext.onReady(function(){
    Ext.QuickTips.init();
		Ext.fly(document.body).on('contextmenu', function(e, target) {
			e.preventDefault();
		});
	
	Config = {
			baseUrl: "http://images.cyberfloralouisiana.com/portal/" //"<?php print $config['configWebPath'];?>"
		,	imageStoreParams: { 
					cmd: 'images'
				,	filter:''
			//	,	code:'TRT'
//				,	dir:'ASC'
//				,	filter:''	
//				,	limit:100
//				,	sort:''	
//				,	start:0
			}
//		,	defaultCollection: "Louisiana State University"
		,	disableCollection: false
		,	showCollection: true
		,	image_id: true
		,	lastModified: true
		,	flickr_PlantID: true
		,	picassa_PlantID: true
		,	gTileProcessed: true
		,	processed: true
		,	mode: "remote"
	}

	var ip = new ImagePortal.ImagePortalRemote({
			height: 380
		,	width: 350
		,	renderTo: 'imagepanel'
	});
	ip.initLoadMethod();
}); 
</script>	 
    
</head>
<body>
<div style="float:right; width: 600px">
		<pre type="syntaxhighlighter" class="brush: js">
&lt;link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-3.3.0/resources/css/ext-all.css">
&lt;link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-3.3.0/resources/css/xtheme-gray.css">

&lt;link href="../../resources/css/style.css" rel="stylesheet" type="text/css">
&lt;link href="layout.css" rel="stylesheet" type="text/css">
&lt;link rel="stylesheet" type="text/css" href="../../resources/css/explorerview.css">
&lt;link rel="stylesheet" type="text/css" href="../../resources/css/tpl.css">   
&lt;link rel="stylesheet" type="text/css" href="GridFilters.css">
&lt;script type="text/javascript" src="http://extjs.cachefly.net/ext-3.3.0/adapter/ext/ext-base.js">&lt;/script>
&lt;script type="text/javascript" src="http://extjs.cachefly.net/ext-3.3.0/ext-all.js">&lt;/script>
&lt;script type="text/javascript" src="http://www.google.com/jsapi">&lt;/script>
&lt;script type="text/javascript">
          google.load("maps", "3",{"other_params":"sensor=false"});
          google.load('visualization', '1', {packages: ['linechart', 'barchart', 'piechart']});
&lt;/script> 
&lt;?php
	//	include("../../config.dynamic.php");
?>

&lt;script type="text/javascript" src="../../resources/js/classes/imageportal-light.js">&lt;/script>
&lt;script type="text/javascript">
  Ext.onReady(function(){
    Ext.QuickTips.init();
		Ext.fly(document.body).on('contextmenu', function(e, target) {
			e.preventDefault();
		});
	
	Config = {
			baseUrl: "http://images.cyberfloralouisiana.com/portal/"
		,	imageStoreParams: { 
					cmd: 'images'
				,	filter:''
			}
		,	disableCollection: false
		,	showCollection: true
		,	image_id: true
		,	lastModified: true
		,	flickr_PlantID: true
		,	picassa_PlantID: true
		,	gTileProcessed: true
		,	processed: true
		,	mode: "remote"
	}

	var ip = new ImagePortal.ImagePortalRemote({
			height: 380
		,	width: 350
		,	renderTo: 'imagepanel'
	});
	ip.initLoadMethod();
}); 
&lt;/script>	 	
...
&lt;div id="imagepanel" style="padding:5px; height: 380px; width: 350px; background-color: #E6E6E0">&lt;/div>
		</pre>
    </div>
	<h3>Biodiveristy Image Server Plants Images Example</h3><br>
    <div id="imagepanel" style="padding:5px; height: 380px; width: 350px; background-color: #E6E6E0"></div><br>
	
    <p>This example can be placed on any webpage.<br>
        <a href="../">See more examples...</a><br>
        <br>
        <script type="text/javascript">
				 SyntaxHighlighter.all()
		</script>
    </p>

</body>
</html>