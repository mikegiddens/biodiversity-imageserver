<?php

if(PHP_SAPI != 'cli') {
	echo 'Use console to access.';
	exit;
}

ob_start();

$expected = array(
		'cmd:'
	,	'barcode:'
	,	'description:'
	,	'eventId:'
	,	'eventTypeId:'
	,	'field:'
	,	'geoId:'
	,	'image:'
	,	'imageId:'
	,	'key:'
	,	'limit:'
	,	'path:'
	,	'size:'
	,	'start:'
	,	'storageId:'
	,	'title:'
	,	'value:'
);

$flag = array(
		'q'
);

$flag = implode('', $flag);

$arguments = getopt($flag, $expected);

foreach($arguments as $key1=>$value1) {
	$$key1 = $value1;
}

function print_console($str, $newline = true, $processTime = false) {
	global $q, $time_start;
	if(!isset($q)) {
		echo $str;
		if($newline) echo "\n";
		if($processTime) echo "Process Time : " . (microtime(true)-$time_start) . "\n";
	}
}

require_once('config.php');
require_once('phpBIS.php');

$sdk = new phpBIS($config['key'], $config['api_path']);

$valid = true;
$code = 0;
$time_start = microtime(true);

switch ( $cmd ) {
	case 'addImage':
		if(!isset($image)) {
			$code = 102;
			$valid = false;
		} elseif(!isset($storageId)) {
			$code = 103;
			$valid = false;
		} elseif(!isset($path)) {
			$code = 104;
			$valid = false;
		} elseif(!file_exists($image)) {
			$code = 105;
			$valid = false;
		}
		if($valid) {
			$result = $sdk->addImage($image, $storageId, $path);
			if($result) {
				print_console('Image Added. Image Id is: '.$result['image_id']);
			} else {
				bis_error();
			}
		} else {
			console_error($code);
		}
		break;
	
	case 'addValue':
		if(!isset($imageId)) {
			$code = 106;
			$valid = false;
		} elseif(!isset($key)) {
			$code = 107;
			$valid = false;
		} elseif(!isset($value)) {
			$code = 108;
			$valid = false;
		}
		if($valid) {
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
					if($result3) {
						print_console('"'.$value.'" added as value for key "'.$key.'" to image: '.$imageId);
					} else {
						bis_error();
					}
				} else {
					$result4 = $sdk->addAttribute($cat_flag, $value);
					if($result4) {
						$result5 = $sdk->addImageAttribute($imageId, $result4['new_id'], $cat_flag);
						if($result5) {
							print_console('"'.$value.'" added as value for key "'.$key.'" to image: '.$imageId);
						} else {
							bis_error();
						}
					} else {
						bis_error();
					}
				}
			} else {
				$result1 = $sdk->addCategory($key);
				if($result1) {
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
						if($result3) {
							print_console('"'.$value.'" added as value for key "'.$key.'" to image: '.$imageId);
						} else {
							bis_error();
						}
					} else {
						$result4 = $sdk->addAttribute($cat_flag, $value);
						if($result4) {
							$result5 = $sdk->addImageAttribute($imageId, $result4['new_id'], $cat_flag);
							if($result5) {
								print_console('"'.$value.'" added as value for key "'.$key.'" to image: '.$imageId);
							} else {
								bis_error();
							}
						} else {
							bis_error();
						}
					}
				} else {
					bis_error();
				}
			}
		} else {
			console_error($code);
		}
		break;
		
	case 'listAttributes':
		if(!isset($imageId)) {
			$code = 106;
			$valid = false;
		}
		if($valid) {
			$result = $sdk->listImageAttributes($imageId);
			if($result) {
				display_array($result['data']);
			} else {
				bis_error();
			}
		} else {
			console_error($code);
		}
		break;
		
	case 'getURL':
		if((!isset($imageId)) && (!isset($barcode))) {
			$code = 101;
			$valid = false;
		}
		if($valid) {
			$size = (isset($size)) ? $size : '';
			if(isset($imageId)) {
				$result = $sdk->getURL('ID', $imageId, $size);
			} else {
				$result = $sdk->getURL('BARCODE', $barcode, $size);
			}
			if($result) {
				print_console($result);
			} else {
				bis_error();
			}
		} else {
			console_error($code);
		}
		break;
		
	case 'addEvent':
		$eventId = (isset($eventId)) ? $eventId : '';
		$title = (isset($title)) ? $title : '';
		$eventTypeId = (isset($eventTypeId)) ? $eventTypeId : '';
		$geoId = (isset($geoId)) ? $geoId : '';
		$description = (isset($description)) ? $description : '';
		$result = $sdk->addEvent($eventId, $title, $eventTypeId, $geoId, $description);
		if($result) {
			print_console('Event added id: '.$result['new_id']);
		} else {
			bis_error();
		}
		break;
		
	case 'listEvents':
		$start = (isset($start)) ? $start : '';
		$limit = (isset($limit)) ? $limit : '';
		$eventId = (isset($eventId)) ? $eventId : '';
		$eventTypeId = (isset($eventTypeId)) ? $eventTypeId : '';
		$geoId = (isset($geoId)) ? $geoId : '';
		$field = (isset($field)) ? $field : '';
		$value = (isset($value)) ? $value : '';
		$result = $sdk->listEvents($start, $limit, $eventId, $eventTypeId, $geoId, $field, $value);
		if($result) {
			if(is_array($result)) {
				display_array($result['results']);
			} else {
				print_console($result);
			}
		} else {
			bis_error();
		}
		break;
	
	case 'addImageToEvent':
		if(!isset($eventId)) {
			$code = 109;
			$valid = false;
		} elseif(!isset($imageId)) {
			$code = 106;
			$valid = false;
		}
		if($valid) {
			$result = $sdk->addImageEvent($eventId, $imageId);
			if($result) {
				print_console('Image added to Event');
			} else {
				bis_error();
			}
		} else {
			console_error($code);
		}
		
		break;
		
	default: 
		$code = 100;
		console_error($code);
		break;
}

function console_error($err, $newline=true, $processTime=false) {
	$ar = array(
		  100 => 'No valid BIS command provided. Usage : --cmd {value}'
		, 101 => 'Either imageId or barcode should be provided. Usage --imageId {value} / --barcode {value}'
		, 102 => 'Path to image file should be provided. Usage --image {/path/filename}'
		, 103 => 'StorageID should be provided. Usage --storageId {value}'
		, 104 => 'Destination path should be provided. Usage --path {/path}'
		, 105 => 'Image file does not exist.'
		, 106 => 'Image Id should be provided. Usage --imageId {value}'
		, 107 => 'Key should be provided. Usage --key {value}'
		, 108 => 'Value should be provided. Usage --value {value}'
		, 109 => 'Event Id should be provided. Usage --eventId {value}'
	);
	print_console('Console Error : ' . $ar[$err], $newline, $processTime);
}

function bis_error($newline=true, $processTime=false) {
	global $sdk;
	if(isset($sdk->lastError))
	print_console('BIS Error : '.$sdk->lastError['code'].': '.$sdk->lastError['msg'], $newline, $processTime);
}

function display_array($result, $level = 0) {
	$tab_space_val = '  ';
	$tmp = $level;
	while($level>0) {
		$tab_space .= $tab_space_val;
		$level--;
	}
	$tab_space .= '-> ';
	if(is_array($result)) {
		foreach($result as $key=>$value) {
			if(is_array($value)) {
				print_console($tab_space.$key.' : ', true, false);
				display_array($value, $tmp+1);
			} else {
				print_console($tab_space.$key.' : '.$value, true, false);
			}
		}
	}
}

ob_end_flush();
?>