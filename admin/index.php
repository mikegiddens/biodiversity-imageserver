<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', '1');

session_start();
//include_once("config.php");
/*
if (!(isset($_SESSION['user']))) {
	header('Location: login.php');
	exit();
}
*/
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Biodiversity Image Server</title>
  <link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-4.1.1-gpl/resources/css/ext-all.css"/>
  <link rel="stylesheet" type="text/css" href="resources/css/style.css"/>
<!--
  <link rel="stylesheet" type="text/css" href="http://extjs.cachefly.net/ext-4.1.0-gpl/resources/css/ext-all-gray.css"/>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/jquery-ui.min.js"></script>
  <link rel='stylesheet' href='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/ui-lightness/jquery-ui.css'>
-->
  <script type="text/javascript" src="http://extjs.cachefly.net/ext-4.1.1-gpl/ext-all-debug.js"></script>
  <script type="text/javascript" src="resources/js/SearchField.js"></script>
  <script type="text/javascript" src="config.js"></script>
  <script type="text/javascript" src="app.js"></script>
</head>
<body></body>
</html>