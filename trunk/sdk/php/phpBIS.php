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
	public function addImage($path) {
		$ext = @pathinfo($path,PATHINFO_EXTENSION);
		$mime = (@strtolower($ext) == 'jpg') ? 'image/jpeg' : 'image/' . @strtolower($ext);
		$data = array('key' => $this->key, 'file' => '@'.$path.';type='.$mime, 'cmd' => 'addImage');
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
		return file_get_contents($res);
	}
	public function addImageAttribute($imageID, $valueID, $categoryID) {
		$data = array();
		$data['imageID'] = $imageID;
		$data['valueID'] = $valueID;
		$data['categoryID'] = $categoryID;
		$data['cmd'] = 'add_image_attribute';
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
		$result = $this->CURL($this->server . '/api.php',$data);
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
	public function deleteEvent($eventId) {
		$data['eventId'] = $eventId;
		$data['cmd'] = 'deleteEvent';
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
	public function getLastError() {
		return $this->lastError;
	}
}
?>