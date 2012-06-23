<?php
/* 
Important : Code for creating initial database entry. DO NOT run the code again unless necessary
*/

echo 'Plese take a look at the code before running.';
exit;

require_once('phpBIS.php');

$sdk = new phpBIS('{yourkey}', 'http://bis.silverbiology.com/dev/resources/api');

/* Create new Event Type */
$eventtype = $sdk->addEventType(null, 'Expedition', 'Expedition');
if(!$eventtype) {
	echo $sdk->lastError['code'].' : '.$sdk->lastError['msg'];
	exit;
}
$eventTypeId = $eventtype['new_id'];

/* Create new Event */
$event = $sdk->addEvent(null, 'Journey to Namibia', $eventTypeId, 1, 'Journey to Namibia');
if(!$event) {
	echo $sdk->lastError['code'].' : '.$sdk->lastError['msg'];
	exit;
}
$eventId = $event['new_id'];

/* List of images to be uploaded */
$imageUrls = array(
		  'http://farm5.staticflickr.com/4064/5163982991_0010da5708_o.jpg'
		, 'http://farm2.staticflickr.com/1326/5164589366_496007ca41_o.jpg'
		, 'http://farm1.staticflickr.com/91/257929962_cf9d7d8fa9_o.jpg'
		, 'http://farm5.staticflickr.com/4106/4846599558_2d15016d01_o.jpg'
		, 'http://farm4.staticflickr.com/3102/2802882000_2fbdd8ffa6_o.jpg'
		 );

/* Upload each image ,add barcode and add to event */
if(is_array($imageUrls) && count($imageUrls)) {
	foreach($imageUrls as $imageUrl) {
		$barcode = uniqid('Namibia_');
		$result = $sdk->addImageFromURL($imageUrl, 2, '/jun20/'.$barcode);
		$sdk->addBarcodeToImage($result['image_id'], $barcode);
		$sdk->addImageEvent($eventId, $result['image_id']);
	}
}

?>
