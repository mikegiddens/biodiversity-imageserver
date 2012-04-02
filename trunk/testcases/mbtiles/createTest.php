<?php

include_once("class.imgTiles.php");

echo '<pre>';

$mbTilesPath = '/home/balu/public_html/silverbiology/imageserver/testcases/mbtiles/dbs/';
$tilePath = '/home/balu/public_html/silverbiology/imageserver/tiles/';
$handle = opendir($tilePath);

while (false !== ($file_name = readdir($handle))) {
	if( $file_name == '.' || $file_name == '..') continue;

	if(is_dir($tilePath . $file_name)) {
		$mb = new imgTiles($mbTilesPath . $file_name . '.sqlite');
		$handle1 = opendir($tilePath . $file_name);
		while (false !== ($zoom = readdir($handle1))) {
			if( $zoom == '.' || $zoom == '..') continue;
			$handle2 = opendir($tilePath . $file_name . '/' . $zoom);
			while (false !== ($tile = readdir($handle2))) {
				if( $tile == '.' || $tile == '..') continue;
				$mb->recordTile($zoom, $tilePath . $file_name . '/' . $zoom . '/' . $tile);
			}
		}
	}
}



?>