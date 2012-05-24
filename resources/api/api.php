<?php
	error_reporting(E_ALL ^ E_NOTICE);
	ini_set('memory_limit', '128M');
	set_time_limit(0);
	session_start();
	ob_start();

	$old_error_handler = set_error_handler("myErrorHandler");

	/**
	 * @author SilverBiology
	 * @website http://www.silverbiology.com
	*/

	$expected = array (
			'cmd'
		,	'active'
		,	'api'
		,	'attributes'
		,	'autoProcess'
		,	'barcode'
		,	'basePath'
		,	'baseUrl'
		,	'browse'
		,	'callback'
		,	'categoryID'
		,	'characters'
		,	'code'
		,	'code1'
		,	'collectionCode'
		,	'collection_id'
		,	'country'
		,	'country_iso'
		,	'date'
		,	'day'
		,	'degree'
		,	'description'
		,	'dir'
		,	'enAccountId'
		,	'eventId'
		,	'eventTypeId'
		,	'exist'
		,	'ext'
		,	'family'
		,	'field'
		,	'filename'
		,	'filenames'
		,	'filter'
		,	'force'
		,	'genus'
		,	'geoId'
		,	'height'
		,	'id'
		,	'imageID'
		,	'imageId'
		,	'imagePath'
		,	'image_id'
		,	'imagesType'
		,	'index'
		,	'key'
		,	'limit'
		,	'month'
		,	'name'
		,	'newImagePath'
		,	'newStorageId'
		,	'nodeApi'
		,	'nodeValue'
		,	'order'
		,	'output'
		,	'photo_summary'
		,	'photo_tags'
		,	'photo_title'
		,	'picassa_PlantID'
		,	'pw'
		,	'rank'
		,	'report_type'
		,	'sc'
		,	'sc_id'
		,	'search_type'
		,	'search_value'
		,	'showOCR'
		,	'sId'
		,	'size'
		,	'sort'
		,	'stage'
		,	'start'
		,	'station'
		,	'station_id'
		,	'stop'
		,	'storage_id'
		,	'tag'
		,	'tiles'
		,	'title'
		,	'tpl'
		,	'type'
		,	'types'
		,	'url'
		,	'user'
		,	'user_id'
		,	'users'
		,	'value'
		,	'valueId'
		,	'valueID'
		,	'week'
		,	'width'
		,	'year'
		,	'zoom'
	);

	// Initialize allowed variables
	foreach ($expected as $formvar)
		$$formvar = (isset(${"_$_SERVER[REQUEST_METHOD]"}[$formvar])) ? ${"_$_SERVER[REQUEST_METHOD]"}[$formvar]:NULL;

	# Getting session in variable and closing the session for writing
	$_tSESSION = $_SESSION;
	session_write_close();

	/**
	 * Function print_c (Print Callback)
	 * This is a wrapper function for print that will place the callback around the output statement
	 */
	function print_c($str) {
		global $callback;
		header('Content-type: application/json');
		if ( isset( $callback ) && $callback != '' ) {
			$cb = $callback . '(' . $str . ')';
		} else {
			$cb = $str;
		}
		print $cb;
	}

	/*
	* Function myErrorHandler
	* Used to catch any errors and send back a custom json error message.
	*/
	function myErrorHandler($errno, $errstr, $errfile, $errline) {
		global $allowed_ips, $config;
		switch ($errno) {

			case E_USER_ERROR:
				$msg = "ERROR [$errno] $errstr";
				$msg .= "  Fatal error on line $errline in file $errfile";
				$msg .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")";
				$msg .= "Aborting...";
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $msg , 'code' => $errno ) ) ) );
				if($config['report_software_errors']) {
					$get = urlencode(json_encode( array( 'datetime' => date('d-M-Y'), 'license' => $config['license'], 'version' => $config['version'], 'errorno' => $errno, 'errorstr' => $errstr, 'errline' => $errline, 'errfile' => $errfile ) ));
					@file_get_contents( $config['error_report_path'] .  '?log=' . $get );
				}
				exit(1);
				break;
/*			
			case E_USER_WARNING:
				echo "<b>WARNING</b> [$errno] $errstr<br />\n";
				break;
			
			case E_USER_NOTICE:
				echo "<b>NOTICE</b> [$errno] $errstr<br />\n";
				break;
			
			default:
				echo "Unknown error type: [$errno] $errstr<br />\n";
				break;
*/
		}
		
		/* Don't execute PHP internal error handler */
		return true;
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
	require_once("classes/access_user/access_user_class.php");
	
// TODO - If in the config the Flickr is not set to true default this to not reuiqre this lib since we do not need the extra code for the cmd.
	require_once("classes/phpFlickr/phpFlickr.php");
	
	$si = new SilverImage($config['mysql']['name']);
	$user_access = new Access_user($config['mysql']['host'], $config['mysql']['user'], $config['mysql']['pass'], $config['mysql']['name']);

	// This is the output type that the program needs to return, defaults to json
	if(!isset($api)) {
		$api = "json";
	}

	// This will control the incoming processes that need to be preformed.
	$valid = true;
	$code = 0;
	$time_start = microtime(true);
	$user_access->db = &$si->db;

	// Type of command to perform
	switch( $cmd ) {

		// Adds logs from SilverImage into BIS
		case 'load_logs':
			switch($config['mode']) {
				case 's3':
					$data['mode'] = $config['mode'];
					$data['s3'] = $config['s3'];
					$data['obj'] = $si->amazon;
					$data['time_start'] = microtime(true);
					$si->logger->setData($data);
					$ret = $si->logger->loadS3Logs();
					break;
				default:
					$data['path_files'] = $config['path']['files'];
					$data['processed_files'] = $config['path']['processed_files'];
					$data['time_start'] = microtime(true);
					$si->logger->setData($data);
					$ret = $si->logger->loadLogs();
					break;
			}
			header('Content-type: application/json');
			if($ret['success']) {
				print_c( json_encode ( array( 'success' => true, 'process_time' => $ret['time'], 'total_files_loaded' =>  $ret['total']) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => 102, 'message' => $si->getError(102)) ) ) );
			}
			break;
	
		// Gets Logs from Logs based on sc_id????
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
					print_c( json_encode( array('success' => true, 'data' => $id) ) );
				}
			} else {
				print_c( json_encode( array('success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		// Returns list results of images stored in BIS
		case 'browse':
			$data['time_start'] = microtime(true);
			$data['filter'] = stripslashes(trim($filter));
			$data['nodeApi'] = trim($nodeApi);
			$data['nodeValue'] = trim($nodeValue);
			
			header('Content-type: application/json');
			if(!in_array($data['nodeApi'], array('alpha', 'Family', 'Genus', 'SpecificEpithet', 'root'))) {
				$code = 114;
				$valid = false;
				print_c( json_encode( array( 'success' => false, 'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			} else {
				$si->image->setData($data);
				$records = $si->image->loadBrowse();
				print_c( json_encode( $records ) );
			}
			break;

		// Service - Should not normally be run as a cron but can be run using the api.
		case 'check-new-images':
			$si->images->clear_files();

			$rr = $si->images->load_from_folder($config['path']['incoming']);
			$images = $si->images->get_files();

			$count = 0;
			if(count($images) && is_array($images)) {
				foreach($images as $image) {
					$image->db = &$si->db;
					$successFlag = $image->moveToImages();
					if($successFlag['success']) {
						$barcode = $image->getName();
						$filename = $image->get('filename');

						$parts = array();
						$parts = preg_split("/[0-9]+/", $barcode);
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
					} else {
						header('Content-type: application/json');
						print_c( json_encode( array('success' => false, 'error' => array('code' => $successFlag['code'], 'message' => $si->getError($successFlag['code'])) ) ) );
						exit();
					}
				}
			}

			header('Content-type: application/json');
			print_c( json_encode( array( 'success' => true, 'process_time' =>  microtime(true) - $time_start, 'total_images' => $count ) ) );
			break;

		// Get storage info from the images path
		case 'storage_info':
			$force = (trim($force) == '1') ? 1 : 0;
			$output = array();
			if($force) {
				$si->image->mkdir_recursive(@dirname($config['storageCache']));
				$data = array();
				$data['used'] = array('text'=>'Size Used','value'=> getdirsize($config['path']['images']));
				$data['free'] = array('text'=>'Free Disk Space','value'=> decodeSize(disk_free_space($config['path']['images'])));
				$data['total'] = array('text'=>'Total Disk Space','value'=> decodeSize(disk_total_space($config['path']['images'])));
				file_put_contents($config['storageCache'],json_encode($data));
				$output = array('success' => true, 'processTime' => microtime(true) - $time_start, 'data' => $data);
			} else {
				if(file_exists($config['storageCache'])) {
					$data = json_decode(stripslashes(file_get_contents($config['storageCache'])));
					$output = array('success' => true, 'processTime' => microtime(true) - $time_start, 'data' => $data);
				} else {
					$output = array('success' => true, 'read' => -1, 'write' => -1);
				}
			}

			header('Content-type: application/json');
			print_c(json_encode($output));
			break;

		case 'process_queue':
			$data['stop'] = $stop;
			$data['time_start'] = microtime(true);
			$data['limit'] = $limit;
			$data['mode'] = $config['mode'];
			$data['s3'] = $config['s3'];
			$data['obj'] = $si->amazon;
			$data['imageIds'] = @json_decode($image_id, true);
			$si->pqueue->setData($data);
			$result = $si->pqueue->process_queue();
			if($result['success']) {
				header('Content-type: application/json');
				print_c( json_encode( array( 'success' => true, 'process_time' => $result['time'], 'total_records' => $result['total'] ) ) );
			}
			break;

		// Get Image returns an image stored on this server
		case 'get_image':
			$data['image_id'] = trim($image_id);
			$data['barcode'] = trim($barcode);
			if($data['image_id'] == '' && $data['barcode'] == '') {
				$valid = false;
				$code = 134;
			}
			$data['width'] = trim($width);
			$data['height'] = trim($height);
			$data['size'] = trim($size);
			$data['type'] = trim($type);
			$data['mode'] = $config['mode'];
			$data['s3'] = $config['s3'];
			$data['obj'] = $si->amazon;

			// Type null defaults to 'jpg'
			$config['allowed_image_format'] = array('jpg', 'jpeg', 'png', 'gif', 'tiff');
			if(($type != '') && !in_array(strtolower($type), $config['allowed_image_format'])) {
				$valid = false;
				$code = 142;
			}

			if($valid) {
				$si->image->setData($data);
				$ar = $si->image->getImage();
				header('Content-type: application/json');
				if($ar['success'] == false) {
					print_c( json_encode( array('success' => false,  'error' => array('code' => $ar['code'], 'message' => $si->getError($ar['code'])) ) ) );
				} else {
					print_c( json_encode( array('success' => true) ) );
				}
			} else {
				header('Content-type: application/json');
				print_c( json_encode( array('success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		# example : cmd=loadTile&filename=USMS000018155&zoom=2&index=tile_16.jpg
		case 'loadTile';
			$filename = @strtolower($filename);
			$index = @str_replace('tile_','',@basename($index,'.jpg'));
			$it = new imgTiles($config['path']['imgTiles'] . $filename . '.sqlite');
			$result = $it->getTileData($zoom, $index);
			
			$type = 'image/jpeg';
			header('Content-Type:' . $type);
			print $result;
			break;

		case 'get_image_tiles':
			$image_id = trim($image_id);
			$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';

			if($image_id == "") {
				$valid = false;
				$code = 107;
			}

			if($valid) {
				$si->image->load_by_id($image_id);
				$barcode = $si->image->getName();
				$filename = $si->image->get('filename');

			/*	$url = $config['tileGenerator'] . '?cmd=loadImage&filename=' . $filename;
				switch($config['mode']) {
					case 's3':
						$tmpPath = $_TMP . $filename;
						$fp = fopen($tmpPath, "w+b");
						# getting the image from s3
						$bucket = $config['s3']['bucket'];
						$key = $si->image->barcode_path($barcode) . $filename;
						$si->amazon->get_object($bucket, $key, array('fileDownload' => $tmpPath));
						$url .= '&absolutePath=' . $_TMP;
						break;
					default:
						break;
				}*/
				//New code starts
				$tmpPath = $si->storage->fileDownload($si->image->get('storage_id'), ($si->image->get('path').'/'.$si->image->get('filename')));
				$t1 = explode("/", $tmpPath);
				$t2 = $t1[count($t1)-1];
				unset($t1[count($t1)-1]);
				$t1 = implode("/", $t1);
				$url = $config['tileGenerator'] . '?cmd=loadImage&filename=' . $t2 . '&absolutePath=' . $t1.'/';
				$t3 = explode(".", $t2);
				//New code ends
				//Replaced $barcode with $t3[0] at two places in the code below.
				$res = json_decode(trim(@file_get_contents($url)));
				if(strtolower($si->storage->getType($si->image->get('storage_id'))) == 's3') {
					@unlink($tmpPath);
				}

				
				if(in_array(@strtolower($tiles),array('create','createclear'))) {
					$si->image->mkdir_recursive( $config['path']['imgTiles'] );
					$tileFolder = @strtolower($t3[0]);
					$it = new imgTiles($config['path']['imgTiles'] . $tileFolder . '.sqlite');

					$handle = @opendir($config['path']['tiles'] . $tileFolder);
					while (false !== ($zoom = @readdir($handle))) {
						if( $zoom == '.' || $zoom == '..') continue;
						$handle1 = @opendir($config['path']['tiles'] . $tileFolder . '/' . $zoom);
						while (false !== ($tile = readdir($handle1))) {
							if( $tile == '.' || $tile == '..') continue;
							$it->recordTile($zoom, $config['path']['tiles'] . $tileFolder . '/' . $zoom . '/' . $tile);
						}
					}
					if(@strtolower($tiles) == 'createclear') {
						$si->image->rmdir_recursive($config['path']['tiles'] . $tileFolder);
					}
				}
				header('Content-type: application/json');
				
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'url' => $config['tileUrl'] . strtolower($t3[0])) ) );
			} else {
				header('Content-type: application/json');
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
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
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'totalCount' => $total, 'data' => $data ) ) );
			}else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		case 'getOCR':
			$objFlag = false;
			if(trim($image_id) != '') {
				$objFlag = $si->image->load_by_id($image_id);
			} else if(trim($barcode) != '') {
				$objFlag = $si->image->load_by_barcode($barcode);
			}
			$ocrData = ($objFlag) ? $si->image->get('ocr_value') : '';
			header('content-type: text/plain');
			print $ocrData;
			break;

		case 'images':
			$time = microtime(true);
			$data['start'] = ($start != '') ? $start : 0;
			$data['limit'] = ($limit != '') ? $limit : 100;
			$data['order'] = json_decode(stripslashes(trim($order)),true);

			$data['showOCR'] = (@in_array(trim($showOCR),array('1','true','TRUE'))) ? true : false;

			if(is_array($filter)) {
				$data['filter'] = $filter;
			} else {
				$data['filter'] = json_decode(stripslashes(trim($filter)),true);
			}
			$data['image_id'] = trim($image_id);
			$data['field'] = trim($field);
			$data['value'] = trim($value);
			if(trim($sort) != '') {
				//$data['order'] = array(array('field' => trim($sort), 'dir' => trim($dir)));
				$data['sort'] = trim($sort);
				$data['dir'] = trim($dir);
			}
			$data['code'] = ($code1 != '') ? $code1 : '';

			$data['characters'] = $characters;
			$data['browse'] = $browse;
			$data['search_value'] = $search_value;
			$data['search_type'] = $search_type;


			if($valid) {
				$si->image->setData($data);
				$data = $si->image->listImages();
				$total = 0;
				if(is_array($data) && count($data)) {
					foreach($data as &$dt) {
						/*switch($config['mode']) {
							case 's3':
								$dt->path = $config['s3']['url'] . $si->image->barcode_path($dt->barcode);
								break;
							default:
								$dt->path = str_replace($config['doc_root'],rtrim($config['base_url'],'/') . '/', $config['path']['images'] . $si->image->barcode_path($dt->barcode));
								break;
						}*/
						$total++;
						$tmpPath = $si->image->getUrl($dt->image_id);
						$dt->path = $tmpPath['baseUrl'];
						$fname = explode(".", $dt->filename);
						$dt->ext = $fname[1];

					}
				}

				//***
				if($output=='rss'){
					include("feedwriter.php");

					$RSSFeed = new FeedWriter(RSS2);
// TODO SHould not be coded her but in the config.					
					$RSSFeed->setTitle('Toronto Image Server');
// TODO THIS should be the config[weburl] or something like that
					$RSSFeed->setLink('http://{WRONG!!!!!}/trt/');
						
					foreach($data as $key => $value){
						
						$key1 = get_object_vars($value);						
						$imgMed = $key1['path'] . $key1['barcode'] . '_m.jpg';
						$imgLarg = $key1['path'] . $key1['barcode'] . '_l.jpg';
					
						$title = $key1['barcode'];  
						$newItem = $RSSFeed->createNewItem();
						 
						//Add elements to the feed item    
						$newItem->setTitle($title);
						$newItem->setLink($img1);
						$newItem->setDescription("<a href='" . $imgLarg . "'><img style='border:1px solid #5C7FB9'src='" . $imgMed . "'/></a>");
						$newItem->setEncloser($imgLarg, '7', 'image/jpeg');
						//set the feed item
						$RSSFeed->addItem($newItem);
					}

					$RSSFeed->genarateFeed();
				} else {
					header('Content-type: application/json');
					//$total = $si->image->db->query_total();
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time, 'totalCount' => $total, 'data' => $data ) ));
				}
			} else {				
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ));
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
				print_c( json_encode( array( 'success' => true, 'totalCount' => $total, 'records' => $data ) ));
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ));
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
				print_c( json_encode( array( 'success' => true, 'totalCount' => $total, 'cacheFile' => $pathUrl, 'records' => $data ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
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
				print_c( json_encode( array( 'success' => true, 'data' => $data ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
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
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time, 'data' => $data ) ));
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ));
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
				print_c( json_encode( $records ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		case 'getVersion':
			header('Content-type: application/json');
			print_c( json_encode( array('success' => true, 'name' => 'Biodiversity Image Server', 'version' => $config['version'] ) ) );
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
					
							$si->pqueue->set('image_id',$si->image->get('image_id'));
							$si->pqueue->set('process_type','all');
							$si->pqueue->save();

							$ar[] = $imageId;
						}
					}
				}
				print_c( json_encode( array('success' => true, 'records' => $ar ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
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
					print_c( json_encode( array( 'success' => true,  'message' => $si->getError(110) ) ) );
				} else {
					print_c( json_encode( array( 'success' => false ) ));
				}
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
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
					print_c( json_encode( array( 'success' => true ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array('code' => $ret['code'], 'message' => $si->getError($ret['code']))) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
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
			print_c( json_encode( array( 'success' => true, 'recordsUpdated' => $records) ) );

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
				print_c( json_encode( array( 'success' => true,  'results' => $results ) ) );
				
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
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
					print_c( json_encode(array('success' => true, 'recordCount' => $ret['recordCount'] ) ) );
				} else {
					print_c( json_encode( array( 'success' => false ) ) );
				}

			} else {
				print_c( json_encode( array( 'success' => false, 'error' => array( 'message' => 's3 Error' ) ) ));
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
				print_c( json_encode( array( 'success' => true, 'totalCount' => $total, 'data' => $data ) ) );
			}else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
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

					switch($config['mode']) {
						case 's3':
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
						break;

						default:
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
						break;

					} # else local

				} # foreach file
			} # if count file
			print_c ( json_encode( array( 'success' => true, 'recordCount' => count($statsArray), 'stats' => $statsArray ) ) );
			break;

		case 'searchEnLabels':
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
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'totalCount' => $totalNotes, 'data' => $data ) ));
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ));
			}

			break;

		# New Image Admin Tasks
		case 'image_characters':
			if(!$user_access->is_logged_in()){
				print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
				exit;
			}
			$start = ($start != '') ? $start : 0;
			$limit = ($limit != '') ? $limit : 25;
			$data['attributes'] = implode(',',json_encode($attributes,true));
			$si->image->setData($data);
			$ret = $si->image->loadImageCharacters();
			if($ret['success']) {
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'totalCount' => $ret['recordCount'], 'data' => $ret['data'] ) ));
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ));
			}
			break;

		case 'add_image_attribute':
			if(!$user_access->is_logged_in()){
				print_c (json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
				exit;
			}

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
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 121, 'msg' => $si->error(121) ) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $code, 'msg' => $si->getError($code) ) ) ) );
			}
			break;

		case 'delete_image_attribute':
			if(!$user_access->is_logged_in()){
				print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
				exit;
			}

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
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 122, 'msg' => $si->getError(122) ) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $code, 'msg' => $si->getError($code) ) ) ) );
			}
			break;

			case 'add_category':
				if(!$user_access->is_logged_in()){
					print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
					exit;
				}

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
						print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 124, 'msg' => $si->getError(124) ) ) ) );
					}
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $code, 'msg' => $si->getError($code) ) ) ) );
				}
				break;

			case 'rename_category':
				if(!$user_access->is_logged_in()){
					print_c (json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
					exit;
				}

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
						print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 125, 'msg' => $si->getError(125) ) ) ) );
					}
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $code, 'msg' => $si->getError($code) ) ) ) );
				}
				break;

			case 'delete_category':
				if(!$user_access->is_logged_in()){
					print_c (json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
					exit;
				}

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
				if(!$user_access->is_logged_in()){
					print_c (json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
					exit;
				}

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
				if(!$user_access->is_logged_in()){
					print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
					exit;
				}

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
				if(!$user_access->is_logged_in()){
					print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
					exit;
				}

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
						print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'recordCount' => count($nodes), 'results' => $nodes)));
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
				$data['nodeApi'] = $nodeApi;
				$data['nodeValue'] = $nodeValue;
				$data['family'] = $family;
				$data['genus'] = $genus;

				if($valid) {
					$si->image->setData($data);
					if(false !== ($nodes = $si->image->loadImageNodesImages())) {
						print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'recordCount' => count($nodes), 'results' => $nodes)));
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
						print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'recordCount' => count($nodes), 'results' => $nodes)));
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
						print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'recordCount' => $si->image->total, 'results' => $nodes)));
					} else {
						print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError(129) , 'code' => 129 ) ) ) );
					}
	
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
			break;

		case 'image-details':
			$data['image_id'] = $image_id;
			if ($data['image_id'] == '') {
				$code = 107;
				$valid = false;
			}
			if($valid) {
				$si->image->setData($data);
				$ar = $si->image->loadImageDetails();

				if($ar['status']) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'results' => $ar['record'] ) ) );
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $sc->error($ar['error']) , 'code' => $ar['error'] ) ) ) );
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
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'results' => $ret ) ) );
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
			break;

			case 'addEvent':
				if(!$user_access->is_logged_in()){
					print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
					exit;
				}

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
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'results' => $ret ) ) );
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
				break;

			case 'deleteEvent':
				if(!$user_access->is_logged_in()) {
					print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
					exit;
				}

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
				
			case 'addImageEvent':
				if(!$user_access->is_logged_in()) {
					print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
					exit;
				}
				if($eventId == '') {
					$valid = false;
					$code = 133;
				}
				if($imageId == '') {
					$valid = false;
					$code = 155;
				}
				if($valid) {
					$si->event->lg->set('action', 'addImageEvent');
					$si->event->lg->set('lastModifiedBy', $_SESSION['user_id']);
					$si->event->addImageEvent($imageId, $eventId);
					print_c( json_encode( array( 'success' => true ) ) );
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
				break;
				
			case 'deleteImageEvent':
				if(!$user_access->is_logged_in()) {
					print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
					exit;
				}
				if($eventId == '') {
					$valid = false;
					$code = 133;
				}
				if($imageId == '') {
					$valid = false;
					$code = 155;
				}
				if($valid) {
					$si->event->lg->set('action', 'deleteImageEvent');
					$si->event->lg->set('lastModifiedBy', $_SESSION['user_id']);
					$si->event->deleteImageEvent($imageId, $eventId);
					print_c( json_encode( array( 'success' => true ) ) );
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
				break;

			case 'addEventType':
				if(!$user_access->is_logged_in()){
					print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
					exit;
				}
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
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'results' => $ret ) ) );
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
				break;

			case 'deleteEventType':
				if(!$user_access->is_logged_in()){
					print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
					exit;
				}
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

		case 'getBoxDetect':
			$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';
			$loadFlag = $existsFlag = false;
				
			if(trim($image_id) != '') {
				$loadFlag = $si->image->load_by_id($image_id);
			} elseif(trim($barcode) != '') {
				$loadFlag = $si->image->load_by_barcode($barcode);
			}

			if(!$loadFlag) {
				$valid = false;
				$code = 135;
			}

			if($valid) {
				$filename = explode('.', $si->image->get('filename'));
				$key = $si->image->get('path') . '/' . $filename[0] . '_box.json';
				if($si->storage->fileExists($si->image->get('storage_id'), $key)) {
					$data = $si->storage->fileGetContents($si->image->get('storage_id'), $key);
					if($data) {
						$existsFlag = true;
					}
				}

				if(!$existsFlag || $force) {
					$image = $si->image->get('path') . '/' . $si->image->get('filename');

					# Getting image
					$image = $si->storage->fileDownload($si->image->get('storage_id'), $image);

					# processing
					putenv("LD_LIBRARY_PATH=/usr/local/lib");
					$data = exec(sprintf("%s %s", $config['boxDetectPath'], $image));

					# saving the json object
					$si->storage->createFile_Data($si->image->get('storage_id'), $key, $data);
				}
				$si->pqueue->deleteProcessQueue($si->image->get('barcode'), 'box_add');
				$si->image->set('box_flag', 1);
				$si->image->save();

				$data = json_decode($data, true);
				$variable = ($data['data']['height'] > $data['data']['width']) ? $data['data']['height'] : $data['data']['width'];
				$data['data']['pixelsPerCentimeter'] = @round($variable/4);
				$data['data']['pixelsPerInch'] = @round($variable/1.57);
				$data['processedTime'] = microtime(true) - $time_start;
				print_c(json_encode($data));
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			}
			break;

		case 'detectBarcode':
			//header('Content-type: application/json');
			if(!$config['zBarImgEnabled']) {
				print json_encode(array('success' => false, 'error' => array('code' => 139, 'message' => $si->getError(139))));
				exit;
			}

			$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';
			$loadFlag = false;

			if(trim($image_id) != '') {
				$loadFlag = $si->image->load_by_id($image_id);
			} elseif(trim($barcode) != '') {
				$loadFlag = $si->image->load_by_barcode($barcode);
			}

			if(!$loadFlag) {
				$valid = false;
				$code = 135;
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			} else {
				# getting image
				$key = $si->image->get('path') . '/' . $si->image->get('filename');
				$cacheFlag = false;
				$explodeFilename = explode(".", $si->image->get('filename'));
				$cachePath = $si->image->get('path') . '/' . $explodeFilename[0] . "-barcodes.json";

				if(strtolower($force) != true) {
					$cacheFlag = $si->storage->fileExists($si->image->get('storage_id'), $cachePath);
				}

				if($cacheFlag) {
					$data = $si->storage->fileGetContents($si->image->get('storage_id'), $cachePath);
					$data = json_decode($data, true);
					$data['processTime'] = microtime(true) - $time_start;
					$data = json_encode($data);
					print_c($data);
				} else {
					// No cache or not using cache
					$image = $si->storage->fileDownload($si->image->get('storage_id'), $key);
					$command = sprintf("%s %s", $config['zBarImgPath'], $image);
					$data = exec($command);
					$tmpArrayArray = explode("\r\n", $data);
					$data = array();
					if(is_array($tmpArrayArray)) {
						foreach($tmpArrayArray as $tmpArray) {
							if($tmpArray != '') {
								$parts = explode(":", $tmpArray);
								$data[] = array('code' => $parts[0], 'value' => $parts[1]);
							}
						}
					}
					if(strtolower($si->storage->getType($si->image->get('storage_id'))) == 's3') {
						@unlink($image);
					}
					$command = sprintf("%s --version ", $config['zBarImgPath']);
					$ver = exec($command);
					$tmpJsonFile = json_encode(array('success' => true, 'processTime' => microtime(true) - $time_start, 'count' => count($data), 'lastTested' => time(), 'software' => 'zbarimg', 'version' => $ver, 'data' => $data));
					$key = $si->image->get('path') . '/' . $explodeFilename[0] . '-barcodes.json';

					$si->storage->createFile_Data($si->image->get('storage_id'), $key, $tmpJsonFile);
					print_c($tmpJsonFile);
				}
			}	
			break;

		case 'generateRemoteAccessKey':
			$whitelist=  array('localhost',  '127.0.0.1');
			if(!in_array($_SERVER['HTTP_HOST'],  $whitelist)){
				//$valid = false;
				$code = 143;
			}
			elseif(!isset($url)) {
				$valid = false;
				$code = 144;
			}
			else {
				$valid = true;
			}

			if($valid) {
				$ip = gethostbyname($url);
				$ip = ip2long($ip);
				if($ip) {
					$key = crypt($ip, $config["secretKey"]);
					$si->remoteAccess->set('ip', $ip);
					$si->remoteAccess->set('key', $key);
					$si->remoteAccess->save();
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'key' => $key ) ) );
				} else {
					$code = 144;
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			}
			break;
			
		case 'listRemoteAccessKeys':
			$whitelist=  array('localhost',  '127.0.0.1');
			if(!in_array($_SERVER['HTTP_HOST'],  $whitelist)){
				//$valid = false;
				$code = 143;
			}
			else {
				$valid = true;
			}

			if($valid) {
				$list = $si->remoteAccess->list_all();
				$listArray = array();
				while($record = $list->fetch_object())
				{
					$item['ip'] = $record->ip;
					$item['key'] = $record->key;
					$item['active'] = $record->active;
					$listArray[] = $item;
				}
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'keys' => $listArray ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			}
			break;
			
		case 'addImage':
			$imagePath = (isset($imagePath))?$imagePath:'';
			if($si->remoteAccess->checkRemoteAccess(ip2long($_SERVER['REMOTE_ADDR']), $key)) {
				if ($_FILES["filename"]["error"] > 0) {
					print_c( json_encode( array( 'success' => false,  'error' => $_FILES["filename"]["error"] ) ) );
				} else {
					$config["allowedImportTypes"] = array(1,2,3); //GIF, JPEG, PNG
					//http://www.php.net/manual/en/function.exif-imagetype.php
					$size = getimagesize($_FILES["filename"]["tmp_name"]);
					if(in_array($size[2],$config["allowedImportTypes"])) {
						if($storage_id!='' && $si->storage->exists($storage_id)) {
							$response = $si->storage->store($_FILES["filename"]["tmp_name"],$storage_id,$_FILES["filename"]["name"], $imagePath);
							if($response['success']) {
								$si->pqueue->set('image_id', $response['image_id']);
								$si->pqueue->set('process_type','all');
								$si->pqueue->save();
								print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'image_id' => $response['image_id'] ) ) );
							} else {
								$code = 151;
								print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
							}
						} else {
							$code = 150;
							print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
						}
					} else {
						$code = 146;
						print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
					}
				}
			} else {
				$code = 145;
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			}
			break;
		
		case 'getImageInfo':
			$loadFlag = false;
			if(trim($image_id) != '') {
				$loadFlag = $si->image->load_by_id($image_id);
			} elseif(trim($barcode) != '') {
				$loadFlag = $si->image->load_by_barcode($barcode);
			}
			if(!$loadFlag) {
				$valid = false;
				$code = 135;
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			} else {
				$data = $si->image->get_all();
				$url = $si->image->getUrl($si->image->get('image_id'));
				$data['url'] = $url['url'];
				$attbr = $si->image->get_all_attributes($image_id);
				if($attbr) $data['attributes'] = $attbr;
				$events = $si->event->get_all_events($image_id);
				if($events) $data['events'] = $events;
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'data' => $data ) ) );
			}
			break;
			
		case 'getImageUrl':
			$loadFlag = false;
			if(trim($image_id) != '') {
				$loadFlag = $si->image->load_by_id($image_id);
			} elseif(trim($barcode) != '') {
				$loadFlag = $si->image->load_by_barcode($barcode);
			}
			if(!$loadFlag) {
				$valid = false;
				$code = 135;
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			} else {
				if(isset($size) && in_array($size, array('s','m','l'))) {
					$size = "_".$size;
				} else {
					$size = "";
				}
				$image_id = $si->image->get('image_id');
				$tmpFilename = explode(".",$si->image->get('filename'));
				$tmpFilename[0] .= $size;
				$filename = implode(".", $tmpFilename);
				if($si->image->image_exists($si->image->get('storage_id'), $si->image->get('path'), $filename)) {
					$url = $si->image->getUrl($image_id);
					header('Content-type: text/plain');
					print($url['baseUrl'] . $filename);
				} else {
					$code = 147;
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
			}
			break;

		case 'addStorageDevice':
			$data['name'] = trim($name);
			$data['description'] = trim($description);
			$data['type'] = trim($type);
			$data['baseUrl'] = trim($baseUrl);
			$data['basePath'] = trim($basePath);
			$data['user'] = trim($user);
			$data['pw'] = trim($pw);
			$data['key'] = trim($key);
			$data['active'] = trim($active)!=''?trim($active):'true';
			if($name=='' || $type=='' || $baseUrl=='') {
				$code = 148;
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			} else {
				$si->storage->set_all($data);
				$si->storage->save();
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start ) ) );
			}
			break;
		
		case 'listStorageDevices':
			if(is_array($si->storage->devices)) {
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'data' => $si->storage->devices ) ) );
			} else {
				$code = 149;
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			}
			break;
		
		case 'addExistingImage':
			if($storage_id == '' || $imagePath == '' || $filename == '') {
				$valid = false;
				$code = 152;
			} elseif(!$si->storage->exists($storage_id)) {
				$valid = false;
				$code = 150;
			} elseif(!$si->image->image_exists($storage_id, $imagePath, $filename)) {
				$valid = false;
				$code = 147;
			} else {
				$valid = true;
			}
			if($valid) {
				$image_id = $si->image->getImageId($filename, $imagePath, $storage_id);
				if(!$image_id) {
					$si->image->set('filename',$filename);
					$si->image->set('storage_id', $storage_id);
					$si->image->set('path', $imagePath);
					$si->image->set('originalFilename', $filename);
					$si->image->save();
					$image_id = $si->image->getImageId($filename, $imagePath, $storage_id);
					$si->pqueue->set('image_id', $image_id);
					$si->pqueue->set('process_type','all');
					$si->pqueue->save();
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'image_id' => $image_id ) ) );
				} else {
					$code = 162;
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
				
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			}
			break;
			
		case 'moveExistingImage':
			if($image_id == '' || $newStorageId == '' || $newImagePath == '') {
				$code = 153;
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			} elseif(!$si->image->field_exists($image_id)) {
				$code = 116;
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			} elseif(!$si->storage->exists($newStorageId)) {
				$code = 150;
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			} else {
				if($si->storage->moveExistingImage($image_id, $newStorageId, $newImagePath)) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start ) ) );
				} else {
					$code = 154;
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
			}
			break;
			
		case 'addSet':
			if(!$user_access->is_logged_in()) {
				print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
				exit;
			}
			if($name == '') {
				$code = 156;
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			} elseif($si->set->exists($name)) {
				$code = 163;
				$si->set->load_by_set_name($name);
				$data['id'] = $si->set->get('id');
				$data['name'] = $si->set->get('name');
				$data['description'] = $si->set->get('description');
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ), 'details' => $data ) ) );
			} else {
				$description = isset($description)?$description:'';
				$si->set->addSet($name, $description);
				$si->set->load_by_set_name($name);
				print_c(json_encode(array('success' => true, setID => $si->set->get('id'))));
			}
			break;
			
		case 'editSet':
			if(!$user_access->is_logged_in()) {
				print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
				exit;
			}
			if($name == '') {
				$valid = false;
				$code = 156;
			} elseif($sId == '') {
				$valid = false;
				$code = 157;
			} elseif(!$si->set->load_by_id($sId)) {
				$valid = false;
				$code = 159;
			}
			
			if($valid) {
				$description = isset($description)?$description:'';
				$si->set->editSet($sId, $name, $description);
				print_c( json_encode( array( 'success' => true ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			}
			break;
			
		case 'deleteSet':
			if(!$user_access->is_logged_in()) {
				print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
				exit;
			}
			if($sId == '') {
				$valid = false;
				$code = 157;
			} elseif(!$si->set->load_by_id($sId)) {
				$valid = false;
				$code = 159;
			}
			if($valid) {
				$si->set->deleteSet($sId);
				print_c( json_encode( array( 'success' => true ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			}
			break;
			
		case 'listSets':
			$data = $si->set->listSet();
			if($data) {
				print_c( json_encode( array( 'success' => true, 'total_count' => $data['count'], 'data' => $data['data'] ) ) );
			} else {
				$code = 170;
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			}
			break;
			
		case 'addSetValue':
			if(!$user_access->is_logged_in()) {
				print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
				exit;
			}
			if($sId == '' || $valueId == '') {
				$valid = false;
				$code = 158;
			} elseif(!$si->set->load_by_id($sId)) {
				$valid = false;
				$code = 159;
			} elseif(!$si->image->exists_attrb_value_by_id($valueId)) {
				$valid = false;
				$code = 164;
			}
			if($valid) {
				$rank = isset($rank)?$rank:0;
				$si->set->addSetValue($sId, $valueId, $rank);
				print_c( json_encode( array( 'success' => true ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			}
			break;
			
		case 'editSetValue':
			if(!$user_access->is_logged_in()) {
				print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
				exit;
			}
			if($sId == '' || $valueId == '' || $id == '') {
				$valid = false;
				$code = 160;
			} elseif(!$si->set->load_by_id($sId)) {
				$valid = false;
				$code = 159;
			} elseif(!$si->image->exists_attrb_value_by_id($valueId)) {
				$valid = false;
				$code = 164;
			} elseif(!$si->set->exists_set_values_by_id($id)) {
				$valid = false;
				$code = 165;
			}
			if($valid) {
				$rank = isset($rank)?$rank:'';
				$si->set->editSetValue($id, $sId, $valueId, $rank);
				print_c( json_encode( array( 'success' => true ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			}
			break;
			
		case 'deleteSetValue':
			if(!$user_access->is_logged_in()) {
				print_c ( json_encode( array( 'success' => false, 'error' => array('message' => $si->getError(113), 'code' => 113 )) ));
				exit;
			}
			if($id == '') {
				$valid = false;
				$code = 166;
			} elseif(!$si->set->exists_set_values_by_id($id)) {
				$valid = false;
				$code = 165;
			}
			if($valid) {
				$si->set->deleteSetValue($id);
				print_c( json_encode( array( 'success' => true ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			}
			break;
			
		case 'listImageBySet':
			if(isset($sId) && $sId!='') {
				if(!$si->set->load_by_id($sId)) {
					$valid = false;
					$code = 159;
				}
			} else {
				$sId = '';
			}
			if($valid) {
				$data = $si->set->listImageBySet($sId);
				print_c( json_encode( array( 'success' => true, 'data' => $data['data'] ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			}
			break;
			
		case 'addImageFromURL':
			if($url == '' || $storage_id == '' || $key == '') {
				$valid = false;
				$code = 167;
			} elseif(!$si->remoteAccess->checkRemoteAccess(ip2long($_SERVER['REMOTE_ADDR']), $key)) {
				$valid = false;
				$code = 145;
			} elseif(!$si->storage->exists($storage_id)) {
				$valid = false;
				$code = 150;
			} elseif(!($section = file_get_contents($url, NULL, NULL, 0, 8))) {
				$valid = false;
				$code = 144;
			} else {
				for($i=0;$i<strlen($section);$i++) {
					$hexString .= dechex(ord($section[$i]));
				}
				if($hexString == '89504e47da1aa') {
					$fileType = 'png';
				} elseif(substr($hexString, 0, 12) == '474946383961' || substr($hexString, 0, 12) == '474946383761') {
					$fileType = 'gif';
				} elseif(substr($hexString, 0, 4) == 'ffd8') {
					$fileType = 'jpg';
				} else {
					$valid = false;
					$code = 146;
				}
			}
			if($valid) {
				$imagePath = (isset($imagePath))?$imagePath:'';
				$temp = explode('/', $url);
				$filename = $temp[count($temp)-1];
				$data = file_get_contents($url);
				file_put_contents($filename, $data);
				$response = $si->storage->store($filename,$storage_id,$filename, $imagePath);
				unlink($filename);
				if($response['success']) {
					$si->pqueue->set('image_id', $response['image_id']);
					$si->pqueue->set('process_type','all');
					$si->pqueue->save();
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'image_id' => $response['image_id'] ) ) );
				} else {
					$code = 151;
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			}
			break;
			
		case 'importMetaDataPackage':
			if($url == '' || $key == '') {
				$valid = false;
				$code = 168;
			} elseif(!$si->remoteAccess->checkRemoteAccess(ip2long($_SERVER['REMOTE_ADDR']), $key)) {
				$valid = false;
				$code = 145;
			} elseif(!($fp = fopen($url, "r"))) {
				$valid = false;
				$code = 144;
			}
			if($valid) {
				$count = 0;
				while($data = fgetcsv($fp, NULL, ",")) {
					if($si->image->importMetaDataPackage($data)) {
						$count++;
					}
				}
				if($count == 0) {
					$code = 169;
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
				} else {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $time_start, 'newEntries' => $count ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($code) , 'code' => $code ) ) ) );
			}
			break;
		


# Test Tasks

		case 's3Test':
			$barcode = 'USMS000018156';
			$tmpPath = sys_get_temp_dir() . '/tmpFile.jpg';

			$fp = fopen($tmpPath, "w+b");
			# getting the image from s3
			$bucket = $config['s3']['bucket'];
			$key = $si->image->barcode_path($barcode) . 'USMS000018156_k.jpg';
			$ret = $si->amazon->if_object_exists($bucket,$key);

			echo '<pre>';
			echo '<br>';
			var_dump($ret);

/*
			$si->amazon->get_object($bucket, $key, array('fileDownload' => $tmpPath));
			fclose($fp);

			$fp = fopen($tmpPath, 'rb');
			header("Content-Type: image/jpeg");
			header("Content-Length: " . filesize($tmpPath));
			fpassthru($fp);
			fclose($fp);
			unlink($tmpPath);
			exit;
*/
			break;

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
			$allowedTypes = array('flickr_add', 'picassa_add', 'zoomify', 'google_tile', 'ocr_add', 'name_add', 'all', 'guess_add');
			$ret = $si->pqueue->clearQueue();
			print_c(json_encode(array('success' => true, 'recordCount' => $ret['recordCount'])));
			break;

		default:
			$code = 100;
			header('Content-type: application/json');
			print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			break;
	}

ob_end_flush();
?>