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
			'api'
		,	'attributeId'
		,	'callback'
		,	'categoryId'
		,	'code'
		,	'collectionId'
		,	'cmd'
		,	'description'
		,	'dir'
		,	'eventId'
		,	'eventTypeId'
		,	'geoFlag'
		,	'geoId'
		,	'group'
		,	'key'
		,	'limit'
		,	'name'
		,	'searchFormat'
		,	'showNames'
		,	'start'
		,	'url'
		,	'title'
		,	'value'

	);

	# Initialize allowed variables
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
	
	function checkAuth() {
		global $si,$userAccess;
		switch($si->authMode) {
			case 'key':
				if(!$si->remoteAccess->checkRemoteAccess(ip2long($_SERVER['REMOTE_ADDR']), $key)) {
					print_c (json_encode( array( 'success' => false, 'error' => array('msg' => $si->getError(103), 'code' => 103 )) ));
					exit();
				}
				break;
		
			case 'session':
			default:
				if(!$userAccess->is_logged_in()) {
					print_c (json_encode( array( 'success' => false, 'error' => array('msg' => $si->getError(104), 'code' => 104 )) ));
					exit();
				}
				break;
		}
	}
	
	/**
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

	require_once("classes/bis.php");
	require_once("classes/access_user/access_user_class.php");
	
	if($config['flkr']['enabled']) {
		require_once("classes/phpFlickr/phpFlickr.php");
	}
	
	$si = new SilverImage($config['mysql']['name']);
	$userAccess = new Access_user($config['mysql']['host'], $config['mysql']['user'], $config['mysql']['pass'], $config['mysql']['name']);

	# This is the output type that the program needs to return, defaults to json
	if(!isset($api)) {
		$api = "json";
	}

	# This will control the incoming processes that need to be preformed.
	$valid = true;
	$errorCode = 0;
	$timeStart = microtime(true);
	$userAccess->db = &$si->db;
	$si->setAuthMode($authMode);

	# Type of command to perform
	switch( $cmd ) {
		case 'attributeAdd':
			checkAuth();
			$data['categoryId'] = $categoryId;
			if($data['categoryId'] == '') {
				$valid = false;
				$errorCode = 101;
			}
			$data['value'] = $value;
			if($data['value'] == '') {
				$valid = false;
				$errorCode = 102;
			}
			if($valid) {
				$si->image->imageSetData($data);
				$id = $si->image->imageAddAttribute();
				if($id) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'attributeId' => $id ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 105, 'msg' => $si->getError(105) ) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $errorCode, 'msg' => $si->getError($errorCode) ) ) ) );
			}
			break;
			
		case 'attributeDelete':
			checkAuth();
			$data['attributeId'] = $attributeId;
			if($data['attributeId'] == "") {
				$valid = false;
				$errorCode = 109;
			}
			if($valid) {
				$si->image->imageSetData($data);
				if($si->image->imageDeleteAttribute()) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $errorCode, 'msg' => $si->getError($errorCode) ) ) ) );
			}
			break;

		case 'attributeList':
				$showNames = (trim($showNames) == 'false') ? false : true;
				$data['start'] = (is_numeric($start)) ? $start : 0;
				$data['limit'] = (is_numeric($limit)) ? $limit : 10;
				// $data['code'] = $code;
				$data['searchFormat'] = in_array(strtolower(trim($searchFormat)),array('exact','left','right','both')) ? strtolower(trim($searchFormat)) : 'both';
				$data['value'] = str_replace('%','%%',trim($value));
				
				$data['categoryId'] = (!is_numeric($categoryId)) ? json_decode(stripslashes(trim($categoryId)),true) : $categoryId;

				if($valid) {
						$si->image->imageSetData($data);
						$ret = $si->image->imageListAttributes();
						$names = array();
						if(!is_null($ret)) {
							while($row = $ret->fetch_object()) {
								$names[] = $showNames ? $row->name : $row;
							}
						}
						print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'totalCount' => $si->image->total, 'records' => $names ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $errorCode, 'msg' => $si->getError($errorCode) ) ) ) );
				}
			break;

		case 'attributeUpdate':
				checkAuth();
				$data['value'] = $value;
				if($data['value'] == "") {
					$valid = false;
					$errorCode = 102;
				}
				$data['attributeId'] = $attributeId;
				if($data['attributeId'] == "") {
					$valid = false;
					$errorCode = 109;
				}
				if($valid) {
					$si->image->imageSetData($data);
					if($si->image->imageRenameAttribute()) {
						print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
					}
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $errorCode, 'msg' => $si->getError($errorCode) ) ) ) );
				}
			break;
			
		case 'categoryAdd':
			checkAuth();
			$data['value'] = $value;
			if($data['value'] == "") {
				$valid = false;
				$errorCode = 102;
			}
			if($valid) {
				$si->image->imageSetData($data);
				$id = $si->image->imageAddCategory();
				if($id) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'categoryId' => $id ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 110, 'msg' => $si->getError(110) ) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $errorCode, 'msg' => $si->getError($errorCode) ) ) ) );
			}
			break;
			
		case 'categoryList':
			$data['start'] = (is_numeric($start)) ? $start : 0;
			$data['limit'] = (is_numeric($limit)) ? $limit : 10;
			$data['searchFormat'] = in_array(strtolower(trim($searchFormat)),array('exact','left','right','both')) ? strtolower(trim($searchFormat)) : 'both';
			$data['value'] = str_replace('%','%%',trim($value));
			
			$data['categoryId'] = (!is_numeric($categoryId)) ? json_decode(stripslashes(trim($categoryId)),true) : $categoryId;
			if($valid) {
				$si->image->imageSetData($data);
				$rets = $si->image->imageListCategory();
				$rets = is_null($rets) ? array() : $rets;
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'totalCount' => $si->image->db->query_total(), 'records' => $rets ) ) );
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $errorCode, 'msg' => $si->getError($errorCode) ) ) ) );
			}
			break;

		case 'categoryUpdate':
			checkAuth();
			$data['value'] = $value;
			if($data['value'] == "") {
				$valid = false;
				$errorCode = 102;
			}
			$data['categoryId'] = $categoryId;
			if($data['categoryId'] == "") {
				$valid = false;
				$errorCode = 101;
			}
			if($valid) {
				$si->image->imageSetData($data);
				if($si->image->imageUpdateCategory()) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 111, 'msg' => $si->getError(111) ) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $errorCode, 'msg' => $si->getError($errorCode) ) ) ) );
			}
			break;
			
		case 'collectionAdd':
			checkAuth();
			if($name == '' || $code == '') {
				$valid = false;
				$errorCode = 125;
			}
			if($valid) {
				$si->collection->lg->logSetProperty('action', 'collectionAdd');
				$si->collection->lg->logSetProperty('lastModifiedBy', $_SESSION['user_id']);
				
				$si->collection->collectionSetProperty('name', $name);
				$si->collection->collectionSetProperty('code', $code);
				
				if($si->collection->collectionSave()) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'collectionId' => $si->collection->insert_id ) ) );
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('code' => 127, 'msg' => $si->getError(127)) ) ));
				}
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $errorCode, 'msg' => $si->getError($errorCode)) ) ));
			}
			break;
			
		case 'collectionDelete':
			checkAuth();
			if($collectionId == '') {
				$valid = false;
				$errorCode = 126;
			} else if(!$si->collection->collectionLoadById($collectionId)) {
				$valid = false;
				$errorCode = 128;
			}
			if($valid) {
				if($si->collection->collectionDelete($collectionId)) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart) ) );
				} else {
					print_c( json_encode( array( 'success' => false,  'error' => array('code' => 127, 'msg' => $si->getError(127)) ) ));
				}
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('code' => $errorCode, 'msg' => $si->getError($errorCode)) ) ));
			}
			break;


		case 'collectionList':
			$data['start'] = ($start == '') ? 0 : $start;
			$data['limit'] = ($limit == '') ? 25 : $limit;
			$data['collectionId'] = (!is_numeric($collectionId)) ? json_decode(stripslashes(trim($collectionId)),true) : $collectionId;
			$data['searchFormat'] = in_array(strtolower(trim($searchFormat)),array('exact','left','right','both')) ? strtolower(trim($searchFormat)) : 'both';
			$data['value'] = str_replace('%','%%',trim($value));
			$data['group'] = $group;
			$data['dir'] = (strtoupper(trim($dir)) == 'DESC') ? 'DESC' : 'ASC';
			$data['code'] = $code;

			if($valid) {
				$si->collection->collectionSetData($data);
				$ret = $si->collection->collectionList();
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'totalCount' => $si->collection->db->query_total(), 'records' => $ret ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($errorCode) , 'code' => $errorCode ) ) ) );
			}
			break;

		case 'collectionUpdate':
			checkAuth();
			if($collectionId == '') {
				$valid = false;
				$errorCode = 126;
			} else if(!$si->collection->collectionLoadById($collectionId)) {
				$valid = false;
				$errorCode = 128;
			}

			if($valid) {
				$si->collection->lg->logSetProperty('action', 'collectionUpdate');
				$si->collection->lg->logSetProperty('lastModifiedBy', $_SESSION['user_id']);

				($name != '') ? $si->collection->collectionSetProperty('name', $name) : '';
				($code != '') ? $si->collection->collectionSetProperty('code', $code) : '';
				($collectionSize != '') ? $si->collection->collectionSetProperty('collectionSize', $collectionSize) : '';
				if($si->collection->collectionSave()) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 129, 'msg' => $si->getError(129) ) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $errorCode, 'msg' => $si->getError($errorCode) ) ) ) );
			}
			break;
			
			
		case 'eventAdd':
			checkAuth();
			if(trim($title) == '') {
				$valid = false;
				$errorCode = 112;
			}
			if(trim($eventTypeId) == '') {
				$valid = false;
				$errorCode = 113;
			}

			if($valid) {
				$si->event->lg->logSetProperty('action', 'eventAdd');
				$si->event->lg->logSetProperty('lastModifiedBy', $_SESSION['user_id']);

				$si->event->eventsSetProperty('geoId', $geoId);
				$si->event->eventsSetProperty('eventTypeId', $eventTypeId);
				$si->event->eventsSetProperty('title', $title);
				$si->event->eventsSetProperty('description', $description);
				$si->event->eventsSetProperty('lastModifiedBy', $_SESSION['user_id']);
				if($si->event->eventsSave()) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'eventId' => $si->event->insert_id ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 114, 'msg' => $si->getError(114) ) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $errorCode, 'msg' => $si->getError($errorCode) ) ) ) );
			}
			break;

		case 'eventDelete':
			checkAuth();
			if($eventId == '') {
				$valid = false;
				$errorCode = 115;
			} else if(!$si->event->eventsLoadById($eventId)) {
				$valid = false;
				$errorCode = 124;
			}
			if($valid) {
				$si->event->lg->logSetProperty('action', 'eventDelete');
				$si->event->lg->logSetProperty('lastModifiedBy', $_SESSION['user_id']);
				if($si->event->eventsDelete($eventId)) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 116, 'msg' => $si->getError(116) ) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($errorCode) , 'code' => $errorCode ) ) ) );
			}
			break;

		case 'eventList':
			$data['start'] = ($start == '') ? 0 : $start;
			$data['limit'] = ($limit == '') ? 100 : $limit;
			$data['eventId'] = (!is_numeric($eventId)) ? json_decode(stripslashes(trim($eventId)),true) : $eventId;
			$data['eventTypeId'] = (!is_numeric($eventTypeId)) ? json_decode(stripslashes(trim($eventTypeId)),true) : $eventTypeId;
			$data['geoId'] = (!is_numeric($geoId)) ? json_decode(stripslashes(trim($geoId)),true) : $geoId;
			$data['searchFormat'] = in_array(strtolower(trim($searchFormat)),array('exact','left','right','both')) ? strtolower(trim($searchFormat)) : 'both';
			$data['value'] = str_replace('%','%%',trim($value));
			$data['geoFlag'] = (strtolower(trim($geoFlag)) == 'true') ? true : false;
			$data['group'] = $group;
			$data['dir'] = (strtoupper(trim($dir)) == 'DESC') ? 'DESC' : 'ASC';

			if($valid) {
				$si->event->eventsSetData($data);
				$ret = $si->event->eventsListRecords();
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'totalCount' => $si->event->db->query_total(), 'records' => $ret ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($errorCode) , 'code' => $errorCode ) ) ) );
			}
			break;

		case 'eventUpdate':
			checkAuth();
			if($eventId == '') {
				$valid = false;
				$errorCode = 115;
			} else if(!$si->event->eventsLoadById($eventId)) {
				$valid = false;
				$errorCode = 117;
			}

			if($valid) {
				$si->event->lg->logSetProperty('action', 'addEvent');
				$si->event->lg->logSetProperty('lastModifiedBy', $_SESSION['user_id']);

				($geoId != '') ? $si->event->eventsSetProperty('geoId', $geoId) : '';
				($eventTypeId != '') ? $si->event->eventsSetProperty('eventTypeId', $eventTypeId) : '';
				($title != '') ? $si->event->eventsSetProperty('title', $title) : '';
				($description != '') ? $si->event->eventsSetProperty('description', $description) : '';
				$si->event->eventsSetProperty('lastModifiedBy', $_SESSION['user_id']);
				if($si->event->eventsSave()) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 118, 'msg' => $si->getError(118) ) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $errorCode, 'msg' => $si->getError($errorCode) ) ) ) );
			}
			break;


		case 'eventTypeAdd':
			checkAuth();
			if(trim($title) == '') {
				$valid = false;
				$errorCode = 112;
			}
			if($valid) {
				$si->eventType->lg->logSetProperty('action', 'eventTypeAdd');
				$si->eventType->lg->logSetProperty('lastModifiedBy', $_SESSION['user_id']);

				$si->eventType->eventTypesSetProperty('title', $title);
				$si->eventType->eventTypesSetProperty('description', $description);
				$si->eventType->eventTypesSetProperty('lastModifiedBy', $_SESSION['user_id']);
				
				if($si->eventType->eventTypesSave()) {
					if(false == $si->eventType->insert_id) {
						print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 123, 'msg' => $si->getError(123) ) ) ) );
					} else {
						print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'eventTypeId' => $si->eventType->insert_id ) ) );
					}
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 119, 'msg' => $si->getError(119) ) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $errorCode, 'msg' => $si->getError($errorCode) ) ) ) );
			}
			break;

		case 'eventTypeList':
			$data['start'] = ($start == '') ? 0 : $start;
			$data['limit'] = ($limit == '') ? 100 : $limit;
			$data['eventTypeId'] = (!is_numeric($eventTypeId)) ? json_decode(stripslashes(trim($eventTypeId)),true) : $eventTypeId;
			$data['searchFormat'] = in_array(strtolower(trim($searchFormat)),array('exact','left','right','both')) ? strtolower(trim($searchFormat)) : 'both';
			$data['value'] = str_replace('%','%%',trim($value));
			$data['group'] = $group;
			$data['dir'] = (strtoupper(trim($dir)) == 'DESC') ? 'DESC' : 'ASC';

			if($valid) {
				$si->eventType->eventTypesSetData($data);
				$ret = $si->eventType->eventTypesListRecords();
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'totalCount' => $si->eventType->db->query_total(), 'records' => $ret ) ) );
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($errorCode) , 'code' => $errorCode ) ) ) );
			}
			break;

		case 'eventTypeDelete':
			checkAuth();
			if($eventTypeId == '') {
				$valid = false;
				$errorCode = 113;
			} elseif(!$si->eventType->eventTypesLoadById($eventTypeId)) {
				$valid = false;
				$errorCode = 120;
			}
			if($valid) {
				$si->eventType->lg->logSetProperty('action', 'deleteEventType');
				$si->eventType->lg->logSetProperty('lastModifiedBy', $_SESSION['user_id']);
				
				if($si->eventType->eventTypesDelete($eventTypeId)) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 121, 'msg' => $si->getError(121) ) ) ) );
				}

			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($errorCode) , 'code' => $errorCode ) ) ) );
			}
			break;

		case 'eventTypeUpdate':
			checkAuth();
			if($eventTypeId == '') {
				$valid = false;
				$errorCode = 113;
			} elseif(!$si->eventType->eventTypesLoadById($eventTypeId)) {
				$valid = false;
				$errorCode = 120;
			}

			if($valid) {
				$si->eventType->lg->logSetProperty('action', 'addEventType');
				$si->eventType->lg->logSetProperty('lastModifiedBy', $_SESSION['user_id']);

				($title != '') ? $si->eventType->eventTypesSetProperty('title', $title) : '';
				($description != '') ? $si->eventType->eventTypesSetProperty('description', $description) : '';
				$si->eventType->eventTypesSetProperty('lastModifiedBy', $_SESSION['user_id']);
				if($si->eventType->eventTypesSave()) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 122, 'msg' => $si->getError(122) ) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $errorCode, 'msg' => $si->getError($errorCode) ) ) ) );
			}
			break;

			
			
		case 'metadataPackageImport':
			if($url == '' || $key == '') {
				$valid = false;
				$errorCode = 106;
			} elseif(!$si->remoteAccess->checkRemoteAccess(ip2long($_SERVER['REMOTE_ADDR']), $key)) {
				$valid = false;
				$errorCode = 103;
			} elseif(!($fp = fopen($url, "r"))) {
				$valid = false;
				$errorCode = 107;
			}
			
			if(!($fp = fopen($url, "r"))) {
				$valid = false;
				$errorCode = 107;
			}
			
			if($valid) {
				$count = 0;
				while($data = fgetcsv($fp, NULL, ",")) {
					if($si->image->imageMetaDataPackageImport($data)) {
						$count++;
					}
				}
				if($count == 0) {
					$errorCode = 108;
					print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($errorCode) , 'code' => $errorCode ) ) ) );
				} else {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'totalCount' => $count ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => array('msg' => $si->getError($errorCode) , 'code' => $errorCode ) ) ) );
			}
			break;

		default:
			$errorCode = 100;
			print_c( json_encode( array( 'success' => false,  'error' => array('code' => $errorCode, 'message' => $si->getError($errorCode)) ) ) );
			break;
	}

ob_end_flush();
?>