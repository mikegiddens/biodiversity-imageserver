<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', '1');

session_start();
include_once("config.php");
if (!(isset($_SESSION['user']) && isset($_SESSION['pw']))) {
		header('Location: login.php');
		exit();
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?php print $config["title"] ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="alternate" href="resources/api/api.php?cmd=images&code=&dir=ASC&filters=&output=rss" type="application/rss+xml" title="Image Feed" id="images" />

	<script>
		var USER = '<?php print $_SESSION["user"]; ?>';
	</script>

    <!-- ** CSS ** -->

	<link rel="stylesheet" type="text/css" href="resources/css/ext-all.css">
	<link rel="stylesheet" type="text/css" href="resources/css/explorerview.css">
	<script type="text/javascript" src="resources/ext/ext-base.js"></script>
	<script type="text/javascript" src="resources/ext/ext-all.js"></script>

	<!-- overrides to base library -->	
	<link rel="stylesheet" type="text/css" href="resources/css/style.css" />
	<link rel="stylesheet" type="text/css" href="resources/css/Portal.css" />
	<link rel="stylesheet" type="text/css" href="resources/css/GroupTab.css" />
	<link rel="stylesheet" type="text/css" href="resources/ext/gridfilters/css/GridFilters.css" />
	<link rel="stylesheet" type="text/css" href="resources/ext/gridfilters/css/RangeMenu.css" /> 
	<link rel="stylesheet" type="text/css" href="resources/css/tpl.css" />   
 
	<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=<?php print $config["google_key"]; ?>" type="text/javascript"></script>
	

  <!-- Reporting -->
  <script type="text/javascript" src="http://www.google.com/jsapi?key=<?php print $config["google_key"]; ?>"></script>
  <script type="text/javascript">
          google.load("maps", "3",{"other_params":"sensor=false"});
          google.load('visualization', '1', {packages: ['linechart', 'barchart', 'piechart']});
  </script> 
		
 	<script type="text/javascript" src="resources/js/classes/GVisualizationPanel.js"></script>
 <!--	<script type="text/javascript" src="resources/js/classes/Ext.ux.GMapPanel.js"></script>	 -->
	
	<script type="text/javascript" src="resources/js/classes/Ext.ux.GMapPanel3.js"></script>	
	
 	<script type="text/javascript" src="config.js"></script> 	
 	<script type="text/javascript" src="resources/ext/treecombo.js"></script> 	
	<script type="text/javascript" src="resources/ext/RandomInt.js"></script>  	
	<script type="text/javascript" src="resources/ext/GroupTabPanel.js"></script>
	<script type="text/javascript" src="resources/ext/GroupTab.js"></script>
 	<script type="text/javascript" src="resources/ext/gridfilters/menu/RangeMenu.js"></script>
	<script type="text/javascript" src="resources/ext/gridfilters/menu/ListMenu.js"></script>	
	<script type="text/javascript" src="resources/ext/gridfilters/GridFilters.js"></script>

	<script type="text/javascript" src="resources/ext/gridfilters/filter/Filter.js"></script>
	<script type="text/javascript" src="resources/ext/gridfilters/filter/StringFilter.js"></script>
	<script type="text/javascript" src="resources/ext/gridfilters/filter/DateFilter.js"></script>
	<script type="text/javascript" src="resources/ext/gridfilters/filter/ListFilter.js"></script>
	<script type="text/javascript" src="resources/ext/gridfilters/filter/NumericFilter.js"></script>
	<script type="text/javascript" src="resources/ext/gridfilters/filter/BooleanFilter.js"></script>
	
	<!-- Ux file for XTemplate-->
	<script type="text/javascript" src="resources/plugins/ext.ux.xtemplate.js"></script>
	
	<script type="text/javascript" src="resources/js/classes/imageportal.help.js"></script>

	<!--Files for expoler view & combobox
	-->
	<script type="text/javascript" src="resources/plugins/explorerview/Ext.ux.grid.ExplorerView.js"></script>
	<script type="text/javascript" src="resources/plugins/TwinComboBox.js"></script>

	<!-- page specific -->
	<script type="text/javascript" src="resources/js/classes/imageportal.notice.js"></script>
	<script type="text/javascript" src="resources/js/classes/imageportal.downloadimage.js"></script>
	<script type="text/javascript" src="resources/js/classes/imageportal.imageinfopanel.js"></script>	
	<script type="text/javascript" src="resources/js/classes/imageportal.sequences.js"></script>	   
	<script type="text/javascript" src="resources/js/classes/imageportal.ivflickr.js"></script>	   
<!--	<script type="text/javascript" src="resources/js/classes/imageportal.ivintractive.js"></script>-->	    
	
	<script type="text/javascript" src="unittest/gmap3/imageportal.ivintractive.js"></script>
	
	<script type="text/javascript" src="resources/js/classes/imageportal.popupinput.js"></script>	   
	<script type="text/javascript" src="resources/js/classes/imageportal.zoom.js"></script>	
	<script type="text/javascript" src="resources/js/classes/imageportal.imageviewer.js"></script>
	<script type="text/javascript" src="resources/js/classes/imageportal.monthrangechart.js"></script>  	
	<script type="text/javascript" src="resources/js/classes/imageportal.image.js"></script>	
	<script type="text/javascript" src="resources/js/classes/imageportal.queue.js"></script>	
	<script type="text/javascript" src="resources/js/classes/imageportal.news.js"></script>	
	<script type="text/javascript" src="resources/js/classes/imageportal.contact.js"></script>	
	<script type="text/javascript" src="resources/js/classes/imageportal.progressofcollection.js"></script>	
	<script type="text/javascript" src="resources/js/classes/imageportal.bystaff.js"></script>  	
	<script type="text/javascript" src="resources/js/classes/imageportal.bycollection.js"></script>
	<script type="text/javascript" src="resources/js/classes/imageportal.hsqueue.js"></script>	
  	<script type="text/javascript" src="resources/js/classes/imageportal.piareports.js"></script>
  	<script type="text/javascript" src="resources/js/classes/imageportal.barreports.js"></script>
  	<script type="text/javascript" src="resources/js/classes/imageportal.reporttree.js"></script>
  	<script type="text/javascript" src="resources/js/classes/imageportal.reportpanel.js"></script>

	<script type="text/javascript" src="resources/js/progressui.js"></script>
</head>
<body>
</body>
</html>