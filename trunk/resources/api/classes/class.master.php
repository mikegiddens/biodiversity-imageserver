<?php

	/**
	 * @copyright SilverBiology, LLC
	 * @author Michael Giddens
	 * @website http://www.silverbiology.com
	*/

	require_once( BASE_PATH . 'resources/api/classes/class.mysqli_database.php');
	require_once( BASE_PATH . 'resources/api/classes/class.master_log.php');
	require_once( BASE_PATH . 'resources/api/classes/class.master_images.php');
	require_once( BASE_PATH . 'resources/api/classes/class.master_image.php');
	require_once( BASE_PATH . 'resources/api/classes/class.master_collection.php');
	require_once( BASE_PATH . 'resources/api/classes/class.process_queue.php');
	require_once( BASE_PATH . 'resources/api/classes/class.misc.php');
	require_once( BASE_PATH . 'resources/api/classes/class.picassa.php');
// 	require_once( BASE_PATH . 'resources/api/classes/class.amazonS3.php');
	require_once( BASE_PATH . 'resources/api/classes/sdk/sdk.class.php');
	require_once( BASE_PATH . 'resources/api/classes/class.bis2hs.php');

	Class SilverImage {
	
		public  $logger;

		function __construct() {
			global $config;
			$this->logger = new Logger();
			$this->images = new Images();
			$this->image = new Image();
			$this->collection = new Collection();
			$this->pqueue = new ProcessQueue();
			$this->picassa = new PicassaWeb();
			$this->amazon = new AmazonS3($config['s3']['accessKey'],$config['s3']['secretKey']);
			$this->bis = new Bis2hs();
			
			$this->logger->db = &$this->db;
			$this->images->db = &$this->db;
			$this->image->db = &$this->db;
			$this->collection->db = &$this->db;
			$this->pqueue->db = &$this->db;
			$this->bis->db = &$this->db;
			
		}

		function load($project) {

			Global $mysql_host, $mysql_pass, $mysql_user,$record;
			$connection_string="server=$mysql_host; database=$project; username=$mysql_user; password=$mysql_pass;";
			$this->db = new MysqliDatabase($connection_string);

			$this->logger->db = &$this->db;
			$this->images->db = &$this->db;
			$this->image->db = &$this->db;
			$this->collection->db = &$this->db;
			$this->pqueue->db = &$this->db;

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
				 , 110 => ' Images Rotated and Added to Queue !.'
				 , 111 => 'degree Should be provided.'
				 , 112 => 'width and height Should be provided.'
				 , 113 => 'User is not logged-in or do not have the previlege to access this command.'
				, 114 => 'Not an allowed value for nodeApi'
			);
			return $ar[$error_code];
		}

	}
	
?>