<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html><head>
<title>Progress of Collection</title>
<link href="../syntaxhighliter/shCore.css" rel="stylesheet" />
<link href="../syntaxhighliter/shThemeDefault.css" rel="stylesheet" />
<script src="../syntaxhighliter/xrepexp.js"></script>
<script src="../syntaxhighliter/shCore.js"></script>
<script type="text/javascript" src="../syntaxhighliter/shBrushJScript.js"></script>

<link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-3.3.0/resources/css/ext-all.css">
<link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-3.3.0/resources/css/xtheme-gray.css">

<link href="../../resources/css/style.css" rel="stylesheet" type="text/css">
<link href="layout.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="http://extjs.cachefly.net/ext-3.3.0/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="http://extjs.cachefly.net/ext-3.3.0/ext-all.js"></script>
<?php
	//	include("../../config.dynamic.php");
?>	
<script type="text/javascript" src="imageportal.progressofcollection.js"></script>	   
<script type="text/javascript" src="imageportal.progressofcollection-remote.js"></script>
	
<script type="text/javascript">
  Ext.onReady(function(){
    Ext.QuickTips.init();
		Ext.fly(document.body).on('contextmenu', function(e, target) {
			e.preventDefault();
		});

	
	Config = {
			baseUrl: "http://images.cyberfloralouisiana.com/portal/" //"<?php print $config['configWebPath'];?>"
		}

	var imagepanel = new ImagePortal.ProgressOfCollectionRemote({
            title: "Specimen Imaging Progress"
        ,   renderTo: 'imagepanel'
        ,   styleImaged: {
                color: 0x8dc63f
            }
        ,   styleNotImaged: {
                color:0xb5de18
            }
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

&lt;script type="text/javascript" src="http://extjs.cachefly.net/ext-3.3.0/adapter/ext/ext-base.js">&lt;/script>
&lt;script type="text/javascript" src="http://extjs.cachefly.net/ext-3.3.0/ext-all.js">&lt;/script>
&lt;?php
	//	include("../../config.dynamic.php");
?>	
&lt;script type="text/javascript" src="imageportal.progressofcollection.js">&lt;/script>	   
&lt;script type="text/javascript" src="imageportal.progressofcollection-remote.js">&lt;/script>
	
&lt;script type="text/javascript">
  Ext.onReady(function(){
    Ext.QuickTips.init();
		Ext.fly(document.body).on('contextmenu', function(e, target) {
			e.preventDefault();
		});

	Config = {
			baseUrl: "http://images.cyberfloralouisiana.com/portal/" 
		}

	var imagepanel = new ImagePortal.ProgressOfCollectionRemote({
            title: "Specimen Imaging Progress"
        ,    width: 800
        ,    renderTo: 'imagepanel'
        ,    styleImaged: {
                color: 0x8dc63f
            }
        ,    styleNotImaged: {
                color:0xb5de18
            }
    });

});
&lt;/script>	
...
&lt;div id="imagepanel" style="padding:5px; height: 380px; width: 350px; background-color: #E6E6E0">&lt;/div>
		</pre>
    </div>

<h3>Biodiveristy Image Server Progress of Collection Example</h3><br>
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