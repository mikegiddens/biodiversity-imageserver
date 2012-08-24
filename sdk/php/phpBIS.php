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
			return false;
		}
	}
*/
	public function imageAddFromExisting($storageDeviceId, $imagePath, $filename) {
		$data['storageDeviceId'] = $storageDeviceId;
		$data['imagePath'] = $imagePath;
		$data['filename'] = $filename;
		$data['key'] = $this->key;
		$data['cmd'] = 'imageAddFromExisting';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return false;
		}
	}
	public function imageAddFromUrl($url, $storageDeviceId, $path) {
		$data['url'] = $url;
		$data['storageDeviceId'] = $storageDeviceId;
		$data['imagePath'] = (trim($path) != '') ? $path : '';
		$data['key'] = $this->key;
		$data['cmd'] = 'imageAddFromUrl';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return false;
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
		$data['cmd'] = 'imageGetUrl';
		$data['size'] = (trim($size) != '') ? $size: '';
		$res = $this->CURL($this->server . '/api.php',$data);
		$result = json_decode($res, true);
		if((isset($result['success'])) && ($result['success'] == false)) {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return false;
		} else {
			return $res;
		}
	}
	public function imageAddAttribute($imageId, $categoryType, $category, $attributeType, $attribute) {
		$data = array();
		$data['imageId'] = $imageId;
		$data['attributeType'] = $attributeType;
		$data['attribute'] = $attribute;
		$data['categoryType'] = $categoryType;
		$data['category'] = $category;
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
			return false;
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
			return false;
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
			return false;
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
			return false;
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
			return false;
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
			return false;
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
			return false;
		}
	}
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
			return false;
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
			return false;
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
			return false;
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
			return false;
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
			return false;
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
			return false;
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
			return false;
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
			return false;
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
			return false;
		}
	}
	public function imageListByEvent($eventId,$size,$attributesFlag) {
		$data['eventId'] = $eventId;
		$data['size'] = $size;
		$data['attributesFlag'] = $attributesFlag;
		$data['cmd'] = 'imageListByEvent';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return false;
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
			return false;
		}
	}
	public function eventTypeList($eventTypeId, $value='',$searchFormat='',$start='',$limit='',$group = '',$dir = 'ASC') {
		$data['start'] = (trim($start) != '') ? $start : '';
		$data['limit'] = (trim($limit) != '') ? $limit : '';
		$data['eventTypeId'] = (trim($eventTypeId) != '') ? $eventTypeId : '';
		$data['searchFormat'] = $searchFormat;
		$data['value'] = $value;
		$data['group'] = $group;
		$data['dir'] = $dir;
		$data['cmd'] = 'eventTypeList';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return false;
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
			return false;
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
			return false;
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
			return false;
		}
	}
	public function setList($setId='', $value='',$searchFormat='') {
		$data['setId'] = $setId;
		$data['searchFormat'] = $searchFormat;
		$data['value'] = $value;
		$data['cmd'] = 'setList';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return false;
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
			return false;
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
			return false;
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
			return false;
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
			return false;
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
			return false;
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
		$data['cmd'] = 'imageList';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return false;
		}
	}
	public function collectionAdd($name, $code) {
		$data['name'] = $name;
		$data['code'] = $code;
		$data['cmd'] = 'collectionAdd';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return false;
		}
	}
	public function imageAddToCollection($imageId, $code) {
		$data['imageId'] = $imageId;
		$data['code'] = $code;
		$data['cmd'] = 'imageAddToCollection';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return false;
		}
	}
	public function imageAddBarcode($imageId, $barcode) {
		$data['imageId'] = $imageId;
		$data['params'] = json_encode(array('barcode' => $barcode));
		$data['cmd'] = 'imageUpdate';
		$result = $this->CURL($this->server . '/api.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return false;
		}
	}
	public function populateOcrProcessQueue($imageIds) {
		$data['imageId'] = json_encode($imageIds);
		$data['cmd'] = 'populateOcrProcessQueue';
		$result = $this->CURL($this->server . '/backup_services.php', $data);
		$result = json_decode($result, true);
		if($result['success'] == true) {
			return $result;
		} else {
			$this->lastError['code'] = $result['error']['code'];
			$this->lastError['msg'] = $result['error']['msg'];
			return false;
		}
	}
	public function getLastError() {
		return $this->lastError;
	}
}
?>