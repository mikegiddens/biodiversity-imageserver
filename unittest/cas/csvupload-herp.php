<?php
set_time_limit(0);
require_once("../phpBIS.php");
$csv = "allfieldnoteslink.csv";
$fp = fopen($csv, "r");
$base_url = 'http://researcharchive.calacademy.org/Image_db/herp/FieldNotes/';
$sdk = new phpBIS('{key}', 'http://bis.silverbiology.com/dev/resources/api');

//$sdk->addCollection('California Academy of Sciences - HERP','CAS-HERP');

$data = fgetcsv($fp, NULL, ",");
$total = 0;
$count = 1;
$start = 406; //Starting row
$limit = 200; //Number of rows
$imageIds = array();
$previous = '';
while($data = fgetcsv($fp, NULL, ",")) {
	$current = $data[2];
	if($previous==$current) continue;
	$previous = $current;
	if($count++<$start) continue;
	$url = $base_url.$data[2].'.jpg';
	$barcode = $data[2];
	$path = '/cas/herp/'.$barcode;
	$result = $sdk->addImageFromURL($url, 2, $path);
	if($result) {
		$imageIds[] = $result['image_id'];
		$sdk->addImageToCollection($result['image_id'], 'CAS-HERP');
		$sdk->addBarcodeToImage($result['image_id'], $barcode);
		$total++;
	} else {
		echo $sdk->lastError['msg']."\n";
	}
	echo ($count-1)."\n";
	if(!--$limit) break;
}
echo $total.' images added.'."\n";
$result = $sdk->populateOcrProcessQueue($imageIds);
if($result) {
	echo count($imageIds).' images added to OCR process queue.'."\n";
} else {
	echo 'Unable to add images to OCR process queue.'."\n";
}
?>
