<?php

	ini_set('memory_limit','400M');
	set_time_limit(0);
	
	$expected=array(
			'mode'
			, 'limit'
			, 'client_id'
			, 'image_server_id'
			, 'image_mode'
	);

	$domain = array('dev' => 'http://dev.helpingscience.org/silverarchive_engine/silverarchive.php', 'sandbox' => 'http://sandbox.helpingscience.org/silverarchive_engine/silverarchive.php');


	// Initialize allowed variables
	foreach ($expected as $formvar)
		$$formvar = (isset(${"_$_SERVER[REQUEST_METHOD]"}[$formvar])) ? ${"_$_SERVER[REQUEST_METHOD]"}[$formvar]:NULL;

	$image_mode = ($image_mode != '') ? $image_mode : 's3';
	$mode = ($mode != '') ? $mode : 'dev';
	$limit = ($limit != '') ? $limit : 100;
	$client_id = ($client_id != '') ? $client_id : 2;
	$image_server_id = ($image_server_id != '') ? $image_server_id : 101;

	

	require_once("../config.php");
	require_once("classes/class.master.php");
	
	$si = new SilverImage;
if ( $si->load( $mysql_name ) ) {
# listing barcodes
$barCount = 0;
$count = 0;
	$flg = false;
	$query =  sprintf(" SELECT DISTINCT `barcode`, `filename` FROM `image` LIMIT %d ", mysql_escape_string($limit));

	$Ret = $si->db->query($query);
	if (is_object($Ret)) {
		while ($Row = $Ret->fetch_object())
		{
			$barCount++;
			$barcode = $Row->barcode;

			$path = PATH_IMAGES . $si->image->barcode_path($barcode) . $Row->filename;
			$ar = getimagesize($path);

			usleep(500000);

			$url = $domain[$mode] . '?task=add_specimensheet&client_id=' . $client_id . '&filename=' . $barcode . '&image_server_id=' . $image_server_id . '&width=' . $ar[0] . '&height=' . $ar[1];
			$rt = file_get_contents($url);
			$rt = json_decode($rt);
			if($rt->success) $count++;
		}

	}

/*
	$rets = $si->db->query_all($query);
	if(!is_null($rets)) {
		$barcodes = array();
		if(is_array($rets) && count($rets)) {
			foreach($rets as $ret) {
				$barCount++;
				$barcode = $ret->barcode;

				$path = $si->image->barcode_path($barcode) . ;
				$ar = getimagesize($path);

				usleep(500000);

				$url = $domain[$mode] . '?task=add_specimensheet&client_id=' . $client_id . '&filename=' . $barcode . '&image_server_id=' . $image_server_id;
				$rt = file_get_contents($url);
				$rt = json_decode($rt);
				if($rt->success) $count++;
			}
		}
	}
*/

/*print '<pre>';
print '<br> Count : ' . count($barcodes);
var_dump($barcodes);*/


print '<br> No. of barcodes : ' . $barCount;
print '<br> No. of files added : ' . $count;

} else print 'Database Not Loaded';


?>