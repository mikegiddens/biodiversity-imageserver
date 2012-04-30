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
		,	'api'
		,	'attributes'
		,	'autoProcess'
		,	'barcode'
		,	'browse'
		,	'callback'
		,	'categoryID'
		,	'characters'
		,	'code'
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
		,	'image_id'
		,	'imagesType'
		,	'index'
		,	'limit'
		,	'month'
		,	'nodeApi'
		,	'nodeValue'
		,	'order'
		,	'output'
		,	'photo_summary'
		,	'photo_tags'
		,	'photo_title'
		,	'picassa_PlantID'
		,	'report_type'
		,	'sc'
		,	'sc_id'
		,	'search_type'
		,	'search_value'
		,	'showOCR'
		,	'size'
		,	'sort'
		,	'stage'
		,	'start'
		,	'station'
		,	'station_id'
		,	'stop'
		,	'tag'
		,	'tiles'
		,	'title'
		,	'tpl'
		,	'type'
		,	'types'
		,	'user_id'
		,	'users'
		,	'value'
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

		// REPORTS		
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
				print_c( json_encode( array( 'success' => true,  'data' => $records ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
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
				print_c( json_encode( array( 'success' => true,  'data' => $records ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
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
				print_c( json_encode( array( 'success' => true,  'data' => $records ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
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
				print_c( json_encode( array( 'success' => true,  'data' => $records ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
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
				print_c( json_encode( array( 'success' => true,  'data' => $records ) ) );
 			}else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
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
				print_c( json_encode( array( 'success' => true,  'data' => $records ) ) );
 			}else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			}
			break;

		case 'image-storage-report':

			$data = array();
			$stats = $si->logger->getImageStorageStats();
			$data[] = array( 'text' => '# of images:', 'value' => $stats['total'] );
			$data[] = array( 'text' => '# of images allowed:', 'value' => $stats['allowed_images'] );

			header('Content-type: application/json');
			print_c( json_encode( array( 'success' => true,  'data' => $data ) ) );
			break;
		// END REPORTS

		default:
			$code = 100;
			header('Content-type: application/json');
			print_c( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			break;
	}

ob_end_flush();	

?>