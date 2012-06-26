<?php
require_once('phpBIS.php');

$sdk = new phpBIS('myqUBSVQ8Idr2', 'http://bis.silverbiology.com/dev/resources/api');

$category = $_REQUEST['category'];
$value = $_REQUEST['value'];

$result = $sdk->listImageBySetKeyValue($category, $value);

if(!$result) {
	echo $sdk->lastError['code']. ' : ' . $sdk->lastError['msg'];
	exit;
}
?>

<HTML>
<HEAD>
<TITLE>Sets - Demo</TITLE>
</HEAD>
<BODY>
<h2><?php echo $value; ?></h2>
<br />
<?php
if(is_array($result['data'])) {
foreach($result['data'] as $set) {
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
<TD><a href="demo_sets_details.php?imageId=<?php echo $image['id'];  ?>&url=<?php echo $image['url'];  ?>"><img src="<?php echo $image['url'];  ?>" width="256" height="160" /></a></TD>
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
</BODY>
</HTML>