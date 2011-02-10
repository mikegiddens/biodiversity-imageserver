<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', '1');
session_start();
ob_start();

	/**
	 * @copyright SilverBiology, LLC
	 * @author Michael Giddens
	 * @website http://www.silverbiology.com
	*/

//	ini_set('memory_limit','200M');
	set_time_limit(0);
	
	$expected=array(
			'cmd'
		,	'stop'
		,	'api'
		,	'limit'
	);
	// Initialize allowed variables
	foreach ($expected as $formvar)
		$$formvar = (isset(${"_$_SERVER[REQUEST_METHOD]"}[$formvar])) ? ${"_$_SERVER[REQUEST_METHOD]"}[$formvar]:NULL;

	/*
	*	Function print_c (Print Callback)
	*	This is a wrapper function for print that will place the callback around the output statement
	*/

	function print_c( $str ) {
		header('Content-type: application/json');
		if ( isset( $_REQUEST['callback'] ) ) {
			$cb = $_REQUEST['callback'] . '(' . $str . ')';
		} else {
			$cb = $str;
		}
		print $cb;
	}

	require_once("../../config.php");

	$path = BASE_PATH . "resources/api/classes/";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);

	require_once("./classes/class.master.php");
	require_once("classes/phpFlickr/phpFlickr.php");
	require_once( "classes/access_user/access_user_class.php");
	
	$si = new SilverImage;
	$user_access = new Access_user($mysql_host, $mysql_user, $mysql_pass, $mysql_name);
	
	// setting picassa constants
	$si->picassa->set('picassa_path',PICASSA_LIB_PATH);
	$si->picassa->set('picassa_user',PICASSA_EMAIL);
	$si->picassa->set('picassa_pass',PICASSA_PASS);
	$si->picassa->set('picassa_album',PICASSA_ALBUM);
	
	// This is the output type that the program needs to return
	if(!isset($api)) {
		$api = "json";
	}

	// This will control the imcoming processes that need to be preformed.
	$valid = true;
	if ( $si->load( $mysql_name ) ) {
	$user_access->db = &$si->db;

	switch( $cmd ) {

		case 'load_logs':
			# needs s3 addition

			if($config['mode'] == 's3') {
				$data['mode'] = $config['mode'];
				$data['s3'] = $config['s3'];
				$data['obj'] = $si->amazon;
				$data['time_start'] = microtime(true);
				$si->logger->setData($data);
				$ret = $si->logger->loadS3Logs();
			} else {
				$data['path_files'] = PATH_FILES;
				$data['processed_files'] = PROCESSED_FILES;
				$data['time_start'] = microtime(true);
				$si->logger->setData($data);
				$ret = $si->logger->loadLogs();
			}

			header('Content-type: application/json');
			if($ret['success']) {
				print ( json_encode ( array( 'success' => true, 'process_time' => $ret['time'], 'total_files_loaded' =>  $ret['total']) ) );
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => 102, 'message' => $si->getError(102)) ) ) );
			}
			break;

		case 'get_id':
			$data['sc_id'] = trim($_REQUEST['sc_id']);
			if($data['sc_id'] == "") {
				$code = 101;
				$valid = false;
			}

			header('Content-type: application/json');
			if($valid) {
				$si->logger->setData($data);
				$si->logger->clearRecords();
				if($si->logger->getId()) {
					$id = $si->logger->getRecords();
					print ( json_encode ( array( 'success' => true, 'data' => $id ) ) );
				}
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		case 'browse':
			$data['time_start'] = microtime(true);
			$data['filter'] = stripslashes(trim($_REQUEST['filter']));
			$data['nodeApi'] = trim($_REQUEST['nodeApi']);
			if(!in_array($data['nodeApi'],array('alpha', 'Family', 'Genus', 'SpecificEpithet'))) {
				$code = 114;
				$valid = false;
			}
			$data['nodeValue'] = trim($_REQUEST['nodeValue']);
			header('Content-type: application/json');
			if($valid) {
				$si->image->setData($data);
				$records = $si->image->loadBrowse();
				print( json_encode( $records ) );
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}

			
			break;

		case 'collection_report':
			$data['date'] = trim($_REQUEST['date']);
			$data['date2'] = trim($_REQUEST['date2']);

			$data['report_type'] = (trim($_REQUEST['report_type']) == '') ? 'year' : trim($_REQUEST['report_type']);
			$data['month'] = trim($_REQUEST['month']);
			$data['year'] = (trim($_REQUEST['year']) == '') ? ($data['report_type'] == 'year' ? date('Y'):'') : trim($_REQUEST['year']);
			$data['station'] = trim($_REQUEST['station']);
			$data['sc'] = trim($_REQUEST['sc']);
			$data['collection_id'] = trim($_REQUEST['collection_id']);

			$data['users'] = json_decode(stripslashes(trim($_REQUEST['users'])),true);

			header('Content-type: application/json');
			if($valid) {
				$si->logger->setData($data);
				$si->logger->loadCollectionReport();
				$records = $si->logger->getRecords();
				print( json_encode( array( 'success' => true,  'data' => $records ) ) );
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		case 'report_by_date_range':
			$data['date'] = trim($_REQUEST['date']);
/*			if($data['date'] == '') {
				$valid = false;
				$code = 103;
			}*/
			$data['date2'] = trim($_REQUEST['date2']);
/*			if($data['date2'] == '') {
				$valid = false;
				$code = 104;
			}*/
			$data['year'] = trim($_REQUEST['year']);
			$data['users'] = json_decode(stripslashes(trim($_REQUEST['users'])),true);
			$data['stage'] = trim($_REQUEST['stage']);
			$data['station'] = trim($_REQUEST['station']);
			$data['sc'] = trim($_REQUEST['sc']);
			$data['user_id'] = trim($_REQUEST['user_id']);

			header('Content-type: application/json');
			if($valid) {
				$si->logger->setData($data);
				$si->logger->clearRecords();
				$si->logger->loadReportByDateRange();
				$records = $si->logger->getRecords();
				print( json_encode( array( 'success' => true,  'data' => $records ) ) );
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		case 'report_by_date':

			$data['date'] = trim($_REQUEST['date']);
			if($data['date'] == '') {
				$valid = false;
				$code = 103;
			}
			$data['users'] = trim($_REQUEST['users']);
			$data['stage'] = trim($_REQUEST['stage']);
			$data['station'] = trim($_REQUEST['station']);
			$data['sc'] = trim($_REQUEST['sc']);

			header('Content-type: application/json');
			if($valid) {
				$si->logger->setData($data);
				$si->logger->loadReportByDate();
				$records = $si->logger->getRecords();
				print( json_encode( array( 'success' => true,  'data' => $records ) ) );
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}

			break;

		case 'graph_report_user':

			$data['date'] = trim($_REQUEST['date']);
/*			if($data['date'] == '') {
				$valid = false;
				$code = 103;
			}*/
			$data['date2'] = trim($_REQUEST['date2']);
// 			if($data['date2'] == '') {
// 				$valid = false;
// 				$code = 104;
// 			}

			$data['report_type'] = (trim($_REQUEST['report_type']) == '') ? 'year' : trim($_REQUEST['report_type']);
			$data['week'] = trim($_REQUEST['week']);
			$data['month'] = trim($_REQUEST['month']);
			$data['year'] = (trim($_REQUEST['year']) == '') ? ($data['report_type'] == 'year' ? date('Y'):'') : trim($_REQUEST['year']);
			$data['station'] = trim($_REQUEST['station']);
			$data['sc'] = trim($_REQUEST['sc']);
			$data['user_id'] = trim($_REQUEST['user_id']);

			$data['users'] = json_decode(stripslashes(trim($_REQUEST['users'])),true);

			header('Content-type: application/json');
			if($valid) {
				$si->logger->setData($data);
				$si->logger->loadGraphReportUsers();
				$records = $si->logger->getRecords();
/*print '<pre>';
print_r($records);*/
				print( json_encode( array( 'success' => true,  'data' => $records ) ) );
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}

			break;

		case 'graph_report_station':

			$data['date'] = trim($_REQUEST['date']);
			if($data['date'] == '') {
				$valid = false;
				$code = 103;
			}
			$data['date2'] = trim($_REQUEST['date2']);
			if($data['date2'] == '') {
				$valid = false;
				$code = 104;
			}
			$data['station'] = trim($_REQUEST['station']);

			header('Content-type: application/json');
			if($valid) {
				$si->logger->setData($data);
				$si->logger->loadGraphReportStations();
				$records = $si->logger->getRecords();
				print( json_encode( array( 'success' => true,  'data' => $records ) ) );
 			}else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		case 'graph_report_sc':

			$data['date'] = trim($_REQUEST['date']);
			if($data['date'] == '') {
				$valid = false;
				$code = 103;
			}
			$data['date2'] = trim($_REQUEST['date2']);
			if($data['date2'] == '') {
				$valid = false;
				$code = 104;
			}
			$data['sc'] = trim($_REQUEST['sc']);

			header('Content-type: application/json');
			if($valid) {
				$si->logger->setData($data);
				$si->logger->loadGraphReportSc();
				$records = $si->logger->getRecords();
				print( json_encode( array( 'success' => true,  'data' => $records ) ) );
 			}else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		case 'image-storage-report':

			$data = array();
			$stats = $si->logger->getImageStorageStats();
			$data[] = array( 'text' => '# of images:', 'value' => $stats['total'] );
			$data[] = array( 'text' => '# of images allowed:', 'value' => $stats['allowed_images'] );

			header('Content-type: application/json');
			print( json_encode( array( 'success' => true,  'data' => $data ) ) );
			break;

		// Service  Should not normally be run as a 
		case 'check-new-images':
			$time_start = microtime(true);
			$si->images->clear_files();
			$rr = $si->images->load_from_folder(PATH_INCOMING);
			$images = $si->images->get_files();
			$count = 0;
			if(count($images) && is_array($images)) {
				foreach($images as $image) {
					$image->db = &$si->db;
					$successFlag = $image->moveToImages();
					if($successFlag) {
						$barcode = $image->getName();
						$filename = $image->get('filename');

						$parts = array();
						$parts = preg_split("/[0-9]+/",$barcode);
						$CollectionCode = $parts[0];
						unset($parts);

						$path = PATH_IMAGES . $image->barcode_path( $barcode ) . $filename;
						$ar = @getimagesize($path);

# if barcode exits already, the image is replaced and the db record is reset and queue populated
						if($image->barcode_exists($barcode)) {
							$image->load_by_barcode($barcode);
						}
						$image->set('barcode',$barcode);
						$image->set('filename',$filename);
						$image->set('flickr_PlantID',0);
						$image->set('picassa_PlantID',0);
						$image->set('gTileProcessed',0);
						$image->set('zoomEnabled',0);
						$image->set('processed',0);

						$image->set('width',$ar[0]);
						$image->set('height',$ar[1]);

						$image->set('CollectionCode',$CollectionCode);

						$image->save();
						unset($image);
	
						$si->pqueue->set('image_id',$barcode);
						$si->pqueue->set('process_type','all');
						$si->pqueue->save();
						$count++;
					} # if file moved correctly
				}
			}
			$time = microtime(true) - $time_start;

			header('Content-type: application/json');
			print( json_encode( array( 'success' => true, 'process_time' => $time, 'total_images' => $count ) ) );
			break;

		case 'storage_info':
			$data = array();
			$data[] = array('text'=>'Size Used','value'=> getdirsize(PATH_IMAGES));
			$data[] = array('text'=>'Free Disk Space','value'=> decodeSize(disk_free_space(PATH_IMAGES)));
			$data[] = array('text'=>'Total Disk Space','value'=> decodeSize(disk_total_space(PATH_IMAGES)));

			header('Content-type: application/json');
			print( json_encode( array( 'success' => true,  'data' => $data ) ) );
			break;

		case 'process_queue':
			$data['stop'] = $stop;
			$data['time_start'] = microtime(true);
			$data['limit'] = $limit;
			$data['mode'] = $config['mode'];
			$data['s3'] = $config['s3'];
			$data['obj'] = $si->amazon;
			$si->pqueue->setData($data);
			$result = $si->pqueue->process_queue();
			if($result['success']) {
				header('Content-type: application/json');
				print( json_encode( array( 'success' => true, 'process_time' => $result['time'], 'total_records' => $result['total'] ) ) );
			}
			break;

		case 'get_image':

			$data['image_id'] = trim($_REQUEST['image_id']);
			if($data['image_id'] == "") {
				$valid = false;
				$code = 107;
			}
			$data['width'] = trim($_REQUEST['width']);
			$data['height'] = trim($_REQUEST['height']);
			$data['size'] = trim($_REQUEST['size']);
			$data['type'] = trim($_REQUEST['type']);
			if($valid) {
				$si->image->setData($data);
				$si->image->getImage();
				header('Content-type: application/json');
				print( json_encode( array( 'success' => true) ) );
			}else {
				header('Content-type: application/json');
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		case 'list_process_queue':

			$data['start'] = ($_REQUEST['start'] != '') ? $_REQUEST['start'] : 0;
			$data['limit'] = ($_REQUEST['limit'] != '') ? $_REQUEST['limit'] : 100;
			if(is_array($_REQUEST['filter'])) {
				$data['filter'] = $_REQUEST['filter'];
			} else {
				$data['filter'] = json_decode(stripslashes($_REQUEST['filter']),true);
			}
			$order = json_decode(stripslashes($_REQUEST['order']),true);
			$dir = (in_array(strtolower(trim($_REQUEST['dir'])),array('asc','desc'))) ? trim($_REQUEST['dir']) : 'ASC';
			if(trim($_REQUEST['sort']) != '') {
				$order[] = array('field' => trim($_REQUEST['sort']), 'dir' => $dir);
			}
			$data['order'] = $order;

			header('Content-type: application/json');
			if($valid) {
				$si->pqueue->setData($data);
				$data = $si->pqueue->listQueue();
				$total = $si->pqueue->db->query_total();
				print( json_encode( array( 'success' => true, 'totalCount' => $total, 'data' => $data ) ) );
			}else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		case 'images':

			$data['start'] = ($_REQUEST['start'] != '') ? $_REQUEST['start'] : 0;
			$data['limit'] = ($_REQUEST['limit'] != '') ? $_REQUEST['limit'] : 100;
			$data['order'] = json_decode(stripslashes(trim($_REQUEST['order'])),true);
			if(is_array($_REQUEST['filter'])) {
				$data['filter'] = $_REQUEST['filter'];
			} else {
				$data['filter'] = json_decode(stripslashes(trim($_REQUEST['filter'])),true);
			}
// print_r($data['filter']);
			$data['image_id'] = trim($_REQUEST['image_id']);
			$data['field'] = trim($_REQUEST['field']);
			$data['value'] = trim($_REQUEST['value']);
			if(trim($_REQUEST['sort']) != '') {
				$data['order'] = array(array('field' => trim($_REQUEST['sort']), 'dir' => trim($_REQUEST['dir'])));
			}

// 			$f = new phpFlickr(FLKR_KEY,FLKR_SECRET);
// 			if( $f->auth_checkToken() === false) {
// 				$f->auth('write');
// 			}

			$data['code'] = ($_REQUEST['code'] != '') ? $_REQUEST['code'] : '';

			header('Content-type: application/json');
			if($valid) {
				$si->image->setData($data);
				$data = $si->image->listImages();

				if(is_array($data) && count($data)) {
					foreach($data as &$dt) {
						if($config['mode'] == 's3') {
	$dt->path = $config['s3']['url'] . $si->image->barcode_path($dt->barcode);
						} else {
	$dt->path =  str_replace(DOC_ROOT,BASE_URL . '/', PATH_IMAGES . $si->image->barcode_path($dt->barcode));
						}

/*
						if($dt->flickr_PlantID !=0 ) {

// 							$flkrData = json_decode($dt->flickr_details,true);

							$flkrData = $f->photos_getInfo($dt->flickr_PlantID);
							$dt->server = $flkrData['server'];
							$dt->farm = $flkrData['farm'];
							$dt->secret = $flkrData['secret'];
						}
*/

					}
				}

				$total = $si->image->db->query_total();
				print_c( json_encode( array( 'success' => true, 'totalCount' => $total, 'data' => $data ) ) );
			}else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}

			break;

		case 'collections':

			$data['start'] = (trim($_REQUEST['start']) != '') ? trim($_REQUEST['start']) : 0;
			$data['limit'] = (trim($_REQUEST['limit']) != '') ? trim($_REQUEST['limit']) : 100;

			$order = stripslashes(trim($_REQUEST['order']));
			if(trim($_REQUEST['sort']) != '') {
				$data['order'] = array(array('field' => trim($_REQUEST['sort']), 'dir' => $_REQUEST['dir']));
			} else {
				if($order == '') { $order = '[{"field":"code","dir":"ASC"}]'; }
				$data['order'] = json_decode($order,true);
			}

			$data['filter'] = json_decode(stripslashes(trim($_REQUEST['filter'])),true);

			header('Content-type: application/json');
			if($valid) {
				$si->collection->setData($data);
				$data = $si->collection->listCollection();
				$total = $si->collection->db->query_total();
				print_c( json_encode( array( 'success' => true, 'totalCount' => $total, 'records' => $data ) ) );
			}else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}

			break;

		case 'image_sequence_cache':

			header('Content-type: application/json');

			$filter['code'] = trim($_REQUEST['code']);
			$filter['exist'] = trim($_REQUEST['exist']);
			if($valid) {

				$pathUrl = IMAGE_SEQUENCE_CACHE;
				$pathUrl = @str_replace(BASE_PATH, BASE_URL . 'biodiversityimageserver/trt/', IMAGE_SEQUENCE_CACHE);


				$si->image->setData($filter);
				$data = $si->image->imageSequenceCache();
				$datalist = json_encode($data);

				$fp = fopen(IMAGE_SEQUENCE_CACHE, 'w');
				fwrite($fp, $datalist);
				fclose($fp);

				$total = $si->image->db->query_total();
				print( json_encode( array( 'success' => true, 'totalCount' => $total, 'cacheFile' => $pathUrl, 'records' => $data ) ) );
			}else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}

			break;

		case 'details':
			$id = trim($_REQUEST['id']);
			$data['start'] = (trim($_REQUEST['start']) != '') ? trim($_REQUEST['start']) : 0;
			$data['limit'] = (trim($_REQUEST['limit']) != '') ? trim($_REQUEST['limit']) : 100;
// 			if($id == '') {
// 				$valid = false;
// 				$code = 109;
// 			}

			header('Content-type: application/json');
			if($valid) {
				if($id != '') {
					$data['filter'] = array(array('data' => array('type' => 'numeric', 'value' => $id, 'comparison' => 'eq'), 'field' => 'collection_id'));
				}

				$si->collection->setData($data);
				$data = $si->collection->listCollection();
				print( json_encode( array( 'success' => true, 'data' => $data ) ) );
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}

			break;

		case 'sizeOfCollection':
			$time = microtime(true);

			$id = trim($_REQUEST['id']);
			$data['start'] = (trim($_REQUEST['start']) != '') ? trim($_REQUEST['start']) : 0;
			$data['limit'] = (trim($_REQUEST['limit']) != '') ? trim($_REQUEST['limit']) : 100;
			header('Content-type: application/json');
			if($valid) {
				$si->collection->setData($data);
				$data = $si->collection->getSizeOfCollection();
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time, 'data' => $data ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		case 'getStationUsers':
			$data = array();
			if(trim($_REQUEST['station_id']) != '') {
				$data['station_id'] = $_REQUEST['station_id'];
			}
			header('Content-type: application/json');
			if($valid) {
				$si->logger->setData($data);
				$si->logger->clearRecords();
				$records = $si->logger->getStationUsers();
// 				print( json_encode( array( 'success' => true,  'data' => $records ) ) );
				print( json_encode( $records ) );
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

/*
		case 'rotate-images':

			$images = json_decode(stripslashes(trim($_REQUEST['images'])),true);
			$imageRotateCount = 0;

			header('Content-type: application/json');
			if($valid) {
				if(is_array($images) && count($images)) {
					foreach($images as $image) {
						$ret = $si->image->rotateImage($image);
						if($ret['success']) {
							$imageRotateCount++;
						}
					}
				}
				print( json_encode( array( 'success' => true,  'message' => $imageRotateCount . ' Images Rotated and Added to Queue !.' ) ) );
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;
*/

		case 'rotate-images':
			$image = array();
			$image['image_id'] = trim($_REQUEST['image_id']);
			$image['degree'] = trim($_REQUEST['degree']);

			if(trim($_REQUEST['image_id']) == '') {
				$code = 107;
				$valid = false;
			}
			if(trim($_REQUEST['degree']) == '') {
				$code = 111;
				$valid = false;
			}

			if(!($user_access->is_logged_in() && $user_access->get_access_level() == 10)){
				$code = 113;
				$valid = false;
			}

			header('Content-type: application/json');
			if($valid) {
				$ret = $si->image->rotateImage($image);
				if($ret['success']) {
					print( json_encode( array( 'success' => true,  'message' => $si->getError(110) ) ) );
				} else {
					print( json_encode( array( 'success' => false ) ));
				}
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;


		case 'delete-image':
			if(trim($_REQUEST['image_id']) != '') {
				$data['image_id'] = trim($_REQUEST['image_id']);
			} else {
				$valid = false;
				$code = 110;
			}

			if(!($user_access->is_logged_in() && $user_access->get_access_level() == 10)){
				$code = 113;
				$valid = false;
			}
			header('Content-type: application/json');
			if($valid) {
				$si->image->setData($data);
				$ret = $si->image->deleteImage();
				if($ret['success']) {
					print( json_encode( array( 'success' => true,  'message' => $ret['message'] ) ) );
				} else {
					print( json_encode( array( 'success' => false,  'message' => $ret['message'] ) ) );
				}
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}

			break;

		case 'generateMissingImages':
			$collectionCodes = json_decode(stripslashes(trim($_REQUEST['collectionCode'])),true);
			$barcodes = array();
			$ct = 0;
			if(is_array($collectionCodes) && count($collectionCodes)) {
				foreach($collectionCodes as $collectionCode) {
					$data['code'] = $collectionCode;
					$si->image->setData($data);
					$ret = $si->image->listImages(false);
					if(!is_null($ret)) {
						while ($row = $ret->fetch_object()) {
							$ct++;
							$path = PATH_IMAGES . $si->image->barcode_path( $row->barcode ) . $row->filename;
							clearstatcache();
							if(!file_exists($path)) {
								$barcodes[] = $row->barcode;
							}
						}
					}
				}
			}
			$count = count($barcodes);
			$barcodes = array_chunk($barcodes, 10);
			$str = '';

			if(count($barcodes) && is_array($barcodes)) {
				$br = array();
				foreach($barcodes as $barcodeChunk) {
					$br[] = @implode(',',$barcodeChunk);
				}
				$str = @implode(',<br>',$br);
			}


			print '<br> Monitored Count : ' . $ct . ' <br> Count : ' . $count . ' <br> Barcodes : <br>' . $str;
			break;

		case 'fixHigherTaxa':
			$sourceUrl = "http://ecat-dev.gbif.org/ws/usage/?rkey=1&q=%s&sort=alpha&pagesize=1&rank=g";
			$sourceUrl2 = "http://ecat-dev.gbif.org/ws/usage/%s";
			$sourceUrl3 = "http://ecat-dev.gbif.org/ws/usage/?rkey=1&q=%s&sort=alpha&pagesize=1&rank=";
			$filter['limit'] = $_REQUEST['limit'];
			$ret = $si->image->getGeneraList($filter);
			$records = 0;
			if(!is_null($ret)) {
				while ($genera = $ret->fetch_object()) {
					$family = '';
					$results = utf8_decode(@file_get_contents(sprintf($sourceUrl,$genera->Genus)));
					$results = json_decode($results,true);
					if($results['data'][0]['higherTaxonID'] == '') {
						$genRet = $si->image->getScientificName($genera->Genus);
// echo '<br>' . sprintf($sourceUrl3,urlencode($genRet->ScientificName));
						$results = utf8_decode(@file_get_contents(sprintf($sourceUrl3,urlencode($genRet->ScientificName))));
						$results = json_decode($results,true);
					}
					if($results['data'][0]['higherTaxonID'] != '') {
						$results2 = json_decode(utf8_decode(@file_get_contents(sprintf($sourceUrl2,$results['data'][0]['higherTaxonID']))),true);
/*
						if(@strtolower($results2['data']['rank']) == 'family') {
						if($genera->Genus != '' && $results2['data']['canonicalName']!= '') {
							$retFamily = $si->image->updateFamilyList($genera->Genus,$results2['data']['canonicalName']);
							if($retFamily['success']){
								$records =+ $retFamily['records'];
							}
						}
						}
*/
						if(@strtolower($results2['data']['rank']) == 'family') {
							$family = $results2['data']['canonicalName'];
						} else if ($results2['data']['family'] != 'null' && $results2['data']['family'] != '') {
							$family = $results2['data']['family'];
// 							$tmp = @explode(' ',trim($results2['data']['family']));
// 							$family = $tmp[0];
						}
						if($genera->Genus != '' && $family != '') {
							$retFamily = $si->image->updateFamilyList($genera->Genus,$family);
							if($retFamily['success']){
								$records =+ $retFamily['records'];
							}
						}
					} # higher taxonId not null
				} # while $ret
			} # if $ret
			print( json_encode( array( 'success' => true, 'recordsUpdated' => $records) ) );

			break;

		case 'getCollectionSpecimenCount':
			header('Content-type: application/json');
			$data['nodeValue'] = trim($_REQUEST['nodeValue']);
			if($data['nodeValue'] == '') {
				$valid = false;
				$code = 115;
			}
			$data['nodeApi'] = trim($_REQUEST['nodeApi']);
			if(!in_array($data['nodeApi'],array('Family','Genus','SpecificEpithet'))) {
				$valid = false;
				$code = 114;
			}
			if($valid) {
				$si->image->setData($data);
				$results = $si->image->getCollectionSpecimenCount();
				print( json_encode( array( 'success' => true,  'results' => $results ) ) );
				
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		# picassa commands

		/**
		 * Lists the photos in the album
		 */
// 		case 'picassa_list_images':
// 			$si->picassa->clientLogin();
// 			$photos = $si->picassa->listPhotos();
// 			break;

		/**
		 * Lists the details of a particular photo
		 */
		case 'picassa_image_details':
			$image_id = trim($_REQUEST['image_id']);
			if($image_id == "") {
				$valid = false;
				$code = 107;
			}

			header('Content-type: application/json');
			if($valid) {
				if($si->image->load_by_id($image_id)) {
					$si->picassa->clientLogin();
					$photos = $si->picassa->getPhotodetails($si->image->get('picassa_PlantID'));
					print( json_encode( array( 'success' => true, 'details' => $photos) ) );
				}
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		/**
		 * Updates the details of a particular photo
		 */
		case 'picassa_update_image':
			$image_id = trim($_REQUEST['image_id']);
			if($image_id == "") {
				$valid = false;
				$code = 107;
			}

			header('Content-type: application/json');
			if($valid) {
				if($si->image->load_by_id($image_id)) {
					$si->picassa->clientLogin();
					$si->picassa->set('photo_title',@trim($_REQUEST['photo_title']));
					$si->picassa->set('photo_summary',@trim($_REQUEST['photo_summary']));
					$si->picassa->set('photo_tags',@trim($_REQUEST['photo_tags']));
					$photos = $si->picassa->updatePhoto($si->image->get('picassa_PlantID'));
					print( json_encode( array( 'success' => true ) ) );
				}
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		/**
		 * add a tag to a particular image
		 */
		case 'picassa_add_tag':
			$image_id = trim($_REQUEST['image_id']);
			$tag = trim($_REQUEST['tag']);
			if($image_id == "") {
				$valid = false;
				$code = 107;
			}

			header('Content-type: application/json');
			if($valid) {
				if($si->image->load_by_id($image_id)) {
					$si->picassa->clientLogin();
					$si->picassa->addTag($si->image->get('picassa_PlantID'),$tag);
					print( json_encode( array( 'success' => true ) ) );
				}
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			
			break;
			
		/**
		 * deletes a specific tag of a particular image
		 */
		case 'picassa_delete_tag':
			$image_id = trim($_REQUEST['image_id']);
			$tag = trim($_REQUEST['tag']);
			if($image_id == "") {
				$valid = false;
				$code = 107;
			}

			header('Content-type: application/json');
			if($valid) {
				if($si->image->load_by_id($image_id)) {
					$si->picassa->clientLogin();
					$si->picassa->deleteTag($si->image->get('picassa_PlantID'),$tag);
					print( json_encode( array( 'success' => true ) ) );
				}
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

/**
 * Populates the image table with the data of images present in the s3 account
 */

		case 'populateS3Data':

			$prefix = 'trt/';
			$response = $si->amazon->list_objects($config['s3']['bucket'],array('prefix' => $prefix));
			if($response->isOK()) {
				$ret = $si->image->populateS3Data($response);
				if($ret['success']) {
					print(json_encode(array('success' => true, 'recordCount' => $ret['recordCount'] ) ) );
				} else {
					print( json_encode( array( 'success' => false ) ) );
				}

			} else {
				print( json_encode( array( 'success' => false, 'error' => array( 'message' => 's3 Error' ) ) ));
			}

			break;

		case 'audit':
			$files = array();
			$files = @json_decode(@stripslashes(trim($_REQUEST['filenames'])),true);
			$autoProcess = @json_decode(@stripslashes(trim($_REQUEST['autoProcess'])),true);
			if(!(is_array($autoProcess) && count($autoProcess))) {
				$autoProcess = array('small' => true, 'medium' => true, 'large' => true, 'google_tile' => true);
			}
			if(!(is_array($files) && count($files))) {
				$data['start'] = (trim($_REQUEST['limit']) != '') ? trim($_REQUEST['limit']) : 0;
				$data['limit'] = trim($_REQUEST['limit']);
				$ret = $si->image->getNonProcessedRecords($data);
				while ($row = $ret->fetch_object())
				{
					$files[] = $row->filename;
				}
			}
			$statsArray = array();
			$tplArray = array('small','medium','large','thumb','google_tile');
			$linkArray = array('small' => '_s','medium' => '_m','large'=>'_l','thumb'=>'_thumb','google_tile' => 'tile_');
			if(is_array($files) && count($files)) {
				foreach($files as $file) {
					$ar = array();
					$fl = @pathinfo($file);
					$barcode = $fl['filename'];
					if($barcode == '') {continue;}
					$prefix = $si->image->barcode_path($barcode);
					$response = $si->amazon->list_objects($config['s3']['bucket'],array('prefix' => $prefix));
					if($response->isOK()) {
						$ar = array_fill_keys($tplArray,false);
						$opArray = array('small','medium','large','thumb','google_tile');
						$body = $response->body;
					
						for($i=0;$i<count($body->Contents);$i++){
							$ky = $body->Contents[$i];
							$filePath = $ky->Key;
							$fileDetails = @pathinfo($filePath);
							$bcode = $fileDetails['filename'];
							if(count($opArray)) {
								foreach($opArray as $op) {
									if(@strpos($bcode,$linkArray[$op]) !== false) {
										$ar[$op] = true;
										$ky = @array_search($op,$opArray);
										if($ky !== false) {
											unset($opArray[$ky]);
										}
										break;
									}
								} # foreach
							}
						} # for contents
						if( is_array($autoProcess) && count($autoProcess) ) {
							foreach($autoProcess as $key => $value ) {
								if($value == true) {
									if(@in_array($key,$tplArray) && $ar[$key] == false) {
										if(!$si->pqueue->field_exists($barcode,$key)) {
											$si->pqueue->set('image_id', $barcode);
											$si->pqueue->set('process_type', $key);
											$si->pqueue->save();
										}
									}
								}
							} # foreach auto-process

						} # if autoprocess

					} # response ok
					$statsArray[] = array('file' => $fl['basename'], 'barcode' => $fl['filename'], 'details' => $ar);


				} # foreach file
			} # if count file
			print_c ( json_encode( array( 'success' => true, 'recordCount' => count($statsArray), 'stats' => $statsArray ) ) );
			break;

case 'auditTest':
echo '<pre>';
$barcode = trim($_REQUEST['barcode']);
// $limit = $_REQUEST['limit'] = 10000;

$accessKey = 'AKIAJO3DSVOINCBELMZQ';
$secretKey = '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';
$bucket = 'silverbiology-imagingtour2010';

$amazon = new AmazonS3($accessKey,$secretKey);

$statsArray = array();

$prefix = $si->image->barcode_path($barcode);
$prefix = "test/";

$response = $amazon->list_objects($bucket,array('prefix' => $prefix,'max-keys' => 1200));

if($response->isOK()) {
	$body = $response->body;
	$barcode = '';
	$skipGtileChk = false;
	$lastFlag = false;

$tplArray = array('small','medium','large','thumb','google_tile');
$linkArray = array('small' => '_s','medium' => '_m','large'=>'_l','thumb'=>'_thumb','google_tile' => 'tile_');

$ar = array_fill_keys($tplArray,false);
$opArray = array('small','medium','large','thumb','google_tile');
$autoProcess = array('small' => true, 'medium' => true, 'large' => true, 'google_tile' => true);

echo '<br> Count : ' . count($body->Contents);

// print_r($body);


	for($i=0;$i<count($body->Contents);$i++){
// if($i >= $limit) break;
		$ky = $body->Contents[$i];
		$filePath = $ky->Key;
		if(stripos($filePath,'labels') !== false || stripos($filePath,'fields') !== false) continue;
		if(stripos($filePath,'google_tiles') !== false) {$ar['google_tile'] = true;continue;}
		$fileDetails = @pathinfo($filePath);
		if(stripos($fileDetails['extension'],'json') !== false) continue;

		# checking for each pic

		if(strpos($fileDetails['filename'],'_s') !== false) {$ar['small'] = true;}
		if(strpos($fileDetails['filename'],'_m') !== false) {$ar['medium'] = true;}
		if(strpos($fileDetails['filename'],'_l') !== false) {$ar['large'] = true;}
		if(strpos($fileDetails['filename'],'_thumb') !== false) {$ar['thumb'] = true;}

		$bcode = str_replace(array('_l','_m','_s','_thumb'),'',$fileDetails['filename']);
		if($bcode != $barcode) {
			if($barcode != '') {
/*
if( is_array($autoProcess) && count($autoProcess) ) {
	foreach($autoProcess as $key => $value ) {
		if($value == true) {
			if(@in_array($key,$tplArray) && $ar[$key] == false) {
				if(!$si->pqueue->field_exists($barcode,$key)) {
					$si->pqueue->set('image_id', $barcode);
					$si->pqueue->set('process_type', $key);
					$si->pqueue->save();
				}
			}
		}
	} # foreach auto-process
} # if autoprocess
*/
echo '<br> Barcode : ' . $barcode;
echo '<br>';
print_r($ar);


			} # barcode != ''
			$barcode = $bcode;
			$skipGtileChk = false;
			$ar = array_fill_keys($tplArray,false);
			$lastFlag = true;
		}


	} # for contents

/*
	if($lastFlag) {
		if( is_array($autoProcess) && count($autoProcess) ) {
			foreach($autoProcess as $key => $value ) {
				if($value == true) {
					if(@in_array($key,$tplArray) && $ar[$key] == false) {
						if(!$si->pqueue->field_exists($barcode,$key)) {
							$si->pqueue->set('image_id', $barcode);
							$si->pqueue->set('process_type', $key);
							$si->pqueue->save();
						}
					}
				}
			} # foreach auto-process
		} # if autoprocess
	} # last flag
*/

} else {
echo 'Not Ok';
} # response ok
// $statsArray[] = array('file' => $fl['basename'], 'barcode' => $fl['filename'], 'details' => $ar);

break;

		case 'clearProcessQueue':
			$types = @json_decode(@stripslashes(trim($_REQUEST['types'])));
			$imageIds = @json_decode(@stripslashes(trim($_REQUEST['imageId'])));
			if(is_array($types) && count($types)) {
				$data['processType'] = $types;
			}
			if(is_array($imageIds) && count($imageIds)) {
				$data['imageIds'] = $imageIds;
			}

			$si->pqueue->setData($data);
			$allowedTypes = array('flicker_add','picassa_add','zoomify','google_tile','ocr_add','name_add');
			$ret = $si->pqueue->clearQueue();
			print_c(json_encode(array('success' => true, 'recordCount' => $ret['recordCount'])));

			break;


		case 'zoomify_test':
			$barcode = trim($_REQUEST['barcode']);
			if ( $si->image->zoomifyImage($barcode) ) {
				print( json_encode( array( 'success' => true ) ) );
			} else {
				$code = 108;
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		case 'amazon_test':

print '<pre>';
echo 'in command';

var_dump($si->amazon);

$response = $si->amazon->list_objects($config['s3']['bucket'], array('prefix' => 'trt/'));
 
// Success?
var_dump($response->isOK());
var_dump(count($response->body->Contents));
exit;

			break;

		case 'flickr_test':
			
			$f = new phpFlickr(FLKR_KEY,FLKR_SECRET);
/*			if( $f->auth_checkToken() === false) {
				$f->auth('write');
			}*/
// 			$rr = $f->photos_getInfo(FLKR_KEY,4682069626,FLKR_SECRET);
			$rr = $f->photos_getInfo(4682069626,FLKR_SECRET);
			echo '<pre>';
			var_dump($rr);

			break;

		default:
			$code = 100;

			header('Content-type: application/json');
			print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			break;

	}
} else {
	print ("Project Not Loaded");
}
ob_end_flush();
?>