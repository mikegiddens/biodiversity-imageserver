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
			'accountName'
		,	'admin0'
		,	'admin1'
		,	'admin2'
		,	'admin3'
		,	'api'
		,	'attribType'
		,	'attribute'
		,	'attributeId'
		,	'baseUrl'
		,	'basePath'
		,	'browse'
		,	'callback'
		,	'category'
		,	'categoryId'
		,	'categoryType'
		,	'characters'
		,	'characterType'
		,	'code'
		,	'collectionId'
		,	'consumerKey'
		,	'consumerSecret'
		,	'country'
		,	'countryIso'
		,	'cmd'
		,	'description'
		,	'dir'
		,	'enAccountId'
		,	'eventId'
		,	'eventTypeId'
		,	'extra'
		,	'fileName'
		,	'filter'
		,	'force'
		,	'geoFlag'
		,	'geographyId'
		,	'geoId'
		,	'group'
		,	'imageId'
		,	'imagePath'
		,	'key'
		,	'limit'
		,	'name'
		,	'notebookGuid'
		,	'order'
		,	'params'
		,	'password'
		,	'searchFormat'
		,	'searchType'
		,	'searchValue'
		,	'showNames'
		,	'showOCR'
		,	'sort'
		,	'start'
		,	'storageDeviceId'
		,	'stream'
		,	'tag'
		,	'type'
		,	'url'
		,	'useRating'
		,	'userName'
		,	'useStatus'
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
				if(!$si->remoteAccess->remoteAccessCheck(ip2long($_SERVER['REMOTE_ADDR']), $key)) {
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
			if($categoryId == '') {
				$valid = false;
				$errorCode = 101;
			} else if(!$si->imageCategory->imageCategoryExists($categoryId)) {
				$valid = false;
				$errorCode = 147;
			}
			if($name == '') {
				$valid = false;
				$errorCode = 148;
			}
			if($valid) {
				$si->imageAttribute->imageAttributeSetProperty('name',$name);
				$si->imageAttribute->imageAttributeSetProperty('categoryId',$categoryId);
				$id = $si->imageAttribute->imageAttributeAdd();
				if($id) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'attributeId' => $id ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray(105) ) ) ) ;
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode) ) ) );
			}
			break;
			
		case 'attributeDelete':
			checkAuth();
			if($attributeId == "") {
				$valid = false;
				$errorCode = 109;
			} else if ($si->imageAttribute->imageAttributeExists($attributeId)) {
				$valid = false;
				$errorCode = 149;
			}
			if($valid) {
				if($si->imageAttribute->imageAttributeDelete($attributeId)) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode) ) ) );
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
					print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode) ) ) );
				}
			break;

		case 'attributeUpdate':
			checkAuth();
			if($attributeId == "") {
				$valid = false;
				$errorCode = 109;
			} else if ($si->imageAttribute->imageAttributeLoadById($attributeId)) {
				$valid = false;
				$errorCode = 149;
			}
			if($valid) {
				(trim($name) != '') ? $si->imageAttribute->imageAttributeSetProperty('name',$name) : '';
				(trim($categoryId) != '') ? $si->imageAttribute->imageAttributeSetProperty('categoryId',$categoryId) : '';

				if($si->imageAttribute->imageAttributeUpdate()) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode) ) ) );
			}
			break;
			
		case 'categoryAdd':
			checkAuth();
			if($title == "") {
				$valid = false;
				$errorCode = 112;
			}
			if($valid) {
				$si->imageCategory->imageCategorySetProperty('title',$title);
				$si->imageCategory->imageCategorySetProperty('description',$description);
				$si->imageCategory->imageCategorySetProperty('elementSet',$elementSet);
				$si->imageCategory->imageCategorySetProperty('term',$term);
				$id = $si->imageCategory->imageCategoryAdd();
				if($id) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'categoryId' => $id ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray(110) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode) ) ) );
			}
			break;

		case 'categoryDelete':
			checkAuth();
			if($categoryId == "") {
				$valid = false;
				$errorCode = 101;
			} else if(!$si->imageCategory->imageCategoryExists($categoryId)) {
				$valid = false;
				$errorCode = 147;
			}
			if($valid) {
				if($si->imageCategory->imageCategoryDelete($categoryId)) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray(146) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode) ) ) );
			}
			break;

		case 'categoryList':
			$data['start'] = (is_numeric($start)) ? $start : 0;
			$data['limit'] = (is_numeric($limit)) ? $limit : 10;
			$data['searchFormat'] = in_array(strtolower(trim($searchFormat)),array('exact','left','right','both')) ? strtolower(trim($searchFormat)) : 'both';
			$data['value'] = str_replace('%','%%',trim($value));
			
			$data['categoryId'] = (!is_numeric($categoryId)) ? json_decode(stripslashes(trim($categoryId)),true) : $categoryId;
			if($valid) {
				$si->imageCategory->imageCategorySetData($data);
				$rets = $si->imageCategory->imageCategoryList();
				$rets = is_null($rets) ? array() : $rets;
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'totalCount' => $si->imageCategory->db->query_total(), 'records' => $rets ) ) );
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode) ) ) );
			}
			break;

		case 'categoryUpdate':
			checkAuth();
			if($categoryId == "") {
				$valid = false;
				$errorCode = 101;
			} else if(!$si->imageCategory->imageCategoryLoadById($categoryId)) {
				$valid = false;
				$errorCode = 147;
			}
			if($valid) {
				$si->imageCategory->imageCategorySetProperty('categoryId',$categoryId);
				(trim($title) != '') ? $si->imageCategory->imageCategorySetProperty('title',$title) : '';
				(trim($description) != '') ? $si->imageCategory->imageCategorySetProperty('description',$description) : '';
				(trim($elementSet) != '') ? $si->imageCategory->imageCategorySetProperty('elementSet',$elementSet) : '';
				(trim($term) != '') ? $si->imageCategory->imageCategorySetProperty('term',$term) : '';
				if($si->imageCategory->imageCategoryUpdate()) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray(111) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode) ) ) );
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
					print_c( json_encode( array( 'success' => false,  'error' => $si->getErrorArray(127) ) ));
				}
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => $si->getErrorArray($errorCode) ) ));
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
					print_c( json_encode( array( 'success' => false,  'error' => $si->getErrorArray(127) ) ));
				}
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => $si->getErrorArray($errorCode) ) ));
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
				print_c( json_encode( array( 'success' => false,  'error' => $si->getErrorArray($errorCode) ) ) );
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
					print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray(129) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode) ) ) );
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
					print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray(114) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode) ) ) );
			}
			break;

		case 'eventDelete':
			checkAuth();
			if($eventId == '') {
				$valid = false;
				$errorCode = 115;
			} else if(!$si->event->eventsLoadById($eventId)) {
				$valid = false;
				$errorCode = 117;
			}
			if($valid) {
				$si->event->lg->logSetProperty('action', 'eventDelete');
				$si->event->lg->logSetProperty('lastModifiedBy', $_SESSION['user_id']);
				if($si->event->eventsDelete($eventId)) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray(116) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => $si->getErrorArray($errorCode) ) ) );
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
				print_c( json_encode( array( 'success' => false,  'error' => $si->getErrorArray($errorCode) ) ) );
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
					print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray(118) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode) ) ) );
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
						print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray(123) ) ) );
					} else {
						print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'eventTypeId' => $si->eventType->insert_id ) ) );
					}
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray(119) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode) ) ) );
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
					print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray(121) ) ) );
				}

			} else {
				print_c( json_encode( array( 'success' => false,  'error' => $si->getErrorArray($errorCode) ) ) );
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
				print_c( json_encode( array( 'success' => false,  'error' => $si->getErrorArray($errorCode) ) ) );
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
					print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray(122) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode) ) ) );
			}
			break;
			
		case 'evernoteAccountAdd':
			checkAuth();
			if($accountName=='' || $userName=='' || $password=='' || $consumerKey=='' || $consumerSecret=='' || $notebookGuid=='') {
				$valid = false;
				$errorCode = 131;
			} else if ($si->en->evernoteAccountsExists($accountName)) {
				$valid = false;
				$errorCode = 132;
			}
			if($valid) {
				$data['accountName'] = $accountName;
				$data['userName'] = $userName;
				$data['password'] = $password;
				$data['consumerKey'] = $consumerKey;
				$data['consumerSecret'] = $consumerSecret;
				$data['notebookGuid'] = $notebookGuid;
				$data['rank'] = (trim($rank)!='') ? $rank : 0;
				$si->en->evernoteAccountsSetAllData($data);
				if(false === ($id = $si->en->evernoteAccountsAdd())) {
					print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray(135)) ));
				} else {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'enAccountId' => $id ) ) );
				}
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
			break;
			
		case 'evernoteAccountDelete':
			checkAuth();
			if($enAccountId==''){
				$valid = false;
				$errorCode = 133;
			} elseif(!$si->en->evernoteAccountsLoadById($enAccountId)) {
				$valid = false;
				$errorCode = 134;
			}
			if($valid) {
				if($si->en->evernoteAccountsDelete($enAccountId)) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray(136)) ));
				}
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
			break;
			
		case 'evernoteAccountList':
			checkAuth();
			$data['start'] = ($start == '') ? 0 : $start;
			$data['limit'] = ($limit == '') ? 100 : $limit;
			$data['enAccountId'] = (!is_numeric($enAccountId)) ? json_decode(stripslashes(trim($enAccountId)),true) : $enAccountId;
			$data['searchFormat'] = in_array(strtolower(trim($searchFormat)),array('exact','left','right','both')) ? strtolower(trim($searchFormat)) : 'both';
			$data['value'] = str_replace('%','%%',trim($value));
			$data['group'] = $group;
			$data['dir'] = (strtoupper(trim($dir)) == 'DESC') ? 'DESC' : 'ASC';
			$si->en->evernoteAccountsSetData($data);
			if($valid) {
				$accounts = $si->en->evernoteAccountsList();
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'totalCount' => $si->en->db->query_total(), 'records' => $accounts ) ) );
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode) ) ) );
			}
			break;
			
		case 'evernoteAccountUpdate':
			checkAuth();
			if($enAccountId==''){
				$valid = false;
				$errorCode = 133;
			} else if(!$si->en->evernoteAccountsLoadById($enAccountId)) {
				$valid = false;
				$errorCode = 134;
			} else if ($accountName!='' && $accountName != $si->en->evernoteAccountsGetProperty('accountName') && $si->en->evernoteAccountsExists($accountName)) {
				$valid = false;
				$errorCode = 132;
			}
			if($valid) {
				if($accountName!='') $data['accountName'] = $accountName;
				if($userName!='') $data['userName'] = $userName;
				if($password!='') $data['password'] = $password;
				if($consumerKey!='') $data['consumerKey'] = $consumerKey;
				if($consumerSecret!='') $data['consumerSecret'] = $consumerSecret;
				if($notebookGuid!='') $data['notebookGuid'] = $notebookGuid;
				if($rank!='') $data['rank'] = $rank;
				$si->en->evernoteAccountsSetAllData($data);
				if($si->en->evernoteAccountsUpdate()) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray(137)) ));
				}
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
			break;

		case 'evernoteSearchByLabel':
			$value = urldecode(trim($value));
			$enAccountId = trim($enAccountId);
			if($value == '') {
				$valid = false;
				$errorCode = 102;
			} elseif($enAccountId!='' && !$si->en->evernoteAccountsFieldExists($enAccountId)) {
				$valid = false;
				$errorCode = 134;
			}
			if($valid) {
				$tag = (trim($tag)!='') ? $tag : '';
				if($tag != '') {
					$tagRecord = $si->en->evernoteTagsLoadByTagName($tag);
					if($tagRecord) {
						$tag = $tagRecord['tagGuid'];
					} else {
						$tag = '';
					}
				}
				$start = (trim($start) == '') ? 0 : trim($start);
				$limit = (trim($limit) == '') ? 25 : trim($limit);
				$data = array();
				$si->en->evernoteAccountsSetData(array('enAccountId' => $enAccountId));
				$accounts = $si->en->evernoteAccountsList();
				$totalNotes = 0;
				if(is_array($accounts) && count($accounts)) {
				$limit = ceil($limit/(count($accounts)));
				foreach($accounts as $account) {
				$evernote_details_json = json_encode($account);

				$url = $config['evernoteUrl'];
				$url .= '?cmd=findNotes&auth=[' . $evernote_details_json . ']&start=' . $start . '&limit=' . $limit . '&searchWord=' . urlencode($value);
				if($tag != '') $url .= '&tag=[\"'.$tag.'\"]';
				$rr = json_decode(@file_get_contents($url),true);
				if($rr['success']) {
					//$totalNotes += $rr['totalNotes'];
					$labels = $rr['data'];
					if(is_array($labels) && count($labels)) {
						foreach($labels as $label) {
							if(!array_key_exists($label,$data)) {
								$ar = array();
								if(!$si->s2l->load_by_labelId($label)) continue;
								$totalNotes++;
								$si->image->imageSetData(array("field"=>"barcode", "value"=>$si->s2l->get('barcode'), "start"=>0, "limit"=>$limit));
								$ar = $si->image->imageList();
								$ar = $ar[0];
								$tmpPath = $si->image->getUrl($ar->imageId);
								$ar->path = $tmpPath['baseUrl'];
								$fname = explode(".", $ar->fileName);
								$ar->ext = $fname[1];
								$ar->en_flag = ($si->s2l->load_by_barcode($ar->barcode)) ? 1 : 0;
								$data[$label] = $ar;
							}
						}
					}
				} # if rr[success]
				} # for
				} # if labels
				$data = @array_values($data);
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'totalCount' => $totalNotes, 'records' => $data ) ));
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}

			break;

# Image commands

		case 'imageAddAttribute':
			checkAuth();
			$data['imageId'] = $imageId;
			$categoryType = in_array(trim($categoryType),array('categoryId','title','term')) ? trim($categoryType) : 'categoryId';
			$attribType = in_array(trim($attribType),array('attributeId','name')) ? trim($attribType) : 'attributeId';
			$force = (trim($force) == 'true') ? true : false;
			if($data['imageId'] == "") {
				$valid = false;
				$errorCode = 157;
			} elseif(trim($category) == '') {
				$valid = false;
				$errorCode = 160;
			} elseif(trim($attribute) == "") {
				$valid = false;
				$errorCode = 159;
			} else {
				if(false === ($data['categoryId'] = $si->imageCategory->imageCategoryGetBy($category,$categoryType))) {
					$valid = false;
					$errorCode = 147;
				} else if(false === ($data['attributeId'] = $si->imageAttribute->imageAttributeGetBy($attribute,$attributeType))) {
					if ($force && $attribType == 'name') {
						$si->imageAttribute->imageAttributeSetProperty('name',$attribute);
						$si->imageAttribute->imageAttributeSetProperty('categoryId',$data['categoryId']);
						$id = $si->imageAttribute->imageAttributeAdd();
						$data['attributeId'] = $id;
					} else {
						$valid = false;
						$errorCode = 149;
					}
				}
			}
			if($valid) {
				$si->image->imageSetData($data);
				if($si->image->imageAttributeAdd()) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray(161)) ));
				}
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
			break;
			
		case 'imageAddFromDnd':
			checkAuth();
			$imagePath = (isset($imagePath))?$imagePath:'';
			$storageDeviceId = (trim($storageDeviceId)!='') ? $storageDeviceId : $si->storage->storageDeviceGetDefault();
			if(!$si->remoteAccess->remoteAccessCheck(ip2long($_SERVER['REMOTE_ADDR']), $key)) {
				$errorCode = 103;
				$valid = false;
			} elseif($storageDeviceId == '' || !$si->storage->storageDeviceExists($storageDeviceId)) {
				$errorCode = 156;
				$valid = false;
			} elseif($fileName=='') {
				$errorCode = 163;
				$valid = false;
			} else {
				$config['allowedImportTypes'] = array(1,2,3); //GIF, JPEG, PNG
				# http://www.php.net/manual/en/function.exif-imagetype.php
				$stream = ($stream != '') ? $stream : '';
				$stream = str_replace(' ','+',$stream);
				$stream = base64_decode($stream);
				if((strpos($fileName,'/')) !== false) {
					$tmpFilename = explode('/', $fileName);
					$fileName = $tmpFilename[count($tmpFilename)-1];
				}
				file_put_contents($fileName, $stream);
				$size = getimagesize($fileName);
				if(!in_array($size[2],$config['allowedImportTypes'])) {
					$errorCode = 164;
					$valid = false;
				}
			}
			if($valid) {
				$response = $si->storage->storageDeviceStore($fileName,$storageDeviceId,$fileName, $imagePath, $key);
				$iEXd = new EXIFread($fileName);
				unlink($fileName);
				if($response['success']) {
					$si->pqueue->processQueueSetProperty('imageId', $response['imageId']);
					$si->pqueue->processQueueSetProperty('processType','all');
					$si->pqueue->processQueueSave();
					
					//Add latitude and longitude - Start
					if($gps = $iEXd->getGPS()) {
						$catId = 0;
						$atrId = 0;
						$catArray = $si->imageCategory->imageCategoryList();
						if(is_array($catArray)) {
							foreach($catArray as $cat) {
								if($cat['title'] == 'Latitude') {
									$catId = $cat['categoryId'];
									break;
								}
							}
						}
						if(!$catId) {
							$si->imageCategory->imageCategorySetProperty('title', 'Latitude');
							$catId = $si->imageCategory->imageCategoryAdd();
						}
						$atrArray = $si->imageAttribute->imageAttributeList($catId);
						if(is_array($atrArray)) {
							foreach($atrArray as $atr) {
								if($atr['name'] == $gps['Latitude']) {
									$atrId = $atr['attributeId'];
									break;
								}
							}
						}
						if(!$atrId) {
							$si->imageAttribute->imageAttributeSetProperty('name',$gps['Latitude']);
							$si->imageAttribute->imageAttributeSetProperty('categoryId',$catId);
							$atrId = $si->imageAttribute->imageAttributeAdd();
						}
						$data['imageId'] = $response['imageId'];
						$data['attributeId'] = $atrId;
						$data['categoryId'] = $catId;
						$si->image->imageSetData($data);
						$si->image->imageAttributeAdd();
						
						$catId = 0;
						$atrId = 0;
						if(is_array($catArray)) {
							foreach($catArray as $cat) {
								if($cat['title'] == 'Longitude') {
									$catId = $cat['categoryId'];
									break;
								}
							}
						}
						if(!$catId) {
							$si->imageCategory->imageCategorySetProperty('title', 'Longitude');
							$catId = $si->imageCategory->imageCategoryAdd();
						}
						$atrArray = $si->imageAttribute->imageAttributeList($catId);
						if(is_array($atrArray)) {
							foreach($atrArray as $atr) {
								if($atr['name'] == $gps['Longitude']) {
									$atrId = $atr['attributeId'];
									break;
								}
							}
						}
						if(!$atrId) {
							$si->imageAttribute->imageAttributeSetProperty('name',$gps['Longitude']);
							$si->imageAttribute->imageAttributeSetProperty('categoryId',$catId);
							$atrId = $si->imageAttribute->imageAttributeAdd();
						}
						$data['imageId'] = $response['imageId'];
						$data['attributeId'] = $atrId;
						$data['categoryId'] = $catId;
						$si->image->imageSetData($data);
						$si->image->imageAttributeAdd();
					}
					//Add latitude and longitude - End
					
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'imageId' => $response['imageId'] ) ) );
				} else {
					print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray(165)) ));
				}
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
			break;

		case 'imageAddFromExisting':
			checkAuth();
			if($imagePath != '') {
				$imagePath = '/' . ltrim($imagePath,'/');
			}
			if($storageDeviceId == '' || $imagePath == '' || $fileName == '') {
				$valid = false;
				$errorCode = 124;
			} elseif(!$si->storage->storageDeviceExists($storageDeviceId)) {
				$valid = false;
				$errorCode = 156;
			} elseif(!$si->image->imageExists($storageDeviceId, $imagePath, $fileName)) {
				$valid = false;
				$errorCode = 168;
			} else {
				$valid = true;
			}
			if($valid) {
				$imageId = $si->image->imageGetId($fileName, $imagePath, $storageDeviceId);
				if(!$imageId) {
					$device = $si->storage->storageDeviceGet($storageDeviceId);
					$ar = @getimagesize($device['basePath'] . '/' . $imagePath . '/' . $fileName);
					
					$si->image->imageSetProperty('fileName',$fileName);
					$si->image->imageSetProperty('storageDeviceId', $storageDeviceId);
					$si->image->imageSetProperty('path', $imagePath);
					$si->image->imageSetProperty('originalFilename', $fileName);
					
					if($si->image->imageSave()) {
						$imageId = $si->image->insert_id;
						$si->pqueue->processQueueSetProperty('imageId', $imageId);
						$si->pqueue->processQueueSetProperty('processType','all');
						$si->pqueue->processQueueSave();
						print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'imageId' => $imageId ) ) );
					} else {
						print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray(167)) ));
					}
				} else {
					print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray(166)) ));
				}
				
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
			break;


		case 'imageDelete':
			checkAuth();
			if(trim($imageId) == '') {
				$valid = false;
				$errorCode = 157;
			}
			if($valid) {
				$data['obj'] = $si->amazon;
				$imageId = (!is_numeric($imageId)) ? json_decode(stripslashes(trim($imageId)),true) : $imageId;
				$items = array();
				if(is_array($imageId)) {
					foreach($imageId as $imid) {
						$data['imageId'] = $imid;
						$si->image->imageSetData($data);
						if($si->image->imageLoadById($data['imageId'])) {
							if(($si->image->imageGetProperty('remoteAccessKey') == $key) || ($key==0)) {
								$ret = $si->image->imageDelete();
								if($ret['success']) $items[] = $imid;
							} else {
								$ret['success'] = false;
								$ret['code'] = 171;
							}
						} else {
							$ret['success'] = false;
							$ret['code'] = 170;
						}
					}
				} else {
					$data['imageId'] = $imageId;
					$si->image->imageSetData($data);
					if($si->image->imageLoadById($data['imageId'])) {
						if(($si->image->imageGetProperty('remoteAccessKey') == $key) || ($key==0)) {
							$ret = $si->image->imageDelete();
							if($ret['success']) $items[] = $imageId;
						} else {
							$ret['success'] = false;
							$ret['code'] = 171;
						}
					} else {
						$ret['success'] = false;
						$ret['code'] = 170;
					}
				}
				
				if(count($items)) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'totalCount' => count($items), 'records' => $items ) ) );
				} else {
					print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($ret['code'])) ));
				}
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
			break;

			
		case 'imageDeleteAttribute':
			checkAuth();
			$data['imageId'] = $imageId;
			if($data['imageId'] == "") {
				$valid = false;
				$errorCode = 157;
			}
			$data['attributeId'] = $attributeId;
			if($data['attributeId'] == "") {
				$valid = false;
				$errorCode = 109;
			}
			if($valid) {
				$si->image->imageSetData($data);
				if($si->image->imageAttributeDelete()) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray(162)) ));
				}
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
			break;
			
		case 'imageList':
			$data['start'] = ($start != '') ? $start : 0;
			$data['limit'] = ($limit != '') ? $limit : 100;
			$data['showOCR'] = (@in_array(trim($showOCR),array('1','true','TRUE'))) ? true : false;
			$data['order'] = json_decode(stripslashes(trim($order)),true);
			if(trim($sort) != '') {
				$data['sort'] = trim($sort);
				$dir = (trim($dir) == 'DESC') ? 'DESC' : 'ASC';
				$data['dir'] = trim($dir);
			}
			if(is_array($filter)) {
				$data['filter'] = $filter;
			} else {
				$data['filter'] = json_decode(stripslashes(trim($filter)),true);
			}
			$data['imageId'] = (!is_numeric($imageId)) ? json_decode(stripslashes(trim($imageId)),true) : $imageId;
			$data['searchFormat'] = in_array(strtolower(trim($searchFormat)),array('exact','left','right','both')) ? strtolower(trim($searchFormat)) : 'both';
			$data['value'] = str_replace('%','%%',trim($value));
			$data['group'] = $group;
			$data['dir'] = (strtoupper(trim($dir)) == 'DESC') ? 'DESC' : 'ASC';
			$data['code'] = ($code != '') ? $code : '';

			$data['characters'] = $characters;
			$data['characterType'] = in_array(strtolower(trim($characterType)), array('ids','string')) ? strtolower(trim($characterType)) : 'string';
			$data['browse'] = $browse;
			$data['searchValue'] = $searchValue;
			$data['searchType'] = $searchType;

			$data['useRating'] = (trim($useRating) == 'true') ? true : false;
			$data['useStatus'] = (trim($useStatus) == 'true') ? true : false;

			if($valid) {
				$si->image->imageSetData($data);
				$data = $si->image->imageList();
				$total = $si->image->total;
				if(is_array($data) && count($data)) {
					foreach($data as &$dt) {
						$device = $si->storage->storageDeviceGet($dt->storageDeviceId);
						$url = $device['baseUrl'];
						switch(strtolower($device['type'])) {
							case 's3':
								$tmp = $dt->path;
								$tmp = substr($tmp, 0, 1)=='/' ? substr($tmp, 1, strlen($tmp)-1) : $tmp;
								$url .= $tmp . '/';
								break;
							case 'local':
								if(substr($url, strlen($url)-1, 1) == '/') {
									$url = substr($url,0,strlen($url)-1);
								}
								$url .= $dt->path . '/';
								break;
						}
						unset($dt->storageDeviceId);
						unset($dt->path);
						$dt->path = $url;
						$fname = explode(".", $dt->fileName);
						$dt->ext = $fname[1];
						$dt->enFlag = ($si->s2l->Specimen2LabelLoadByBarcode($dt->barcode)) ? 1 : 0;
					}
				}

				if($api=='rss'){
					include("feedwriter.php");

					$RSSFeed = new FeedWriter(RSS2);
					$RSSFeed->setTitle($config['rssFeed']['title']);
					$RSSFeed->setLink($config['rssFeed']['webUrl']);
						
					foreach($data as $key => $value){
						
						$key1 = get_object_vars($value);						
						$imgMed = $key1['path'] . $key1['barcode'] . '_m.jpg';
						$imgLarg = $key1['path'] . $key1['barcode'] . '_l.jpg';
					
						$title = $key1['barcode'];  
						$newItem = $RSSFeed->createNewItem();
						 
						# Add elements to the feed item    
						$newItem->setTitle($title);
						$newItem->setLink($img1);
						$newItem->setDescription("<a href='" . $imgLarg . "'><img style='border:1px solid #5C7FB9'src='" . $imgMed . "'/></a>");
						$newItem->setEncloser($imgLarg, '7', 'image/jpeg');
						# set the feed item
						$RSSFeed->addItem($newItem);
					}

					$RSSFeed->genarateFeed();
				} else {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'totalCount' => $total, 'records' => $data ) ));
				}
			} else {				
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
			break;

		case 'imageUpdate':
			checkAuth();
			if($imageId == "") {
				$valid = false;
				$errorCode = 157;
			} elseif(!$si->image->imageLoadById($imageId)) {
				$valid = false;
				$errorCode = 158;
			}
			if($valid) {
				$fieldsArray = array('fileName','barcode','width','height','family','genus','specificEpithet','rank','author','title','description','globalUniqueIdentifier','creativeCommons','characters','flickrPlantID','flickrDetails','picassaPlantID','zoomEnabled','ScientificName','collectionCode','catalogueNumber','tmpFamily','tmpFamilyAccepted','tmpGenus','tmpGenusAccepted','storageDeviceId','path','originalFilename','remoteAccessKey','statusType','rating');
				$params = @json_decode(@stripslashes(trim($params)),true);
				if(is_array($params) && count($params)) {
					foreach($params as $key => $value) {
						if(@in_array($key,$fieldsArray)) {
							$si->image->imageSetProperty($key,$value);
						}
					}
					$si->image->imageSave();
				}
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart)));
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
			break;


			
# Geopraphy Commands			
		case 'geographyAdd':
			checkAuth();
			if($country == '' || $countryIso == '') {
				$valid = false;
				$errorCode = 138;
			} else if ($si->geography->geographyCountryExists($country)) {
				$valid = false;
				$errorCode = 139;
			} else if ($si->geography->geographyCountryIsoExists($countryIso)) {
				$valid = false;
				$errorCode = 140;
			}
			if($valid) {
				$si->geography->geographySetProperty('country', $country);
				$si->geography->geographySetProperty('countryIso', $countryIso);
				$si->geography->geographySetProperty('admin0', $admin0);
				$si->geography->geographySetProperty('admin1', $admin1);
				$si->geography->geographySetProperty('admin2', $admin2);
				$si->geography->geographySetProperty('admin3', $admin3);
				if(false === ($id = $si->geography->geographySave())) {
					print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray(143)) ));
				} else {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'geographyId' => $id ) ) );
				}
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
			break;
			
		case 'geographyDelete':
			checkAuth();
			if($geographyId==''){
				$valid = false;
				$errorCode = 141;
			} elseif(!$si->geography->geographyLoadById($geographyId)) {
				$valid = false;
				$errorCode = 142;
			}
			if($valid) {
				if($si->geography->geographyDelete($geographyId)) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray(144)) ));
				}
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
			break;

		case 'geographyImport':
			
			break;
			
		case 'geographyList':
			checkAuth();
			$data['start'] = ($start == '') ? 0 : $start;
			$data['limit'] = ($limit == '') ? 100 : $limit;
			$data['geographyId'] = (!is_numeric($geographyId)) ? json_decode(stripslashes(trim($geographyId)),true) : $geographyId;
			$data['countryIso'] = $countryIso;
			$data['searchFormat'] = in_array(strtolower(trim($searchFormat)),array('exact','left','right','both')) ? strtolower(trim($searchFormat)) : 'both';
			$data['value'] = str_replace('%','%%',trim($value));
			$data['group'] = $group;
			$data['dir'] = (strtoupper(trim($dir)) == 'DESC') ? 'DESC' : 'ASC';
			$si->geography->geographySetData($data);
			if($valid) {
				$records = $si->geography->geographyList();
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'totalCount' => $si->geography->db->query_total(), 'records' => $records ) ) );
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode) ) ) );
			}
			break;
			
		case 'geographyUpdate':
			checkAuth();
			if($geographyId ==''){
				$valid = false;
				$errorCode = 141;
			} elseif(!$si->geography->geographyLoadById($geographyId)) {
				$valid = false;
				$errorCode = 142;
			} else if ($country != '' && $country != $si->geography->geographyGetProperty('country') && $si->geography->geographyCountryExists($country)) {
				$valid = false;
				$errorCode = 139;
			} else if ($countryIso != '' && $countryIso != $si->geography->geographyGetProperty('countryIso') && $si->geography->geographyCountryIsoExists($countryIso)) {
				$valid = false;
				$errorCode = 140;
			}
			if($valid) {
				($country != '' ) ? $si->geography->geographySetProperty('country', $country) : '';
				($countryIso != '' ) ? $si->geography->geographySetProperty('countryIso', $countryIso) : '';
				($admin0 != '' ) ? $si->geography->geographySetProperty('admin0', $admin0) : '';
				($admin1 != '' ) ? $si->geography->geographySetProperty('admin1', $admin1) : '';
				($admin2 != '' ) ? $si->geography->geographySetProperty('admin2', $admin2) : '';
				($admin3 != '' ) ? $si->geography->geographySetProperty('admin3', $admin3) : '';
				if($si->geography->geographyUpdate()) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray(145)) ));
				}
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
			break;

		case 'helpingscienceQueueList':
			$data['start'] = ($start != '') ? $start : 0;
			$data['limit'] = ($limit != '') ? $limit : 100;
			if(is_array($filter)) {
				$data['filter'] = $filter;
			} else {
				$data['filter'] = json_decode(stripslashes($filter),true);
			}
			$data['clientId'] = (!is_numeric($clientId)) ? json_decode(stripslashes(trim($clientId)),true) : $clientId;
			$data['collectionId'] = (!is_numeric($collectionId)) ? json_decode(stripslashes(trim($collectionId)),true) : $collectionId;
			$data['imageServerId'] = (!is_numeric($imageServerId)) ? json_decode(stripslashes(trim($imageServerId)),true) : $imageServerId;
			$data['searchFormat'] = in_array(strtolower(trim($searchFormat)),array('exact','left','right','both')) ? strtolower(trim($searchFormat)) : 'both';
			$data['value'] = str_replace('%','%%',trim($value));
			$data['group'] = $group;
			$data['dir'] = (strtoupper(trim($dir)) == 'DESC') ? 'DESC' : 'ASC';

			if($valid) {
				$si->bis->bis2HsSetData($data);
				$data = $si->bis->bis2HsList();
				print_c( json_encode( array( 'success' => true, 'totalCount' => $si->bis->db->query_total(), 'records' => $data ) ) );
			}else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}

			break;
			
		case 'metadataPackageImport':
			if($url == '' || $key == '') {
				$valid = false;
				$errorCode = 106;
			} elseif(!$si->remoteAccess->remoteAccessCheck(ip2long($_SERVER['REMOTE_ADDR']), $key)) {
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
					print_c( json_encode( array( 'success' => false,  'error' => $si->getErrorArray($errorCode) ) ) );
				} else {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'totalCount' => $count ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false,  'error' => $si->getErrorArray($errorCode) ) ) );
			}
			break;
			
		case 'remoteAccessKeyList':
			$whitelist=  array('localhost',  '127.0.0.1');
			if(!in_array($_SERVER['HTTP_HOST'],  $whitelist)){
				//$valid = false;
				$errorCode = 150;
			}
			if($valid) {
				$list = $si->remoteAccess->remoteAccessList();
				$listArray = array();
				while($record = $list->fetch_object())
				{
					$item['ip'] = $record->ip;
					$item['key'] = $record->key;
					$item['active'] = $record->active;
					$listArray[] = $item;
				}
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'records' => $listArray ) ) );
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
			break;

		case 'storageDeviceAdd':
			checkAuth();
			$data['name'] = trim($name);
			$data['description'] = trim($description);
			$data['type'] = trim($type);
			$data['baseUrl'] = trim($baseUrl);
			$data['basePath'] = trim($basePath);
			$data['userName'] = trim($userName);
			$data['password'] = trim($password);
			$data['key'] = trim($key);
			$data['extra2'] = trim($extra);
			$data['active'] = (trim($active) == 'false') ? false : true;
			$default = (trim($default) == 'true') ? true : false;
			if($name=='' || $type=='' || $baseUrl=='') {
				$errorCode = 151;
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			} else {
				$si->storage->storageDeviceSetAll($data);
				$id = $si->storage->storageDeviceSave();
				if($id) {
					if($default) {
						$si->storage->storageDeviceSetDefault($id);
					}
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'storageDeviceId' => $id ) ) );
				} else {
					print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray(152)) ));
				}
			}
			break;
		
		case 'storageDeviceDelete':
			checkAuth();
			if(($storageDeviceId == '') || (!$si->storage->storageDeviceExists($storageDeviceId))) {
				$valid = false;
				$errorCode = 156;
			}
			if($valid){
				if($si->storage->storageDeviceDelete($storageDeviceId)) {
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray(154)) ));
				}
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
			break;
		
		case 'storageDeviceList':
			if(is_array($si->storage->devices)) {
				print_c( json_encode( array( 'success' => true, 'totalCount' => count($si->storage->devices), 'processTime' => microtime(true) - $timeStart, 'records' => $si->storage->devices ) ) );
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray(155)) ));
			}
			break;
			
		case 'storageDeviceUpdate':
			checkAuth();
			if(($storageDeviceId == '') || (!$si->storage->storageDeviceExists($storageDeviceId))) {
				$valid = false;
				$errorCode = 156;
			}
			if($valid){
				$data = $si->storage->storageDeviceGet($storageDeviceId);
				(trim($name) != '' ) ? $data['name'] = trim($name) : '';
				(trim($description) != '' ) ? $data['description'] = trim($description) : '';
				(trim($type) != '' ) ? $data['type'] = trim($type) : '';
				(trim($baseUrl) != '' ) ? $data['baseUrl'] = trim($baseUrl) : '';
				(trim($basePath) != '' ) ? $data['basePath'] = trim($basePath) : '';
				(trim($userName) != '' ) ? $data['userName'] = trim($userName) : '';
				(trim($password) != '' ) ? $data['password'] = trim($password) : '';
				(trim($key) != '' ) ? $data['key'] = trim($key) : '';
				(trim($extra) != '' ) ? $data['extra2'] = trim($extra) : '';
				in_array(@strtolower(trim($active)), array('true','false')) ? ($data['active'] = (@strtolower(trim($active) == 'false')) ? false : true) : '';
				$default = (trim($default) == 'true') ? true : false;
				$si->storage->storageDeviceSetAll($data);
				if($si->storage->storageDeviceUpdate()) {
					if($default) {
						$si->storage->storageDeviceSetDefault($storageDeviceId);
					}
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
				} else {
					print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray(153)) ));
				}
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
		
			break;
			
		case 'storageDeviceSetDefault':
			if(($storageDeviceId == '') || (!$si->storage->storageDeviceExists($storageDeviceId))) {
				$valid = false;
				$errorCode = 156;
			}
			if($valid){
				$si->storage->storageDeviceSetDefault($storageDeviceId);
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart ) ) );
			} else {
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			}
			break;

			
		default:
			$errorCode = 100;
				print_c (json_encode( array( 'success' => false, 'error' => $si->getErrorArray($errorCode)) ));
			break;
	}

ob_end_flush();
?>