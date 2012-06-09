<?php
set_time_limit(0);
require_once("../phpBIS.php");
$csv = "botimages.csv";
$fp = fopen($csv, "r");
$base_url = 'http://researcharchive.calacademy.org/Image_db/botany/';
$sdk = new phpBIS('{key}', 'http://bis.silverbiology.com/dev/resources/api');

//$sdk->addCollection('California Academy of Sciences - BOT','CAS-BOT');

$data = fgetcsv($fp, NULL, ",");
$total = 0;
$count = 1;
$start = 211; //Starting row
$limit = 100; //Number of rows
$imageIds = array();
while($data = fgetcsv($fp, NULL, ",")) {
	if(!(strpos($data[4], '-thumb') === false)) continue;
	if($count++<$start) continue;
	$url = $base_url.$data[4];
	$filename_parts = explode('.', $data[4]);
	$barcode = $filename_parts[0];
	$path = '/cas/bot/'.$barcode;
	$result = $sdk->addImageFromURL($url, 2, $path);
	if($result) {
		$imageIds[] = $result['image_id'];
		$sdk->addImageToCollection($result['image_id'], 'CAS-BOT');
		$sdk->addBarcodeToImage($result['image_id'], $barcode);
		$total++;
	} else {
		echo $sdk->lastError['msg']."\n";
	}
 echo ($count-1)."\n";
	if(!--$limit) break;
}
echo $total.' images added.';
$result = $sdk->populateOcrProcessQueue($imageIds);
if($result) {
	echo count($imageIds).' images added to OCR process queue.'."\n";
} else {
	echo 'Unable to add images to OCR process queue.'."\n";
}
?>
