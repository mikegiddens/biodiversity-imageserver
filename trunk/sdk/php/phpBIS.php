<?php
error_reporting(E_ALL ^ E_NOTICE);
class phpBIS
{
	public $lastError = array();
	public function __construct($key,$server/* = 'http://bis.silverbiology.com/dev/resources/api/'*/) {
		$this->key = $key;
		$this->server = $server;
	}

	private function CURL($url, $post = null) {
		$result = false;
		$curl = curl_init($url);
		if (is_resource($curl) === true) {
			curl_setopt($curl, CURLOPT_FAILONERROR, true);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			if (isset($post) === true) {
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
			}
			$result = curl_exec($curl);
			curl_close($curl);
		}
		return $result;
	}
/*
# Not used currently, use imageAddFromExisting, imageAddToCollection
	public function addImage($source, $storageDeviceId, $destinationPath) {
		$stream = file_get_contents($source);
		if((strpos($source, '/')) !== false) {
			$source = explode('/', $source);
			$filename = $source[count($source)-1];
		} else if((strpos($source, '\\')) !== false) {
			$source = explode('\\', $source);
			$filename = $source[count($source)-1];
		} else {
			$filename = $source;
		}
		$data['key'] = $this->key;
		$data['imagePath'] = $destinationPath;
		$data['storageDeviceId'] = $storageDeviceId;
		$data['filename'] = $filename;
		$data['stream'] = $stream;
		$data['cmd'] = 'addImage';
		$result = $this->CURL($this->server . '/api.php',$data);
		$result = json_decode($result,true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
*/
	public function attributeAdd($categoryId,$name) {
		$data = array();
		$data['categoryId'] = $categoryId;
		$data['name'] = $name;
		$data['cmd'] = 'attributeAdd';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php',$data);
		$result = json_decode($result,true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function attributeDelete($attributeId) {
		$data = array();
		$data['attributeId'] = $attributeId;
		$data['cmd'] = 'attributeDelete';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php',$data);
		$result = json_decode($result,true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function attributeList($categoryId,$showNames=true,$value='',$searchFormat='',$start='',$limit='') {
		$data['categoryId'] = $categoryId;
		$data['showNames'] = $showNames;
		$data['start'] = $start;
		$data['limit'] = $limit;
		$data['searchFormat'] = $searchFormat;
		$data['value'] = $value;
		$data['cmd'] = 'attributeList';
		$result = $this->CURL($this->server . '/api.php',$data);
		$result = json_decode($result,true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function attributeUpdate($attributeId,$name,$categoryId) {
		$data = array();
		$data['attributeId'] = $attributeId;
		$data['name'] = $name;
		$data['categoryId'] = $categoryId;
		$data['cmd'] = 'attributeUpdate';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php',$data);
		$result = json_decode($result,true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function categoryAdd($title = '',$description = '',$elementSet = '',$term = '') {
		$data = array();
		$data['title'] = $title;
		$data['description'] = $description;
		$data['elementSet'] = $elementSet;
		$data['term'] = $term;
		$data['cmd'] = 'categoryAdd';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php',$data);
		$result = json_decode($result,true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function categoryUpdate($categoryId,$title = '',$description = '',$elementSet = '',$term = '') {
		$data = array();
		$data['categoryId'] = $categoryId;
		$data['title'] = $title;
		$data['description'] = $description;
		$data['elementSet'] = $elementSet;
		$data['term'] = $term;
		$data['cmd'] = 'categoryUpdate';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php',$data);
		$result = json_decode($result,true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function categoryDelete($categoryId) {
		$data = array();
		$data['categoryId'] = $categoryId;
		$data['cmd'] = 'categoryDelete';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php',$data);
		$result = json_decode($result,true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function categoryList($categoryId='',$value='',$searchFormat='',$start='',$limit='') {
		$data['start'] = $start;
		$data['limit'] = $limit;
		$data['searchFormat'] = $searchFormat;
		$data['value'] = $value;
		$data['categoryId'] = $categoryId.

		$data['cmd'] = 'categoryList';
		$result = $this->CURL($this->server . '/api.php',$data);
		$result = json_decode($result,true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function collectionAdd($name, $code) {
		$data['name'] = $name;
		$data['code'] = $code;
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'collectionAdd';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function collectionDelete($collectionId) {
		$data['collectionId'] = $collectionId;
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'collectionDelete';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function collectionList( $properties = array()) {
		$data['start'] = $properties['start'];
		$data['limit'] = $properties['limit'];
		$data['order'] = $properties['order'];
		$data['filter'] = $properties['filter'];
		$data['collectionId'] = $properties['imageId'];
		$data['sort'] = $properties['sort'];
		$data['dir'] = $properties['dir'];
		$data['code'] = $properties['code'];
		$data['value'] = $properties['value'];
		$data['searchFormat'] = $properties['searchFormat'];
		$data['group'] = $properties['group'];
		$data['cmd'] = 'collectionList';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function collectionUpdate($collectionId, $properties = array()) {
		$data['name'] = (isset($properties['name']) && $properties['name'] != '') ? $properties['name'] : '';
		$data['code'] = (isset($properties['code']) && $properties['code'] != '') ? $properties['code'] : '';
		$data['collectionSize'] = (isset($properties['collectionSize']) && $properties['collectionSize'] != '') ? $properties['collectionSize'] : '';
		$data['collectionId'] = $collectionId;
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'collectionUpdate';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function eventAdd($eventId, $title, $eventTypeId, $geographyId, $description) {
		$data['eventId'] = (trim($eventId) != '') ? $eventId : '';
		$data['title'] = (trim($title) != '' ) ? $title : '';
		$data['eventTypeId'] = (trim($eventTypeId) != '') ? $eventTypeId : '';
		$data['geographyId'] = (trim($geographyId) != '') ? $geographyId : '';
		$data['description'] = (trim($description) != '') ? $description : '';
		$data['cmd'] = 'eventAdd';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result,true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
		
	}
	public function eventList( $eventId, $eventTypeId, $geographyId, $value='',$searchFormat='',$start='',$limit='') {
		$data['start'] = (trim($start) != '') ? $start : '';
		$data['limit'] = (trim($limit) != '') ? $limit : '';
		$data['eventId'] = (trim($eventId) != '') ? $eventId : '';
		$data['eventTypeId'] = (trim($eventTypeId) != '') ? $eventTypeId : '';
		$data['geographyId'] = (trim($geographyId) != '') ? $geographyId : '';
		$data['searchFormat'] = $searchFormat;
		$data['value'] = $value;
		$data['cmd'] = 'eventList';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function eventDelete($eventId) {
		$data['eventId'] = $eventId;
		$data['cmd'] = 'eventDelete';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result,true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function eventUpdate($eventId, $properties = array()) {
		$data['eventId'] = (trim($eventId) != '') ? $eventId : '';
		$data['title'] = (isset($properties['title']) && trim($properties['title']) != '') ? $properties['title'] : '';
		$data['eventTypeId'] = (isset($properties['eventTypeId']) && trim($properties['eventTypeId']) != '') ? $properties['eventTypeId'] : '';
		$data['geographyId'] = (isset($properties['']) && trim($properties['geographyId']) != '') ? $properties['geographyId'] : '';
		$data['description'] = (isset($properties['description']) && trim($properties['description']) != '') ? $properties['description'] : '';
		$data['cmd'] = 'eventUpdate';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result,true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
		
	}
	public function eventTypeAdd($title, $description) {
		$data['title'] = $title;
		$data['description'] = $description;
		$data['cmd'] = 'eventTypeAdd';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function eventTypeList($properties) {
		$data['start'] = (isset($properties['start']) && trim($properties['start']) != '') ? $properties['start']: '';
		$data['limit'] = (isset($properties['limit']) && trim($properties['limit']) != '') ? $properties['limit']: '';
		$data['group'] = (isset($properties['group']) && trim($properties['group']) != '') ? $properties['group']: '';
		$data['dir'] = (isset($properties['dir']) && trim($properties['dir']) != '') ? $properties['dir']: 'ASC';
		$data['value'] = (isset($properties['value']) && trim($properties['value']) != '') ? $properties['value']: '';
		$data['eventTypeId'] = (isset($properties['eventTypeId']) && trim($properties['eventTypeId']) != '') ? $properties['eventTypeId']: '';
		$data['searchFormat'] = (isset($properties['searchFormat']) && trim($properties['searchFormat']) != '') ? $properties['searchFormat']: '';
		$data['cmd'] = 'eventTypeList';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function eventTypeDelete($eventTypeId) {
		$data['eventTypeId'] = $eventTypeId;
		$data['cmd'] = 'eventTypeDelete';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function eventTypeUpdate($eventTypeId,$properties = array()) {
		$data['eventTypeId'] = $eventTypeId;
		$data['title'] = (isset($properties['title']) && trim($properties['title']) != '') ? $properties['title']: '';
		$data['description'] = (isset($properties['description']) && trim($properties['description']) != '') ? $properties['description']: '';
		$data['cmd'] = 'eventTypeUpdate';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function evernoteAccountAdd($accountName, $userName, $password, $consumerKey, $consumerSecret, $notebookGuid) {
		$data['accountName'] = $accountName;
		$data['userName'] = $userName;
		$data['password'] = $password;
		$data['consumerKey'] = $consumerKey;
		$data['consumerSecret'] = $consumerSecret;
		$data['notebookGuid'] = $notebookGuid;
		$data['cmd'] = 'evernoteAccountAdd';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function evernoteAccountDelete($enAccountId) {
		$data['enAccountId'] = $enAccountId;
		$data['cmd'] = 'evernoteAccountDelete';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function evernoteAccountList( $properties = array()) {
		$data['start'] = $properties['start'];
		$data['limit'] = $properties['limit'];
		$data['enAccountId'] = $properties['imageId'];
		$data['sort'] = $properties['sort'];
		$data['dir'] = $properties['dir'];
		$data['value'] = $properties['value'];
		$data['searchFormat'] = $properties['searchFormat'];
		$data['group'] = $properties['group'];
		$data['cmd'] = 'evernoteAccountList';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function evernoteAccountUpdate($enAccountId, $properties = array()) {
		$data['enAccountId'] = $enAccountId;
		$data['userName'] = (isset($properties['userName']) && trim($properties['userName']) != '') ? $properties['userName']: '';
		$data['password'] = (isset($properties['password']) && trim($properties['password']) != '') ? $properties['password']: '';
		$data['consumerKey'] = (isset($properties['consumerKey']) && trim($properties['consumerKey']) != '') ? $properties['consumerKey']: '';
		$data['consumerSecret'] = (isset($properties['consumerSecret']) && trim($properties['consumerSecret']) != '') ? $properties['consumerSecret']: '';
		$data['notebookGuid'] = (isset($properties['notebookGuid']) && trim($properties['notebookGuid']) != '') ? $properties['notebookGuid']: '';
		$data['cmd'] = 'evernoteAccountAdd';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function evernoteSearchByLabel($properties = array()) {
		$data['enAccountId'] = (isset($properties['enAccountId']) && trim($properties['enAccountId']) != '') ? $properties['enAccountId']: '';
		$data['value'] = (isset($properties['value']) && trim($properties['value']) != '') ? $properties['value']: '';
		$data['tag'] = (isset($properties['tag']) && trim($properties['tag']) != '') ? $properties['tag']: '';
		$data['start'] = (isset($properties['start']) && trim($properties['start']) != '') ? $properties['start']: '';
		$data['limit'] = (isset($properties['limit']) && trim($properties['limit']) != '') ? $properties['limit']: '';
		$data['cmd'] = 'evernoteSearchByLabel';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageListNodes( $properties = array()) {
		$data['nodeApi'] = $properties['nodeApi'];
		$data['nodeValue'] = $properties['nodeValue'];
		$data['family'] = $properties['family'];
		$data['genus'] = $properties['genus'];
		$data['cmd'] = 'imageListNodes';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageListNodesCharacters( $properties = array()) {
		$data['nodeApi'] = $properties['nodeApi'];
		$data['nodeValue'] = $properties['nodeValue'];
		$data['family'] = $properties['family'];
		$data['genus'] = $properties['genus'];
		$data['cmd'] = 'imageListNodes';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageListCharacters( $properties = array()) {
		$data['start'] = $properties['start'];
		$data['limit'] = $properties['limit'];
		$data['characters'] = $properties['characters'];
		$data['browse'] = $properties['browse'];
		$data['searchValue'] = $properties['searchValue'];
		$data['searchType'] = $properties['searchType'];
		$data['cmd'] = 'imageListCharacters';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageModifyRechop($imageId) {
		$data['imageId'] = $properties['imageId'];
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'imageListCharacters';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageMoveExisting($imageId,$newStorageId,$newImagePath) {
		$data['imageId'] = $properties['imageId'];
		$data['newStorageId'] = $properties['newStorageId'];
		$data['newImagePath'] = $properties['newImagePath'];
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'imageListCharacters';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageList( $properties = array()) {
		$data['start'] = $properties['start'];
		$data['limit'] = $properties['limit'];
		$data['order'] = $properties['order'];
		$data['showOCR'] = (trim($properties['showOCR']) != '') ? $properties['showOCR'] : false;
		$data['filter'] = $properties['filter'];
		$data['imageId'] = $properties['imageId'];
		$data['sort'] = $properties['sort'];
		$data['dir'] = $properties['dir'];
		$data['code'] = $properties['code'];
		$data['characters'] = $properties['characters'];
		$data['browse'] = $properties['browse'];
		$data['searchValue'] = $properties['searchValue'];
		$data['searchType'] = $properties['searchType'];
		$data['value'] = $properties['value'];
		$data['searchFormat'] = $properties['searchFormat'];
		$data['group'] = $properties['group'];
		$data['useRating'] = $properties['useStatus'];
		$data['useStatus'] = $properties['useStatus'];
		$data['associations'] = $properties['associations'];
		$data['cmd'] = 'imageList';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageAddToCollection($imageId, $code) {
		$data['imageId'] = $imageId;
		$data['code'] = $code;
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'imageAddToCollection';
		$result = $this->CURL($this->server . '/apiTest.php', $data);
// echo ($result);exit;
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageAddBarcode($imageId, $barcode) {
		$data['imageId'] = $imageId;
		$data['params'] = json_encode(array('barcode' => $barcode));
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'imageUpdate';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageUpdate($imageId, $params = array()) {
		$data['imageId'] = $imageId;
		if(isset($params['ocrValue']) && $params['ocrValue'] != '') {
			$params['ocrValue'] = utf8_encode($params['ocrValue']);
		}
		if(is_array($params) && count($params)) {
			$data['params'] = json_encode($params);
		}
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'imageUpdate';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageAddFromLocal($source, $destinationPath, $params = array()) {
		$stream = file_get_contents($source);
		if((strpos($source, '/')) !== false) {
			$source = explode('/', $source);
			$filename = $source[count($source)-1];
		} else if((strpos($source, '\\')) !== false) {
			$source = explode('\\', $source);
			$filename = $source[count($source)-1];
		} else {
			$filename = $source;
		}
		$data['key'] = $this->key;
		$data['imagePath'] = $destinationPath;
		$data['storageDeviceId'] = (isset($params['storageDeviceId']) && $params['storageDeviceId'] != '') ? $params['storageDeviceId'] : '';
		$data['barcode'] = (isset($params['barcode']) && $params['barcode'] != '') ? $params['barcode'] : '';
		$data['code'] = (isset($params['code']) && $params['code'] != '') ? $params['code'] : '';
		$data['filename'] = $filename;
		$data['stream'] = $stream;
		$data['cmd'] = 'imageAddFromLocal';
		$result = $this->CURL($this->server . '/api.php',$data);
		
		$result = json_decode($result,true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}

	public function imageAddFromExisting($storageDeviceId, $imagePath, $filename) {
		$data['storageDeviceId'] = $storageDeviceId;
		$data['imagePath'] = $imagePath;
		$data['filename'] = $filename;
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'imageAddFromExisting';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageAddFromUrl($url, $storageDeviceId, $path) {
		$data['url'] = $url;
		$data['storageDeviceId'] = $storageDeviceId;
		$data['imagePath'] = (trim($path) != '') ? $path : '';
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'imageAddFromUrl';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageAddFromServer($filename,$sourcePath,$destinationPath,$storageDeviceId = '',$loadFlag = 'copy') {
		$loadFlag =  (@strtolower($loadFlag) == 'move') ?'move' : 'copy';
		$data['filename'] = $filename;
		$data['imagePath'] = $sourcePath;
		$data['destinationPath'] = $destinationPath;
		$data['storageDeviceId'] = $storageDeviceId;
		$data['loadFlag'] = $loadFlag;
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'imageAddFromServer';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageDelete($imageId) {
		$data['imageId'] = $imageId;
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'imageDelete';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageGetUrl($type, $code, $size) {
		$data = array();
		switch($type) {
			case 'ID':
				$data['imageId'] = $code;
				break;
			case 'BARCODE':
				$data['barcode'] = $code;
				break;
		}
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'imageGetUrl';
		$data['size'] = (trim($size) != '') ? $size: '';
		$res = $this->CURL($this->server . '/api.php',$data);
		$result = json_decode($res, true);
		if((isset($result['success'])) && ($result['success'] == false)) {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		} else {
			return $result;
		}
	}
	public function imageAddAttribute($imageId, $categoryType, $category, $attributeType, $attribute, $force = false) {
		$data = array();
		$data['imageId'] = $imageId;
		$data['attribType'] = $attributeType;
		$data['attribute'] = $attribute;
		$data['categoryType'] = $categoryType;
		$data['category'] = $category;
		$data['force'] = ($force === true) ? 'true' : 'false';
		$data['cmd'] = 'imageAddAttribute';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php',$data);
		$result = json_decode($result,true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageDeleteAttribute($imageId, $attributeId) {
		$data = array();
		$data['imageId'] = $imageId;
		$data['attributeId'] = $attributeId;
		$data['cmd'] = 'imageDeleteAttribute';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php',$data);
		$result = json_decode($result,true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageListAttribute($imageId) {
		$data['imageId'] = $imageId;
		$data['cmd'] = 'imageListAttribute';
		$result = $this->CURL($this->server . '/api.php',$data);
		$result = json_decode($result,true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageAddToEvent($eventId, $imageId) {
		$data['eventId'] = $eventId;
		$data['imageId'] = $imageId;
		$data['cmd'] = 'imageAddToEvent';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageDeleteFromEvent($eventId, $imageId) {
		$data['eventId'] = $eventId;
		$data['imageId'] = $imageId;
		$data['cmd'] = 'imageDeleteFromEvent';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageListByEvent($eventId,$size,$attributesFlag) {
		$data['eventId'] = $eventId;
		$data['size'] = $size;
		$data['attributesFlag'] = $attributesFlag;
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		$data['cmd'] = 'imageListByEvent';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageDetectBarcode($imageId = '', $barcode = '') {
		if($imageId == '' && $barcode == '') {
			return array('success' => false, 'error' => array('code' => 181, 'msg' => 'Invalid barcode or imageId.'));
		} else {
			$data['imageId'] = $imageId;
			$data['barcode'] = $barcode;
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
			$data['cmd'] = 'imageDetectBarcode';
			$result = $this->CURL($this->server . '/api.php', $data);
			$result = json_decode($result, true);
			if($result['success'] == true) {
				return $result;
			} else {
				$this->lastError['code'] = $result['error']['code'];
				$this->lastError['msg'] = $result['error']['msg'];
				return $result;
			}
		}
	}
	public function imageDetectColorBox($imageId = '', $barcode = '', $force = false) {
		if($imageId == '' && $barcode == '') {
			return array('success' => false, 'error' => array('code' => 181, 'msg' => 'Invalid barcode or imageId.'));
		} else {
			$data['imageId'] = $imageId;
			$data['barcode'] = $barcode;
			$data['force'] = ($force === true) ? 'true' : 'false';
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
			$data['cmd'] = 'imageDetectBarcode';
			$result = $this->CURL($this->server . '/api.php', $data);
			$result = json_decode($result, true);
			if($result['success'] == true) {
				return $result;
			} else {
				$this->lastError['code'] = $result['error']['code'];
				$this->lastError['msg'] = $result['error']['msg'];
				return $result;
			}
		}
	}
	public function imageGetOcr($imageId = '', $barcode = '') {
		if($imageId == '' && $barcode == '') {
			return array('success' => false, 'error' => array('code' => 181, 'msg' => 'Invalid barcode or imageId.'));
		} else {
			$data['imageId'] = $imageId;
			$data['barcode'] = $barcode;
			$data['cmd'] = 'imageGetOcr';
			$result = $this->CURL($this->server . '/api.php', $data);
			$result = json_decode($result, true);
			if($result['success'] == true) {
				return $result;
			} else {
				$this->lastError['code'] = $result['error']['code'];
				$this->lastError['msg'] = $result['error']['msg'];
				return $result;
			}
		}
	}
	public function imageTilesGet($imageId = '', $barcode = '') {
		if($imageId == '' && $barcode == '') {
			return array('success' => false, 'error' => array('code' => 181, 'msg' => 'Invalid barcode or imageId.'));
		} else {
			$data['imageId'] = $imageId;
			$data['barcode'] = $barcode;
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
			$data['cmd'] = 'imageTilesGet';
			$result = $this->CURL($this->server . '/api.php', $data);
			$result = json_decode($result, true);
			if($result['success'] == true) {
				return $result;
			} else {
				$this->lastError['code'] = $result['error']['code'];
				$this->lastError['msg'] = $result['error']['msg'];
				return $result;
			}
		}
	}
	public function imageTilesLoad($filename,$zoom,$index) {
		$data['filename'] = $filename;
		$data['zoom'] = $zoom;
		$data['index'] = 'index';
		$data['cmd'] = 'imageTilesLoad';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function geographyAdd($country, $countryIso,$properties) {
		$data['country'] = $country;
		$data['countryIso'] = $countryIso;
		$data['admin0'] = (isset($properties['admin0']) && $properties['admin0'] != '') ? $properties['admin0'] : '';
		$data['admin1'] = (isset($properties['admin1']) && $properties['admin1'] != '') ? $properties['admin1'] : '';
		$data['admin2'] = (isset($properties['admin2']) && $properties['admin2'] != '') ? $properties['admin2'] : '';
		$data['admin3'] = (isset($properties['admin3']) && $properties['admin3'] != '') ? $properties['admin3'] : '';
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'geographyAdd';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function geographyDelete($geographyId) {
		$data['geographyId'] = $geographyId;
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'geographyDelete';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function geographyList($properties) {
		$data['geographyId'] = $properties['geographyId'];
		$data['countryIso'] = $properties['countryIso'];
		$data['start'] = $properties['start'];
		$data['limit'] = $properties['limit'];
		$data['order'] = $properties['order'];
		$data['sort'] = $properties['sort'];
		$data['dir'] = $properties['dir'];
		$data['value'] = $properties['value'];
		$data['searchFormat'] = $properties['searchFormat'];
		$data['group'] = $properties['group'];
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'geographyList';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function geographyUpdate($geographyId,$properties) {
		$data['geographyId'] = $geographyId;
		$data['countryIso'] = (isset($properties['countryIso']) && $properties['countryIso'] != '') ? $properties['countryIso'] : '';
		$data['admin0'] = (isset($properties['admin0']) && $properties['admin0'] != '') ? $properties['admin0'] : '';
		$data['admin1'] = (isset($properties['admin1']) && $properties['admin1'] != '') ? $properties['admin1'] : '';
		$data['admin2'] = (isset($properties['admin2']) && $properties['admin2'] != '') ? $properties['admin2'] : '';
		$data['admin3'] = (isset($properties['admin3']) && $properties['admin3'] != '') ? $properties['admin3'] : '';
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'geographyUpdate';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function metadataPackageImport($url = '', $key = '') {
		if($url == '' && $key == '') {
			return array('success' => false, 'error' => array('code' => 106, 'msg' => 'url and key should be provided.'));
		} else {
			$data['url'] = $url;
			$data['key'] = $this->key;
			$data['cmd'] = 'metadataPackageImport';
			$result = $this->CURL($this->server . '/api.php', $data);
			$result = json_decode($result, true);
			if($result['success'] == true) {
				return $result;
			} else {
				$this->lastError['code'] = $result['error']['code'];
				$this->lastError['msg'] = $result['error']['msg'];
				return $result;
			}
		}
	}
	public function metadataPackageList() {
		$data['cmd'] = 'metadataPackageList';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function setAdd($name, $description) {
		$data['name'] = $name;
		$data['description'] = (trim($description) != '') ? $description : '';
		$data['cmd'] = 'setAdd';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			if(isset($result['details'])) {
				$this->lastError['details'] = $result['details'];
			}
		}
	}
	public function setUpdate($setId, $name, $description) {
		$data['setId'] = $setId;
		$data['name'] = $name;
		$data['description'] = (trim($description) != '') ? $description : '';
		$data['cmd'] = 'setUpdate';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function setDelete($setId) {
		$data['setId'] = $setId;
		$data['cmd'] = 'setDelete';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function setList($setId='', $value='',$searchFormat='') {
		$data['setId'] = $setId;
		$data['searchFormat'] = $searchFormat;
		$data['value'] = $value;
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'setList';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function setValueAdd($setId, $attributeId, $rank) {
		$data['setId'] = $setId;
		$data['attributeId'] = $attributeId;
		$data['rank'] = (trim($rank) != '') ? $rank : 0;
		$data['cmd'] = 'setValueAdd';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function setValueUpdate($setValueId, $setId, $valueId, $rank) {
		$data['setValueId'] = $setValueId;
		$data['setId'] = $setId;
		$data['valueId'] = $valueId;
		$data['rank'] = (trim($rank) != '') ? $rank : '';
		$data['cmd'] = 'setValueUpdate';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function setValueDelete($setValueId) {
		$data['id'] = $setValueId;
		$data['cmd'] = 'setValueDelete';
		/* if(PHP_SAPI == 'cli') */{
			$data['authMode'] = 'key';
			$data['key'] = $this->key;
		}
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageListBySet($setId) {
		$data['setId'] = (trim($setId) != '') ? $setId : '';
		$data['cmd'] = 'imageListBySet';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function imageListBySetKeyValue($category, $attribute) {
		$data['category'] = (trim($category) != '') ? $category : '';
		$data['attribute'] = (trim($attribute) != '') ? $attribute : '';
		$data['cmd'] = 'imageListBySetKeyValue';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	
	public function populateOcrProcessQueue($imageIds) {
		$data['imageId'] = json_encode($imageIds);
		$data['authMode'] = 'key';
		$data['key'] = $this->key;
		$data['cmd'] = 'populateOcrProcessQueue';
		$result = $this->CURL($this->server . '/backup_services.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return $result;
		}
	}
	public function getLastError() {
		return $this->lastError;
	}
}
?>