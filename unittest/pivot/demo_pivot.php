<?php
require_once('phpBIS.php');

$sdk = new phpBIS('{yourkey}', 'http://bis.silverbiology.com/dev/resources/api');

$category = $_REQUEST['category'];
$values = json_decode($_REQUEST['value'], true);

foreach($values as $value) {
	$result[] =$sdk->listImageBySetKeyValue($category, $value);
}

if(!$result) {
	echo $sdk->lastError['code']. ' : ' . $sdk->lastError['msg'];
	exit;
}

$sets = array();
foreach($result as $res) {
	foreach($res['data'] as $set) {
		if(!in_array($set[0]['name'], $sets)) {
			$sets[] = $set[0]['name'];
		}
	}
}
?>

<HTML>
<HEAD>
<TITLE>Demo</TITLE>
</HEAD>
<BODY>
<h2><?php echo $category; ?></h2>
<table bgcolor="#CFCFCF">
<tr><th></th>
<?php
foreach($sets as $set) {
	echo '<th>' . $set . '</th>';
}
?>
</tr>
<?php
$i = 0;
foreach($result as $res) {
?>
<tr>
<?php
echo '<th>' . $values[$i++] . '</th>';
foreach($sets as $set) {
	$flag = 0;
	foreach($res['data'] as $r) {
		if($r[0]['name']==$set) {
			?> <td> <img src="<?php echo $r[0]['values'][0]['images'][0]['url'];  ?>" width="192" height="120" /> </td> <?php
			$flag = 1;
			break;
		} 
	}
	if($flag == 0) {
		?> <td> No Image </td> <?php
	}
}
?>
</tr>
<?php
}
?>
</table>
</BODY>
</HTML>