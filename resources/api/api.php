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
		,	'categoryId'
		,	'cmd'
		,	'key'
		,	'limit'
		,	'searchFormat'
		,	'showNames'
		,	'start'
		,	'url'
		,	'value'
		,	'valueId'
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

	require_once("classes/class.master.php");
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
					print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'valueId' => $id ) ) );
				} else {
					print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => 105, 'msg' => $si->getError(105) ) ) ) );
				}
			} else {
				print_c( json_encode( array( 'success' => false, 'error' => array ( 'code' => $errorCode, 'msg' => $si->getError($errorCode) ) ) ) );
			}
			break;
			
		case 'attributeDelete':
			checkAuth();
			$data['valueId'] = $valueId;
			if($data['valueId'] == "") {
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
						print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart , 'records' => $names ) ) );
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
				$data['valueId'] = $valueId;
				if($data['valueId'] == "") {
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
				// $rets = is_null($rets) ? array() : $rets;
				print_c( json_encode( array( 'success' => true, 'processTime' => microtime(true) - $timeStart, 'records' => $rets ) ) );
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