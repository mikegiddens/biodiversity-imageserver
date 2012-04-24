<?php

	/**
	 * @copyright SilverBiology, LLC
	 * @author Michael Giddens
	 * @website http://www.silverbiology.com
	*/

	require_once( $config['path']['base'] . 'resources/api/classes/class.mysqli_database.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.master_log.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.master_images.php');
	require_once( $config['path']['base'] . 'resources/api/classes/class.master_image.php');
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

	Class SilverImage {

		public  $logger;

		function __construct($project) {
			global $config;

			$this->load($project);

			$this->logger = new Logger($this->db);
			$this->images = new Images($this->db);
			$this->image = new Image($this->db);
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
		}

		function load($project) {
			global $config;
			$connection_string="server={$config['mysql']['host']}; database=$project; username={$config['mysql']['user']}; password={$config['mysql']['pass']};";
			$this->db = new MysqliDatabase($connection_string);

			return( true );
		}

		public function getError ($error_code) {
			$ar = array (
				   100 => 'No Command Provided'
				 , 101 => 'sc_id Should be provided'
				 , 102 => 'Logs not Loaded'
				 , 103 => 'Date Should be provided.'
				 , 104 => 'Date2 Should be provided.'
				 , 105 => 'start_id Should be provided.'
				 , 106 => 'filename Should be provided.'
				 , 107 => 'image_id Should be provided.'
				 , 108 => 'Barcode was not found.'
				 , 109 => 'id Should be provided.'
				 , 110 => 'Images Rotated and Added to Queue.'
				 , 111 => 'degree Should be provided.'
				 , 112 => 'width and height Should be provided.'
				 , 113 => 'User is not logged-in or do not have the previlege to access this command.'
				, 114 => 'Not an allowed value for nodeApi'
				, 115 => 'Database Not Loaded'
				, 116 => 'Image Id does not exist.'
				, 117 => 'Error in deleting from the database.'
				, 118 => 'Value should be provided.'
				, 119 => 'Evernote Account Id should be provided.'
				, 120 => 'valueID should be provided.'
				, 121 => 'Image Attribute Not Added'
				, 122 => 'Image Attribute Not Deleted'
				, 123 => 'value should be given'
				, 124 => 'Category Not Added'
				, 125 => 'Category Not Renamed'
				, 126 => 'categoryID should be given'
				, 127 => 'Image Characters Not Loaded'
				, 128 => 'Not an allowed value for nodeApi'
				, 129 => 'Image List Not Loaded'
				, 130 => 'Title should be given'
				, 131 => 'Event Type Id should be given'
				, 132 => 'Title should be given'
				, 133 => 'Event Id should be given'
				, 134 => 'Barcode Or Image Id should be given'
				, 135 => 'Not a Valid Barcode Or Image Id'
				, 136 => 'Tesseract Not Enabled'
				, 137 => 'Box Detect Not Enabled.'
				, 138 => 'Size or width and height should be provided.'
				, 139 => 'zBarImg Not Enabled.'
				, 140 => 'Error Directory does not exist, or does not have write permission.'
				, 141 => 'Images Directory does not exist, or does not have write permission.'
				, 142 => 'Requested file type is not supported currently'
			);
			return $ar[$error_code];
		}

	}
	
?>