<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', '1');

/**
 * Flick API for CFLA Images Server
 */
	ini_set('memory_limit','128M');

	$expected=array(
		  'cmd'
		, 'limit'
		, 'stop' # stop is the number of seconds that the loop should run
	);
	// Initialize allowed variables
	foreach ($expected as $formvar)
		$$formvar = (isset(${"_$_SERVER[REQUEST_METHOD]"}[$formvar])) ? ${"_$_SERVER[REQUEST_METHOD]"}[$formvar]:NULL;


require_once("../../config.php");
$path = BASE_PATH . "resources/api/classes/";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once("classes/phpFlickr/phpFlickr.php");
require_once("classes/class.master.php");
require_once("classes/class.picassa.php");
require_once("classes/class.misc.php");

$si = new SilverImage;
if ( $si->load( $mysql_name ) ) {

	switch($cmd) {
		case 'populateNameFinderProcessQueue':
			$time_start = microtime(true);
			$count = 0;
			$filter['start'] = 0;
			$filter['limit'] = $_REQUEST['limit'];

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
			$filter['limit'] = $_REQUEST['limit'];
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
			$time = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time, 'total_records_added' => $count));
			break;
		case 'populateFlickrProcessQueue':
// populate the queue for uploading to flickr
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
// populate the queue for uploading to picassa
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
// populate the queue for creating Google Map Tiles
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
// populate the queue for Zoomify process
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
// populate the queue with non-processed images
			$time_start = microtime(true);
			$count = 0;
			$filter['start'] = 0;
			$filter['limit'] = $_REQUEST['limit'];
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
		case 'processOcr':
			$time_start = microtime(true);
			$tStart = time();
			$loop_flag = true;
			$images_array = array();$image_count = 0;
			while($loop_flag) {
				$tDiff = time() - $tStart;
				if( ($stop != '') && ( $tDiff > $stop) ) $loop_flag = false;
				if($limit != '' && $image_count >= $limit) $loop_flag = false;
				$record = $si->pqueue->popQueue('ocr_add');
				if($record === false) {
					$loop_flag = false;
				} else {
					if($config['mode'] == 's3') {
						$tmpFileName = 'Img_' . time();
						$tmpFilePath = sys_get_temp_dir() . '/' . $tmpFileName;
						$tmpFile = $tmpFilePath . '.jpg';
						$key = $si->image->barcode_path($record->image_id) . $record->image_id . '.jpg';

						$si->amazon->get_object($config['s3']['bucket'], $key, array('fileDownload' => $tmpFile));
/*
if(file_exists($tmpFile)) {
echo '<br> Temp File Created';
}
*/
					} else {
// echo '<br> In local Path';
						$tmpFilePath = PATH_IMAGES . $si->image->barcode_path($record->image_id) . $record->image_id;
						$tmpFile = $tmpFilePath . '.jpg';
					}
					$tmpImage = $tmpFilePath . '_tmp.jpg';
					$cd = "convert " . $tmpFile . " -colorspace Gray  -contrast-stretch 15% " . $tmpImage;
// echo '<br> Command : ' . $cd;
					exec($cd);

					$command = sprintf("/usr/local/bin/tesseract %s %s", $tmpImage, $tmpFilePath);
// echo '<br> Tesseract Command : ' . $command;
					exec($command);

					exec(sprintf("rm %s",$tmpImage));

					if(@file_exists($tmpFilePath . '.txt')){
// echo '<br> Text File Exists';
						$value = file_get_contents($tmpFilePath . '.txt');
						$si->image->load_by_barcode($record->image_id);
						$images_array[] = array('image_id' => $si->image->get('image_id'), 'barcode' => $si->image->get('barcode'));
						$image_count++;
	
						$si->image->set('ocr_flag',1);
						$si->image->set('ocr_value',$value);
						$si->image->save();
					}

					if($config['mode'] == 's3') {
						exec(sprintf("rm %s",$tmpFile));
						exec(sprintf("rm %s",$tmpFilePath . '.txt'));
					}

				}
			}
			$time_taken = microtime(true) - $time_start;
			header('Content-type: application/json');
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
			$time_start = microtime(true);
			$tStart = time();
			$loop_flag = true;
			$f = new phpFlickr(FLKR_KEY,FLKR_SECRET);
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
						$tmpFilePath = sys_get_temp_dir() . '/' . $tmpFileName;
						$image = $tmpFilePath . '.jpg';
						$key = $si->image->barcode_path($record->image_id) . $record->image_id . '.jpg';
						$si->amazon->get_object($config['s3']['bucket'], $key, array('fileDownload' => $image));
					} else {
						$image = PATH_IMAGES . $si->image->barcode_path($record->image_id) . $record->image_id . '.jpg';
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
						exec(sprintf("rm %s",$image));
					}

				}
			}
			$time_taken = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time_taken, 'total' => $image_count, 'images' => $images_array));

			break;
		case 'uploadPicassa':
			$time_start = microtime(true);
			$tStart = time();
			$loop_flag = true;

			$picassa = new PicassaWeb;
			
			$picassa->set('picassa_path',PICASSA_LIB_PATH);
			$picassa->set('picassa_user',PICASSA_EMAIL);
			$picassa->set('picassa_pass',PICASSA_PASS);
			$picassa->set('picassa_album',PICASSA_ALBUM);
			
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
						$tmpFile = sys_get_temp_dir() . '/' . 'Img_' . time() . '.jpg';
						$key = $si->image->barcode_path($record->image_id) . $record->image_id . '.jpg';
						$si->amazon->get_object($config['s3']['bucket'], $key, array('fileDownload' => $tmpFile));
						$image['tmp_name'] = $tmpFile;
					} else {
						$image['tmp_name'] = PATH_IMAGES . $si->image->barcode_path($record->image_id) . $record->image_id . '.jpg';
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
					exec(sprintf("rm %s",$tmpFile));
				}
			}
			$time_taken = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time_taken, 'total' => $image_count, 'images' => $images_array));
			break;

		case 'populateEnLabels':
			$time_start = microtime(true);
			$start_date = $si->s2l->getLatestDate();
			$hsUrl = 'http://eria.helpingscience.org/silverarchive_engine/silverarchive.php';
			$paramArray = array('task' => 'getEnLabels', 'start_date' => $start_date);
			$jsonObject = CURL(trim($hsUrl),$paramArray);
			$jsonObject = json_decode($jsonObject,true);
			if($jsonObject['success']) {
				$labels = $jsonObject['results'];
				if(is_array($labels) && count($labels)) {
					foreach($labels as $label) {
						$si->s2l->set('labelId',$label['label_id']);
						$si->s2l->set('evernoteAccountId',$label['evernote_account']);
						$si->s2l->set('barcode',$label['barcode']);
					}
				}
			}
			$time_taken = microtime(true) - $time_start;
			print json_encode(array('success' => true, 'process_time' => $time_taken, 'labelCount' => $labelCount));
			break;

# Test Tasks

		case 'flickr_test':
			$barcode = 'NLU0062321';
			if($si->image->load_by_barcode($barcode)) {
				$f = new phpFlickr(FLKR_KEY,FLKR_SECRET);
// print '<pre>';
// var_dump($f);
				if( $f->auth_checkToken() === false) {
	// 				print '<br> In Oauth Loop';
					$f->auth('write');
				}

				$image = PATH_IMAGES . $si->image->barcode_path($barcode) . $barcode . '.jpg';
// print '<br>' . $image;
// 				$res = $f->sync_upload( $image, $barcode );
# change setting photo to private while uploading
				$res = $f->sync_upload( $image, $barcode,'','', 0 );

// 				$res = $f->photos_getInfo($si->image->get('flickr_PlantID'));
				var_dump($res);
			} else {
				print 'image Not Loaded';
			}

/*			$photo_id = '3566869992';
			$f = new phpFlickr(FLKR_KEY,FLKR_SECRET);
			if( $f->auth_checkToken() === false) {
				$f->auth('write');
			}
			$f->photos_addTags($res,'ulm00000003 copyright:(CyberFlora-Louisiana)');*/
			break;

		case 'picassa_test':
// print '<pre>';
// 
// $picassa = new PicassaWeb;
// $picassa->set('picassa_path',PICASSA_LIB_PATH);
// $picassa->set('picassa_user',PICASSA_EMAIL);
// $picassa->set('picassa_pass',PICASSA_PASS);
// $picassa->set('picassa_album',PICASSA_ALBUM);
// $picassa->clientLogin();
// 
// // list photos
// // $picassa->listPhotos();
// 
// // list tags
// $tag_array = $picassa->listTags(5344875490023456322);
// print '<br> Tags List : ';
// print_r($tag_array);
// 
// // get photo details
// $photo_details = $picassa->getPhotodetails(5344875490023456322);
// print '<br> Photo Details : ';
// print_r($photo_details);
// 
// // update photo
// $picassa->set('photo_title','');
// $picassa->set('photo_summary','');
// $picassa->set('photo_tags','');
// $photo_details = $picassa->updatePhoto(5344875490023456322);
// $photo_details = $picassa->getPhotodetails(5344875490023456322);
// print '<br> Photo Details : ';
// print_r($photo_details);

// add tags
// print '<br> Adding Tag : ';
// $tag = $picassa->addTag(5344875490023456322,'test tag1');
// var_dump($tag);
// $tag = $picassa->addTag(5344875490023456322,'test tag2');
// 
// $tag_array = $picassa->listTags(5344875490023456322);
// print '<br> Tags List : ';
// print_r($tag_array);

// delete tags
// $picassa->deleteTag(5344875490023456322,'test tag1');
// $photo_details = $picassa->getPhotodetails(5344875490023456322);
// print '<br> Photo Details : ';
// print_r($photo_details);

			break;

			default:
				print json_encode(array('success' => false, 'message' => 'No Task Provided'));
				break;
		}

	} else {
		print json_encode(array('success' => false, 'message' => "Project Not Loaded"));
	}

function getNames ($barcode) {
	global $si;
	if($barcode == '') {
		return array('success' => false);
	}
	
	$url = BASE_URL . '/images/specimensheets/';
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