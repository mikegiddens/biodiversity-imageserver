<?php
require_once('phpBIS.php');

$sdk = new phpBIS('{YourKey}', '{URL_to_api_folder}');

$category = $_REQUEST['category'];
$value = $_REQUEST['value'];

$result = $sdk->listImageBySetKeyValue($category, $value);

if(!$result) {
	echo $sdk->lastError['code']. ' : ' . $sdk->lastError['msg'];
	exit;
}

$urls = array();
$imageIds = array();
$details = array();
$label = 'A';

if(is_array($result['data'])) {
	foreach($result['data'] as $set) {
		if(is_array($set[0]['values'])) {
			foreach($set[0]['values'] as $value) {
				if(is_array($value['images'])) {
					foreach($value['images'] as $image) {
						$urls[$label] = $image['url'];
						$imageIds[$label] = $image['id'];
						$details[$label] = $set[0]['name'] . ', ' . $value['value'];
						$label++;
					}
				}
			}
		}
	}
}
?>
<!DOCTYPE HTML>
<HTML>
<HEAD>
<TITLE>Demo Image - Labels</TITLE>
</HEAD>
<BODY bgcolor="#CFCFCF">
<div>
<h2 style="text-align:center;"><?php echo $_REQUEST['value']; ?></h2>
<div style="width:785px; margin:0 auto;">
<div id="images" style="width:785px;">
	<?php
	foreach($urls as $key=>$value) {
	?>
		<div style="float:left; width:194px; padding:1px;">
		<a href="demo_details.php?imageId=<?php echo $imageIds[$key];  ?>&url=<?php echo $value;  ?>">
		<img src="<?php echo $value; ?>" width="192" height="120" />
		<span><?php echo $key; ?></span>
		</a>
		</div>
	<?php
	}
	?>
</div>
<div id="keys" style="width:785px; float:left; margin-top:20px;">
	<?php
	foreach($details as $key=>$value) {
		echo '<b>'.$key.'.</b> '.$value.'. ';
	}
	?>
</div>
</div>
</BODY>
</HTML>