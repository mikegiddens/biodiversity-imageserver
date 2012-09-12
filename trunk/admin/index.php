<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', '1');

session_start();
include_once("../config.php");

if (!(isset($_SESSION['user']))) {
	header('Location: login.php');
	exit();
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Biodiversity Image Server</title>
  <link rel="stylesheet" type="text/css" href="resources/css/style.css"/>
  <link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-4.1.1-gpl/resources/css/ext-all-gray.css"/>
  <script type="text/javascript" src="http://extjs.cachefly.net/ext-4.1.1-gpl/ext-all-debug.js"></script>
  <script type="text/javascript" src="resources/js/SearchField.js"></script>
  <script type="text/javascript" src="config.js"></script>
  <script type="text/javascript" src="app.js"></script>
</head>
<body>
	<div id="loading-mask"></div>
	<div id="loading">
        <div class="loading-indicator">
            <img src="resources/img/biodiversity-image-server-logo.jpg" style="margin-right:8px;" align="absmiddle"/><br/>
            <img src="resources/img/loading.gif" style="margin-right:6px;" align="absmiddle"/>
            <span id="loading-msg">Loading Biodiversity Image Server</span>
        </div>
	</div>
</body>
</html>
