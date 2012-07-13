<?php
require_once('phpBIS.php');
$sdk = new phpBIS('{yourKey}', 'http://bis.silverbiology.com/dev/resources/api');
$imageid = $_REQUEST['imageId'];
$imUrl = $sdk->getURL('ID', $imageid, 'l');
if($imUrl === false) {
	echo 'Invalid ImageId given.';
	exit;
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Demo - GPS info from EXIF</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta charset="UTF-8">
    <style type="text/css">
	html, body {
		margin: 0;
        	padding: 0;
  		height: 100%;
	}
	#map_canvas {
        	margin: 0;
        	padding: 0;
        	height:70%;
		width:45%;
      	}
    </style>
  </head>
  <body>
  Load Time : <span id="loadTime"></span>
  <br />
  <div style="width:48%; float:left; margin-left:10px;">
  <img src="<?php echo $imUrl; ?>" width="95%" />
  <br />
  <?php
  	$imgAttr = $sdk->listImageAttributes($imageid);
	$processTime = $imgAttr['processTime'];
	$gpsFlag = 0;
	$lat = 1;
	$long = 180;
	$zoom = 1;
	if(is_array($imgAttr['data'])) {
		foreach($imgAttr['data'] as $attr) {
			echo $attr['key']. ' : ';
			if($attr['key'] == 'Latitude') {
				$gpsFlag = 1;
				$lat = $attr['values'][0]['value'];
				$zoom = 10;
			}
			if($attr['key'] == 'Longitude') {
				$gpsFlag = 1;
				$long = $attr['values'][0]['value'];
				$zoom = 10;
			}
			$cflag = 0;
			foreach($attr['values']	as $val) {
				if($cflag) echo ', ';
				echo $val['value'];
				$cflag++;
			}
			echo '<br />';
		}
	}
	if($gpsFlag == 0) {
		echo '<br><br>No GPS info';
	}
  ?>
  </div>
  <div id="map_canvas"></div>
  <script type="text/javascript">
	document.getElementById("loadTime").innerHTML = '<?php printf("%.5f", $processTime ); ?>' + ' s';
  </script>
  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
  <script type="text/javascript">
      var map;
      var myLatlng = new google.maps.LatLng(<?php echo $lat; ?>,<?php echo $long; ?>);
      function initialize() {	
        var myOptions = {
	  zoom : <?php echo $zoom; ?>,
   	  center : myLatlng,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map = new google.maps.Map(document.getElementById('map_canvas'), myOptions);
        <?php if($gpsFlag != 0) { ?>
        var marker = new google.maps.Marker({
          position: myLatlng,
          map: map,
          title:"Image"
        });
        <?php } ?>
      }
      google.maps.event.addDomListener(window, 'load', initialize);
  </script>
  </body>
</html>
