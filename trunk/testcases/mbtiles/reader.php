<?php

include_once("class.MBTiles.php");
$zoom = 2;
$file_name = 'test000002';
$index = 3;

$mbTilesPath = '/home/balu/public_html/silverbiology/imageserver/testcases/mbtiles/dbs/';
// $tilePath = '/home/balu/public_html/silverbiology/imageserver/tiles/';

$mb = new MBTiles($mbTilesPath . $file_name . '.db');
$result = $mb->exec(" SELECT tile_data FROM tiles WHERE zoom_level = %d AND cell = %d ", $zoom, $index);
$data = $result->fetchArray();

$type = 'image/jpeg';
header('Content-Type:'.$type);
echo $data['tile_data'];


?>