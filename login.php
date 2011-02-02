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

</head>

<body>


<form name="login" action="<?PHP print $_SERVER['PHP_SELF']; ?>" method="POST">
<div>
<label style="font-weight:bold;"><img src="images/silverimage.png" width="136" height="70" /><br />
</label>
<hr size="1" noshade="noshade"><br>
<div>
	<table width="400" border="0" cellspacing="5" cellpadding="0">
		<tr>
		<td>User Name</td>
		<td><input type="text" name="user" id="user" /></td>
	</tr>
	<tr>
		<td>Password</td>
		<td><input type="password" name="pass" id="pass" /></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="go" value="Login" /></td>
	</tr>
</table>
<br><br>
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