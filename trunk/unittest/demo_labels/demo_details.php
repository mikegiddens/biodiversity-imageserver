<?php
require_once('phpBIS.php');

$sdk = new phpBIS('{yourKey}', 'http://bis.silverbiology.com/dev/resources/api');

$imageid = $_REQUEST['imageId'];

?>
<!DOCTYPE HTML>
<HTML>
<HEAD>
<TITLE>Demo Image - Labels Details</TITLE>
</HEAD>
<BODY>
Load Time : <span id="loadTime"></span>
<br />
<img src="<?php echo $sdk->getURL('ID', $imageid, 'l'); ?>" />
<br />
<?php
$imgAttr = $sdk->listImageAttributes($imageid);

$processTime = $imgAttr['processTime'];

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
<script type="text/javascript">
	document.getElementById("loadTime").innerHTML = '<?php printf("%.5f", $processTime ); ?>' + ' s';
</script>
</BODY>
</HTML>