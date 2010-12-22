<?php
$barcode = @trim($_REQUEST['barcode']);

function barcode_path( $barcode ) {
        $id = $barcode;
        if ((strlen($id))>8){
		$loop_flag = true;$i = 0;
		while($loop_flag){
			if(substr($barcode,$i) * 1) {
				$loop_flag = false;
			} else {
				$i++;
			}
			if($i>8) $loop_flag = false;
		}
	        $prefix = strtolower(substr($id, 0, $i));
            	$id= substr($id, $i);
        } else {
            $prefix="";
        }
        $destPath  = $prefix . "/";
        $destPath .= (int) ($id / 1000000) . "/";
        $destPath .= (int) ( ($id % 1000000) / 10000) . "/";
        $destPath .= (int) ( ($id % 10000) / 100) . "/";
        $destPath .= (int) ( $id % 100 ) . "/";
        return( $destPath );
    }

$barcode_path = barcode_path($barcode);
$path = '../images/specimensheets/' . $barcode_path . 'google_tiles/';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Test</title>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=ABQIAAAAAyfV5uiFUU0YNfAxI16F5RSoUK3TRYoaqWgf85KtuuUI7z8orBQ0yLL_ip01xzPzDg2iY4JvI8lY0A" type="text/javascript"></script>
<script src="resources/js/gtile/elabel.js"></script>
<script src="resources/js/gtile/class.ruler.js"></script>

</head>

<body onunload="GUnload()">
	<input type="button" id="add" value="Ruler" onclick="add()" style="width:150px;" title="Insert Ruler">
	<div id="map" style="width: 900px; height: 600px;"></div>

<script type="text/javascript">

	var map = new GMap2(document.getElementById("map"));
	map.enableScrollWheelZoom();
	
	// ============================================================
	// ====== Create a copyright entry =====
	var copyright = new GCopyright(1, new GLatLngBounds(new GLatLng(-90, -180),	new GLatLng(90, 180)), 0, "Copyright 2009 CyberFlora Louisiana");

	// ============================================================
	// ====== Create a copyright collection =====
	// ====== and add the copyright to it   =====
	var copyrightCollection = new GCopyrightCollection('(Specimen: ...)');
	copyrightCollection.addCopyright(copyright);
	
	// ============================================================
	// == Write our own getTileUrl function ========
	// In this case the tiles are names like  8053_5274_3.jpg      
	CustomGetTileUrl=function(a,b){
// 		return "../images/ulm/0/0/0/6/google_tiles/" + (5 - b) + "/tile_"+ 1 + "_" + a.x + "_" + a.y + ".jpg"
		return '<?php print $path; ?>' + (5 - b) + "/tile_"+ 1 + "_" + a.x + "_" + a.y + ".jpg"
	}

	// ============================================================
	// ===== Create the GTileLayer =====
	// ===== adn apply the CustomGetTileUrl to it
	var tileLayers = [ new GTileLayer(copyrightCollection , 1, 5)];
	tileLayers[0].getTileUrl = CustomGetTileUrl;
	
	// ============================================================
	// ===== Create the GMapType =====
	// ===== and add it to the map =====
	var custommap = new GMapType(tileLayers, new GMercatorProjection(18), "SpecimenSheet" );
	map.addMapType(custommap);

	map.addControl(new GLargeMapControl());

	var centerLat = 0, centerLong = 0, initialZoom = 1;
	map.setCenter(new GLatLng(centerLat, centerLong), initialZoom, custommap);
	map.setZoom(parseInt(initialZoom));

	var line = null;
	document.getElementById('map').style.backgroundColor = 'black';

</script>

</body>
</html>
