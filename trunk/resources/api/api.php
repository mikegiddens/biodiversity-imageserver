<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', '1');
ini_set('memory_limit','128M');
set_time_limit(0);
session_start();
ob_start();

	/**
	 * @copyright SilverBiology, LLC
	 * @author Michael Giddens
	 * @website http://www.silverbiology.com
	*/

	set_time_limit(0);
	
	$expected = array (
		'cmd'
		,'stop'
		,'api'
		,'output'
		,'callback'
		,'start'
		,'limit'
		,'order'
		,'dir'
		,'sort'
		,'filter'
		,'nodeApi'
		,'nodeValue'
		,'sc'
		,'sc_id'
		,'collection_id'
		,'id'
		,'station_id'
		,'user_id'
		,'image_id'
		,'enAccountId'
		,'barcode'
		,'imageId'
		,'date'
		,'date'
		,'week'
		,'month'
		,'year'
		,'width'
		,'height'
		,'size'
		,'photo_title'
		,'photo_summary'
		,'photo_tags'
		,'picassa_PlantID'
		,'collectionCode'
		,'tag'
		,'report_type'
		,'station'
		,'users'
		,'stage'
		,'type'
		,'field'
		,'value'
		,'code'
		,'exist'
		,'degree'
		,'filenames'
		,'autoProcess'
		,'types'
		,'tiles'
		,'filename'
		,'zoom'
		,'index'
		,'attributes'

		,'characters'
		,'browse'
		,'search_type'
		,'search_value'
		,'valueID'
		,'categoryID'
		,'value'
		,'imageID'

		,'nodeApi'
		,'nodeValue'
		,'family'
		,'genus'
		,'imagesType'
		,'tpl'

		,'country'
		,'country_iso'
		,'geoId'

		,'eventId'
		,'eventTypeId'
		,'geoId'
		,'title'
		,'description'
	);

	// Initialize allowed variables
	foreach ($expected as $formvar)
		$$formvar = (isset(${"_$_SERVER[REQUEST_METHOD]"}[$formvar])) ? ${"_$_SERVER[REQUEST_METHOD]"}[$formvar]:NULL;

	# Closing the session for writing
	session_write_close();


	/**
	 * Function print_c (Print Callback)
	 * This is a wrapper function for print that will place the callback around the output statement
	 */

	function print_c( $str, $callback = '' ) {
		header('Content-type: application/json');
		if ( isset( $callback ) && $callback != '' ) {
			$cb = $callback . '(' . $str . ')';
		} else {
			$cb = $str;
		}
		print $cb;
	}

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

	require_once("classes/class.master.php");
	require_once("classes/phpFlickr/phpFlickr.php");
	require_once( "classes/access_user/access_user_class.php");
	
	$si = new SilverImage($config['mysql']['name']);
	$user_access = new Access_user($config['mysql']['host'], $config['mysql']['user'], $config['mysql']['pass'], $config['mysql']['name']);
	
	// setting picassa constants
	$si->picassa->set('picassa_path',$config['picassa']['lib_path']);
	$si->picassa->set('picassa_user',$config['picassa']['email']);
	$si->picassa->set('picassa_pass',$config['picassa']['pass']);
	$si->picassa->set('picassa_album',$config['picassa']['album']);
	
	// This is the output type that the program needs to return
	if(!isset($api)) {
		$api = "json";
	}

	// This will control the incoming processes that need to be preformed.
	$valid = true;
	$code = 0;
	$time_start = microtime(true);

	$user_access->db = &$si->db;

	switch( $cmd ) {

		case 'load_logs':
			if($config['mode'] == 's3') {
				$data['mode'] = $config['mode'];
				$data['s3'] = $config['s3'];
				$data['obj'] = $si->amazon;
				$data['time_start'] = microtime(true);
				$si->logger->setData($data);
				$ret = $si->logger->loadS3Logs();
			} else {
				$data['path_files'] = $config['path']['files'];
				$data['processed_files'] = $config['path']['processed_files'];
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
			$data['sc_id'] = trim($sc_id);
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
			$data['filter'] = stripslashes(trim($filter));
			$data['nodeApi'] = trim($nodeApi);
			if(!in_array($data['nodeApi'],array('alpha', 'Family', 'Genus', 'SpecificEpithet'))) {
				$code = 114;
				$valid = false;
			}
			$data['nodeValue'] = trim($nodeValue);
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
			$data['date'] = trim($date);
			$data['date2'] = trim($date2);

			$data['report_type'] = (trim($report_type) == '') ? 'year' : trim($report_type);
			$data['month'] = trim($month);
			$data['year'] = (trim($year) == '') ? ($data['report_type'] == 'year' ? date('Y'):'') : trim($year);
			$data['station'] = trim($station);
			$data['sc'] = trim($sc);
			$data['collection_id'] = trim($collection_id);

			$data['users'] = json_decode(stripslashes(trim($users)),true);

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
			$data['date'] = trim($date);
			$data['date2'] = trim($date2);
			$data['year'] = trim($year);
			$data['users'] = json_decode(stripslashes(trim($users)),true);
			$data['stage'] = trim($stage);
			$data['station'] = trim($station);
			$data['sc'] = trim($sc);
			$data['user_id'] = trim($user_id);

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

			$data['date'] = trim($date);
			if($data['date'] == '') {
				$valid = false;
				$code = 103;
			}
			$data['users'] = trim($users);
			$data['stage'] = trim($stage);
			$data['station'] = trim($station);
			$data['sc'] = trim($sc);

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

			$data['date'] = trim($date);
			$data['date2'] = trim($date2);

			$data['report_type'] = (trim($report_type) == '') ? 'year' : trim($report_type);
			$data['week'] = trim($week);
			$data['month'] = trim($month);
			$data['year'] = (trim($year) == '') ? ($data['report_type'] == 'year' ? date('Y'):'') : trim($year);
			$data['station'] = trim($station);
			$data['sc'] = trim($sc);
			$data['user_id'] = trim($user_id);

			$data['users'] = json_decode(stripslashes(trim($users)),true);

			header('Content-type: application/json');
			if($valid) {
				$si->logger->setData($data);
				$si->logger->loadGraphReportUsers();
				$records = $si->logger->getRecords();
				print( json_encode( array( 'success' => true,  'data' => $records ) ) );
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}

			break;

		case 'graph_report_station':

			$data['date'] = trim($date);
			if($data['date'] == '') {
				$valid = false;
				$code = 103;
			}
			$data['date2'] = trim($date2);
			if($data['date2'] == '') {
				$valid = false;
				$code = 104;
			}
			$data['station'] = trim($station);

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

			$data['date'] = trim($date);
			if($data['date'] == '') {
				$valid = false;
				$code = 103;
			}
			$data['date2'] = trim($date2);
			if($data['date2'] == '') {
				$valid = false;
				$code = 104;
			}
			$data['sc'] = trim($sc);

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

			$rr = $si->images->load_from_folder($config['path']['incoming']);
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

						$path = $config['path']['images'] . $image->barcode_path( $barcode ) . $filename;
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
			$data[] = array('text'=>'Size Used','value'=> getdirsize($config['path']['images']));
			$data[] = array('text'=>'Free Disk Space','value'=> decodeSize(disk_free_space($config['path']['images'])));
			$data[] = array('text'=>'Total Disk Space','value'=> decodeSize(disk_total_space($config['path']['images'])));

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
			$data['imageIds'] = @json_decode($image_id,true);
			$si->pqueue->setData($data);
			$result = $si->pqueue->process_queue();
			if($result['success']) {
				header('Content-type: application/json');
				print( json_encode( array( 'success' => true, 'process_time' => $result['time'], 'total_records' => $result['total'] ) ) );
			}
			break;

		case 'get_image':

			$data['image_id'] = trim($image_id);
			if($data['image_id'] == "") {
				$valid = false;
				$code = 107;
			}
			$data['width'] = trim($width);
			$data['height'] = trim($height);
			$data['size'] = trim($size);
			$data['type'] = trim($type);
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

		case 'loadTile';
			$index = @str_replace('tile_','',@basename($index,'.jpg'));
			$it = new imgTiles($config['path']['imgTiles'] . $filename . '.sqlite');
			$result = $it->getTileData($zoom, $index);
			
			$type = 'image/jpeg';
			header('Content-Type:'.$type);
			print $result;
			break;

		case 'get_image_tiles':
			$time_start = microtime(true);
			$image_id = trim($image_id);
			if($image_id == "") {
				$valid = false;
				$code = 107;
			}
			if($valid) {
				$si->image->load_by_id($image_id);
				$barcode = $si->image->getName();
				$filename = $si->image->get('filename');

				$url = $config['tileGenerator'] . '?cmd=loadImage&filename=' . $filename;
				if($config['mode'] == 's3') {
					$tmpPath = sys_get_temp_dir() . '/' . $filename;
					$fp = fopen($tmpPath, "w+b");
					# getting the image from s3
					$bucket = $config['s3']['bucket'];
					$key = $si->image->barcode_path($barcode) . $filename;
					$si->amazon->get_object($bucket, $key, array('fileDownload' => $tmpPath));
					$url .= '&absolutePath=' . sys_get_temp_dir() . '/';
				}
				$res = json_decode(trim(@file_get_contents($url)));
				if($config['mode'] == 's3') {
					@unlink($tmpPath);
				}

				if(in_array(@strtolower($tiles),array('create','createclear'))) {
					$si->image->mkdir_recursive( $config['path']['imgTiles'] );
					$tileFolder = strtolower($barcode);
					$it = new imgTiles($config['path']['imgTiles'] . $tileFolder . '.sqlite');

					$handle = opendir($config['path']['tiles'] . $tileFolder);
					while (false !== ($zoom = readdir($handle))) {
						if( $zoom == '.' || $zoom == '..') continue;
						$handle1 = opendir($config['path']['tiles'] . $tileFolder . '/' . $zoom);
						while (false !== ($tile = readdir($handle1))) {
							if( $tile == '.' || $tile == '..') continue;
							$it->recordTile($zoom, $config['path']['tiles'] . $tileFolder . '/' . $zoom . '/' . $tile);
						}
					}
					if(@strtolower($tiles) == 'createclear') {
						$si->image->rmdir_recursive($config['path']['tiles'] . $tileFolder);
					}
				}
				$processTime = microtime(true) - $time_start;
				header('Content-type: application/json');
				print( json_encode( array( 'success' => true, 'processTime' => $processTime, 'url' => $config['tileUrl'] . strtolower($barcode)) ) );
			}else {
				header('Content-type: application/json');
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		case 'list_process_queue':

			$data['start'] = ($start != '') ? $start : 0;
			$data['limit'] = ($limit != '') ? $limit : 100;
			if(is_array($filter)) {
				$data['filter'] = $filter;
			} else {
				$data['filter'] = json_decode(stripslashes($filter),true);
			}
			$order = json_decode(stripslashes($order),true);
			$dir = (in_array(strtolower(trim($dir)),array('asc','desc'))) ? trim($dir) : 'ASC';
			if(trim($sort) != '') {
				$order[] = array('field' => trim($sort), 'dir' => $dir);
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
			$time = microtime(true);
			$data['start'] = ($start != '') ? $start : 0;
			$data['limit'] = ($limit != '') ? $limit : 100;
			$data['order'] = json_decode(stripslashes(trim($order)),true);
			if(is_array($filter)) {
				$data['filter'] = $filter;
			} else {
				$data['filter'] = json_decode(stripslashes(trim($filter)),true);
			}
			$data['image_id'] = trim($image_id);
			$data['field'] = trim($field);
			$data['value'] = trim($value);
			if(trim($sort) != '') {
				$data['order'] = array(array('field' => trim($sort), 'dir' => trim($dir)));
			}
			$data['code'] = ($code != '') ? $code : '';

			$data['characters'] = $characters;
			$data['browse'] = $browse;
			$data['search_value'] = $search_value;
			$data['search_type'] = $search_type;


			if($valid) {
				$si->image->setData($data);
				$data = $si->image->listImages();

				if(is_array($data) && count($data)) {
					foreach($data as &$dt) {
						if($config['mode'] == 's3') {
	$dt->path = $config['s3']['url'] . $si->image->barcode_path($dt->barcode);
						} else {
	$dt->path =  str_replace($config['doc_root'],rtrim($config['base_url'],'/') . '/', $config['path']['images'] . $si->image->barcode_path($dt->barcode));
						}

					}
				}
//***
				if($output=='rss'){
					include("feedwriter.php");
						
						$TestFeed = new FeedWriter(RSS2);
						$TestFeed->setTitle('Toronto Image Server');
						$TestFeed->setLink('http://a1.silverbiology.com/biodiversityimageserver/trt/');
						

						foreach($data as $key=>$value){
							
							$key1=get_object_vars($value);
							
							$imgMed = $key1['path'].$key1['barcode'].'_m.jpg';
							$imgLarg = $key1['path'].$key1['barcode'].'_l.jpg';
						
							$title = $key1['barcode'];  
							$newItem = $TestFeed->createNewItem();
						   
							//Add elements to the feed item    
							$newItem->setTitle($title);
							$newItem->setLink($img1);
							$newItem->setDescription("<a href='".$imgLarg."'><img style='border:1px solid #5C7FB9'src='".$imgMed."'/></a>");
							$newItem->setEncloser($imgLarg,'7','image/jpeg');
							//set the feed item
							$TestFeed->addItem($newItem);
						}

					  $TestFeed->genarateFeed();
				
				} else{
					header('Content-type: application/json');
					$total = $si->image->db->query_total();
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time, 'totalCount' => $total, 'data' => $data ) ), $callback );
				}
			}else {
				
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ), $callback );
			}

			break;

		case 'collections':

			$data['start'] = (trim($start) != '') ? trim($start) : 0;
			$data['limit'] = (trim($limit) != '') ? trim($limit) : 100;

			$order = stripslashes(trim($order));
			if(trim($sort) != '') {
				$data['order'] = array(array('field' => trim($sort), 'dir' => $dir));
			} else {
				if($order == '') { $order = '[{"field":"code","dir":"ASC"}]'; }
				$data['order'] = json_decode($order,true);
			}

			$data['filter'] = json_decode(stripslashes(trim($filter)),true);

			header('Content-type: application/json');
			if($valid) {
				$si->collection->setData($data);
				$data = $si->collection->listCollection();
				$total = $si->collection->db->query_total();
				print_c( json_encode( array( 'success' => true, 'totalCount' => $total, 'records' => $data ) ), $callback );
			}else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ), $callback );
			}

			break;

		case 'image_sequence_cache':

			header('Content-type: application/json');

			$filter['code'] = trim($code);
			$filter['exist'] = trim($exist);
			if($valid) {

				$pathUrl = $config['image_sequence_cache'];
				$pathUrl = @str_replace($config['path']['base'], $config['base_url'] . 'biodiversityimageserver/trt/', $config['image_sequence_cache']);


				$si->image->setData($filter);
				$data = $si->image->imageSequenceCache();
				$datalist = json_encode($data);

				$fp = fopen($config['image_sequence_cache'], 'w');
				fwrite($fp, $datalist);
				fclose($fp);

				$total = $si->image->db->query_total();
				print( json_encode( array( 'success' => true, 'totalCount' => $total, 'cacheFile' => $pathUrl, 'records' => $data ) ) );
			}else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}

			break;

		case 'details':
			$id = trim($id);
			$data['start'] = (trim($start) != '') ? trim($start) : 0;
			$data['limit'] = (trim($limit) != '') ? trim($limit) : 100;
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

			$id = trim($id);
			$data['start'] = (trim($start) != '') ? trim($start) : 0;
			$data['limit'] = (trim($limit) != '') ? trim($limit) : 100;
			header('Content-type: application/json');
			if($valid) {
				$si->collection->setData($data);
				$data = $si->collection->getSizeOfCollection();
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time, 'data' => $data ) ), $callback );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ), $callback );
			}
			break;

		case 'getStationUsers':
			$data = array();
			if(trim($station_id) != '') {
				$data['station_id'] = $station_id;
			}
			header('Content-type: application/json');
			if($valid) {
				$si->logger->setData($data);
				$si->logger->clearRecords();
				$records = $si->logger->getStationUsers();
				print( json_encode( $records ) );
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		case 'getVersion':
			header('Content-type: application/json');
			print( json_encode( array('success' => true, 'version' => $config['version'] ) ) );
			break;

		case 'rechop':
			if(trim($image_id) == '') {
				$valid = false;
				$code = 107;
			}
			header('Content-type: application/json');
			if($valid) {
				$ar = array();
				if(is_numeric($image_id)) {
					$imageIds = array($image_id);
				} else {
					$imageIds = json_decode($image_id,true);
				}
				if(is_array($imageIds) && count($imageIds)) {
					foreach($imageIds as $imageId) {
						if($si->image->load_by_id($imageId)) {
							$si->image->set('flickr_PlantID',0);
							$si->image->set('picassa_PlantID',0);
							$si->image->set('gTileProcessed',0);
							$si->image->set('zoomEnabled',0);
							$si->image->set('processed',0);
							$si->image->save();
					
							$si->pqueue->set('image_id',$si->image->get('barcode'));
							$si->pqueue->set('process_type','all');
							$si->pqueue->save();

							$ar[] = $imageId;
						}
					}
				}
				print( json_encode( array('success' => true, 'records' => $ar ) ) );
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		case 'rotate-images':
			$image = array();
			$image['image_id'] = trim($image_id);
			$image['degree'] = trim($degree);
			$image['obj'] = $si->amazon;

			if(trim($image_id) == '') {
				$code = 107;
				$valid = false;
			}
			if(trim($degree) == '') {
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
			if(trim($image_id) != '') {
				$data['image_id'] = trim($image_id);
			} else {
				$valid = false;
				$code = 107;
			}

			if(!($user_access->is_logged_in() && $user_access->get_access_level() == 10)){
				$code = 113;
				$valid = false;
			}

			$data['obj'] = $si->amazon;

			header('Content-type: application/json');
			if($valid) {
				$si->image->setData($data);
				$ret = $si->image->deleteImage();
				if($ret['success']) {
					print( json_encode( array( 'success' => true ) ) );
				} else {
					print( json_encode( array( 'success' => false, 'error' => array('code' => $ret['code'], 'message' => $si->getError($ret['code']))) ) );
				}
			} else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}

			break;

		case 'generateMissingImages':
			$collectionCodes = json_decode(stripslashes(trim($collectionCode)),true);
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
							$path = $config['path']['images'] . $si->image->barcode_path( $row->barcode ) . $row->filename;
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
			$filter['limit'] = $limit;
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
			$data['nodeValue'] = trim($nodeValue);
			if($data['nodeValue'] == '') {
				$valid = false;
				$code = 115;
			}
			$data['nodeApi'] = trim($nodeApi);
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
		 * Lists the details of a particular photo
		 */
		case 'picassa_image_details':
			$image_id = trim($image_id);
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
			$image_id = trim($image_id);
			if($image_id == "") {
				$valid = false;
				$code = 107;
			}

			header('Content-type: application/json');
			if($valid) {
				if($si->image->load_by_id($image_id)) {
					$si->picassa->clientLogin();
					$si->picassa->set('photo_title',@trim($photo_title));
					$si->picassa->set('photo_summary',@trim($photo_summary));
					$si->picassa->set('photo_tags',@trim($photo_tags));
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
			$image_id = trim($image_id);
			$tag = trim($tag);
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
			$image_id = trim($image_id);
			$tag = trim($tag);
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

		case 'listHSQueue':
			$data['start'] = ($start != '') ? $start : 0;
			$data['limit'] = ($limit != '') ? $limit : 100;
			if(is_array($filter)) {
				$data['filter'] = $filter;
			} else {
				$data['filter'] = json_decode(stripslashes($filter),true);
			}
			$order = json_decode(stripslashes($order),true);
			$dir = (in_array(strtolower(trim($dir)),array('asc','desc'))) ? trim($dir) : 'ASC';
			if(trim($sort) != '') {
				$order[] = array('field' => trim($sort), 'dir' => $dir);
			}
			$data['order'] = $order;

			header('Content-type: application/json');
			if($valid) {
				$si->bis->setData($data);
				$data = $si->bis->listHSQueue();
				$total = $si->bis->db->query_total();
				print( json_encode( array( 'success' => true, 'totalCount' => $total, 'data' => $data ) ) );
			}else {
				print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}

			break;

/**
 * Audits the images and reports and populate the pqueue with the missing ones
 * @param string filenames : json list of the filenames
 * @param string autoProcess : json list to autoProcess
 */

		case 'audit':
			$autoProcessTemplate = array('small' => true, 'medium' => true, 'large' => true, 'google_tile' => false, 'flickr_add' =>  false, 'picassa_add' => false);
			$statsArray = array();
			$tplArray = array('small','medium','large','google_tile','flickr_add','picassa_add');
			$linkArray = array('small' => '_s','medium' => '_m','large'=>'_l','google_tile' => 'tile_');

			$files = array();
			$files = @json_decode(@stripslashes(trim($filenames)),true);
			$autoProcess = @json_decode(@stripslashes(trim($autoProcess)),true);

			if(!(is_array($autoProcess) && count($autoProcess))) {
				$autoProcess = $autoProcessTemplate;
			} else {
				foreach($autoProcessTemplate as $key => $value) {
					if(!@array_key_exists($key,$autoProcess)) {
						$autoProcess[$key] = false;
					}
				}
			}
			if(!(is_array($files) && count($files))) {
				$data['start'] = (trim($limit) != '') ? trim($limit) : 0;
				$data['limit'] = trim($limit);
				$ret = $si->image->getNonProcessedRecords($data);
				if(is_object($ret) && !is_null($ret)) {
					while ($row = $ret->fetch_object())
					{
						$files[] = $row->filename;
					}
				}
			}
			if(is_array($files) && count($files)) {
				foreach($files as $file) {
					$ar = array();
					$fl = @pathinfo($file);
					$barcode = $fl['filename'];
					if($barcode == '') {continue;}

					if($config['mode'] == 's3') {
						$prefix = $si->image->barcode_path($barcode);
						$response = $si->amazon->list_objects($config['s3']['bucket'],array('prefix' => $prefix));
						if($response->isOK()) {
							$ar = array_fill_keys($tplArray,false);
							$opArray = array('small','medium','large','google_tile');
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
							$displayAr = array();
							$displayAr = $ar;
							if($ar['small'] == false && $ar['medium'] == false && $ar['large'] == false && $autoProcess['small'] == true && $autoProcess['medium'] == true && $autoProcess['large'] == true) {
								unset($ar['small']);
								unset($ar['medium']);
								unset($ar['large']);
								if(!$si->pqueue->field_exists($barcode,'all')) {
									$si->pqueue->set('image_id', $barcode);
									$si->pqueue->set('process_type', 'all');
									$si->pqueue->save();
								}
							}
							if( is_array($autoProcess) && count($autoProcess) ) {
								foreach($autoProcess as $key => $value ) {
									if($value === true) {
										if(@in_array($key,$tplArray) && $ar[$key] === false) {
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
						$statsArray[] = array('file' => $fl['basename'], 'barcode' => $fl['filename'], 'details' => $displayAr);
					} else {
					# config mode local
	
						$imagePath = $config['path']['images'] . $si->image->barcode_path( $barcode );
						clearstatcache();
						if(@file_exists($imagePath)) {
							$ar = array_fill_keys($tplArray,false);
							$opArray = array('small','medium','large');
							$handle = opendir($imagePath);
							while (false !== ($file = readdir($handle))) {
								if( $file == '.' || $file == '..' ) continue;
								if(count($opArray)) {
									foreach($opArray as $op) {
										if(@strpos($file,$linkArray[$op]) !== false) {
											$ar[$op] = true;
											$ky = @array_search($op,$opArray);
											if($ky !== false) {
												unset($opArray[$ky]);
											}
											break;
										}
									} # foreach
								}
								if (@strpos($file,'google_tile') !== false) {
									$ar['google_tile'] = true;
								}
							} # while
						} # is valid path
						$displayAr = array();
						$displayAr = $ar;
						if($ar['small'] == false && $ar['medium'] == false && $ar['large'] == false && $autoProcess['small'] == true && $autoProcess['medium'] == true && $autoProcess['large'] == true) {
							unset($ar['small']);
							unset($ar['medium']);
							unset($ar['large']);
							if(!$si->pqueue->field_exists($barcode,'all')) {
								$si->pqueue->set('image_id', $barcode);
								$si->pqueue->set('process_type', 'all');
								$si->pqueue->save();
							}
						}
						if( is_array($autoProcess) && count($autoProcess) ) {
							foreach($autoProcess as $key => $value ) {
								if($value === true) {
									if(@in_array($key,$tplArray) && $ar[$key] === false) {
										if(!$si->pqueue->field_exists($barcode,$key)) {
											$si->pqueue->set('image_id', $barcode);
											$si->pqueue->set('process_type', $key);
											$si->pqueue->save();
										}
									}
								}
							} # foreach auto-process
						} # if autoprocess
						$statsArray[] = array('file' => $fl['basename'], 'barcode' => $fl['filename'], 'details' => $displayAr);

					} # else local

				} # foreach file
			} # if count file
			print_c ( json_encode( array( 'success' => true, 'recordCount' => count($statsArray), 'stats' => $statsArray ) ), $callback );
			break;

		case 'searchEnLabels':
			$time_start = microtime(true);
			$searchWord = urldecode(trim($value));
			$enAccountId = trim($enAccountId);
			if($searchWord == '') {
				$valid = false;
				$code = 118;
			}
			if($valid) {
				$start = (trim($start) == '') ? 0 : trim($start);
				$limit = (trim($limit) == '') ? 25 : trim($limit);
				$data = array();
				$accounts = $si->en->getAccounts();
				$totalNotes = 0;
				if(is_array($accounts) && count($accounts)) {
				$limit = ceil($limit/(count($accounts)));
				foreach($accounts as $account) {
				$evernote_details_json = json_encode($account);

				$url = $config['evernoteUrl'] . '?cmd=findNotes&auth=' . $evernote_details_json . '&start=' . $start . '&limit=' . $limit . '&searchWord=' . urlencode($searchWord);
				$rr = json_decode(@file_get_contents($url),true);
				if($rr['success']) {
					$totalNotes += $rr['totalNotes'];
					$labels = $rr['data'];
					if(is_array($labels) && count($labels)) {
						foreach($labels as $label) {
							if(!array_key_exists($label,$data)) {
								$ar = array();
								$si->s2l->load_by_labelId($label);

								$si->image->setdata(array('field' => 'barcode', 'value' =>$si->s2l->get('barcode') ));
								$ar = $si->image->listImages();
								$ar = $ar[0];
								if($config['mode'] == 's3') {
									$ar->path = $config['s3']['url'] . $si->image->barcode_path($si->s2l->get('barcode'));
								} else {
									$ar->path =  str_replace($config['doc_root'],$config['base_url'] . '/', $config['path']['images'] . $si->image->barcode_path($si->s2l->get('barcode')));
								}
								$data[$label] = $ar;
							}
						}
					}
				} # if rr[success]
				} # for
				} # if labels
				$data = @array_values($data);
				$time = microtime(true) - $time_start;
				print_c( json_encode( array( 'success' => true, 'processTime' => $time, 'totalCount' => $totalNotes, 'data' => $data ) ), $callback );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ), $callback );
			}

			break;

# New Image Admin Tasks

		case 'image_characters':
			$time_start = microtime(true);
			$start = ($start != '') ? $start : 0;
			$limit = ($limit != '') ? $limit : 25;
			$data['attributes'] = implode(',',json_encode($attributes,true));
			$si->image->setData($data);
			$ret = $si->image->loadImageCharacters();
			$processTime = microtime(true) - $time_start;
			if($ret['success']) {
				print_c( json_encode( array( 'success' => true, 'processTime' => $time, 'totalCount' => $ret['recordCount'], 'data' => $ret['data'] ) ), $callback );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ), $callback );
			}
			break;

		case 'add_image_attribute':
			$time_start = microtime(true);
			$data['imageID'] = $imageID;
			if($data['imageID'] == "") {
				$valid = false;
				$code = 107;
			}
			$data['valueID'] = $valueID;
			if($data['valueID'] == "") {
				$valid = false;
				$code = 120;
			}
			$data['categoryID'] = $categoryID;
			if($valid) {
				$si->image->setData($data);
				if($si->image->addImageAttribute()) {
					$processTime = microtime(true) - $time_start;
					print_c( json_encode( array( 'success' => true, 'processTime' => $processTime ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 121, 'msg' => $si->error(121) ) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $code, 'msg' => $si->getError($code) ) ) ) );
			}
			break;

		case 'delete_image_attribute':
			$time_start = microtime(true);
			$data['imageID'] = $imageID;
			if($data['imageID'] == "") {
				$valid = false;
				$code = 107;
			}
			$data['valueID'] = $valueID;
			if($data['valueID'] == "") {
				$valid = false;
				$code = 120;
			}
			if($valid) {
				$si->image->setData($data);
				if($si->image->deleteImageAttribute()) {
					$processTime = microtime(true) - $time_start;
					print_c( json_encode( array( 'success' => true, 'processTime' => $processTime ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 122, 'msg' => $sa->error(122) ) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $code, 'msg' => $si->getError($code) ) ) ) );
			}
			break;

			case 'add_category':
				$time_start = microtime(true);
				$data['value'] = $value;
				if($data['value'] == "") {
					$valid = false;
					$code = 123;
				}
				if($valid) {
					$si->image->setData($data);
					$id = $si->image->addCategory();
					if($id) {
						print_c( json_encode( array( 'success' => true, 'new_id' => $id ) ) );
					} else {
						print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 124, 'msg' => $sa->error(124) ) ) ) );
					}
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $code, 'msg' => $si->getError($code) ) ) ) );
				}
				break;

			case 'rename_category':
				$time_start = microtime(true);
				$data['value'] = $value;
				if($data['value'] == "") {
					$valid = false;
					$code = 123;
				}
				$data['valueID'] = $valueID;
				if($data['valueID'] == "") {
					$valid = false;
					$code = 120;
				}
				if($valid) {
					$si->image->setData($data);
					if($si->image->renameCategory()) {
						print_c( json_encode( array( 'success' => true ) ) );
					} else {
						print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 125, 'msg' => $sa->error(125) ) ) ) );
					}
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $code, 'msg' => $si->getError($code) ) ) ) );
				}
				break;

			case 'delete_category':
				$time_start = microtime(true);
				$data['categoryID'] = $categoryID;
				if($data['categoryID'] == "") {
					$valid = false;
					$code = 126;
				}
				if($valid) {
					$si->image->setData($data);
					if($si->image->deleteCategory()) {
						print_c( json_encode( array( 'success' => true ) ) );
					}
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $code, 'msg' => $si->getError($code) ) ) ) );
				}
				break;

			case 'add_attribute':
				$time_start = microtime(true);
				$data['categoryID'] = $categoryID;
				if($data['categoryID'] == "") {
					$valid = false;
					$code = 126;
				}
				$data['value'] = $value;
				if($data['value'] == "") {
					$valid = false;
					$code = 123;
				}
				if($valid) {
					$si->image->setData($data);
					$id = $si->image->addAttribute();
					if($id) {
						print_c( json_encode( array( 'success' => true, 'new_id' => $id ) ) );
					}
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $code, 'msg' => $si->getError($code) ) ) ) );
				}
				break;

			case 'rename_attribute':
				$time_start = microtime(true);
				$data['value'] = $value;
				if($data['value'] == "") {
					$valid = false;
					$code = 123;
				}
				$data['valueID'] = $valueID;
				if($data['valueID'] == "") {
					$valid = false;
					$code = 120;
				}
				if($valid) {
					$si->image->setData($data);
					if($si->image->renameAttribute()) {
						print_c( json_encode( array( 'success' => true ) ) );
					}
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $code, 'msg' => $si->getError($code) ) ) ) );
				}
				break;

			case 'delete_attribute':
				$time_start = microtime(true);
				$data['valueID'] = $valueID;
				if($data['valueID'] == "") {
					$valid = false;
					$code = 120;
				}
				if($valid) {
					$si->image->setData($data);
					if($si->image->deleteAttribute()) {
						print_c( json_encode( array( 'success' => true ) ) );
					}
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $code, 'msg' => $si->getError($code) ) ) ) );
				}
				break;

# Image Tasks

			case 'image-nodes-characters':
				$nodeApi = ($nodeApi != '') ? @strtolower($nodeApi) : 'root';
				if(!in_array($nodeApi, array('root'))) {
					$code = 128;
					$valid = false;
				}
				$data['nodeValue'] = $nodeValue;
				$data['family'] = $family;
				$data['genus'] = $genus;

				if($valid) {
					$si->image->setData($data);
					if(false !== ($nodes = $si->image->loadImageNodesCharacters())) {
						$processTime = microtime(true) - $time_start;
						print_c( json_encode( array( 'success' => true, 'processTime' => $processTime, 'recordCount' => count($nodes), 'results' => $nodes)));
					} else {
						print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError(127) , 'code' => 127 ) ) ) );
					}
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
				break;

			case 'image-nodes':
				$nodeApi = ($nodeApi != '') ? @strtolower($nodeApi) : 'root';
				if(!in_array($nodeApi, array('root', 'alpha', 'families', 'family', 'genera', 'genus', 'scientificname') )) {
					$code = 128;
					$valid = false;
				}
				$data['nodeValue'] = $nodeValue;
				$data['family'] = $family;
				$data['genus'] = $genus;

				if($valid) {
					$si->image->setData($data);
					if(false !== ($nodes = $si->image->loadImageNodesImages())) {
						$processTime = microtime(true) - $time_start;
						print_c( json_encode( array( 'success' => true, 'processTime' => $processTime, 'recordCount' => count($nodes), 'results' => $nodes)));
					} else {
						print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError(127) , 'code' => 127 ) ) ) );
					}
	
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
		
				break;

			case 'image-characters':
				$data['start'] = ($start == '') ? 0 : $start;
				$data['limit'] = ($limit == '') ? 100 : $limit;
				$data['browse'] = $browse;
				$data['characters'] = $characters;
				$data['search_value'] = $search_value;
				$data['search_type'] = $search_type;
		
				if($valid) {
					$si->image->setData($data);
					if(false !== ($nodes = $si->image->loadCharacterList())) {
						$processTime = microtime(true) - $time_start;
						print_c( json_encode( array( 'success' => true, 'processTime' => $processTime, 'recordCount' => count($nodes), 'results' => $nodes)));
					} else {
						print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError(127) , 'code' => 127 ) ) ) );
					}
	
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
				break;

			case 'image-list':
				$data['start'] = ($start == '') ? 0 : $start;
				$data['limit'] = ($limit == '') ? 100 : $limit;
				$data['imagesType'] = (in_array($imagesType, array(1,2,3))) ? $imagesType : 2;
				$data['tpl'] = ($tpl == '') ? 'defaultImageTemplate.tpl' : $tpl;

				$data['browse'] = $browse;
				$data['characters'] = $characters;
				$data['search_value'] = $search_value;
				$data['search_type'] = $search_type;
				$data['filter'] = $filter;
				$data['sort'] = $sort;
				$data['dir'] = $dir;
	
				if($valid) {
					$si->image->setData($data);
					if(false !== ($nodes = $si->image->loadImageList())) {
						$processTime = microtime(true) - $time_start;
						print_c( json_encode( array( 'success' => true, 'processTime' => $processTime, 'recordCount' => $si->image->total, 'results' => $nodes)));
					} else {
						print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError(129) , 'code' => 129 ) ) ) );
					}
	
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
			break;

		case 'listGeographyRecords':
				$data['start'] = ($start == '') ? 0 : $start;
				$data['limit'] = ($limit == '') ? 100 : $limit;
				$data['country'] = $country;
				$data['country_iso'] = $country_iso;
				$data['field'] = $field;
				$data['value'] = $value;
				$data['geoId'] = $geoId;

				if($valid) {
					$si->geograghy->setData($data);
					$ret = $si->geograghy->listRecords();
					$processTime = microtime(true) - $time_start;
					print_c( json_encode( array( 'success' => true, 'processTime' => $processTime, 'results' => $ret ) ) );
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
			break;

			case 'addEvent':
				if(!$si->event->load_by_id($eventId)) {
				# new record
					if(trim($title) == '') {
						$valid = false;
						$code = 130;
					}
					if(trim($eventTypeId) == '') {
						$valid = false;
						$code = 131;
					}
				}

				if($valid) {
					$si->event->lg->set('action', 'addEvent');
					$si->event->lg->set('lastModifiedBy', $_SESSION['user_id']);

					($geoId != '') ? $si->event->set('geoId', $geoId) : '';
					($eventTypeId != '') ? $si->event->set('eventTypeId', $eventTypeId) : '';
					($title != '') ? $si->event->set('title', $title) : '';
					($description != '') ? $si->event->set('description', $description) : '';
					$si->event->set('lastModifiedBy', $_SESSION['user_id']);
					$si->event->save();

					print_c( json_encode( array( 'success' => true, 'new_id' => $si->event->insert_id ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $code, 'msg' => $si->getError($code) ) ) ) );
				}
				break;

			case 'listEvents':
				$data['start'] = ($start == '') ? 0 : $start;
				$data['limit'] = ($limit == '') ? 100 : $limit;
				$data['eventId'] = $eventId;
				$data['eventTypeId'] = $eventTypeId;
				$data['geoId'] = $geoId;
				$data['field'] = $field;
				$data['value'] = $value;

				if($valid) {
					$si->event->setData($data);
					$ret = $si->event->listRecords();
					$processTime = microtime(true) - $time_start;
					print_c( json_encode( array( 'success' => true, 'processTime' => $processTime, 'results' => $ret ) ) );
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
				break;

			case 'deleteEvent':
				if($eventId == '') {
					$valid = false;
					$code = 133;
				}
				if($valid) {
					$si->event->lg->set('action', 'deleteEvent');
					$si->event->lg->set('lastModifiedBy', $_SESSION['user_id']);

					$si->event->delete($eventId);
					print_c( json_encode( array( 'success' => true ) ) );
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
				break;

			case 'addEventType':
				if(!$si->eventType->load_by_id($eventTypeId)) {
				# new record
					if(trim($title) == '') {
						$valid = false;
						$code = 130;
					}
				}

				if($valid) {
					$si->eventType->lg->set('action', 'addEventType');
					$si->eventType->lg->set('lastModifiedBy', $_SESSION['user_id']);

					($title != '') ? $si->eventType->set('title', $title) : '';
					($description != '') ? $si->eventType->set('description', $description) : '';
					$si->eventType->set('lastModifiedBy', $_SESSION['user_id']);
					$si->eventType->save();

					print_c( json_encode( array( 'success' => true, 'new_id' => $si->eventType->insert_id ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $code, 'msg' => $si->getError($code) ) ) ) );
				}
				break;

			case 'listEventTypes':
				$data['start'] = ($start == '') ? 0 : $start;
				$data['limit'] = ($limit == '') ? 100 : $limit;
				$data['eventTypeId'] = $eventTypeId;
				$data['title'] = $title;
				$data['field'] = $field;
				$data['value'] = $value;

				if($valid) {
					$si->eventType->setData($data);
					$ret = $si->eventType->listRecords();
					$processTime = microtime(true) - $time_start;
					print_c( json_encode( array( 'success' => true, 'processTime' => $processTime, 'results' => $ret ) ) );
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
				break;

			case 'deleteEventType':
				if($eventTypeId == '') {
					$valid = false;
					$code = 131;
				}
				if($valid) {
					$si->eventType->lg->set('action', 'deleteEventType');
					$si->eventType->lg->set('lastModifiedBy', $_SESSION['user_id']);
					$si->eventType->delete($eventTypeId);
					print_c( json_encode( array( 'success' => true ) ) );
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
				break;



# Test Tasks

		case 'clearProcessQueue':
			$types = @json_decode(@stripslashes(trim($types)));
			$imageIds = @json_decode(@stripslashes(trim($imageId)));
			if(is_array($types) && count($types)) {
				$data['processType'] = $types;
			}
			if(is_array($imageIds) && count($imageIds)) {
				$data['imageIds'] = $imageIds;
			}

			$si->pqueue->setData($data);
			$allowedTypes = array('flickr_add','picassa_add','zoomify','google_tile','ocr_add','name_add','all');
			$ret = $si->pqueue->clearQueue();
			print_c(json_encode(array('success' => true, 'recordCount' => $ret['recordCount'])), $callback);

			break;

		default:
			$code = 100;

			header('Content-type: application/json');
			print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			break;

	}

ob_end_flush();
?>
