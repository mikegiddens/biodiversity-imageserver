<?php

	/**
	 * @author SilverBiology
	 * @website http://www.silverbiology.com
	*/

	require_once( $config['path']['base'] . 'resources/api/classes/bis.bis2hs.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.collection.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.events.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.evernote.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.EXIFread.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.geography.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.image.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.imageRating.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.images.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.imgTiles.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.log.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.masterLog.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.misc.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.mysqliDatabase.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.picassa.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.processQueue.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.remoteAccess.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.set.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.specimen2label.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.storageDevice.php');
	require_once( $config['path']['base'] . 'resources/api/classes/bis.userPermissions.php');
	require_once( $config['path']['base'] . 'resources/api/classes/sdk/sdk.class.php');

	Class SilverImage {

		public  $logger;

		function __construct($project) {
			global $config;
			$this->load($project);
			$this->authMode = 'session';
			
			if($config['mode'] == 's3') {
				$this->amazon = new AmazonS3(array('key' => $config['s3']['accessKey'],'secret' => $config['s3']['secretKey']));
			} else {
				$this->amazon = NULL;
			}
			$this->bis = new Bis2Hs($this->db);
			$this->collection = new Collection($this->db);
			$this->event = new Event($this->db);
			$this->eventType = new EventTypes($this->db);
			$this->en = new EvernoteAccounts($this->db);
			$this->geography = new Geography($this->db);
			$this->image = new Image($this->db);
			$this->imageAttribute = new ImageAttribValue($this->db);
			$this->imageCategory = new ImageAttribType($this->db);
			$this->imageRating = new ImageRating($this->db);
			$this->images = new Images($this->db);
			$this->lg = new LogClass($this->db);
			$this->logger = new Logger($this->db);
			$this->picassa = new PicassaWeb();
			$this->pqueue = new ProcessQueue($this->db);
			$this->remoteAccess = new RemoteAccess($this->db);
			$this->s2l = new Specimen2label($this->db);
			$this->userPerm = new UserPermissions($this->db);
			$this->set = new Set($this->db);
			$this->storage = new StorageDevice($this->db);
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

		public function getErrorArray($errorCode) {
			return array('msg' => $this->getError($errorCode), 'code' => $errorCode );
		}
		
		public function getError($errorCode) {
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
				, 109 => 'attributeId should be provided.'
				, 110 => 'Category Not Added.'
				, 111 => 'Category Not Updated.'
				, 112 => 'title should be given.'
				, 113 => 'eventTypeId should be given.'
				, 114 => 'Event Could Not Added.'
				, 115 => 'eventId should be given.'
				, 116 => 'Event Could Not be Deleted.'
				, 117 => 'Event Does Not Exist.'
				, 118 => 'Event Could Not be Updated.'
				, 119 => 'Event Type Could Not be Added.'
				, 120 => 'Event Type Does Not Exist.'
				, 121 => 'Event Type Could Not be Deleted.'
				, 122 => 'Event Type Could Not be Updated.'
				, 123 => 'Event Type Already Exists.'
				, 124 => 'Event Does Not Exist.'
				, 125 => 'name and code should be provided.'
				, 126 => 'collectionId should be given.'
				, 127 => 'Collection Could Not be Deleted.'
				, 128 => 'Collection Does Not Exist.'
				, 129 => 'Collection Could Not be Updated.'
				, 130 => 'Collection Could Not be Added.'
				, 131 => 'accountName, userName, password, consumerKey, consumerSecret and notebookGuid should be provided.'
				, 132 => 'Evernote accountName already exists.'
				, 133 => 'enAccountId should be provided.'
				, 134 => 'enAccountId does not exist.'
				, 135 => 'Evernote Account Could Not be Added.'
				, 136 => 'Evernote Account Could Not be Deleted.'
				, 137 => 'Evernote Account Could Not be Updated.'
				, 138 => 'country and countryIso should be provided.'
				, 139 => 'country already exists.'
				, 140 => 'countryIso already exists.'
				, 141 => 'geographyId should be provided.'
				, 142 => 'Geography Does Not Exist.'
				, 143 => 'Geography Could Not be Added.'
				, 144 => 'Geography Could Not be Deleted.'
				, 145 => 'Geography Could Not be Updated.'
				, 146 => 'Category Could Not be Deleted.'
				, 147 => 'Category Does Not be Exist.'
				, 148 => 'name should be provided.'
				, 149 => 'Attribute Does Not Exist.'
				, 150 => 'Insufficient privileges to run this command.'



			);
			return $ar[$errorCode];
		}

	}

?>