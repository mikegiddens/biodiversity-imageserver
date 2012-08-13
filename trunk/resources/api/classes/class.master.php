<?php

	/**
	 * @author SilverBiology
	 * @website http://www.silverbiology.com
	*/

	require_once( $config['path']['base'] . 'resources/api/classes/class.mysqli_database.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.master_image.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.master_images.php');
	
	
	require_once( $config['path']['base'] . 'resources/api/classes/class.master_log.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.master_collection.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.process_queue.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.misc.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.picassa.php');
	require_once( $config['path']['base'] . 'resources/api/classes/sdk/sdk.class.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.bis2hs.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.specimen2label.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.master_evernote.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.imgTiles.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.geography.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.events.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.log.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.user_permissions.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.remoteaccess.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.storage.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.set.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.EXIFread.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.imageRating.php');

	Class SilverImage {

		public  $logger;

		function __construct($project) {
			global $config;
			$this->load($project);
			$this->image = new Image($this->db);
			$this->images = new Images($this->db);
			$this->logger = new Logger($this->db);
			$this->collection = new Collection($this->db);
			$this->pqueue = new ProcessQueue($this->db);
			$this->picassa = new PicassaWeb();
			if($config['mode'] == 's3') {
				$this->amazon = new AmazonS3(array('key' => $config['s3']['accessKey'],'secret' => $config['s3']['secretKey']));
			} else {
				$this->amazon = NULL;
			}
			$this->bis = new Bis2hs($this->db);
			$this->s2l = new Specimen2label($this->db);
			$this->en = new EvernoteAccounts($this->db);
			$this->geography = new Geography($this->db);
			$this->event = new Event($this->db);
			$this->eventType = new EventTypes($this->db);
			$this->lg = new LogClass($this->db);
			$this->userPerm = new UserPermissions($this->db);
			$this->remoteAccess = new RemoteAccess($this->db);
			$this->storage = new Storage($this->db);
			$this->set = new Set($this->db);
			$this->imageRating = new ImageRating($this->db);
			$this->authMode = 'session';
		}

		function load($project) {
			global $config;
			$connection_string="server={$config['mysql']['host']}; database=$project; username={$config['mysql']['user']}; password={$config['mysql']['pass']};";
			$this->db = new MysqliDatabase($connection_string);
			return( true );
		}

		public function setAuthMode($authMode) {
			$expectedAM = array('session', 'key');
			if(in_array(strtolower($authMode), $expectedAM)) {
				$this->authMode = strtolower($authMode);
			}
		}
		
		public function getError($error_code) {
			$ar = array (
				100 => 'No Command Provided.'
				, 101 => 'categoryId Should be provided.'
				, 102 => 'value should be given.'
				, 103 => 'Invalid Key given.'
				, 104 => 'User is not logged-in or do not have the privilege to access this command.'
				, 105 => 'Image Attribute Not Added'
				, 106 => 'url and key should be provided.'
				, 107 => 'Invalid URL given.'
				, 108 => 'No new records added.'
				, 109 => 'valueId should be provided.'
				, 110 => 'Category Not Added'


			);
			return $ar[$error_code];
		}

	}

?>