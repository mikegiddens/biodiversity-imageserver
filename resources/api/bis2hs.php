<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', '1');

ini_set('memory_limit','400M');
set_time_limit(0);

# echo '<pre>';

$expected=array(
		  'barcode'
		, 'clientId'
		, 'collectionId'
		, 'imageId'
		, 'imageServerId'
		, 'limit'
		, 'mode'
);

$domain = array('dev' => 'http://dev.helpingscience.org/silverarchive_engine/silverarchive.php', 'sandbox' => 'http://sandbox.helpingscience.org/silverarchive_engine/silverarchive.php','eria' => 'http://eria.helpingscience.org/silverarchive_engine/silverarchive.php');

// Initialize allowed variables
foreach ($expected as $formvar)
	$$formvar = (isset(${"_$_SERVER[REQUEST_METHOD]"}[$formvar])) ? ${"_$_SERVER[REQUEST_METHOD]"}[$formvar]:NULL;


require_once('../../config.php');
if(@file_exists('../../hs-config.php')) {
	require_once('../../hs-config.php');
} else {
	print '<br> HS Config File Does Not Exist ';
	exit;
}
$path = $config['path']['base'] . "resources/api/classes/";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once('classes/bis.php');

$si = new SilverImage($config['mysql']['name']);

$mode = ($mode != '') ? $mode : 'eria';
$limit = ($limit != '') ? $limit : 100;

$clientId = ($clientId != '') ? $clientId : $config['clientId'];
$collectionId = ($collectionId != '') ? $collectionId : $config['collectionId'];
$imageServerId = ($imageServerId != '') ? $imageServerId : $config['imageServerId'];
$cardFlag = (trim($cardFlag) != '') ? $cardFlag : $config['cardFlag'];
$cardFlag = (strtolower(trim($cardFlag)) == 'false') ? false : true;

$valid = true;

# listing barcodes
$barCount = 0;
$count = 0;
$where = '';

$barcodes = @json_decode(stripslashes(trim($barcode)),true);
if(is_array($barcodes) && count($barcodes)) {
	@array_walk($barcodes,'escapeFn');
	$where .= sprintf(" AND `barcode` IN ('%s') ",@implode("','",$barcodes));
}

$imageId = @json_decode(stripslashes(trim($imageId)),true);
if(is_array($imageId) && count($imageId)) {
	$where .= sprintf(" AND `imageId` IN ('%s') ",@implode(',',$imageId));
}

$query = ' SELECT `imageId`, `barcode`, `filename`, `storageDeviceId`, `path` FROM `image` WHERE `imageId` NOT IN ( SELECT `imageId` FROM `bis2Hs`) ' . $where . ' ORDER BY `timestampModified` DESC ' . sprintf(" LIMIT %d ", $limit);

$Ret = $si->db->query($query);
if (is_object($Ret)) {
	while ($Row = $Ret->fetch_object())
	{
		$barCount++;
		$image_id = $Row->imageId;
		$barcode = $Row->barcode;
		$filename =  $Row->filename;

		$device = $si->storage->storageDeviceGet($Row->storageDeviceId);
		$url = $device['baseUrl'];
		switch(strtolower($device['type'])) {
			case 's3':
				$tmp = $Row->path;
				$tmp = ltrim($tmp,'/');
				$url .= $tmp . '/' . $filename;
				break;
			case 'local':
				$url = rtrim($url,'/') . '/';
				$url .= ($Row->path == '/' || $Row->path == '') ? '' : trim($Row->path,'/') . '/';
				$url .= $filename;
				break;
		}
		$ar = getimagesize($url);

		usleep(500000);
		
		$rt = '';
		
		if($cardFlag) {
			$imagesArray = array(array('width' => $ar[0], 'height' => $ar[1], 'filename' => $filename));
			$url = $domain[$mode] . '?task=addCardImages&clientId=' . $clientId . '&imageServerId=' . $imageServerId . '&collectionId=' . $collectionId . '&duplicate_check=1&images=' . json_encode($imagesArray);
			$rt = file_get_contents($url);
			$rt = json_decode($rt);
			// $imagesArray = array();
			// if(count($imagesArray) >= 10) {
			// }
		} else {
			$url = $domain[$mode] . '?task=add_specimensheet&client_id=' . $clientId . '&filename=' . $barcode . '&image_server_id=' . $imageServerId . '&collection_id=' . $collectionId . '&width=' . $ar[0] . '&height=' . $ar[1] . '&duplicate_check=1';
			$rt = file_get_contents($url);
			$rt = json_decode($rt);
		}
		if(is_object($rt) && $rt->success) {
		
			$count++;
			$si->bis->bis2HsSetProperty('imageId',$image_id);
			$si->bis->bis2HsSetProperty('filename',$filename);
			$si->bis->bis2HsSetProperty('barcode',$barcode);
			$si->bis->bis2HsSetProperty('clientId',$clientId);
			$si->bis->bis2HsSetProperty('collectionId',$collectionId);
			$si->bis->bis2HsSetProperty('imageServerId',$imageServerId);
			$si->bis->bis2HsSave();
		} else {
			# checking if the collection - ss limit is reached.
			if($rt->error->code == 158) {
				$valid = false;
				$message = $rt->error->message;
				break;
			}
		}

	} # while
} # if object

/*
if(count($imagesArray)) {
	$url = $domain[$mode] . '?task=addCardImages&clientId=' . $clientId . '&imageServerId=' . $imageServerId . '&collectionId=' . $collectionId . '&duplicate_check=1&images=' . json_encode($imagesArray);
	$rt = file_get_contents($url);
	$rt = json_decode($rt);
	$imagesArray = array();
	if(is_object($rt) && $rt->success) {
		$count++;
		$si->bis->bis2HsSetProperty('imageId',$image_id);
		$si->bis->bis2HsSetProperty('filename',$filename);
		$si->bis->bis2HsSetProperty('barcode',$barcode);
		$si->bis->bis2HsSetProperty('clientId',$clientId);
		$si->bis->bis2HsSetProperty('collectionId',$collectionId);
		$si->bis->bis2HsSetProperty('imageServerId',$imageServerId);
		$si->bis->bis2HsSave();
	}
}
*/
header('Content-type: application/json');
if($valid) {
	print( json_encode( array( 'success' => true, 'barcodesAdded' => $barCount ) ) );
} else {
	print( json_encode( array( 'success' => false, 'barcodesAdded' => $barCount, 'error' => array('code' => '', 'message' => $message ) ) ) );
}

function escapeFn(&$value) {
	$value = mysql_escape_string($value);
}

?>