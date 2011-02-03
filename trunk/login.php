<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', '1');

ob_start();
session_start();
require_once("config.php");
require_once("resources/api/classes/access_user/access_user_class.php");

if(isset($_POST['go'])) {

	$user_access = new Access_user( $mysql_host, $mysql_user, $mysql_pass, $mysql_name );
	if (!$user_access->connected) {
		$message = 'Not a valid db connection.';
	} else {
		$ret = $user_access->login_user($_POST['user'],$_POST['pass']);
		if($ret){
			header('Location: index-debug.php');
			exit();
		} else {
			$message = 'Invalid User Name / Password ';
		}
	}
}

?>

<html>
<head>
  <title><?php print $config["title"] ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
.ibox {
	border: thin solid #060;
	padding: 3px;
}

a:link {
	color: #060;
}
a:visited {
	color: #060;
}
a:hover {
	color: #060;
}
a:active {
	color: #060;
}

.ibox:focus { 
  background-color:#A5DC4B; 
}
body,td,th {
	font-family: Arial, Helvetica, sans-serif;
}

-->
</style>
</head>

<body onLoad="document.forms[0].user.focus();">
<br><br>
<form name="login" action="<?php print $_SERVER['PHP_SELF']; ?>" method="POST">
<div style="width: 400px;  margin-left: auto; margin-right: auto;" align="center">
<label style="font-weight:bold;"><img src="images/biodiversity-image-server-logo.jpg" width="318" height="108" alt="Biodiveristy Image Server"><br />
</label>
<hr size="1" noshade="noshade"><br>
<div>
	<table width="400" border="0" cellspacing="5" cellpadding="0">
		<tr>
		<td width="75">&nbsp;</td>
		<td width="80">User Name</td>
		<td width="225"><input type="text" name="user" class="ibox" id="user" /></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>Password</td>
		<td><input type="password" name="pass" class="ibox" id="pass" /></td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
		<td><input type="submit" name="go" value="Login" /></td>
	</tr>
	<tr>
	  <td colspan="3" align="center" style="font-size:10px;"><p>If you are not registered you may use <strong>u:guest p:guest</strong> to sign in.</p>
	    <p><a href="http://code.google.com/p/biodiversity-imageserver/" target="_blank">Click here to learn more about Biodiversity Image Server.</a></p></td>
	  </tr>
  </table>
<br>
<?php
if($message != '') {
	print "<div class='error'>$message</div>";
}
?>
</div>
</div>
</form>
</body>
</html>