<?php
require_once('phpBIS.php');

$sdk = new phpBIS('{yourkey}', 'http://bis.silverbiology.com/dev/resources/api');

$eventId = $_REQUEST['eventId'];

$imageList = $sdk->listImagesByEvent($eventId);

if(!$imageList) {
	echo $sdk->lastError['code']. ' : ' . $sdk->lastError['msg'];
	exit;
}
$url = array();
$imid = array();
if(is_array($imageList['imageIds']) && count($imageList['imageIds'])) {
	foreach($imageList['imageIds'] as $imageId) {
		$imid[] = $imageId;
		$url[] = $sdk->getURL('ID', $imageId, 'l');
	}
}
$listEvents = $sdk->listEvents(0, 1, $eventId, null, null, null, null);
$listEventTypes = $sdk->listEventTypes(0, 1, $listEvents['results'][0]['eventTypeId'], null, null, null, null);
?>
<HTML>
<HEAD>
<TITLE>Events - Demo - <?php echo $listEvents['results'][0]['title']; ?></TITLE>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<script type="text/javascript" src="coin-slider.min.js"></script>
<link rel="stylesheet" href="coin-slider-styles.css" type="text/css" />
<script type="text/javascript">
	$(document).ready(function() {
		$('#coin-slider').coinslider({width:700, height: 500});
	});
</script>

</HEAD>
<BODY>
<H3 align="center"><?php echo $listEvents['results'][0]['title']; ?></H3>
<BR />
<div style="text-align:center;"><?php echo 'Geo Location : '.$listEvents['results'][0]['admin_0'].', '.$listEvents['results'][0]['country']; ?></div>
<div id='coin-slider' style="margin-left:auto; margin-right:auto;">
	<?php
	for($i=0;$i<count($url);$i++) {
	$imgAttr = $sdk->listImageAttributes($imid[$i]);
	?>
	<a href="#">
		<img src='<?php echo $url[$i];  ?>' width="600" height="400" >
		<span>
		<?php
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
		//echo 'Image Id : '.$imid[$i];
		?>
		</span>
	</a>
	<?php
	}
	?>
</div>

</BODY>
</HTML>
