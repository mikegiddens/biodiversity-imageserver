<?php
require_once('phpBIS.php');

$sdk = new phpBIS('{yourkey}', 'http://bis.silverbiology.com/dev/resources/api');

$category = $_REQUEST['category'];
//$values = json_decode($_REQUEST['value'], true);

$result1 = $sdk->listCategories();
$cat_flag = 0;
if(isset($result1['data']) && is_array($result1['data'])) {
	foreach($result1['data'] as $res1) {
		if(strtolower($res1['title']) == strtolower($category)) {
			$cat_flag = $res1['typeID'];
			break;
		}
	}
}
if($cat_flag) {
	$result2 = $sdk->list_attributes($cat_flag);
	$val_flag = 0;
	if(isset($result2['data']) && is_array($result2['data'])) {
		foreach($result2['data'] as $res2) {
			$values[] = $res2['name'];
			$val_flag = 1;
		}
	}
}
if(!$val_flag) exit;

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