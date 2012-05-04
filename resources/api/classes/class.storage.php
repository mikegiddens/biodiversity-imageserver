<?php

class Storage {
	
	public $db, $record, $devices;
	
	public function __construct($db = null) {
		$this->db = $db;
		$this->getAllDevices();
	}
	
	public function getAllDevices() {
		$query = "SELECT * FROM `storage_device`";
		$array = $this->db->query($query);
		$cnt = 0;
		while($item = $array->fetch_object()) {
			$this->devices[$cnt]['storage_id']  =  $item->storage_id;
			$this->devices[$cnt]['name']  =  $item->name;
			$this->devices[$cnt]['description']  =  $item->description;
			$this->devices[$cnt]['type']  =  $item->type;
			$this->devices[$cnt]['baseUrl']  =  $item->baseUrl;
			$this->devices[$cnt]['basePath']  =  $item->basePath;
			$this->devices[$cnt]['user']  =  $item->user;
			$this->devices[$cnt]['pw']  =  $item->pw;
			$this->devices[$cnt]['key']  =  $item->key;
			$this->devices[$cnt]['active']  =  $item->active;
			$this->devices[$cnt]['extra1']  =  $item->extra1;
			$this->devices[$cnt]['extra2']  =  $item->extra2;
			$cnt++;
		}
	}
	
	public function set( $field, $value ) {
		$this->record[$field] = $value;
		return(true);
	}
	
	public function set_all( $data ) {
		foreach($data as $key => $value) {
			$this->record[$key] = $value;
		}
	}
	
	public function fetch( $field ) {
		if (isset($this->record[$field])) {
			return( $this->record[$field] );
		} else {
			return(false);
		}
	}
	
	public function save() {
		$query = sprintf("INSERT IGNORE INTO `storage_device` SET `name` = '%s', `description` = '%s', `type` = '%s', `baseUrl` = '%s', `basePath` = '%s', `user` = '%s', `pw` = '%s', `key` = '%s', `active` = '%s', `extra1` = '%s', `extra2` = '%s' ;"
		, mysql_escape_string($this->fetch('name'))
		, mysql_escape_string($this->fetch('description'))
		, mysql_escape_string($this->fetch('type'))
		, mysql_escape_string($this->fetch('baseUrl'))
		, mysql_escape_string($this->fetch('basePath'))
		, mysql_escape_string($this->fetch('user'))
		, mysql_escape_string($this->fetch('pw'))
		, mysql_escape_string($this->fetch('key'))
		, mysql_escape_string($this->fetch('active'))
		, mysql_escape_string($this->fetch('extra1'))
		, mysql_escape_string($this->fetch('extra2'))
		);
		if($this->db->query($query)) {
			return(true);
		} else {
			return (false);
		}
	}
	
	public function exists($storage_id) {
		if(is_array($this->devices)) {
			foreach($this->devices as $device) {
				if($device['storage_id'] == $storage_id) {
					return true;
				}
			}
			return false;
		} else {
			return false;
		}
	}
	
	public function get($storage_id) {
		if(is_array($this->devices)) {
			foreach($this->devices as $device) {
				if($device['storage_id'] == $storage_id) {
					return $device;
				}
			}
			return false;
		} else {
			return false;
		}
	}
	
	public function getType($storage_id) {
		if(is_array($this->devices)) {
			foreach($this->devices as $device) {
				if($device['storage_id'] == $storage_id) {
					return $device['type'];
				}
			}
			return false;
		} else {
			return false;
		}
	}
	
	public function store($tmpFile, $storage_id, $storageFileName, $storageFilePath='') {
		if($tmpFile!='' && $storage_id!='' && $storageFileName!='' && $this->exists($storage_id)) {
			$device = $this->get($storage_id);
			switch(strtolower($this->getType($storage_id))) {
				case 's3':
					$amazon = new AmazonS3(array('key' => $device['pw'],'secret' => $device['key']));
					$response = $amazon->create_object ($device['basePath'], $storageFilePath . '/' .  $storageFileName, array('fileUpload' => $tmpFile,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
					if($response->isOK()) {
						return true;
					} else {
						return false;
					}
					break;
				case 'local':
					$img = new Image();
					$img->mkdir_recursive($device['basePath'].'/'.$storageFilePath);
					$fp = fopen($tmpFile, "r");
					$response = file_put_contents($device['basePath'].'/'.$storageFilePath . '/' .  $storageFileName, $fp);
					fclose($fp);
					if($response) {
						return true;
					} else {
						return false;
					}
					break;
				default:
					return false;
					break;
			}
		} else {
			return false;
		}
	}	
}
?>