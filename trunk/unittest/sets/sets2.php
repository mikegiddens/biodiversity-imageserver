<?php
require_once('phpBIS.php');

$sdk = new phpBIS('{yourKey}', 'http://bis.silverbiology.com/dev/resources/api');

$category = $_REQUEST['category'];
$value = $_REQUEST['value'];
$setids = $_REQUEST['sets'];

$setids = json_decode($setids, true);
if(!is_array($setids)) exit;

$result = $sdk->listImageBySetKeyValue($category, $value);

if(!$result) {
	echo $sdk->lastError['code']. ' : ' . $sdk->lastError['msg'];
	exit;
}

$processTime = $result['processTime'];
?>

<HTML>
<HEAD>
<TITLE>Sets - Demo</TITLE>
</HEAD>
<BODY>
Load Time : <span id="loadTime"></span>
<br />
<h2><?php echo $value; ?></h2>
<br />
<?php
if(is_array($result['data'])) {
foreach($result['data'] as $set) {
	if(!(in_array($set[0]['id'], $setids))) continue;
?>
<h3><?php echo $set[0]['name']; ?></h3>
<table>
<TR>
<?php
if(is_array($set[0]['values'])) {
foreach($set[0]['values'] as $value) {
?>
<TD>
<table border="1">
<TR><TH colspan="<?php echo count($value['images']);  ?>"><?php echo $value['value'];  ?></TH></TR>
<TR>
<?php 
if(is_array($value['images'])) {
foreach($value['images'] as $image) {
?>
<TD><a href="demo_sets_details.php?imageId=<?php echo $image['id'];  ?>&url=<?php echo $sdk->getURL('ID', $image['id'], 'l');  ?>"><img src="<?php echo $sdk->getURL('ID', $image['id'], 'm');  ?>" width="256" height="160" /></a></TD>
<?php
} }
?>
</TR>
</table>
<?php
} }
?>
</TD>
</TR>
</table>
<br /><br />
<?php
} }
?>
<script type="text/javascript">
	document.getElementById("loadTime").innerHTML = '<?php printf("%.5f", $processTime ); ?>' + ' s';
</script>
</BODY>
</HTML>