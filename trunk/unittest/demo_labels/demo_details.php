<?php
require_once('phpBIS.php');

$sdk = new phpBIS('{YourKey}', '{URL_to_api_folder}');

$imageid = $_REQUEST['imageId'];
$url = $_REQUEST['url'];
?>
<img src="<?php echo $url; ?>" />
<br />
<?php
$imgAttr = $sdk->listImageAttributes($imageid);
foreach($imgAttr['data'] as $attr) {
			echo $attr['key']. ' : ';
			$cflag = 0;
			foreach($attr['values']	as $val) {
				if($cflag) echo ', ';
				echo $val['value'];
				$cflag++;
			}
			echo '<br />';
		}
?>