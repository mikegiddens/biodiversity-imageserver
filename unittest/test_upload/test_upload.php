<?php
require_once('../../sdk/php/phpBIS.php');
$timeStart = microtime(true);
$sdk = new phpBIS('myqUBSVQ8Idr2', 'http://bis.silverbiology.com/dev/resources/api');

$source = 'testImage.jpg';
$storageId = 2;
$destinationPath = '/jun28/c';
$barcode = 'testImage';
$code = 'TSTCN';

$params = array('storageId' =>  $storageId, 'barcode' => $barcode, 'code' => $code);
$result = $sdk->imageAddFromLocal($source, $destinationPath,$params);

if($result) {
	echo '<pre>';
	var_dump($result);
} else {
	echo '<pre>';
	var_dump($sdk->lastError);
}
?>