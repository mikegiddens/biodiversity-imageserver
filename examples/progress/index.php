<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html><head>
<title>Progress of Collection</title>

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
</script>	 
    
</head>
<body>
	<div id="imagepanel"></div>
</body>
</html>