<?php

class phpBIS
{
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
	public function createObject($path) {
		$ext = @pathinfo($path,PATHINFO_EXTENSION);
		$mime = (@strtolower($ext) == 'jpg') ? 'image/jpeg' : 'image/' . @strtolower($ext);
		$data = array('key' => $this->key, 'file' => '@'.$path.';type='.$mime, 'cmd' => 'createObject');
		$res = $this->CURL($this->server . '/api.php',$data);
		return $res;
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
		$res = $this->CURL($this->server . '/api.php',$data);
		return $res;
	}
	public function deleteImageAttribute($imageID, $valueID) {
		$data = array();
		$data['imageID'] = $imageID;
		$data['valueID'] = $valueID;
		$data['cmd'] = 'delete_image_attribute';
		$res = $this->CURL($this->server . '/api.php',$data);
		return $res;
	}
	public function addCategory($value) {
		$data = array();
		$data['value'] = $value;
		$data['cmd'] = 'add_category';
		$res = $this->CURL($this->server . '/api.php',$data);
		return $res;
	}
	public function renameCategory($valueID,$value) {
		$data = array();
		$data['valueID'] = $valueID;
		$data['value'] = $value;
		$data['cmd'] = 'rename_category';
		$res = $this->CURL($this->server . '/api.php',$data);
		return $res;
	}
	public function deleteCategory($categoryID) {
		$data = array();
		$data['categoryID'] = $categoryID;
		$data['cmd'] = 'delete_category';
		$res = $this->CURL($this->server . '/api.php',$data);
		return $res;
	}
	public function addAttribute($categoryID,$value) {
		$data = array();
		$data['categoryID'] = $categoryID;
		$data['value'] = $value;
		$data['cmd'] = 'add_attribute';
		$res = $this->CURL($this->server . '/api.php',$data);
		return $res;
	}
	public function renameAttribute($valueID,$value) {
		$data = array();
		$data['valueID'] = $valueID;
		$data['value'] = $value;
		$data['cmd'] = 'rename_attribute';
		$res = $this->CURL($this->server . '/api.php',$data);
		return $res;
	}
	public function deleteAttribute($valueID) {
		$data = array();
		$data['valueID'] = $valueID;
		$data['cmd'] = 'delete_attribute';
		$res = $this->CURL($this->server . '/api.php',$data);
		return $res;
	}
}
?>