<?php

/*
  Function to add Key - Value pair for an image.
  Creates key/value if they dont exist
*/
function addKeyValue($imageId, $key, $value) {
	if($imageId=='' || $key=='' || $value=='') return false;
	global $sdk;
	$result1 = $sdk->listCategories();
	$cat_flag = 0;
	if(isset($result1['data']) && is_array($result1['data'])) {
		foreach($result1['data'] as $res1) {
			if(strtolower($res1['title']) == strtolower($key)) {
				$cat_flag = $res1['typeID'];
				break;
			}
		}
	}
	if($cat_flag) {
		$result2 = $sdk->list_attributes($cat_flag);
		$val_flag = 0;
		if(isset($result2['data']) && is_array($result2['data'])) {
			foreach($result2['data'] as $res2) {
				if(strtolower($res2['name']) == strtolower($value)) {
					$val_flag = $res2['valueID'];
					break;
				}
			}
		}
		if($val_flag) {
			$result3 = $sdk->addImageAttribute($imageId, $val_flag, $cat_flag);
		} else {
			$result4 = $sdk->addAttribute($cat_flag, $value);
			$result5 = $sdk->addImageAttribute($imageId, $result4['new_id'], $cat_flag);
		}
	} else {
		$result1 = $sdk->addCategory($key);
		$cat_flag = $result1['new_id'];
		$result2 = $sdk->list_attributes($cat_flag);
		$val_flag = 0;
		if(isset($result2['data']) && is_array($result2['data'])) {
			foreach($result2['data'] as $res2) {
				if(strtolower($res2['name']) == strtolower($value)) {
					$val_flag = $res2['valueID'];
					break;
				}
			}
		}
		if($val_flag) {
			$result3 = $sdk->addImageAttribute($imageId, $val_flag, $cat_flag);
		} else {
			$result4 = $sdk->addAttribute($cat_flag, $value);
			$result5 = $sdk->addImageAttribute($imageId, $result4['new_id'], $cat_flag);
		}
	}
	return true;
}

require_once('phpBIS.php');

$time_start = microtime(true);
$sdk = new phpBIS('{yourkey}', 'http://bis.silverbiology.com/dev/resources/api');

/* Add key value pair for each image */
$imageIds = array(911, 912, 913, 914, 915);
foreach($imageIds as $imageId) {
	addKeyValue($imageId, 'Scientific Name', 'Quercus alba');
	addKeyValue($imageId, 'Copyright', 'None');
	addKeyValue($imageId, 'Credit', 'None');
}

/* Add key value pair for each image */
$imageIds = array(916, 917, 918, 919, 920);
foreach($imageIds as $imageId) {
	addKeyValue($imageId, 'Scientific Name', 'Quercus coccinea');
	addKeyValue($imageId, 'Copyright', 'None');
	addKeyValue($imageId, 'Credit', 'None');
}

/* Add key value pair for each image */
$imageIds = array(921, 922, 923, 924, 925);
foreach($imageIds as $imageId) {
	addKeyValue($imageId, 'Scientific Name', 'Quercus falcata');
	addKeyValue($imageId, 'Copyright', 'None');
	addKeyValue($imageId, 'Credit', 'None');
}

/* Add key value pair for each image */
$imageIds = array(911, 916, 921);
foreach($imageIds as $imageId) {
	addKeyValue($imageId, 'Leaf', 'General');
}

/* Add key value pair for each image */
$imageIds = array(912, 917, 922);
foreach($imageIds as $imageId) {
	addKeyValue($imageId, 'Lower surface', 'General');
}

/* Add key value pair for each image */
$imageIds = array(913, 918, 923);
foreach($imageIds as $imageId) {
	addKeyValue($imageId, 'Acorn', 'General');
}

/* Add key value pair for each image */
$imageIds = array(914, 919, 924);
foreach($imageIds as $imageId) {
	addKeyValue($imageId, 'Twigs', 'General');
}

/* Add key value pair for each image */
$imageIds = array(915, 920, 925);
foreach($imageIds as $imageId) {
	addKeyValue($imageId, 'Bark', 'General');
}

header('Content-type: application/json');
print(json_encode(array("success" => true, "process_time" => microtime(true) - $time_start)));

?>