<?php

error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', '1');

/**
 * @author
 * @copyright
 */

$expected = array(
		'cmd'
	,	'path'
	,	'barcode'
	,	'filename'
	,	'callback'
	,	'absolutePath'
);

// Initialize allowed variables
foreach ($expected as $formvar)
	$$formvar = (isset(${"_$_SERVER[REQUEST_METHOD]"}[$formvar])) ? ${"_$_SERVER[REQUEST_METHOD]"}[$formvar]:NULL;

$valid = true;
$code = 0;

switch($cmd) {

	case 'loadImage':
		$timeStart = time();
 		include_once('./classes/class.silvertile.php');
 		include_once('./classes/functions.php');

		if($filename == '') {
			$valid = false;
			$code = 102;
		}

		if($valid) {
			$sharpenFlag = (trim($_REQUEST['sharpen']) == 'true') ? true : false;
			$path_images = ($absolutePath == '') ? PATH_IMAGES . barcode_path($filename) : $absolutePath;
			$tile = new SilverTile($path_images, $filename, $sharpenFlag);
	
			if(!$tile->cacheExist()) {
				$tile->createTiles();
			} else {
				$tile->touchCache();
			}
	
			$urlPath = $tile->getUrl();
			$dimensions = $tile->getOriginalDimensions();
	
			$time = time() - $timeStart;
	
			$ar = array('success' => true, 'processTime' => $time, 'copyright' => COPYRIGHT, 'url' => $urlPath, 'width' => $dimensions['width'], 'height' => $dimensions['height'], 'zoomLevel' => $tile->getZoomLevel());
		} else {
			$ar = array('success' => false, 'error' => array('code' => $code, 'message' => getError($code)));
		}

		if ($callback) {
			print_j($ar,$callback);
		} else {
			print_c($ar);
		}
		break;

	case 'purgeDir':
		$timeStart = time();
 		include_once('./classes/class.silvertile.php');
		$tile = new SilverTile;

		$path_cache = PATH_CACHE;
		if(!defined('PATH_CACHE') || $path_cache == '' ) {
			$path_cache = '/var/www/cache/';
		}
		$folderCount = 0;
		$file = $tile->findOldestFile($path_cache);
		if($file != false) {
			$folderCount++;
			system('rm -rf ' . $path_cache . $file);
		}
		$time = time() - $timeStart;
		$ar = array('success' => true, 'processTime' => $time, 'foldersPurged' => $folderCount);
		if ($callback) {
			print_j($ar,$callback);
		} else {
			print_c($ar);
		}
		break;

	default:
		$code = 101;
		$ar = array('success' => false, 'error' => array('code' => $code, 'message' => getError($code)));
		if ($callback) {
			print_j($ar,$callback);
		} else {
			print_c($ar);
		}

		break;
}

function print_j($ar,$callback) {
	header('Content-Type: application/javascript');
	print $callback . '(' . json_encode($ar) . ');';
}

function print_c($ar) {
	header('Content-Type: application/json');
	print json_encode($ar);
}

function getError($code) {
	$ar = array(
		  101 => 'No Command Given'
		, 102 => 'filename Not Given'
	);
	return $ar[$code];
}
?>