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
$imageIds = array(900, 901, 902, 903, 904, 905, 906, 907, 908, 909, 910);
foreach($imageIds as $imageId) {
	addKeyValue($imageId, 'Scientific Name', 'Juglans cinerea');
	addKeyValue($imageId, 'Family', 'Juglandaceae');
	addKeyValue($imageId, 'Copyright', 'None');
	addKeyValue($imageId, 'Credit', 'None');
}

/* Add key value pair for each image */
$imageIds = array(900, 901);
foreach($imageIds as $imageId) {
	addKeyValue($imageId, 'Whole tree (or vine)', 'General');
}

/* Add key value pair for each image */
$imageIds = array(902, 903);
foreach($imageIds as $imageId) {
	addKeyValue($imageId, 'Whole tree (or vine)', 'View up trunk');
}

/* Add key value pair for each image */
$imageIds = array(904);
foreach($imageIds as $imageId) {
	addKeyValue($imageId, 'Fruit', 'Unspecified');
}

/* Add key value pair for each image */
$imageIds = array(905, 906);
foreach($imageIds as $imageId) {
	addKeyValue($imageId, 'Fruit', 'Lateral');
}

/* Add key value pair for each image */
$imageIds = array(907, 908);
foreach($imageIds as $imageId) {
	addKeyValue($imageId, 'Fruit', 'Section');
}

/* Add key value pair for each image */
$imageIds = array(909, 910);
foreach($imageIds as $imageId) {
	addKeyValue($imageId, 'Seed', 'General');
}

header('Content-type: application/json');
print(json_encode(array("success" => true, "process_time" => microtime(true) - $time_start)));

?>
