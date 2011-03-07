<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html><head>
<title>Specimen Image</title>

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
	<div id="imagepanel"></div>
</body>
</html>