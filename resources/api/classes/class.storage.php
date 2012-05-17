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
		$img = new Image($this->db);
		if($tmpFile!='' && $storage_id!='' && $storageFileName!='' && $this->exists($storage_id)) {
			$device = $this->get($storage_id);
			switch(strtolower($this->getType($storage_id))) {
				case 's3':
					$amazon = new AmazonS3(array('key' => $device['pw'],'secret' => $device['key']));
					$storageFilePath1 = substr($storageFilePath,0,1)=='/' ? substr($storageFilePath,1,strlen($storageFilePath)-1) : $storageFilePath;
					$response = $amazon->create_object ($device['basePath'], $storageFilePath1 . '/' .  $storageFileName, array('fileUpload' => $tmpFile,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
					if($response->isOK()) {
						$result['image_id'] = $img->getImageId($storageFileName, $storageFilePath, 1);
						if(!$result['image_id']) {
							$img->set('filename',$storageFileName);
							$img->set('storage_id', 1);
							$img->set('path', $storageFilePath);
							$img->set('originalFilename', $storageFileName);
							$img->save();
							$result['image_id'] = $img->getImageId($storageFileName, $storageFilePath, 1);
						}
						$result['success'] = true;
						return $result;
					} else {
						$result['success'] = false;
						return $result;
					}
					break;
				case 'local':
					$img->mkdir_recursive($device['basePath'].$storageFilePath);
					$fp = fopen($tmpFile, "r");
					$response = file_put_contents($device['basePath'].$storageFilePath . '/' .  $storageFileName, $fp);
					fclose($fp);
					if($response) {
						$result['image_id'] = $img->getImageId($storageFileName, $storageFilePath, 2);
						if(!$result['image_id']) {
							$img->set('filename',$storageFileName);
							$img->set('storage_id', 2);
							$img->set('path', $storageFilePath);
							$img->set('originalFilename', $storageFileName);
							$img->save();
							$result['image_id'] = $img->getImageId($storageFileName, $storageFilePath, 2);
						}
						$result['success'] = true;
						return $result;
					} else {
						$result['success'] = false;
						return $result;
					}
					break;
				default:
					$result['success'] = false;
					return $result;
					break;
			}
		} else {
			$result['success'] = false;
			return $result;
		}
	}
		
	public function delete($storage_id, $storageFileName, $storageFilePath='') {
		if($storage_id == '' || $storageFileName == '') {
			return false;
		} else {
			$device = $this->get($storage_id);
			switch(strtolower($this->getType($storage_id))) {
				case 's3':
					$amazon = new AmazonS3(array('key' => $device['pw'],'secret' => $device['key']));
					$storageFilePath1 = substr($storageFilePath,0,1)=='/' ? substr($storageFilePath,1,strlen($storageFilePath)-1) : $storageFilePath;
					$response = $amazon->delete_object ($device['basePath'], $storageFilePath1 . '/' .  $storageFileName);
					break;
				
				case 'local':
					@unlink($device['basePath'].$storageFilePath . '/' .  $storageFileName);
					break;
					
				default:
					return false;
					break;
			}
			return true;
		}
	}
	
	public function moveExistingImage($image_id, $newStorageId, $newImagePath) {
		$img = new Image($this->db);
		if($image_id == '' || $newStorageId == '' || $newImagePath == '' || !$img->field_exists($image_id) || !$this->exists($newStorageId)) {
			return false;
		}
		if($img->load_by_id($image_id)) {
			if(($img->get('storage_id') == $newStorageId) && ($img->get('path') == $newImagePath)) {
				return true;
			} else {
				$device1 = $this->get($img->get('storage_id'));
				$device2 = $this->get($newStorageId);
				
				switch(strtolower($device2['type'])) {
					case 's3':
						$amazon = new AmazonS3(array('key' => $device2['pw'],'secret' => $device2['key']));
						switch(strtolower($device1['type'])) {
							case 's3':
								$tmp = $img->get('path');
								$tmp = substr($tmp,0,1)=='/' ? substr($tmp,1,strlen($tmp)-1) : $tmp;
								$source = $tmp . '/' . $img->get('filename');
								$tmpImage = $img->get('filename');
								$fp = fopen($tmpImage, "w+b");
								$amazon->get_object($device1['basePath'], $source, array('fileDownload' => $tmpImage));
								fclose($fp);
								$tmp = $newImagePath;
								$tmp = substr($tmp,0,1)=='/' ? substr($tmp,1,strlen($tmp)-1) : $tmp;
								$response = $amazon->create_object ($device2['basePath'], $tmp . '/' .  $img->get('filename'), array('fileUpload' => $tmpImage,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
								unlink($tmpImage);
								break;
							
							case 'local':
								$source = $device1['basePath'] . $img->get('path') . '/' . $img->get('filename');
								$tmp = $newImagePath;
								$tmp = substr($tmp,0,1)=='/' ? substr($tmp,1,strlen($tmp)-1) : $tmp;
								$response = $amazon->create_object ($device2['basePath'], $tmp . '/' .  $img->get('filename'), array('fileUpload' => $source,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
								break;
						}
						if($response->isOK()) {
							$this->delete($img->get('storage_id'), $img->get('filename'), $img->get('path'));
							$img->set('storage_id', $newStorageId);
							$img->set('path', $newImagePath);
							$img->save();
							return true;
						} else {
							return false;
						}
						break;
					
					case 'local':
						switch(strtolower($device1['type'])) {
							case 's3':
								$amazon = new AmazonS3(array('key' => $device1['pw'],'secret' => $device1['key']));
								$tmp = $img->get('path');
								$tmp = substr($tmp,0,1)=='/' ? substr($tmp,1,strlen($tmp)-1) : $tmp;
								$source = $tmp . '/' . $img->get('filename');
								$tmpImage = $img->get('filename');
								$fp = fopen($tmpImage, "w+b");
								$amazon->get_object($device1['basePath'], $source, array('fileDownload' => $tmpImage));
								fclose($fp);
								$img->mkdir_recursive($device2['basePath'].$newImagePath);
								$fp = fopen($tmpImage, "r");
								$response = file_put_contents($device2['basePath'].$newImagePath . '/' .  $img->get('filename'), $fp);
								fclose($fp);
								unlink($tmpImage);
								break;
							
							case 'local':
								$source = $device1['basePath'] . $img->get('path') . '/' . $img->get('filename');
								$img->mkdir_recursive($device2['basePath'].$newImagePath);
								$fp = fopen($source, "r");
								$response = file_put_contents($device2['basePath'].$newImagePath . '/' .  $img->get('filename'), $fp);
								fclose($fp);
								break;
						}
						$this->delete($img->get('storage_id'), $img->get('filename'), $img->get('path'));
						$img->set('storage_id', $newStorageId);
						$img->set('path', $newImagePath);
						$img->save();
						return true;
						break;
				}
			}
		} else {
			return false;
		}
	}
	
	public function fileExists($storage_id, $key) {
		if($storage_id == '' || $key == '') return false;
		$device = $this->get($storage_id);
		switch(strtolower($device['type'])) {
			case 's3':
				$key = substr($key,0,1)=='/' ? substr($key,1,strlen($key)-1) : $key;
				$amazon = new AmazonS3(array('key' => $device['pw'],'secret' => $device['key']));
				if($amazon->if_object_exists($device['basePath'], $key)) {
					return true;
				} else {
					return false;
				}
				break;
				
			case 'local':
				$key = substr($key,0,1)!='/' ? '/'.$key : $key;
				if(@file_exists($device['basePath'].$key)) {
					return true;
				} else {
					return false;
				}
				break;
				
			default:
				return false;
				break;
		}
	}
	
	public function fileGetContents($storage_id, $key) {
		if($storage_id == '' || $key == '') return false;
		$device = $this->get($storage_id);
		switch(strtolower($device['type'])) {
			case 's3':
				$amazon = new AmazonS3(array('key' => $device['pw'],'secret' => $device['key']));
				$key = substr($key,0,1)=='/' ? substr($key,1,strlen($key)-1) : $key;
				$tmpPath = uniqid('tmpFile');
				$fp = fopen($tmpPath, "w+b");
				$response = $amazon->get_object($device['basePath'], $key, array('fileDownload' => $tmpPath));
				fclose($fp);
				if($response->isOK()) {
					$data = @file_get_contents($tmpPath);
					@unlink($tmpPath);
					return $data;
				} else {
					@unlink($tmpPath);
					return false;
				}
				break;
				
			case 'local':
				$key = substr($key,0,1)!='/' ? '/'.$key : $key;
				$data = @file_get_contents($device['basePath'] . $key);
				if($data) {
					return $data;
				} else {
					return false;
				}
				break;
				
			default:
				return false;
				break;
		}
	}
	
	public function fileDownload($storage_id , $key) {
		if($storage_id == '' || $key == '') return false;
		$device = $this->get($storage_id);
		switch(strtolower($device['type'])) {
			case 's3':
				$amazon = new AmazonS3(array('key' => $device['pw'],'secret' => $device['key']));
				$key = substr($key,0,1)=='/' ? substr($key,1,strlen($key)-1) : $key;
				$tmp = explode("/" , $key);
				if(is_array($tmp)) {
					$tmp1 = $tmp[count($tmp)-1];
				} else {
					$tmp1 = $tmp;
				}
				$tmpPath = '/tmp/'.uniqid('d').$tmp1;
				$fp = fopen($tmpPath, "w+b");
				$response = $amazon->get_object($device['basePath'], $key, array('fileDownload' => $tmpPath));
				fclose($fp);
				if($response->isOK()) {
					return $tmpPath;
				} else {
					@unlink($tmpPath);
					return false;
				}
				break;
				
			case 'local':
				$key = substr($key,0,1)!='/' ? '/'.$key : $key;
				return ($device['basePath'] . $key);
				
			default:
				return false;
				break;
		}
	}
	
	public function createFile_Data($storage_id, $key, $data) {
		if($storage_id == '' || $key == '' || $data == '') return false;
		$device = $this->get($storage_id);
		switch(strtolower($device['type'])) {
			case 's3':
				$amazon = new AmazonS3(array('key' => $device['pw'],'secret' => $device['key']));
				$key = substr($key,0,1)=='/' ? substr($key,1,strlen($key)-1) : $key;
				$tmp = explode("/" , $key);
				if(is_array($tmp)) {
					$tmp1 = $tmp[count($tmp)-1];
				} else {
					$tmp1 = $tmp;
				}
				$tmpPath = '/tmp/'.uniqid('u').$tmp1;
				@file_put_contents($tmpPath, $data);
				$response = $amazon->create_object ($device['basePath'], $key, array('fileUpload' => $tmpPath,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
				@unlink($tmpPath);
				if($response->isOK()) {
					return true;
				} else {
					return false;
				}
				break;
				
			case 'local':
				$key = substr($key,0,1)!='/' ? '/'.$key : $key;
				if(@file_put_contents($device['basePath'] . $key, $data)) {
					return true;
				} else {
					return false;
				}
				break;
				
			default:
				return false;
				break;
		}
	}
	
}

?>