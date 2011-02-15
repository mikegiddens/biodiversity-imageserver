<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', '1');

ini_set('memory_limit','400M');
set_time_limit(0);

$expected=array(
		'mode'
		, 'limit'
		, 'client_id'
		, 'collection_id'
		, 'image_server_id'
		, 'image_mode'
		, 'barcodes'
);

$domain = array('dev' => 'http://dev.helpingscience.org/silverarchive_engine/silverarchive.php', 'sandbox' => 'http://sandbox.helpingscience.org/silverarchive_engine/silverarchive.php');


// Initialize allowed variables
foreach ($expected as $formvar)
	$$formvar = (isset(${"_$_SERVER[REQUEST_METHOD]"}[$formvar])) ? ${"_$_SERVER[REQUEST_METHOD]"}[$formvar]:NULL;

$image_mode = ($image_mode != '') ? $image_mode : 's3';
$mode = ($mode != '') ? $mode : 'dev';
$limit = ($limit != '') ? $limit : 100;


require_once('../../config.php');
if(@file_exists('../../hs-config.php')) {
	require_once('../../hs-config.php');
} else {
	print '<br> HS Config File Does Not Exist ';
	exit;
}
$path = BASE_PATH . "resources/api/classes/";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once('classes/class.master.php');

$si = new SilverImage;

$client_id = ($client_id != '') ? $client_id : $config['client_id'];
$collection_id = ($collection_id != '') ? $collection_id : $config['collection_id'];
$image_server_id = ($image_server_id != '') ? $image_server_id : $config['image_server_id'];


if ( $si->load( $mysql_name ) ) {
	# listing barcodes
	$barCount = 0;
	$count = 0;
	$where = '';
	
	$barcodes = @json_decode(stripslashes(trim($barcodes)),true);
	if(is_array($barcodes) && count($barcodes)) {
		@array_walk($barcodes,'escapeFn');
		$where .= sprintf(" AND `barcode` IN ('%s') ",@implode("','",$barcodes));
	}
/*
	$image_id = $si->bis->getId();
	if($image_id !== false) {
		$where .= sprintf(" WHERE 1=1 AND `image_id` > '%s' ", mysql_escape_string($image_id));
	}
	$query =  sprintf(" SELECT `image_id`, `barcode`, `filename` FROM `image` %s LIMIT %d ",$where, mysql_escape_string($limit));
*/

	$query = ' SELECT `image_id`, `barcode`, `filename` FROM `image` WHERE `image_id` NOT IN ( SELECT `image_id` FROM `bis2hs`) ' . $where . ' ORDER BY `timestamp_modified` DESC ' . sprintf(" LIMIT %d ", $limit);

	$Ret = $si->db->query($query);
	if (is_object($Ret)) {
		while ($Row = $Ret->fetch_object())
		{
			$barCount++;
			$image_id = $Row->image_id;
			$barcode = $Row->barcode;
			$filename =  $Row->filename;

			if($image_mode == 's3') {
				$path = $config['s3']['url'] . $si->image->barcode_path($barcode) . $Row->filename;
			} else {
				$path = PATH_IMAGES . $si->image->barcode_path($barcode) . $Row->filename;
			}
			$ar = getimagesize($path);

			usleep(500000);

			$url = $domain[$mode] . '?task=add_specimensheet&client_id=' . $client_id . '&filename=' . $barcode . '&image_server_id=' . $image_server_id . '&collection_id=' . $collection_id . '&width=' . $ar[0] . '&height=' . $ar[1];

			$rt = file_get_contents($url);
			$rt = json_decode($rt);
			if($rt->success) {
				$count++;
				$si->bis->set('image_id',$image_id);
				$si->bis->set('filename',$filename);
				$si->bis->set('barcode',$barcode);
				$si->bis->set('client_id',$client_id);
				$si->bis->set('collection_id',$collection_id);
				$si->bis->set('imageserver_id',$image_server_id);
				$si->bis->save();
			}

		} # while
	} # if object

	print '<br> No. of barcodes : ' . $barCount;
	print '<br> No. of files added : ' . $count;

} else print 'Database Not Loaded';


function escapeFn(&$value) {
	$value = mysql_escape_string($value);
}

?>