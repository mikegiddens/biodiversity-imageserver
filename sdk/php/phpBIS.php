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
	public function addImage($source, $storageId, $destinationPath) {
		$stream = file_get_contents($source);
		if((strpos($source, '/')) !== false) {
			$source = explode('/', $source);
			$filename = $source[count($source)-1];
		} else {
			$filename = $source;
		}
		$data['key'] = $this->key;
		$data['imagePath'] = $destinationPath;
		$data['storage_id'] = $storageId;
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
	public function addExistingImage($storageId, $path, $filename) {
		$data['storage_id'] = $storageId;
		$data['imagePath'] = $path;
		$data['filename'] = $filename;
		$data['cmd'] = 'addExistingImage';
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
	public function addImageFromURL($url, $storageId, $path) {
		$data['url'] = $url;
		$data['storage_id'] = $storageId;
		$data['imagePath'] = (trim($path) != '') ? $path : '';
		$data['key'] = $this->key;
		$data['cmd'] = 'addImageFromURL';
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
	public function getURL($type, $code, $size) {
		$data = array();
		switch($type) {
			case 'ID':
				$data['image_id'] = $code;
				break;
			case 'BARCODE':
				$data['barcode'] = $code;
				break;
		}
		$data['cmd'] = 'getImageUrl';
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
	public function addImageAttribute($imageID, $valueID, $categoryID) {
		$data = array();
		$data['imageID'] = $imageID;
		$data['valueID'] = $valueID;
		$data['categoryID'] = $categoryID;
		$data['cmd'] = 'add_image_attribute';
		if(PHP_SAPI == 'cli'){
			$data['interface'] = 'cli';
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
	public function deleteImageAttribute($imageID, $valueID) {
		$data = array();
		$data['imageID'] = $imageID;
		$data['valueID'] = $valueID;
		$data['cmd'] = 'delete_image_attribute';
		if(PHP_SAPI == 'cli'){
			$data['interface'] = 'cli';
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
	public function listImageAttributes($imageID) {
		$data['imageID'] = $imageID;
		$data['cmd'] = 'list_image_attributes';
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
	public function addCategory($value) {
		$data = array();
		$data['value'] = $value;
		$data['cmd'] = 'add_category';
		if(PHP_SAPI == 'cli'){
			$data['interface'] = 'cli';
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
	public function renameCategory($valueID,$value) {
		$data = array();
		$data['valueID'] = $valueID;
		$data['value'] = $value;
		$data['cmd'] = 'rename_category';
		if(PHP_SAPI == 'cli'){
			$data['interface'] = 'cli';
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
	public function deleteCategory($categoryID) {
		$data = array();
		$data['categoryID'] = $categoryID;
		$data['cmd'] = 'delete_category';
		if(PHP_SAPI == 'cli'){
			$data['interface'] = 'cli';
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
	public function listCategories() {
		$data['cmd'] = 'list_categories';
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
	public function addAttribute($categoryID,$value) {
		$data = array();
		$data['categoryID'] = $categoryID;
		$data['value'] = $value;
		$data['cmd'] = 'add_attribute';
		if(PHP_SAPI == 'cli'){
			$data['interface'] = 'cli';
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
	public function renameAttribute($valueID,$value) {
		$data = array();
		$data['valueID'] = $valueID;
		$data['value'] = $value;
		$data['cmd'] = 'rename_attribute';
		if(PHP_SAPI == 'cli'){
			$data['interface'] = 'cli';
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
	public function deleteAttribute($valueID) {
		$data = array();
		$data['valueID'] = $valueID;
		$data['cmd'] = 'delete_attribute';
		if(PHP_SAPI == 'cli'){
			$data['interface'] = 'cli';
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
	public function list_attributes($categoryID) {
		$data['categoryID'] = $categoryID;
		$data['cmd'] = 'list_attributes';
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
	public function addEvent($eventId, $title, $eventTypeId, $geoId, $description) {
		$data['eventId'] = (trim($eventId) != '') ? $eventId : '';
		$data['title'] = (trim($title) != '' ) ? $title : '';
		$data['eventTypeId'] = (trim($eventTypeId) != '') ? $eventTypeId : '';
		$data['geoId'] = (trim($geoId) != '') ? $geoId : '';
		$data['description'] = (trim($description) != '') ? $description : '';
		$data['cmd'] = 'addEvent';
		if(PHP_SAPI == 'cli'){
			$data['interface'] = 'cli';
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
	public function listEvents($start, $limit, $eventId, $eventTypeId, $geoId, $field, $value) {
		$data['start'] = (trim($start) != '') ? $start : '';
		$data['limit'] = (trim($limit) != '') ? $limit : '';
		$data['eventId'] = (trim($eventId) != '') ? $eventId : '';
		$data['eventTypeId'] = (trim($eventTypeId) != '') ? $eventTypeId : '';
		$data['geoId'] = (trim($geoId) != '') ? $geoId : '';
		$data['field'] = (trim($field) != '') ? $field : '';
		$data['value'] = (trim($value) != '') ? $value : '';
		$data['cmd'] = 'listEvents';
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
	public function deleteEvent($eventId) {
		$data['eventId'] = $eventId;
		$data['cmd'] = 'deleteEvent';
		if(PHP_SAPI == 'cli'){
			$data['interface'] = 'cli';
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
	public function addImageEvent($eventId, $imageId) {
		$data['eventId'] = $eventId;
		$data['imageId'] = $imageId;
		$data['cmd'] = 'addImageEvent';
		if(PHP_SAPI == 'cli'){
			$data['interface'] = 'cli';
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
	public function deleteImageEvent($eventId, $imageId) {
		$data['eventId'] = $eventId;
		$data['imageId'] = $imageId;
		$data['cmd'] = 'deleteImageEvent';
		if(PHP_SAPI == 'cli'){
			$data['interface'] = 'cli';
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
	public function addEventType($eventTypeId, $title, $description) {
		$data['eventTypeId'] = $eventTypeId;
		$data['title'] = $title;
		$data['description'] = $description;
		$data['cmd'] = 'addEventType';
		if(PHP_SAPI == 'cli'){
			$data['interface'] = 'cli';
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
	public function listEventTypes($start, $limit, $eventTypeId, $title, $field, $value) {
		$data['start'] = (trim($start) != '') ? $start : '';
		$data['limit'] = (trim($limit) != '') ? $limit : '';
		$data['eventTypeId'] = (trim($eventTypeId) != '') ? $eventTypeId : '';
		$data['title'] = (trim($title) != '') ? $title : '';
		$data['field'] = (trim($field) != '') ? $field : '';
		$data['value'] = (trim($value) != '') ? $value : '';
		$data['cmd'] = 'listEventTypes';
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
	public function deleteEventType($eventTypeId) {
		$data['eventTypeId'] = $eventTypeId;
		$data['cmd'] = 'deleteEventType';
		if(PHP_SAPI == 'cli'){
			$data['interface'] = 'cli';
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
	public function addSet($name, $description) {
		$data['name'] = $name;
		$data['description'] = (trim($description) != '') ? $description : '';
		$data['cmd'] = 'addSet';
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
	public function editSet($sId, $name, $description) {
		$data['sId'] = $sId;
		$data['name'] = $name;
		$data['description'] = (trim($description) != '') ? $description : '';
		$data['cmd'] = 'editSet';
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
	public function deleteSet($sId) {
		$data['sId'] = $sId;
		$data['cmd'] = 'deleteSet';
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
	public function listSets() {
		$data['cmd'] = 'listSets';
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
	public function addSetValue($sId, $valueId, $rank) {
		$data['sId'] = $sId;
		$data['valueId'] = $valueId;
		$data['rank'] = (trim($rank) != '') ? $rank : 0;
		$data['cmd'] = 'addSetValue';
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
	public function editSetValue($id, $sId, $valueId, $rank) {
		$data['id'] = $id;
		$data['sId'] = $sId;
		$data['valueId'] = $valueId;
		$data['rank'] = (trim($rank) != '') ? $rank : '';
		$data['cmd'] = 'editSetValue';
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
	public function deleteSetValue($id) {
		$data['id'] = $id;
		$data['cmd'] = 'deleteSetValue';
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
	public function listImageBySet($sId) {
		$data['sId'] = (trim($sId) != '') ? $sId : '';
		$data['cmd'] = 'listImageBySet';
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
	public function listImages($start, $limit, $order, $showOCR, $filter, $image_id, $field, $value, $sort, $dir, $code1, $characters, $browse, $search_value, $search_type) {
		$data['start'] = (trim($start) != '') ? $start : 0;
		$data['limit'] = (trim($limit) != '') ? $limit : 100;
		$data['order'] = $order;
		$data['showOCR'] = (trim($showOCR) != '') ? $showOCR : false;
		$data['filter'] = $filter;
		$data['image_id'] = $image_id;
		$data['field'] = $field;
		$data['value'] = $value;
		$data['sort'] = $sort;
		$data['dir'] = (trim($sort) != '') ? ((trim($dir) != '') ? $dir : 'ASC') : '';
		$data['code1'] = (trim($code1) != '') ? $code1 : '';
		$data['characters'] = $characters;
		$data['browse'] = $browse;
		$data['search_value'] = $search_value;
		$data['search_type'] = $search_type;
		$data['output'] = 'JSON';
		$data['cmd'] = 'images';
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
	public function getLastError() {
		return $this->lastError;
	}
}
?>