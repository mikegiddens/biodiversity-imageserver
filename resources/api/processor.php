<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', '1');

/**
 * Flick API for CFLA Images Server
 */
	ini_set('memory_limit','128M');

	$expected=array(
		  'advFilter'
		, 'advFilterId'
		, 'barcode'
		, 'cmd'
		, 'collectionCode'
		, 'enAccountId'
		, 'id'
		, 'imageId'
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
	require_once("classes/bis.php");
	require_once("classes/bis.picassa.php");
	require_once("classes/bis.misc.php");
	require_once("classes/bis.gbif.php");

	$si = new SilverImage($config['mysql']['name']);
	$timeStart = microtime(true);	

	switch($cmd) {
		case 'populateBoxDetect':
			header('Content-type: application/json');
			if(!$config['ratioDetect']) {
				print json_encode(array('success' => false, 'error' => array('code' => 137, 'message' => $si->getError(137))));
				exit;
			}
			$timeStart = microtime(true);
			$count = 0;
			$filter['start'] = 0;
			$filter['limit'] = $limit;
			
			if($advFilterId != '') {
				if($si->advFilter->advFilterLoadById($advFilterId)) {
					$advFilter  = $si->advFilter->advFilterGetProperty('filter');
				}
			}
			$advFilter = json_decode(stripslashes(trim($advFilter)),true);
	
			$idArray = array();
			if(is_numeric($imageId)) {
				$imageIds = array($imageId);
			} else {
				$imageIds = json_decode(@stripslashes(trim($imageId)), true);
			}
			if(is_array($imageIds) && count($imageIds)) {
				$idArray = @array_fill_keys($imageIds,'id');
			}
			if(is_numeric($barcode)) {
				$barcodes = array($barcode);
			} else {
				$barcodes = json_decode(@stripslashes(trim($barcode)), true);
			}
			if(is_array($barcodes) && count($barcodes)) {
				$idArray = $idArray + @array_fill_keys($barcodes,'code');
			} else if($barcode != '') {
				$idArray = $idArray + @array_fill_keys($barcode,'code');
			}
			if(is_array($advFilter) && count($advFilter)) {
				$qry = $si->image->getByCrazyFilter($advFilter, true);
				$ret = $si->db->query($qry);
				$count = $si->db->query_total();
				$qry = $si->image->getByCrazyFilter($advFilter);
				// $query = " INSERT IGNORE INTO processQueue(imageId, processType) SELECT im.barcode, 'box_add' FROM ($qry) im ";
				$query = " INSERT IGNORE INTO processQueue(imageId, processType, dateAdded) SELECT im.imageId, 'box_add', NOW() FROM ($qry) im ";
				// echo $query; exit;
				$si->db->query($query);
			} else if(is_array($idArray) && count($idArray)) {
				foreach($idArray as $id => $code) {
					$func = ($code == 'id') ? 'imageLoadById' : 'imageLoadByBarcode';
					if(!$si->image->{$func}($id)) continue;
					// if(!$si->pqueue->processQueueFieldExists($si->image->imageGetProperty('barcode'),'box_add')) {
						// $si->pqueue->processQueueSetProperty('imageId', $si->image->imageGetProperty('barcode'));
						// $si->pqueue->processQueueSetProperty('processType', 'box_add');
						// $si->pqueue->processQueueSave();
						// $count++;
					// }
					if(!$si->pqueue->processQueueFieldExists($si->image->imageGetProperty('imageId'),'box_add')) {
						$si->pqueue->processQueueSetProperty('imageId', $si->image->imageGetProperty('imageId'));
						$si->pqueue->processQueueSetProperty('processType', 'box_add');
						$si->pqueue->processQueueSave();
						$count++;
					}
				}
			} else {
				$where = '';
				if(is_numeric($filter['start']) && is_numeric($filter['limit'])) {
					$where = sprintf(" LIMIT %s, %s ", $filter['start'], $filter['limit']);
				}
				$query = 'SELECT count(*) ct FROM `image` WHERE ( `boxFlag` = 0 OR `boxFlag` IS NULL )' . $where;
				$rt = $si->db->query_one($query);
				$count = $rt->ct;
				// $query = " INSERT IGNORE INTO processQueue(imageId, processType) SELECT barcode, 'box_add' FROM `image` WHERE ( `boxFlag` = 0 OR `boxFlag` IS NULL ) " . $where;
				$query = " INSERT IGNORE INTO processQueue(imageId, processType, dateAdded) SELECT imageId, 'box_add', NOW() FROM `image` WHERE ( `boxFlag` = 0 OR `boxFlag` IS NULL ) " . $where;
				$si->db->query($query);
				
			}
			$time = microtime(true) - $timeStart;
			print json_encode(array('success' => true, 'processTime' => $time, 'totalCount' => $count));
			break;
	
		case 'populateNameFinderProcessQueue':
			header('Content-type: application/json');
			$timeStart = microtime(true);
			$count = 0;
			$filter['start'] = 0;
			$filter['limit'] = $limit;
			
			if($advFilterId != '') {
				if($si->advFilter->advFilterLoadById($advFilterId)) {
					$advFilter  = $si->advFilter->advFilterGetProperty('filter');
				}
			}
			$advFilter = json_decode(stripslashes(trim($advFilter)),true);
	
			$idArray = array();
			if(is_numeric($imageId)) {
				$imageIds = array($imageId);
			} else {
				$imageIds = json_decode(@stripslashes(trim($imageId)), true);
			}
			if(is_array($imageIds) && count($imageIds)) {
				$idArray = @array_fill_keys($imageIds,'id');
			}
			if(is_numeric($barcode)) {
				$barcodes = array($barcode);
			} else {
				$barcodes = json_decode(@stripslashes(trim($barcode)), true);
			}
			if(is_array($barcodes) && count($barcodes)) {
				$idArray = $idArray + @array_fill_keys($barcodes,'code');
			}
			if(is_array($advFilter) && count($advFilter)) {
				$qry = $si->image->getByCrazyFilter($advFilter, true);
				$ret = $si->db->query($qry);
				$count = $si->db->query_total();
				$qry = $si->image->getByCrazyFilter($advFilter);
				// $query = " INSERT IGNORE INTO processQueue(imageId, processType) SELECT im.barcode, 'name_add' FROM ($qry) im ";
				$query = " INSERT IGNORE INTO processQueue(imageId, processType, dateAdded) SELECT im.imageId, 'name_add', NOW() FROM ($qry) im ";
				$si->db->query($query);
			} else if(is_array($idArray) && count($idArray)) {
				foreach($idArray as $id => $code) {
					$func = ($code == 'id') ? 'imageLoadById' : 'imageLoadByBarcode';
					if(!$si->image->{$func}($id)) continue;
					// if(!$si->pqueue->processQueueFieldExists($si->image->imageGetProperty('barcode'),'name_add')) {
						// $si->pqueue->processQueueSetProperty('imageId', $si->image->imageGetProperty('barcode'));
						// $si->pqueue->processQueueSetProperty('processType', 'name_add');
						// $si->pqueue->processQueueSave();
						// $count++;
						// if($limit != '' && $count >= $limit) {
							// $countFlag = false;
						// }
					// }
					if(!$si->pqueue->processQueueFieldExists($si->image->imageGetProperty('imageId'),'name_add')) {
						$si->pqueue->processQueueSetProperty('imageId', $si->image->imageGetProperty('imageId'));
						$si->pqueue->processQueueSetProperty('processType', 'name_add');
						$si->pqueue->processQueueSave();
						$count++;
						if($limit != '' && $count >= $limit) {
							$countFlag = false;
						}
					}
				}
			} else {
				$where = '';
				if(is_numeric($filter['start']) && is_numeric($filter['limit'])) {
					$where = sprintf(" LIMIT %s, %s ", $filter['start'], $filter['limit']);
				}
				$query = 'SELECT count(*) ct FROM `image` WHERE ( `nameFinderFlag` = 0 OR `nameFinderFlag` IS NULL ) AND `ocrFlag` = 1 ' . $where;
				$rt = $si->db->query_one($query);
				$count = $rt->ct;
				// $query = " INSERT IGNORE INTO processQueue(imageId, processType) SELECT barcode, 'name_add' FROM `image` WHERE ( `nameFinderFlag` = 0 OR `nameFinderFlag` IS NULL ) AND `ocrFlag` = 1 " . $where;
				$query = " INSERT IGNORE INTO processQueue(imageId, processType, dateAdded) SELECT imageId, 'name_add', NOW() FROM `image` WHERE ( `nameFinderFlag` = 0 OR `nameFinderFlag` IS NULL ) AND `ocrFlag` = 1 " . $where;
				$si->db->query($query);
				
			}
			$time = microtime(true) - $timeStart;
			print json_encode(array('success' => true, 'processTime' => $time, 'totalCount' => $count));
			break;
		case 'populateOcrProcessQueue':
			header('Content-type: application/json');
			$timeStart = microtime(true);
			$count = 0;
			$filter['start'] = 0;
			$filter['limit'] = $limit;

			$idArray = array();
			if(is_numeric($imageId)) {
				$imageIds = array($imageId);
			} else {
				$imageIds = json_decode(@stripslashes(trim($imageId)), true);
			}
			if(is_array($imageIds) && count($imageIds)) {
				$idArray = @array_fill_keys($imageIds,'id');
			}
			if(is_numeric($barcode)) {
				$barcodes = array($barcode);
			} else {
				$barcodes = json_decode(@stripslashes(trim($barcode)), true);
			}
			if(is_array($barcodes) && count($barcodes)) {
				$idArray = $idArray + @array_fill_keys($barcodes,'code');
			}

			if($advFilterId != '') {
				if($si->advFilter->advFilterLoadById($advFilterId)) {
					$advFilter  = $si->advFilter->advFilterGetProperty('filter');
				}
			}
			$advFilter = json_decode(stripslashes(trim($advFilter)),true);
			if(is_array($advFilter) && count($advFilter)) {
				$qry = $si->image->getByCrazyFilter($advFilter, true);
				$ret = $si->db->query($qry);
				$count = $si->db->query_total();
				$qry = $si->image->getByCrazyFilter($advFilter);
				$query = " INSERT IGNORE INTO processQueue(imageId, processType, dateAdded) SELECT im.imageId, 'ocr_add', NOW() FROM ($qry) im ";
				// echo $query; exit;
				$si->db->query($query);

			} else if(is_array($idArray) && count($idArray)) {
				foreach($idArray as $id => $code) {
					$func = ($code == 'id') ? 'imageLoadById' : 'imageLoadByBarcode';
					if(!$si->image->{$func}($id)) continue;
					if(!$si->pqueue->processQueueFieldExists($si->image->imageGetProperty('imageId'),'ocr_add')) {
						$si->pqueue->processQueueSetProperty('imageId', $si->image->imageGetProperty('imageId'));
						$si->pqueue->processQueueSetProperty('processType', 'ocr_add');
						$si->pqueue->processQueueSave();
						$count++;
						if($limit != '' && $count >= $limit) {
							$countFlag = false;
						}
					}
				}
			} else {
				$where = '';
				if(is_numeric($filter['start']) && is_numeric($filter['limit'])) {
					$where = sprintf(" LIMIT %s, %s ", $filter['start'], $filter['limit']);
				}
				$query = 'SELECT count(*) ct FROM `image` WHERE ( `ocrFlag` = 0 OR `ocrFlag` IS NULL ) AND `processed` = 1 ' . $where;
				$rt = $si->db->query_one($query);
				$count = $rt->ct;
				$query = " INSERT IGNORE INTO processQueue(imageId, processType, dateAdded) SELECT imageId, 'ocr_add', NOW() FROM `image` WHERE ( `ocrFlag` = 0 OR `ocrFlag` IS NULL ) AND `processed` = 1 " . $where;
				$si->db->query($query);
			}
			$time = microtime(true) - $timeStart;
			print json_encode(array('success' => true, 'processTime' => $time, 'totalCount' => $count));
			break;
		case 'populateFlickrProcessQueue':
		# populate the queue for uploading to flickr
			$timeStart = microtime(true);
			$count = 0;
	
			$ret = $si->image->imageGetFlickrRecords();
			$countFlag = true;
			while(($record = $ret->fetch_object()) && ($countFlag)) {
				if(!$si->pqueue->processQueueFieldExists($record->barcode,'flickr_add')) {
					$si->pqueue->processQueueSetProperty('imageId', $record->barcode);
					$si->pqueue->processQueueSetProperty('processType', 'flickr_add');
					$si->pqueue->processQueueSave();
					$count++;
					if($limit != '' && $count >= $limit) {
						$countFlag = false;
					}
				}
			}
			$time = microtime(true) - $timeStart;
			print json_encode(array('success' => true, 'processTime' => $time, 'totalCount' => $count));
			break;
		case 'populatePicassaProcessQueue':
		# populate the queue for uploading to picassa
			$timeStart = microtime(true);
			$count = 0;
			$ret = $si->image->imageGetPicassaRecords();
			$countFlag = true;
			while(($record = $ret->fetch_object()) && ($countFlag)) {
				if(!$si->pqueue->processQueueFieldExists($record->barcode,'picassa_add')) {
					$si->pqueue->processQueueSetProperty('imageId', $record->barcode);
					$si->pqueue->processQueueSetProperty('processType', 'picassa_add');
					$si->pqueue->processQueueSave();
					$count++;
					if($limit != '' && $count >= $limit) {
						$countFlag = false;
					}
				}
			}
			$time = microtime(true) - $timeStart;
			print json_encode(array('success' => true, 'processTime' => $time, 'totalCount' => $count));
			break;
		case 'populateGTileProcessQueue':
		# populate the queue for creating Google Map Tiles
			$timeStart = microtime(true);
			$count = 0;
	
			$ret = $si->image->imageGetGTileRecords();
			if (is_object($ret)) {
				$record = array();
				$countFlag = true;
				while(($record = $ret->fetch_object()) && $countFlag){
					if(!$si->pqueue->processQueueFieldExists($record->barcode,'google_tile')) {
						$si->pqueue->processQueueSetProperty('imageId', $record->barcode);
						$si->pqueue->processQueueSetProperty('processType', 'google_tile');
						$si->pqueue->processQueueSave();
						$count++;
						if($limit != '' && $count >= $limit) {
							$countFlag = false;
						}
					}
				}
			}
			$time = microtime(true) - $timeStart;
			print json_encode(array('success' => true, 'processTime' => $time, 'totalCount' => $count));
			break;
		case 'populateZoomifyProcessQueue':
		# populate the queue for Zoomify process
			$timeStart = microtime(true);
			$count = 0;
	
			$ret = $si->image->imageGetZoomifyRecords();
			$countFlag = true;
			while(($record = $ret->fetch_object()) && $countFlag) {
				if(!$si->pqueue->processQueueFieldExists($record->barcode,'zoomify')) {
					$si->pqueue->processQueueSetProperty('imageId', $record->barcode);
					$si->pqueue->processQueueSetProperty('processType', 'zoomify');
					$si->pqueue->processQueueSave();
					$count++;
					if($limit != '' && $count >= $limit) {
						$countFlag = false;
					}
				}
			}
			$time = microtime(true) - $timeStart;
			print json_encode(array('success' => true, 'processTime' => $time, 'totalCount' => $count));
			break;
		case 'populateProcessQueue':
		# populate the queue with non-processed images
			$timeStart = microtime(true);
			$count = 0;
			$filter['start'] = 0;
			$filter['limit'] = $limit;
			$ret = $si->image->imgeGetNonProcessedRecords($filter);
			$countFlag = true;
			while(($record = $ret->fetch_object()) && $countFlag) {
				if(!$si->pqueue->processQueueFieldExists($record->barcode,'all')) {
					$si->pqueue->processQueueSetProperty('imageId', $record->barcode);
					$si->pqueue->processQueueSetProperty('processType', 'all');
					$si->pqueue->processQueueSave();
					$count++;
					if($limit != '' && $count >= $limit) {
						$countFlag = false;
					}
				}
			}
			$time = microtime(true) - $timeStart;
			header('Content-type: application/json');
			print json_encode(array('success' => true, 'processTime' => $time, 'totalCount' => $count));
			break;
		case 'processOCR':
			if(!$config['tesseractEnabled']) {
				header('Content-type: application/json');
				print json_encode(array('success' => false, 'error' => array('code' => 137, 'message' => $si->getError(137))));
				exit;
			}
			$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';
	
			$timeStart = microtime(true);
			$tStart = time();
			$loopFlag = true;
			$images_array = array();$imageCount = 0;
			while($loopFlag) {
				$tDiff = time() - $tStart;
				if( ($stop != '') && ( $tDiff > $stop) ) $loopFlag = false;
	
				if($limit != '') {
					if($limit == 0) break;
				}
				if($limit != '' && $imageCount >= ($limit - 1)) $loopFlag = false;
	
				$record = $si->pqueue->processQueuePop('ocr_add');
				if($record === false) {
					$loopFlag = false;
				} else {
					$si->image->imageLoadById($record->imageId);
					$device = $si->storage->storageDeviceGet($si->image->imageGetProperty('storageDeviceId'));
					switch(strtolower($device['type'])) {
						case 's3':
							$tmpFileName = 'Img_' . uniqid();
							$tmpFilePath = $_TMP . $tmpFileName . '.jpg';
							$tmpFile = $tmpFilePath;
							$key = $si->image->imageGetProperty('path') . '/' . $si->image->imageGetProperty('filename');
							$key = (substr($key, 0, 1)=='/') ? (substr($key, 1, strlen($key)-1)) : ($key);
							$si->amazon->get_object($device['basePath'], $key, array('fileDownload' => $tmpFile));
							break;
						case 'local':
							$tmpFilePath = $device['basePath'] . $si->image->imageGetProperty('path') . '/' . $si->image->imageGetProperty('filename');
							$tmpFile = $tmpFilePath;
							break;
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
						$images_array[] = array('imageId' => $si->image->imageGetProperty('imageId'), 'barcode' => $si->image->imageGetProperty('barcode'));
						$imageCount++;
	
						$si->image->imageSetProperty('ocrFlag',1);
						$si->image->imageSetProperty('ocrValue',$value);
						$si->image->imageSave();
					}
	
					if(strtolower($device['type']) == 's3') {
						@unlink($tmpFile);
						@unlink($tmpFilePath . '.txt');
					}
	
				}
			}
			$time_taken = microtime(true) - $timeStart;
			header('Content-type: application/json');
			print json_encode(array('success' => true, 'processTime' => $time_taken, 'totalCount' => $imageCount, 'records' => $images_array));
			break;
		case 'processBoxDetect':
			header('Content-type: application/json');
			if(!$config['ratioDetect']) {
				print json_encode(array('success' => false, 'error' => array('code' => 137, 'message' => $si->getError(137))));
				exit;
			}
			$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';
	
			$timeStart = microtime(true);
			$tStart = time();
			$loopFlag = true;
			$images_array = array();$imageCount = 0;
	
			$flag = false;
			$idArray = array();
			$imageIds = json_decode(@stripslashes(trim($imageId)),true);
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
						$func = ($code == 'id') ? 'imageLoadById' : 'imageLoadByBarcode';
						if(!$si->image->{$func}($id)) continue;
						$device = $si->storage->storageDeviceGet($si->image->imageGetProperty('storageDeviceId'));
						# getting image
						switch(strtolower($device['type'])) {
							case 's3':
								$amazon = new AmazonS3(array('key' => $device['password'],'secret' => $device['key']));
								$tmpPath = $_TMP . $si->image->imageGetProperty('filename');
								$fp = fopen($tmpPath, "w+b");
								$amazon->get_object($device['basePath'], $si->image->imageGetProperty('path').$si->image->imageGetProperty('filename'), array('fileDownload' => $tmpPath));
								// $si->amazon->get_object($config['s3']['bucket'], $key, array('fileDownload' => $tmpPath));
								fclose($fp);
								$image = $tmpPath;
								break;
							case 'local':
								// $image = $config['path']['images'] . $key;
								$image = $device['basePath'] . $si->image->imageGetProperty('path') . '/' . $si->image->imageGetProperty('filename');
								break;
						}
						$bcode = @explode('.',$si->image->imageGetProperty('filename'));
						@array_pop($bcode);
						$bcode = @implode('.',$bcode);


						# processing
						putenv("LD_LIBRARY_PATH=/usr/local/lib");
						$data = exec(sprintf("%s %s", $config['boxDetectPath'], $image));
						# putting the json data
						$key = $si->image->imageGetProperty('path') . '/' . $bcode . '_box.json';
						switch(strtolower($device['type'])) {
							case 's3':
								$tmpJson = $_TMP . $bcode . '_box.json';
								@file_put_contents($tmpJson,$data);
								$response = $amazon->create_object ($device['basePath'], $key, array('fileUpload' => $tmpJson,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
								@unlink($tmpJson);
								@unlink($tmpPath);
								break;
							case 'local':
								// @file_put_contents($config['path']['images'] . $key,$data);
								@file_put_contents($device['basePath'] . $key,$data);
								break;
						}
						$images_array[] = array('imageId' => $si->image->imageGetProperty('imageId'), 'barcode' => $si->image->imageGetProperty('barcode'));
						$imageCount++;
						$si->pqueue->deleteProcessQueue($si->image->imageGetProperty('imageId'),'box_add');
						$si->image->imageSetProperty('boxFlag',1);
						$si->image->imageSave();
					}
				}
			} else {
				while($loopFlag) {
					$tDiff = time() - $tStart;
					if( ($stop != '') && ( $tDiff > $stop) ) $loopFlag = false;
					if($limit != '') {
						if($limit == 0) break;
					}
					if($limit != '' && $imageCount >= ($limit - 1)) $loopFlag = false;
					$record = $si->pqueue->processQueuePop('box_add');
					if($record === false) {
						$loopFlag = false;
					} else {
						// $si->image->imageLoadByBarcode($record->imageId );
						$si->image->imageLoadById($record->imageId );
						$device = $si->storage->storageDeviceGet($si->image->imageGetProperty('storageDeviceId'));
	
						# getting image
						switch(strtolower($device['type'])) {
							case 's3':
								$amazon = new AmazonS3(array('key' => $device['password'],'secret' => $device['key']));
								$tmpPath = $_TMP . $si->image->imageGetProperty('filename');
								$fp = fopen($tmpPath, "w+b");
								$amazon->get_object($device['basePath'], $si->image->imageGetProperty('path').$si->image->imageGetProperty('filename'), array('fileDownload' => $tmpPath));
								// $si->amazon->get_object($config['s3']['bucket'], $key, array('fileDownload' => $tmpPath));
								fclose($fp);
								$image = $tmpPath;
								break;
							case 'local':
								// $image = $config['path']['images'] . $key;
								$image = $device['basePath'] . $si->image->imageGetProperty('path') . '/' . $si->image->imageGetProperty('filename');
								break;
						}
						
						$bcode = @explode('.',$si->image->imageGetProperty('filename'));
						@array_pop($bcode);
						$bcode = @implode('.',$bcode);
						
						# processing
						putenv("LD_LIBRARY_PATH=/usr/local/lib");
						$data = exec(sprintf("%s %s", $config['boxDetectPath'], $image));
						# putting the json data
						$key = $si->image->imageGetProperty('path') . '/' . $bcode . '_box.json';
						switch(strtolower($device['type'])) {
							case 's3':
								$tmpJson = $_TMP . $bcode . '_box.json';
								@file_put_contents($tmpJson,$data);
								$response = $amazon->create_object ($device['basePath'], $key, array('fileUpload' => $tmpJson,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
								@unlink($tmpJson);
								@unlink($tmpPath);
								break;
							case 'local':
								// @file_put_contents($config['path']['images'] . $key,$data);
								@file_put_contents($device['basePath'] . $key,$data);
								break;
						}
						$images_array[] = array('imageId' => $si->image->imageGetProperty('imageId'), 'barcode' => $si->image->imageGetProperty('barcode'));
						$imageCount++;
						$si->image->imageSetProperty('boxFlag',1);
						$si->image->imageSave();
					}
				} # while
			}
			$time_taken = microtime(true) - $timeStart;
			print json_encode(array('success' => true, 'processTime' => $time_taken, 'totalCount' =>$imageCount, 'records' => $images_array));
			break;
		case 'processNameFinder':
			$timeStart = microtime(true);
			$tStart = time();
			$loopFlag = true;
			$imageCount = 0;
			while($loopFlag) {
				$tDiff = time() - $tStart;
				if( ($stop != '') && ( $tDiff > $stop) ) $loopFlag = false;
				if($limit != '' && $imageCount >= $limit) $loopFlag = false;
				$record = $si->pqueue->processQueuePop('name_add');
				if($record === false) {
					$loopFlag = false;
				} else {
	
					$ret = getNames($record->imageId);
					$si->image->imageLoadById($record->imageId);
					if($ret['success']) {
						$si->image->imageSetProperty('family',$ret['data']['family']);
						$si->image->imageSetProperty('genus',$ret['data']['genus']);
						$si->image->imageSetProperty('scientificName',$ret['data']['scientificName']);
						$si->image->imageSetProperty('specificEpithet',$ret['data']['specificEpithet']);
						$si->image->imageSetProperty('nameFinderValue',$ret['data']['rawData']);
					}
					$si->image->imageSetProperty('nameFinderFlag',1);
					$si->image->imageSave();
	
					$imageCount++;
				}
			}
			$time_taken = microtime(true) - $timeStart;
			print json_encode(array('success' => true, 'processTime' => $time_taken, 'totalCount' => $imageCount));
			
			break;
	
		case 'uploadFlickr':
			$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';
	
			$timeStart = microtime(true);
			$tStart = time();
			$loopFlag = true;
			$f = new phpFlickr($config['flkr']['key'],$config['flkr']['secret']);
			if( $f->auth_checkToken() === false) {
				$f->auth('write');
			}
	
			$images_array = array();$imageCount = 0;
			while($loopFlag) {
				$tDiff = time() - $tStart;
				if( ($stop != '') && ( $tDiff > $stop) ) $loopFlag = false;
				if($limit != '' && $imageCount >= $limit) $loopFlag = false;
				$record = $si->pqueue->processQueuePop('flickr_add');
				if($record === false) {
					$loopFlag = false;
				} else {
	
					if($config['mode'] == 's3') {
						$tmpFileName = 'Img_' . time();
						$tmpFilePath = $_TMP . $tmpFileName;
						$image = $tmpFilePath . '.jpg';
						$key = $si->image->imageBarcodePath($record->imageId) . $record->imageId . '.jpg';
						$si->amazon->get_object($config['s3']['bucket'], $key, array('fileDownload' => $image));
					} else {
						$image = $config['path']['images'] . $si->image->imageBarcodePath($record->imageId) . $record->imageId . '.jpg';
					}
					# change setting photo to private while uploading
					$res = $f->sync_upload( $image, $record->imageId, '', '', 0 );
					if( $res != false ) {
	
						$flkrData = $f->photos_getInfo($res);
						$flickr_details = json_encode(array('server' => $flkrData['server'],'farm' => $flkrData['farm'],'secret' => $flkrData['secret']));
	
						$tags = "{$record->imageId} copyright:(CyberFlora-Louisiana)";
						$f->photos_addTags($res,$tags);
						$si->image->imageLoadByBarcode($record->imageId);
	
						$images_array[] = array('imageId' => $si->image->imageGetProperty('imageId'), 'barcode' => $si->image->imageGetProperty('barcode'));
						$imageCount++;
	
						$si->image->imageSetProperty('flickrPlantId',$res);
						$si->image->imageSetProperty('flickrModified',date('Y-m-d H:i:s'));
						$si->image->imageSetProperty('flickrDetails',$flickr_details);
						$si->image->imageSave();
					}
	
					if($config['mode'] == 's3') {
						@unlink($image);
					}
	
				}
			}
			$time_taken = microtime(true) - $timeStart;
			print json_encode(array('success' => true, 'processTime' => $time_taken, 'totalCount' =>$imageCount, 'records' => $images_array));
	
			break;
		case 'uploadPicassa':
			$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';
	
			$timeStart = microtime(true);
			$tStart = time();
			$loopFlag = true;
	
			$picassa = new PicassaWeb;
			
			$picassa->set('picassa_path',$config['picassa']['lib_path']);
			$picassa->set('picassa_user',$config['picassa']['email']);
			$picassa->set('picassa_pass',$config['picassa']['pass']);
			$picassa->set('picassa_album',$config['picassa']['album']);
			
			$picassa->clientLogin();
	
			$images_array = array();$imageCount = 0;
	
			while($loopFlag) {
				if( ($stop != "") && ((time() - $tStart) > $stop) ) $loopFlag = false;
				if($limit != '' && $imageCount >= $limit) $loopFlag = false;
				$record = $si->pqueue->processQueuePop('picassa_add');
				if($record === false) {
					$loopFlag = false;
				} else {
					$image = array();
					if($config['mode'] == 's3') {
						$tmpFile = $_TMP . 'Img_' . time() . '.jpg';
						$key = $si->image->imageBarcodePath($record->imageId) . $record->imageId . '.jpg';
						$si->amazon->get_object($config['s3']['bucket'], $key, array('fileDownload' => $tmpFile));
						$image['tmp_name'] = $tmpFile;
					} else {
						$image['tmp_name'] = $config['path']['images'] . $si->image->imageBarcodePath($record->imageId) . $record->imageId . '.jpg';
					}
					$image['name'] = $record->imageId;
					$image['type'] = 'image/jpeg';
					$image['tags'] = $record->imageId;
					$album_id = $picassa->getAlbumID();
					$res = $picassa->addPhoto($image);
					if( $res != false ) {
						
						$si->image->imageLoadByBarcode($record->imageId);
	
						$images_array[] = array('imageId' => $si->image->imageGetProperty('imageId'), 'barcode' => $si->image->imageGetProperty('barcode'));
						$imageCount++;
	
						$si->image->imageSetProperty('picassaPlantId',$res);
						$si->image->imageSetProperty('picassaModified',date('Y-m-d H:i:s'));
						$si->image->imageSave();
					}
				}
				if($config['mode'] == 's3') {
					@unlink($tmpFile);
				}
			}
			$time_taken = microtime(true) - $timeStart;
			print json_encode(array('success' => true, 'processTime' => $time_taken, 'totalCount' =>$imageCount, 'records' => $images_array));
			break;
	
		case 'populateEnLabels':
			$timeStart = microtime(true);
			$start_date = $si->s2l->getLatestDate();

			$url = $config['hsUrl'] . '?task=getEnLabels&start_date=' . $start_date;
			// echo $url; exit;
			$jsonObject = @stripslashes(@file_get_contents($url));
	
			$jsonObject = json_decode($jsonObject,true);
			if($jsonObject['success']) {
				$labels = $jsonObject['results'];
				if(is_array($labels) && count($labels)) {
					foreach($labels as $label) {
						$si->s2l->Specimen2LabelSetProperty('labelId',$label['label_id']);
						$si->s2l->Specimen2LabelSetProperty('evernoteAccountId',$label['evernote_account']);
						$si->s2l->Specimen2LabelSetProperty('barcode',$label['barcode']);
						$si->s2l->Specimen2LabelSetProperty('dateAdded',$label['date_added']);
						if($si->s2l->Specimen2LabelSave()) {
							$labelCount++;
						}
					}
				}
			}
			$time_taken = microtime(true) - $timeStart;
			print json_encode(array('success' => true, 'processTime' => $time_taken, 'labelCount' => $labelCount));
			break;
	
		case 'populateGuessTaxaProcessQueue':
			$timeStart = microtime(true);
			$count = 0;
			$filter['start'] = 0;
			$filter['limit'] = $limit;
			if(trim($imageId) != '') {
				$imageIds = json_decode(stripslashes($imageId),true);
				if(is_array($imageIds) && count($imageIds)) {
					foreach($imageIds as $imageId) {
						$loadFlag = false;
						if(!is_numeric($imageId)) {
							$loadFlag = $si->image->imageLoadByBarcode($imageId);
						} else {
							$loadFlag = $si->image->imageLoadById($imageId);
						}
						if($loadFlag) {
							if(!$si->pqueue->processQueueFieldExists($si->image->imageGetProperty('barcode'),'guess_add')) {
								$si->pqueue->processQueueSetProperty('imageId', $si->image->imageGetProperty('barcode'));
								$si->pqueue->processQueueSetProperty('processType', 'guess_add');
								$si->pqueue->processQueueSave();
								$count++;
							}
						}
					}
				}
			} else {
				$ret = $si->image->imageGetGuessTaxaRecords($filter);
				$countFlag = true;
				while(($record = $ret->fetch_object()) && ($countFlag)) {
					if(!$si->pqueue->processQueueFieldExists($record->barcode,'guess_add')) {
						$si->pqueue->processQueueSetProperty('imageId', $record->barcode);
						$si->pqueue->processQueueSetProperty('processType', 'guess_add');
						$si->pqueue->processQueueSave();
						$count++;
						if($limit != '' && $count >= $limit) {
							$countFlag = false;
						}
					}
				}
			}
			$time = microtime(true) - $timeStart;
			print json_encode(array('success' => true, 'processTime' => $time, 'totalCount' => $count));
			break;
	
		case 'processGuessTaxa':
			if(!$config['tesseractEnabled']) {
				header('Content-type: application/json');
				print json_encode(array('success' => false, 'error' => array('code' => 137, 'message' => $si->getError(137))));
				exit;
			}
			$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';
	
			$timeStart = microtime(true);
			$tStart = time();
			$loopFlag = true;
			$images_array = array();$imageCount = 0;
			while($loopFlag) {
				$tDiff = time() - $tStart;
				if( ($stop != '') && ( $tDiff > $stop) ) $loopFlag = false;
	
				if($limit != '') {
					if($limit == 0) break;
				}
				if($limit != '' && $imageCount >= ($limit - 1)) $loopFlag = false;
	
				$record = $si->pqueue->processQueuePop('guess_add');
				if($record === false) {
					$loopFlag = false;
				} else {
					$imageId = $record->imageId;
					$si->image->imageLoadByBarcode($imageId);
					if(!($si->image->imageGetProperty('ocr_flag')))
					{
					//Perform ocr and store values
						if($config['mode'] == 's3') {
							$tmpFileName = 'Img_' . microtime();
							$tmpFilePath = $_TMP . $tmpFileName;
							$tmpFile = $tmpFilePath . '.jpg';
							$key = $si->image->imageBarcodePath($record->imageId) . $record->imageId . '.jpg';
	
							$si->amazon->get_object($config['s3']['bucket'], $key, array('fileDownload' => $tmpFile));
						} else {
							$tmpFilePath = $config['path']['images'] . $si->image->imageBarcodePath($record->imageId) . $record->imageId;
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
							$si->image->imageLoadByBarcode($record->imageId);
							$images_array[] = array('imageId' => $si->image->imageGetProperty('imageId'), 'barcode' => $si->image->imageGetProperty('barcode'));
							$imageCount++;
	
							$si->image->imageSetProperty('ocrFlag',1);
							$si->image->imageSetProperty('ocrValue',$value);
							$si->image->imageSave();
						}
						if($config['mode'] == 's3') {
							@unlink($tmpFile);
							@unlink($tmpFilePath . '.txt');
						}
					}
					$si->image->imageLoadByBarcode($imageId);
					$imageCount++;
					$array = gbifNameFinder($si->image->imageGetProperty('ocrValue'));
					if($array) {
						foreach($array as $names) {
							$array1 = gbifChecklistBank($names);
							$array2 = gbifFullRecord($array1['taxonID']);
							$expectedRank = array('family','genus');
							if(strtolower($array2['taxonomicStatus']=='synonym')) {
								if(in_array(strtolower($array1['rank']),$expectedRank))
								{
									$si->image->imageSetProperty('tmp'.ucfirst($array1['rank']),$array2['canonicalName']);
									$si->image->imageSetProperty('guessFlag',1);
									$si->image->imageSave();
								}
							}
							else
							{
								if(in_array(strtolower($array1['rank']),$expectedRank))
								{
									$si->image->imageSetProperty('tmp'.ucfirst($array1['rank']).'Accepted',$array2['higherTaxon']);
									$si->image->imageSetProperty('guessFlag',1);
									$si->image->imageSave();
								}
							}
						}
					}
					
				}
			}
			$time_taken = microtime(true) - $timeStart;
			header('Content-type: application/json');
			print json_encode(array('success' => true, 'processTime' => $time_taken, 'totalCount' => $imageCount, 'images' => $images_array));
			break;
			
		case 'populateEvernoteProcessQueue':
			$timeStart = microtime(true);
			$count = 0;
			$filter['start'] = 0;
			$filter['limit'] = $limit;
			$filter['collectionCode'] = (trim($collectionCode!='')) ? $collectionCode : '';
			
			if($advFilterId != '') {
				if($si->advFilter->advFilterLoadById($advFilterId)) {
					$advFilter  = $si->advFilter->advFilterGetProperty('filter');
				}
			}
			$advFilter = json_decode(stripslashes(trim($advFilter)),true);
	
			$idArray = array();
			if(is_numeric($imageId)) {
				$imageIds = array($imageId);
			} else {
				$imageIds = json_decode(@stripslashes(trim($imageId)), true);
			}
			if(is_array($imageIds) && count($imageIds)) {
				$idArray = @array_fill_keys($imageIds,'id');
			}

			if(is_numeric($barcode)) {
				$barcodes = array($barcode);
			} else {
				$barcodes = json_decode(@stripslashes(trim($barcode)), true);
			}
			
			if(is_array($barcodes) && count($barcodes)) {
				$idArray = $idArray + @array_fill_keys($barcodes,'code');
			} else if($barcode != '') {
				$idArray = $idArray + @array_fill_keys($barcode,'code');
			}
			
			if(is_array($advFilter) && count($advFilter)) {
				$qry = $si->image->getByCrazyFilter($advFilter, true);
				$ret = $si->db->query($qry);
				$count = $si->db->query_total();
				$qry = $si->image->getByCrazyFilter($advFilter);
				$query = " INSERT IGNORE INTO processQueue(imageId, processType, dateAdded) SELECT im.imageId, 'evernote', NOW()  FROM ($qry) im ";
				// echo $query; exit;
				$si->db->query($query);
			} else if(is_array($idArray) && count($idArray)) {
				foreach($idArray as $id => $code) {
					$func = ($code == 'id') ? 'imageLoadById' : 'imageLoadByBarcode';
					if(!$si->image->{$func}($id)) continue;
					if(!$si->pqueue->processQueueFieldExists($si->image->imageGetProperty('imageId'),'evernote')) {
						$si->pqueue->processQueueSetProperty('imageId', $si->image->imageGetProperty('imageId'));
						$si->pqueue->processQueueSetProperty('processType', 'evernote');
						$si->pqueue->processQueueSave();
						$count++;
					}
				}
			} else {
				$where = '';
				if(is_numeric($filter['start']) && is_numeric($filter['limit'])) {
					$where = sprintf(" LIMIT %s, %s ", $filter['start'], $filter['limit']);
				}
				$query = 'SELECT count(*) ct FROM `image` WHERE (  `processed` = 0 OR `processed` IS NULL ' . (($filter['collectionCode'] != '') ? sprintf(" AND `collectionCode` = '%s' ", $filter['collectionCode']) : '' ) . ' )' . $where;
				$rt = $si->db->query_one($query);
				$count = $rt->ct;
				$query = " INSERT IGNORE INTO processQueue(imageId, processType, dateAdded) SELECT imageId, 'evernote', NOW() FROM `image` WHERE (  `processed` = 0 OR `processed` IS NULL " . (($filter['collectionCode'] != '') ? sprintf(" AND `collectionCode` = '%s' ", $filter['collectionCode']) : '' ) . " ) " . $where;
				$si->db->query($query);
				
			}
			
/*			
			if(trim($imageId) != '') {
				if(is_numeric($imageId)) {
					$imageIds = array($imageId);
				} else {
					$imageIds = json_decode(stripslashes($imageId),true);
				}
				if(is_array($imageIds) && count($imageIds)) {
					foreach($imageIds as $imageId) {
						$loadFlag = false;
						if(!is_numeric($imageId)) {
							$loadFlag = $si->image->imageLoadByBarcode($imageId);
						} else {
							$loadFlag = $si->image->imageLoadById($imageId);
						}
						if($loadFlag) {
							if(!$si->pqueue->processQueueFieldExists($si->image->imageGetProperty('imageId'),'evernote')) {
								$si->pqueue->processQueueSetProperty('imageId', $si->image->imageGetProperty('imageId'));
								$si->pqueue->processQueueSetProperty('processType', 'evernote');
								$si->pqueue->processQueueSave();
								$count++;
							}
						}
					}
				}
			} else {
				$ret = $si->image->imgeGetNonProcessedRecords($filter);
				$countFlag = true;
				while(($record = $ret->fetch_object()) && $countFlag) {
					if(!$si->pqueue->processQueueFieldExists($record->imageId,'evernote')) {
						$si->pqueue->processQueueSetProperty('imageId', $record->imageId);
						$si->pqueue->processQueueSetProperty('processType', 'evernote');
						$si->pqueue->processQueueSave();
						$count++;
						if($limit != '' && $count >= $limit) {
							$countFlag = false;
						}
					}
				}
			}
*/			
			$time = microtime(true) - $timeStart;
			header('Content-type: application/json');
			print json_encode(array('success' => true, 'processTime' => $time, 'totalCount' => $count));
			break;
			
		case 'processEvernoteProcessQueue':
			$timeStart = microtime(true);
			$tStart = time();
			$loopFlag = true;
			$imageCount = 0;
			if(!$si->en->evernoteAccountsLoadById( $enAccountId )) {
				print json_encode(array('success' => false, 'message' => 'No valid evernote account id given'));
				exit;
			}
			while($loopFlag) {
				$tDiff = time() - $tStart;
				if( ($stop != '') && ( $tDiff > $stop) ) $loopFlag = false;
				$record = $si->pqueue->processQueuePop('evernote');
				if($record === false) {
					$loopFlag = false;
				} else {
					$si->image->imageLoadById($record->imageId);
					
					$url = $config['evernoteUrl']."?cmd=add_note";
					$url .= "&title=".$si->image->imageGetProperty('barcode');
					if($si->image->imageGetProperty('collectionCode') != '') {
						$tagName = "CollectionCode:".$si->image->imageGetProperty('collectionCode');
						$url .= "&tag=[\"".$tagName."\"]";
					}
					$label = $si->image->imageGetUrl($record->imageId);
					$url .= "&label=".$label['url'];
					$url .= "&auth=[".json_encode($si->en->evernoteAccountsGetDetails()).']';
					$result = file_get_contents($url);
					$result = json_decode($result, true);
					if($result['success']) {
						$si->s2l->Specimen2LabelSetProperty('labelId',$result['noteRet']['noteRet']['updateSequenceNum']);
						$si->s2l->Specimen2LabelSetProperty('evernoteAccountId',$enAccountId);
						$si->s2l->Specimen2LabelSetProperty('barcode',$si->image->imageGetProperty('barcode'));
						$si->s2l->Specimen2LabelSave();
						if($si->image->imageGetProperty('collectionCode') != '')
						$si->en->evernoteTagsAdd($tagName, $result['noteRet']['noteRet']['tagGuids'][0]);
						$imageCount++;
					}
				}
				if($limit != '' && $imageCount >= $limit) $loopFlag = false;
			}
			$time_taken = microtime(true) - $timeStart;
			header('Content-type: application/json');
			print json_encode(array('success' => true, 'processTime' => $time_taken, 'totalCount' =>$imageCount));
			break;
	
	# Test Tasks
	
			default:
				print json_encode(array('success' => false, 'message' => 'No cmd Provided'));
				break;
		}
	
	
	function getNames($imageId) {
		global $si,$config;
		if(!$si->image->imageLoadById($imageId)) {
			return array('success' => false);
		}
		$device = $si->storage->storageDeviceGet($si->image->imageGetProperty('storageDeviceId'));
		if($si->image->imageGetProperty('path') != '') {
			$url = @rtrim($device['baseUrl'],'/') . '/' . @trim($si->image->imageGetProperty('path'),'/') . '/' . $si->image->imageGetProperty('filename') . '.txt';
		} else {
			$url = @rtrim($device['baseUrl'],'/') . '/' . $si->image->imageGetProperty('filename') . '.txt';
		}
		$sourceUrl = 'http://namefinding.ubio.org/find?';
		// $sourceUrl2 = 'http://tools.gbif.org/ws/taxonfinder?';
		$sourceUrl2 = 'http://ecat-dev.gbif.org/ws/indexer?';
		
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