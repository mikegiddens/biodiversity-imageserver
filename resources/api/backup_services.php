<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', '1');

/**
 * Flick API for CFLA Images Server
 */
	ini_set('memory_limit','128M');

	$expected=array(
		  'cmd'
		, 'barcode'
		, 'id'
		, 'image_id'
		, 'limit'
		, 'stop' # stop is the number of seconds that the loop should run
	);

	// Initialize allowed variables
	foreach ($expected as $formvar)
		$$formvar = (isset(${"_$_SERVER[REQUEST_METHOD]"}[$formvar])) ? ${"_$_SERVER[REQUEST_METHOD]"}[$formvar]:NULL;

	if (PHP_SAPI === 'cli') {
	
		function parseArgs($argv){
			array_shift($argv);
			$out = array();
			foreach ($argv as $arg){
				if (substr($arg,0,2) == '--'){
				$eqPos = strpos($arg,'=');
				if ($eqPos === false){
					$key = substr($arg,2);
					$out[$key] = isset($out[$key]) ? $out[$key] : true;
				} else {
					$key = substr($arg,2,$eqPos-2);
					$out[$key] = substr($arg,$eqPos+1);
				}
				} else if (substr($arg,0,1) == '-'){
				if (substr($arg,2,1) == '='){
					$key = substr($arg,1,1);
					$out[$key] = substr($arg,3);
				} else {
					$chars = str_split(substr($arg,1));
					foreach ($chars as $char){
					$key = $char;
					$out[$key] = isset($out[$key]) ? $out[$key] : true;
					}
				}
				} else {
				$out[] = $arg;
				}
			}
			return $out;
		}
		
		$args = (parseArgs($argv));
		if ($args) {
			foreach($args as $key => $value) {
				$$key = $value;
			}
		}
		
		include_once( dirname($_SERVER['PHP_SELF']) . '/../../config.php');
	} else {
		include_once('../../config.php');
	}

	$path = $config['path']['base'] . "resources/api/classes/";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	require_once("classes/phpFlickr/phpFlickr.php");
	require_once("classes/class.master.php");
	require_once("classes/class.picassa.php");
	require_once("classes/class.misc.php");

	$si = new SilverImage($config['mysql']['name']);
	$time_start = microtime(true);	

	switch($cmd) {
		case 'populateBoxDetect':
			header('Content-type: application/json');
			if(!$config['ratioDetect']) {
				print json_encode(array('success' => false, 'error' => array('code' => 137, 'message' => $si->getError(137))));
				exit;
			}
			$time_start = microtime(true);
			$count = 0;
			$filter['start'] = 0;
			$filter['limit'] = $limit;
	
			$flag = false;
			$idArray = array();
			$imageIds = json_decode(@stripslashes(trim($image_id)), true);
			if(is_array($imageIds) && count($imageIds)) {
				$flag = true;
				$idArray = @array_fill_keys($imageIds,'id');
			}
			$barcodes = json_decode(@stripslashes(trim($barcode)), true);
			if(is_array($barcodes) && count($barcodes)) {
				$flag = true;
				$idArray = $idArray + @array_fill_keys($barcodes,'code');
			}
			if($flag) {
				if(is_array($idArray) && count($idArray)) {
					foreach($idArray as $id => $code) {
						$func = ($code == 'id') ? 'load_by_id' : 'load_by_barcode';
						if(!$si->image->{$func}($id)) continue;
						if(!$si->pqueue->field_exists($si->image->get('barcode'),'box_add')) {
							$si->pqueue->set('image_id', $si->image->get('barcode'));
							$si->pqueue->set('process_type', 'box_add');
							$si->pqueue->save();
							$count++;
						}
					}
				}
			} else {
				$ret = $si->image->getBoxRecords($filter);
				$countFlag = true;
				while(($record = $ret->fetch_object()) && ($countFlag)) {
					if(!$si->pqueue->field_exists($record->barcode,'box_add')) {
						$si->pqueue->set('image_id', $record->barcode);
						$si->pqueue->set('process_type', 'box_add');
						$si->pqueue->save();
						$count++;
						if($limit != '' && $count >= $limit) {
							$countFlag = false;
						}
					}
				}
			}
			$time = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time, 'total_records_added' => $count));
			break;
	
		case 'populateNameFinderProcessQueue':
			$time_start = microtime(true);
			$count = 0;
			$filter['start'] = 0;
			$filter['limit'] = $limit;
	
			$ret = $si->image->getNameFinderRecords($filter);
			$countFlag = true;
			while(($record = $ret->fetch_object()) && ($countFlag)) {
				if(!$si->pqueue->field_exists($record->barcode,'name_add')) {
					$si->pqueue->set('image_id', $record->barcode);
					$si->pqueue->set('process_type', 'name_add');
					$si->pqueue->save();
					$count++;
					if($limit != '' && $count >= $limit) {
						$countFlag = false;
					}
				}
			}
			$time = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time, 'total_records_added' => $count));
			break;
		case 'populateOcrProcessQueue':
			$time_start = microtime(true);
			$count = 0;
			$filter['start'] = 0;
			$filter['limit'] = $limit;
			if(trim($image_id) != '') {
				$imageIds = json_decode(stripslashes($image_id),true);
				if(is_array($imageIds) && count($imageIds)) {
					foreach($imageIds as $imageId) {
						$loadFlag = false;
						if(!is_numeric($imageId)) {
							$loadFlag = $si->image->load_by_barcode($imageId);
						} else {
							$loadFlag = $si->image->load_by_id($imageId);
						}
						if($loadFlag) {
							if(!$si->pqueue->field_exists($si->image->get('barcode'),'ocr_add')) {
								$si->pqueue->set('image_id', $si->image->get('barcode'));
								$si->pqueue->set('process_type', 'ocr_add');
								$si->pqueue->save();
								$count++;
							}
						}
					}
				}
			} else {
				$ret = $si->image->getOcrRecords($filter);
				$countFlag = true;
				while(($record = $ret->fetch_object()) && ($countFlag)) {
					if(!$si->pqueue->field_exists($record->barcode,'ocr_add')) {
						$si->pqueue->set('image_id', $record->barcode);
						$si->pqueue->set('process_type', 'ocr_add');
						$si->pqueue->save();
						$count++;
						if($limit != '' && $count >= $limit) {
							$countFlag = false;
						}
					}
				}
			}
			$time = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time, 'total_records_added' => $count));
			break;
		case 'populateFlickrProcessQueue':
		# populate the queue for uploading to flickr
			$time_start = microtime(true);
			$count = 0;
	
			$ret = $si->image->getFlickrRecords();
			$countFlag = true;
			while(($record = $ret->fetch_object()) && ($countFlag)) {
				if(!$si->pqueue->field_exists($record->barcode,'flickr_add')) {
					$si->pqueue->set('image_id', $record->barcode);
					$si->pqueue->set('process_type', 'flickr_add');
					$si->pqueue->save();
					$count++;
					if($limit != '' && $count >= $limit) {
						$countFlag = false;
					}
				}
			}
			$time = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time, 'total_records_added' => $count));
			break;
		case 'populatePicassaProcessQueue':
		# populate the queue for uploading to picassa
			$time_start = microtime(true);
			$count = 0;
			$ret = $si->image->getPicassaRecords();
			$countFlag = true;
			while(($record = $ret->fetch_object()) && ($countFlag)) {
				if(!$si->pqueue->field_exists($record->barcode,'picassa_add')) {
					$si->pqueue->set('image_id', $record->barcode);
					$si->pqueue->set('process_type', 'picassa_add');
					$si->pqueue->save();
					$count++;
					if($limit != '' && $count >= $limit) {
						$countFlag = false;
					}
				}
			}
			$time = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time, 'total records added' => $count));
			break;
		case 'populateGTileProcessQueue':
		# populate the queue for creating Google Map Tiles
			$time_start = microtime(true);
			$count = 0;
	
			$ret = $si->image->getGTileRecords();
			if (is_object($ret)) {
				$record = array();
				$countFlag = true;
				while(($record = $ret->fetch_object()) && $countFlag){
					if(!$si->pqueue->field_exists($record->barcode,'google_tile')) {
						$si->pqueue->set('image_id', $record->barcode);
						$si->pqueue->set('process_type', 'google_tile');
						$si->pqueue->save();
						$count++;
						if($limit != '' && $count >= $limit) {
							$countFlag = false;
						}
					}
				}
			}
			$time = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time, 'total_records_added' => $count));
			break;
		case 'populateZoomifyProcessQueue':
		# populate the queue for Zoomify process
			$time_start = microtime(true);
			$count = 0;
	
			$ret = $si->image->getZoomifyRecords();
			$countFlag = true;
			while(($record = $ret->fetch_object()) && $countFlag) {
				if(!$si->pqueue->field_exists($record->barcode,'zoomify')) {
					$si->pqueue->set('image_id', $record->barcode);
					$si->pqueue->set('process_type', 'zoomify');
					$si->pqueue->save();
					$count++;
					if($limit != '' && $count >= $limit) {
						$countFlag = false;
					}
				}
			}
			$time = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time, 'total_records_added' => $count));
			break;
		case 'populateProcessQueue':
		# populate the queue with non-processed images
			$time_start = microtime(true);
			$count = 0;
			$filter['start'] = 0;
			$filter['limit'] = $limit;
			$ret = $si->image->getNonProcessedRecords($filter);
			$countFlag = true;
			while(($record = $ret->fetch_object()) && $countFlag) {
				if(!$si->pqueue->field_exists($record->barcode,'all')) {
					$si->pqueue->set('image_id', $record->barcode);
					$si->pqueue->set('process_type', 'all');
					$si->pqueue->save();
					$count++;
					if($limit != '' && $count >= $limit) {
						$countFlag = false;
					}
				}
			}
			$time = microtime(true) - $time_start;
			header('Content-type: application/json');
			print json_encode(array('success' => true, 'process_time' => $time, 'total_records_added' => $count));
			break;
		case 'processOCR':
			if(!$config['tesseractEnabled']) {
				header('Content-type: application/json');
				print json_encode(array('success' => false, 'error' => array('code' => 137, 'message' => $si->getError(137))));
				exit;
			}
			$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';
	
			$time_start = microtime(true);
			$tStart = time();
			$loop_flag = true;
			$images_array = array();$image_count = 0;
			while($loop_flag) {
				$tDiff = time() - $tStart;
				if( ($stop != '') && ( $tDiff > $stop) ) $loop_flag = false;
	
				if($limit != '') {
					if($limit == 0) break;
				}
				if($limit != '' && $image_count >= ($limit - 1)) $loop_flag = false;
	
				$record = $si->pqueue->popQueue('ocr_add');
				if($record === false) {
					$loop_flag = false;
				} else {
					if($config['mode'] == 's3') {
						$tmpFileName = 'Img_' . microtime();
						$tmpFilePath = $_TMP . $tmpFileName;
						$tmpFile = $tmpFilePath . '.jpg';
						$key = $si->image->barcode_path($record->image_id) . $record->image_id . '.jpg';
	
						$si->amazon->get_object($config['s3']['bucket'], $key, array('fileDownload' => $tmpFile));
					} else {
						$tmpFilePath = $config['path']['images'] . $si->image->barcode_path($record->image_id) . $record->image_id;
						$tmpFile = $tmpFilePath . '.jpg';
					}
	
					if($config['image_processing'] == 1) {
						$tmpImage = $tmpFilePath . '_tmp.jpg';
						$cd = "convert " . $tmpFile . " -colorspace Gray  -contrast-stretch 15% " . $tmpImage;
						exec($cd);
						$command = sprintf("%s %s %s", $config['tesseractPath'], $tmpImage, $tmpFilePath);
						exec($command);
						@unlink($tmpImage);
					} else {
						$command = sprintf("%s %s %s", $config['tesseractPath'], $tmpFile, $tmpFilePath);
						exec($command);
					}
	
					if(@file_exists($tmpFilePath . '.txt')){
						$value = file_get_contents($tmpFilePath . '.txt');
						$si->image->load_by_barcode($record->image_id);
						$images_array[] = array('image_id' => $si->image->get('image_id'), 'barcode' => $si->image->get('barcode'));
						$image_count++;
	
						$si->image->set('ocr_flag',1);
						$si->image->set('ocr_value',$value);
						$si->image->save();
					}
	
					if($config['mode'] == 's3') {
						@unlink($tmpFile);
						@unlink($tmpFilePath . '.txt');
					}
	
				}
			}
			$time_taken = microtime(true) - $time_start;
			header('Content-type: application/json');
			print json_encode(array('success' => true, 'process_time' => $time_taken, 'total' => $image_count, 'images' => $images_array));
			break;
		case 'processBoxDetect':
			header('Content-type: application/json');
			if(!$config['ratioDetect']) {
				print json_encode(array('success' => false, 'error' => array('code' => 137, 'message' => $si->getError(137))));
				exit;
			}
			$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';
	
			$time_start = microtime(true);
			$tStart = time();
			$loop_flag = true;
			$images_array = array();$image_count = 0;
	
			$flag = false;
			$idArray = array();
			$imageIds = json_decode(@stripslashes(trim($image_id)),true);
			if(is_array($imageIds) && count($imageIds)) {
				$flag = true;
				$idArray = @array_fill_keys($imageIds,'id');
			}
			$barcodes = json_decode(@stripslashes(trim($barcode)),true);
			if(is_array($barcodes) && count($barcodes)) {
				$flag = true;
				$idArray = $idArray + @array_fill_keys($barcodes,'code');
			}
			if($flag) {
				if(is_array($idArray) && count($idArray)) {
					foreach($idArray as $id => $code) {
						$func = ($code == 'id') ? 'load_by_id' : 'load_by_barcode';
						if(!$si->image->{$func}($id)) continue;
						# getting image
						if($config['mode'] == 's3') {
							$tmpPath = $_TMP . $si->image->get('filename');
							$key = $si->image->barcode_path($si->image->get('barcode')) . $si->image->get('filename');
							$si->amazon->get_object($config['s3']['bucket'], $key, array('fileDownload' => $tmpPath));
							$image = $tmpPath;
						} else {
							$image = $config['path']['images'] . $key;
						}
						# processing
						putenv("LD_LIBRARY_PATH=/usr/local/lib");
						$data = exec(sprintf("%s %s", $config['boxDetectPath'], $image));
						# putting the json data
						if($config['mode'] == 's3') {
							$tmpJson = $_TMP . $si->image->get('barcode') . '_box.json';
							$key = $si->image->barcode_path($si->image->get('barcode')) . $si->image->get('barcode') . '_box.json';
							@file_put_contents($tmpJson,$data);
							$response = $si->amazon->create_object ($config['s3']['bucket'], $key, array('fileUpload' => $tmpJson,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
							@unlink($tmpJson);
							@unlink($tmpPath);
						} else {
							@file_put_contents($config['path']['images'] . $key,$data);
						}
						$images_array[] = array('image_id' => $si->image->get('image_id'), 'barcode' => $si->image->get('barcode'));
						$image_count++;
						$si->pqueue->deleteProcessQueue($si->image->get('barcode'),'box_add');
						$si->image->set('box_flag',1);
						$si->image->save();
					}
				}
			} else {
				while($loop_flag) {
					$tDiff = time() - $tStart;
					if( ($stop != '') && ( $tDiff > $stop) ) $loop_flag = false;
					if($limit != '') {
						if($limit == 0) break;
					}
					if($limit != '' && $image_count >= ($limit - 1)) $loop_flag = false;
					$record = $si->pqueue->popQueue('box_add');
					if($record === false) {
						$loop_flag = false;
					} else {
						$si->image->load_by_barcode($record->image_id );
	
						# getting image
						if($config['mode'] == 's3') {
							$tmpPath = $_TMP . $si->image->get('filename');
							$key = $si->image->barcode_path($si->image->get('barcode')) . $si->image->get('filename');
							$fp = fopen($tmpPath, "w+b");
							$si->amazon->get_object($config['s3']['bucket'], $key, array('fileDownload' => $tmpPath));
							fclose($fp);
							$image = $tmpPath;
						} else {
							$image = $config['path']['images'] . $key;
						}
						# processing
						putenv("LD_LIBRARY_PATH=/usr/local/lib");
						$data = exec(sprintf("%s %s", $config['boxDetectPath'], $image));
						# putting the json data
						if($config['mode'] == 's3') {
							$tmpJson = $_TMP . $si->image->get('barcode') . '_box.json';
							$key = $si->image->barcode_path($si->image->get('barcode')) . $si->image->get('barcode') . '_box.json';
							@file_put_contents($tmpJson,$data);
							$response = $si->amazon->create_object ($config['s3']['bucket'], $key, array('fileUpload' => $tmpJson,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
							@unlink($tmpJson);
							@unlink($tmpPath);
						} else {
							@file_put_contents($config['path']['images'] . $key,$data);
						}
						$images_array[] = array('image_id' => $si->image->get('image_id'), 'barcode' => $si->image->get('barcode'));
						$image_count++;
						$si->image->set('box_flag',1);
						$si->image->save();
					}
				} # while
			}
			$time_taken = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time_taken, 'total' => $image_count, 'images' => $images_array));
			break;
		case 'processNameFinder':
			$time_start = microtime(true);
			$tStart = time();
			$loop_flag = true;
			$image_count = 0;
			while($loop_flag) {
				$tDiff = time() - $tStart;
				if( ($stop != '') && ( $tDiff > $stop) ) $loop_flag = false;
				if($limit != '' && $image_count >= $limit) $loop_flag = false;
				$record = $si->pqueue->popQueue('name_add');
				if($record === false) {
					$loop_flag = false;
				} else {
	
					$ret = getNames($record->image_id);
					$si->image->load_by_barcode($record->image_id);
					if($ret['success']) {
						$si->image->set('Family',$ret['data']['family']);
						$si->image->set('Genus',$ret['data']['genus']);
						$si->image->set('ScientificName',$ret['data']['scientificName']);
						$si->image->set('SpecificEpithet',$ret['data']['specificEpithet']);
						$si->image->set('namefinder_value',$ret['data']['rawData']);
					}
					$si->image->set('namefinder_flag',1);
					$si->image->save();
	
					$image_count++;
				}
			}
			$time_taken = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time_taken, 'total' => $image_count));
			
			break;
	
		case 'uploadFlickr':
			$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';
	
			$time_start = microtime(true);
			$tStart = time();
			$loop_flag = true;
			$f = new phpFlickr($config['flkr']['key'],$config['flkr']['secret']);
			if( $f->auth_checkToken() === false) {
				$f->auth('write');
			}
	
			$images_array = array();$image_count = 0;
			while($loop_flag) {
				$tDiff = time() - $tStart;
				if( ($stop != '') && ( $tDiff > $stop) ) $loop_flag = false;
				if($limit != '' && $image_count >= $limit) $loop_flag = false;
				$record = $si->pqueue->popQueue('flickr_add');
				if($record === false) {
					$loop_flag = false;
				} else {
	
					if($config['mode'] == 's3') {
						$tmpFileName = 'Img_' . time();
						$tmpFilePath = $_TMP . $tmpFileName;
						$image = $tmpFilePath . '.jpg';
						$key = $si->image->barcode_path($record->image_id) . $record->image_id . '.jpg';
						$si->amazon->get_object($config['s3']['bucket'], $key, array('fileDownload' => $image));
					} else {
						$image = $config['path']['images'] . $si->image->barcode_path($record->image_id) . $record->image_id . '.jpg';
					}
					# change setting photo to private while uploading
					$res = $f->sync_upload( $image, $record->image_id, '', '', 0 );
					if( $res != false ) {
	
						$flkrData = $f->photos_getInfo($res);
						$flickr_details = json_encode(array('server' => $flkrData['server'],'farm' => $flkrData['farm'],'secret' => $flkrData['secret']));
	
						$tags = "{$record->image_id} copyright:(CyberFlora-Louisiana)";
						$f->photos_addTags($res,$tags);
						$si->image->load_by_barcode($record->image_id);
	
						$images_array[] = array('image_id' => $si->image->get('image_id'), 'barcode' => $si->image->get('barcode'));
						$image_count++;
	
						$si->image->set('flickr_PlantID',$res);
						$si->image->set('flickr_modified',date('Y-m-d H:i:s'));
						$si->image->set('flickr_details',$flickr_details);
						$si->image->save();
					}
	
					if($config['mode'] == 's3') {
						@unlink($image);
					}
	
				}
			}
			$time_taken = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time_taken, 'total' => $image_count, 'images' => $images_array));
	
			break;
		case 'uploadPicassa':
			$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';
	
			$time_start = microtime(true);
			$tStart = time();
			$loop_flag = true;
	
			$picassa = new PicassaWeb;
			
			$picassa->set('picassa_path',$config['picassa']['lib_path']);
			$picassa->set('picassa_user',$config['picassa']['email']);
			$picassa->set('picassa_pass',$config['picassa']['pass']);
			$picassa->set('picassa_album',$config['picassa']['album']);
			
			$picassa->clientLogin();
	
			$images_array = array();$image_count = 0;
	
			while($loop_flag) {
				if( ($stop != "") && ((time() - $tStart) > $stop) ) $loop_flag = false;
				if($limit != '' && $image_count >= $limit) $loop_flag = false;
				$record = $si->pqueue->popQueue('picassa_add');
				if($record === false) {
					$loop_flag = false;
				} else {
					$image = array();
					if($config['mode'] == 's3') {
						$tmpFile = $_TMP . 'Img_' . time() . '.jpg';
						$key = $si->image->barcode_path($record->image_id) . $record->image_id . '.jpg';
						$si->amazon->get_object($config['s3']['bucket'], $key, array('fileDownload' => $tmpFile));
						$image['tmp_name'] = $tmpFile;
					} else {
						$image['tmp_name'] = $config['path']['images'] . $si->image->barcode_path($record->image_id) . $record->image_id . '.jpg';
					}
					$image['name'] = $record->image_id;
					$image['type'] = 'image/jpeg';
					$image['tags'] = $record->image_id;
					$album_id = $picassa->getAlbumID();
					$res = $picassa->addPhoto($image);
					if( $res != false ) {
						
						$si->image->load_by_barcode($record->image_id);
	
						$images_array[] = array('image_id' => $si->image->get('image_id'), 'barcode' => $si->image->get('barcode'));
						$image_count++;
	
						$si->image->set('picassa_PlantID',$res);
						$si->image->set('picassa_modified',date('Y-m-d H:i:s'));
						$si->image->save();
					}
				}
				if($config['mode'] == 's3') {
					@unlink($tmpFile);
				}
			}
			$time_taken = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time_taken, 'total' => $image_count, 'images' => $images_array));
			break;
	
		case 'populateEnLabels':
			$time_start = microtime(true);
			$start_date = $si->s2l->getLatestDate();
	
			$url = $config['hsUrl'] . '?task=getEnLabels&start_date=' . $start_date;
			$jsonObject = @stripslashes(@file_get_contents($url));
	
			$jsonObject = json_decode($jsonObject,true);
			if($jsonObject['success']) {
				$labels = $jsonObject['results'];
				if(is_array($labels) && count($labels)) {
					foreach($labels as $label) {
						$si->s2l->set('labelId',$label['label_id']);
						$si->s2l->set('evernoteAccountId',$label['evernote_account']);
						$si->s2l->set('barcode',$label['barcode']);
						$si->s2l->set('dateAdded',$label['date_added']);
						if($si->s2l->save()) {
							$labelCount++;
						}
					}
				}
			}
			$time_taken = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time_taken, 'labelCount' => $labelCount));
			break;
	
		case 'populateGuessTaxaProcessQueue':
			$time_start = microtime(true);
			$count = 0;
			$filter['start'] = 0;
			$filter['limit'] = $limit;
			if(trim($image_id) != '') {
				$imageIds = json_decode(stripslashes($image_id),true);
				if(is_array($imageIds) && count($imageIds)) {
					foreach($imageIds as $imageId) {
						$loadFlag = false;
						if(!is_numeric($imageId)) {
							$loadFlag = $si->image->load_by_barcode($imageId);
						} else {
							$loadFlag = $si->image->load_by_id($imageId);
						}
						if($loadFlag) {
							if(!$si->pqueue->field_exists($si->image->get('barcode'),'guess_add')) {
								$si->pqueue->set('image_id', $si->image->get('barcode'));
								$si->pqueue->set('process_type', 'guess_add');
								$si->pqueue->save();
								$count++;
							}
						}
					}
				}
			} else {
				$ret = $si->image->getOcrRecords($filter);
				$countFlag = true;
				while(($record = $ret->fetch_object()) && ($countFlag)) {
					if(!$si->pqueue->field_exists($record->barcode,'guess_add')) {
						$si->pqueue->set('image_id', $record->barcode);
						$si->pqueue->set('process_type', 'guess_add');
						$si->pqueue->save();
						$count++;
						if($limit != '' && $count >= $limit) {
							$countFlag = false;
						}
					}
				}
			}
			$time = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time, 'total_records_added' => $count));
			break;
	
		case 'processGuessTaxa':
			if(!$config['tesseractEnabled']) {
				header('Content-type: application/json');
				print json_encode(array('success' => false, 'error' => array('code' => 137, 'message' => $si->getError(137))));
				exit;
			}
			$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';
	
			$time_start = microtime(true);
			$tStart = time();
			$loop_flag = true;
			$images_array = array();$image_count = 0;
			while($loop_flag) {
				$tDiff = time() - $tStart;
				if( ($stop != '') && ( $tDiff > $stop) ) $loop_flag = false;
	
				if($limit != '') {
					if($limit == 0) break;
				}
				if($limit != '' && $image_count >= ($limit - 1)) $loop_flag = false;
	
				$record = $si->pqueue->popQueue('guess_add');
				if($record === false) {
					$loop_flag = false;
				} else {
					$imageId = $record->image_id;
					$si->image->load_by_barcode($imageId);
					if(!($si->image->get('ocr_flag')))
					{
					//Perform ocr and store values
							if($config['mode'] == 's3') {
							$tmpFileName = 'Img_' . microtime();
							$tmpFilePath = $_TMP . $tmpFileName;
							$tmpFile = $tmpFilePath . '.jpg';
							$key = $si->image->barcode_path($record->image_id) . $record->image_id . '.jpg';
	
							$si->amazon->get_object($config['s3']['bucket'], $key, array('fileDownload' => $tmpFile));
						} else {
							$tmpFilePath = $config['path']['images'] . $si->image->barcode_path($record->image_id) . $record->image_id;
							$tmpFile = $tmpFilePath . '.jpg';
						}
	
						if($config['image_processing'] == 1) {
							$tmpImage = $tmpFilePath . '_tmp.jpg';
							$cd = "convert " . $tmpFile . " -colorspace Gray  -contrast-stretch 15% " . $tmpImage;
							exec($cd);
							$command = sprintf("%s %s %s", $config['tesseractPath'], $tmpImage, $tmpFilePath);
							exec($command);
							@unlink($tmpImage);
						} else {
							$command = sprintf("%s %s %s", $config['tesseractPath'], $tmpFile, $tmpFilePath);
							exec($command);
						}
	
						if(@file_exists($tmpFilePath . '.txt')){
							$value = file_get_contents($tmpFilePath . '.txt');
							$si->image->load_by_barcode($record->image_id);
							$images_array[] = array('image_id' => $si->image->get('image_id'), 'barcode' => $si->image->get('barcode'));
							$image_count++;
	
							$si->image->set('ocr_flag',1);
							$si->image->set('ocr_value',$value);
							$si->image->save();
						}
	
						if($config['mode'] == 's3') {
							@unlink($tmpFile);
							@unlink($tmpFilePath . '.txt');
						}
					}
					$si->image->load_by_barcode($imageId);
					$ocrValue = urlencode($si->image->get('ocr_value'));
					$gbifURL = "http://ecat-dev.gbif.org/tf?type=text&format=json&input=".$ocrValue;
					$data = file_get_contents($gbifURL);
					$array = json_decode($data,true);
					//Incomplete...
				}
			}
			$time_taken = microtime(true) - $time_start;
			header('Content-type: application/json');
			print json_encode(array('success' => true, 'process_time' => $time_taken, 'total' => $image_count, 'images' => $images_array));
			break;
	
	# Test Tasks
	
			default:
				print json_encode(array('success' => false, 'message' => 'No Task Provided'));
				break;
		}
	
	
	function getNames($barcode) {
		global $si,$config;
		if($barcode == '') {
			return array('success' => false);
		}
		
		$url = $config['base_url'] . '/images/specimensheets/';
		$sourceUrl = 'http://namefinding.ubio.org/find?';
		$sourceUrl2 = 'http://tools.gbif.org/ws/taxonfinder?';
		
		$url = $url . $si->image->barcode_path($barcode) . $barcode . '.txt';
		$netiParams = array('input' => $url, 'type' => 'url', 'format' => 'json', 'client' => 'neti');
		$taxonParams = array('input' => $url, 'type' => 'url', 'format' => 'json');
		$getUrl = @http_build_query($netiParams);
		$data = json_decode(@file_get_contents($sourceUrl . $getUrl),true);
	
		if( !(is_array($data['names']) && count($data['names'])) ) {
			$getUrl = @http_build_query($taxonParams);
			$data = json_decode(@file_get_contents($sourceUrl2 . $getUrl),true);
		}
		$family = '';
		$genus = '';
		$scientificName = '';
		
		if( is_array($data['names']) && count($data['names']) ) {
			foreach($data['names'] as $dt) {
				# check 1
				$word = $dt['scientificName'];
				$word = preg_replace('/\s+/',' ',trim($word));
	
				$posFlag = false;
				$word = @strtolower($word);
				# $pos = @strripos($word,'acae');
				$pos = @strripos($word,'ceae');
				$pregFlag = (preg_match('/[^A-Za-z\s]/',$word) == 0) ? true : false;
				if($pregFlag) {
					if( ( ( $pos + 4 ) >= strlen($word) ) && (strlen($word) > 4) ) {
						if($family == '') {
							$family = @ucfirst($word);
						}
					} else {
						$posFlag = true;
					}
				
					$wd = explode(' ',$word);
					if(count($wd) == 2) {
						if($scientificName == '') {
							$scientificName = @ucfirst($word);
						}
						if($genus == '') {
							$genus = @ucfirst($wd[0]);
							$specificEpithet = $wd[1];
						}
					} else if (count($wd) == 1) {
						if($posFlag) {
							if($genus == '') {
								$genus = @ucfirst($word);
							}
						}
					}
				} # preg flag
				if($family != '' && $genus != '' && $scientificName != '') {
					return array('success' => true, 'data' => array('family' => $family,'genus' => $genus,'scientificName' => $scientificName,'specificEpithet' => $specificEpithet, 'rawData' => json_encode($data['names'])));
				}
			} # foreach
			if($family != '' || $genus != '' || $scientificName != '') {
				return array('success' => true, 'data' => array('family' => $family,'genus' => $genus,'scientificName' => $scientificName,'specificEpithet' => $specificEpithet, 'rawData' => json_encode($data['names'])));
			}
		}
		return array('success' => false);
	}

?>