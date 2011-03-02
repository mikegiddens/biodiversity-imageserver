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
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php print $config["title"] ?></title>

	<script>
		var USER = '<?php print $_SESSION["user"]; ?>';
	</script>


	<link rel="stylesheet" type="text/css" href="resources/css/ext-all.css">
	<link rel="stylesheet" type="text/css" href="resources/css/explorerview.css">
	<script type="text/javascript" src="resources/ext/ext-base.js"></script>
	<script type="text/javascript" src="resources/ext/ext-all.js"></script>

	<link rel="stylesheet" type="text/css" href="resources/css/style.css" />
	<link rel="stylesheet" type="text/css" href="resources/css/Portal.css" />
	<link rel="stylesheet" type="text/css" href="resources/css/GroupTab.css" />
	<link rel="stylesheet" type="text/css" href="resources/ext/gridfilters/css/GridFilters.css" />
	<link rel="stylesheet" type="text/css" href="resources/ext/gridfilters/css/RangeMenu.css" /> 
	<link rel="stylesheet" type="text/css" href="resources/css/tpl.css" />   
 
  <script type="text/javascript" src="http://www.google.com/jsapi?key=<?php print $config["google_key"]; ?>"></script>
  <script type="text/javascript">
          google.load("maps", "3",{"other_params":"sensor=false"});
          google.load('visualization', '1', {packages: ['linechart', 'barchart', 'piechart']});
  </script> 

 	<script type="text/javascript" src="config.js"></script> 	
	<script type="text/javascript" src="resources/js/classes/biodiversity.js"></script>
	<script type="text/javascript" src="resources/js/progressui.js"></script>
</head>
<body>
</body>
</html>