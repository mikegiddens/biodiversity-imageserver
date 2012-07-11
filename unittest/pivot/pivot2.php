<?php
require_once('phpBIS.php');

$sdk = new phpBIS('{yourKey}', 'http://bis.silverbiology.com/dev/resources/api');

$category = $_REQUEST['category'];
$displaySets = $_REQUEST['sets'];
$displaySets = json_decode($displaySets, true);
if(!is_array($displaySets)) exit;
//$values = json_decode($_REQUEST['value'], true);

$result1 = $sdk->listCategories();

$processTime = $result1['processTime'];

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
	$processTime += $result2['processTime'];
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
	$temp = $sdk->listImageBySetKeyValue($category, $value);
	$result[] = $temp;
	$processTime += $temp['processTime'];
}

if(!$result) {
	echo $sdk->lastError['code']. ' : ' . $sdk->lastError['msg'];
	exit;
}

$sets = array();
foreach($result as $res) {
	foreach($res['data'] as $set) {
		if(!in_array($set[0]['name'], $sets) && in_array($set[0]['id'], $displaySets)) {
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
Load Time : <span id="loadTime"></span>
<br />
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
			echo '<td>';
			foreach($r[0]['values'] as $r1) {
			?>
			<div style="float:left; margin-left:2px; background-color:#99FF66; padding:1px;">
			<center><?php echo $r1['value']; ?></center>
			<br />
			<img src="<?php echo $sdk->getURL('ID', $r1['images'][0]['id'], 'm');  ?>" width="192" height="120" />
			</div>
			<?php
			}
			echo '</td>';
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
<script type="text/javascript">
	document.getElementById("loadTime").innerHTML = '<?php printf("%.5f", $processTime ); ?>' + ' s';
</script>
</BODY>
</HTML>