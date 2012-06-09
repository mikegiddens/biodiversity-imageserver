<?php
set_time_limit(0);
require_once("../phpBIS.php");
$csv = "IchTypesImgData.csv";
$fp = fopen($csv, "r");
$base_url = 'http://researcharchive.calacademy.org/Image_db/IchTypes/';
$sdk = new phpBIS('{key}', 'http://bis.silverbiology.com/dev/resources/api');

//$sdk->addCollection('California Academy of Sciences - ICH','CAS-ICH');
//$cat = $sdk->addCategory('Image Type');
//$attr1 = $sdk->addAttribute($cat['new_id'], 'Illustration');
//$attr2 = $sdk->addAttribute($cat['new_id'], 'Photograph');
//$attr3 = $sdk->addAttribute($cat['new_id'], 'Radiograph');

$data = fgetcsv($fp, NULL, ",");
$total = 0;
$count = 1;
$start = 11; //Starting row
$limit = 190; //Number of rows
while($data = fgetcsv($fp, NULL, ",")) {
	if($data[2]!='hi') continue;
	if($count++<$start) continue;
	$url = $base_url.$data[1].'/'.$data[2].'/'.$data[3];
	$filename_parts = explode('.', $data[3]);
	$barcode = $filename_parts[0];
	$path = '/cas/ich/'.$barcode;
	$result = $sdk->addImageFromURL($url, 2, $path);
	if($result) {
		$sdk->addImageToCollection($result['image_id'], 'CAS-ICH');
		$sdk->addBarcodeToImage($result['image_id'], $barcode);
		switch($data[1]) {
			case 'illus':
				$attr = 15 /*$attr1['new_id']*/;
				break;
			case 'photo':
				$attr = 16 /*$attr2['new_id']*/;
				break;
			case 'radio':
				$attr = 17 /*$attr3['new_id']*/;
				break;
			default:
				$attr = 0;
				break;
		}
		$sdk->addImageAttribute($result['image_id'], $attr, 42 /*$cat['new_id']*/);
		$total++;
	} else {
		echo $sdk->lastError['msg']."\n";
	}
 echo ($count-1)."\n";
	if(!--$limit) break;
}
echo $total.' images added.';
?>
