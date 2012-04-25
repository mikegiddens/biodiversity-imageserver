<?php
	error_reporting(E_ALL ^ E_NOTICE);
	ini_set('memory_limit', '128M');
	set_time_limit(0);
	session_start();
	ob_start();

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
	
	$si = new SilverImage($config['mysql']['name']);
	$user_access = new Access_user($config['mysql']['host'], $config['mysql']['user'], $config['mysql']['pass'], $config['mysql']['name']);

	// setting picassa constants
	$si->picassa->set('picassa_path', $config['picassa']['lib_path']);
	$si->picassa->set('picassa_user', $config['picassa']['email']);
	$si->picassa->set('picassa_pass', $config['picassa']['pass']);
	$si->picassa->set('picassa_album', $config['picassa']['album']);

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
		default:
			$code = 100;
			header('Content-type: application/json');
			print( json_encode( array( 'success' => false,  'error' => array('code' => $code, 'message' => $si->getError($code)) ) ) );
			break;
	}

ob_end_flush();
?>