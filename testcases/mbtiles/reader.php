<?php
/*
include_once("class.imgTiles.php");
$zoom = 2;
$file_name = 'test000002';
$index = 3;

$mbTilesPath = '/home/balu/public_html/silverbiology/imageserver/testcases/mbtiles/dbs/';
// $tilePath = '/home/balu/public_html/silverbiology/imageserver/tiles/';

$mb = new imgTiles($mbTilesPath . $file_name . '.db');
$cmd = sprintf(" SELECT tile_data FROM tiles WHERE zoom_level = %d AND cell = %d ", $zoom, $index);
$result = $mb->querySingle($cmd);
$result = trim($result,"'");

$type = 'image/jpeg';
header('Content-Type:'.$type);
echo $result;
*/

$link = mysql_connect('localhost', 'root', '');
$db_selected = mysql_select_db('word', $link);

$mbTilesPath = '/home/balu/public_html/silverbiology/imageserver/testcases/mbtiles/dbs/tst.jpg';

$img = '/home/balu/public_html/silverbiology/imageserver/tiles/test000002/2/tile_1.jpg';

$fp = fopen($img, 'r');
$imgContent = fread($fp, filesize($img));
fclose($fp);


$sql = sprintf(" INSERT INTO `test_stream` (`stream`) VALUES ('%s') ", mysql_escape_string($imgContent));
mysql_query($sql,$link);

$res = mysql_query(" SELECT `stream` FROM `test_stream` WHERE id = 1 ");
$result = mysql_fetch_assoc($res);

$type = 'image/jpeg';
header('Content-Type:'.$type);
echo file_get_contents($result['stream']);
