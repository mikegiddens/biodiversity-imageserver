<?php

class StorageDevice {
	
	public $db, $record, $devices;
	
	public function __construct($db = null) {
		$this->db = $db;
		$this->storageDeviceGetAll();
		$this->record['defaultStorage'] = 0;
	}
	
	public function storageDeviceGetAll() {
		$query = "SELECT * FROM `storageDevice`";
		$array = $this->db->query($query);
		$cnt = 0;
		while($item = $array->fetch_object()) {
			$this->devices[$cnt]['storageDeviceId']  =  $item->storageDeviceId;
			$this->devices[$cnt]['name']  =  $item->name;
			$this->devices[$cnt]['description']  =  $item->description;
			$this->devices[$cnt]['type']  =  $item->type;
			$this->devices[$cnt]['baseUrl']  =  $item->baseUrl;
			$this->devices[$cnt]['basePath']  =  $item->basePath;
			$this->devices[$cnt]['user']  =  $item->user;
			$this->devices[$cnt]['pw']  =  $item->pw;
			$this->devices[$cnt]['key']  =  $item->key;
			$this->devices[$cnt]['active']  =  $item->active;
			$this->devices[$cnt]['defaultStorage']  =  $item->defaultStorage;
			$this->devices[$cnt]['extra2']  =  $item->extra2;
			$cnt++;
		}
	}
	
	public function storageDeviceSetProperty( $field, $value ) {
		$this->record[$field] = $value;
		return(true);
	}
	
	public function storageDeviceSetAll( $data ) {
		foreach($data as $key => $value) {
			$this->record[$key] = $value;
		}
	}
	
	public function storageDeviceGetProperty( $field ) {
		if (isset($this->record[$field])) {
			return( $this->record[$field] );
		} else {
			return(false);
		}
	}
	
	public function storageDeviceSave() {
		$query = sprintf("INSERT IGNORE INTO `storageDevice` SET `name` = '%s', `description` = '%s', `type` = '%s', `baseUrl` = '%s', `basePath` = '%s', `user` = '%s', `pw` = '%s', `key` = '%s', `active` = '%s', `defaultStorage` = '%s', `extra2` = '%s' ;"
		, mysql_escape_string($this->storageDeviceGetProperty('name'))
		, mysql_escape_string($this->storageDeviceGetProperty('description'))
		, mysql_escape_string($this->storageDeviceGetProperty('type'))
		, mysql_escape_string($this->storageDeviceGetProperty('baseUrl'))
		, mysql_escape_string($this->storageDeviceGetProperty('basePath'))
		, mysql_escape_string($this->storageDeviceGetProperty('user'))
		, mysql_escape_string($this->storageDeviceGetProperty('pw'))
		, mysql_escape_string($this->storageDeviceGetProperty('key'))
		, mysql_escape_string($this->storageDeviceGetProperty('active'))
		, mysql_escape_string($this->storageDeviceGetProperty('defaultStorage'))
		, mysql_escape_string($this->storageDeviceGetProperty('extra2'))
		);
		if($this->db->query($query)) {
			$this->storageDeviceGetAll();
			return($this->db->insert_id);
		} else {
			return (false);
		}
	}
	
	public function storageDeviceExists($storageDeviceId) {
		if(is_array($this->devices)) {
			foreach($this->devices as $device) {
				if($device['storageDeviceId'] == $storageDeviceId) {
					return true;
				}
			}
			return false;
		} else {
			return false;
		}
	}
	
	public function storageDeviceGet($storageDeviceId) {
		if(is_array($this->devices)) {
			foreach($this->devices as $device) {
				if($device['storageDeviceId'] == $storageDeviceId) {
					return $device;
				}
			}
			return false;
		} else {
			return false;
		}
	}
	
	public function storageDeviceGetType($storageDeviceId) {
		if(is_array($this->devices)) {
			foreach($this->devices as $device) {
				if($device['storageDeviceId'] == $storageDeviceId) {
					return $device['type'];
				}
			}
			return false;
		} else {
			return false;
		}
	}
	
	public function storageDeviceGetDefault() {
		if(is_array($this->devices)) {
			foreach($this->devices as $device) {
				if($device['defaultStorage'] == 1) {
					return $device['storageDeviceId'];
				}
			}
			return false;
		} else {
			return false;
		}
	}
	
	public function storageDeviceSetDefault($storageDeviceId) {
		if(($storageDeviceId != '') && ($this->storageDeviceExists($storageDeviceId))) {
			$query = "UPDATE `storageDevice` SET `defaultStorage` = 0";
			$this->db->query($query);
			$query = sprintf("UPDATE `storageDevice` SET `defaultStorage` = 1 WHERE `storageDeviceId` = '%s'", mysql_escape_string($storageDeviceId));
			$this->db->query($query);
			return true;
		} else {
			return false;
		}
	}
	
	public function storageDeviceStore($tmpFile, $storageDeviceId, $storageFileName, $storageFilePath='', $remoteAccesskey=0) {
		$img = new Image($this->db);
		if($tmpFile!='' && $storageDeviceId!='' && $storageFileName!='' && $this->storageDeviceExists($storageDeviceId)) {
			$device = $this->storageDeviceGet($storageDeviceId);
			switch(strtolower($this->storageDeviceGetType($storageDeviceId))) {
				case 's3':
					$amazon = new AmazonS3(array('key' => $device['pw'],'secret' => $device['key']));
					$storageFilePath1 = substr($storageFilePath,0,1)=='/' ? substr($storageFilePath,1,strlen($storageFilePath)-1) : $storageFilePath;
					$response = $amazon->create_object ($device['basePath'], $storageFilePath1 . '/' .  $storageFileName, array('fileUpload' => $tmpFile,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
					if($response->isOK()) {
						$result['imageId'] = $img->imageGetId($storageFileName, $storageFilePath, 1);
						if(!$result['imageId']) {
							$img->imageSetProperty('filename',$storageFileName);
							$img->imageSetProperty('storageDeviceId', 1);
							$img->imageSetProperty('path', $storageFilePath);
							$img->imageSetProperty('originalFilename', $storageFileName);
							$img->imageSetProperty('remoteAccessKey', $remoteAccesskey);
							$img->save();
							$result['imageId'] = $img->imageGetId($storageFileName, $storageFilePath, 1);
						}
						$result['success'] = true;
						return $result;
					} else {
						$result['success'] = false;
						return $result;
					}
					break;
				case 'local':
					$img->imageMkdirRecursive($device['basePath'].$storageFilePath);
					$fp = fopen($tmpFile, "r");
					$response = file_put_contents($device['basePath'].$storageFilePath . '/' .  $storageFileName, $fp);
					fclose($fp);
					if($response) {
						$result['imageId'] = $img->imageGetId($storageFileName, $storageFilePath, 2);
						if(!$result['imageId']) {
							$img->imageSetProperty('filename',$storageFileName);
							$img->imageSetProperty('storageDeviceId', 2);
							$img->imageSetProperty('path', $storageFilePath);
							$img->imageSetProperty('originalFilename', $storageFileName);
							$img->imageSetProperty('remoteAccessKey', $remoteAccesskey);
							$img->save();
							$result['imageId'] = $img->imageGetId($storageFileName, $storageFilePath, 2);
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
		
	public function storageDeviceDelete($storageDeviceId, $storageFileName, $storageFilePath='') {
		if($storageDeviceId == '' || $storageFileName == '') {
			return false;
		} else {
			$device = $this->storageDeviceGet($storageDeviceId);
			switch(strtolower($this->storageDeviceGetType($storageDeviceId))) {
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
	
	public function moveExistingImage($imageId, $newStorageId, $newImagePath) {
		$img = new Image($this->db);
		if($imageId == '' || $newStorageId == '' || $newImagePath == '' || !$img->field_exists($imageId) || !$this->storageDeviceExists($newStorageId)) {
			return false;
		}
		if($img->load_by_id($imageId)) {
			if(($img->get('storageDeviceId') == $newStorageId) && ($img->get('path') == $newImagePath)) {
				return true;
			} else {
				$device1 = $this->get($img->get('storageDeviceId'));
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
							$this->delete($img->get('storageDeviceId'), $img->get('filename'), $img->get('path'));
							$img->imageSetProperty('storageDeviceId', $newStorageId);
							$img->imageSetProperty('path', $newImagePath);
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
								$img->imageMkdirRecursive($device2['basePath'].$newImagePath);
								$fp = fopen($tmpImage, "r");
								$response = file_put_contents($device2['basePath'].$newImagePath . '/' .  $img->get('filename'), $fp);
								fclose($fp);
								unlink($tmpImage);
								break;
							
							case 'local':
								$source = $device1['basePath'] . $img->get('path') . '/' . $img->get('filename');
								$img->imageMkdirRecursive($device2['basePath'].$newImagePath);
								$fp = fopen($source, "r");
								$response = file_put_contents($device2['basePath'].$newImagePath . '/' .  $img->get('filename'), $fp);
								fclose($fp);
								break;
						}
						$this->delete($img->get('storageDeviceId'), $img->get('filename'), $img->get('path'));
						$img->imageSetProperty('storageDeviceId', $newStorageId);
						$img->imageSetProperty('path', $newImagePath);
						$img->save();
						return true;
						break;
				}
			}
		} else {
			return false;
		}
	}
	
	public function fileExists($storageDeviceId, $key) {
		if($storageDeviceId == '' || $key == '') return false;
		$device = $this->get($storageDeviceId);
		if(!$device) return false;
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
	
	public function fileGetContents($storageDeviceId, $key) {
		if($storageDeviceId == '' || $key == '') return false;
		$device = $this->get($storageDeviceId);
		if(!$device) return false;
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
	
	public function fileDownload($storageDeviceId , $key) {
		if($storageDeviceId == '' || $key == '') return false;
		$device = $this->get($storageDeviceId);
		if(!$device) return false;
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
				$tmpPath = '/tmp/'.$tmp1;
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
	
	public function createFile_Data($storageDeviceId, $key, $data) {
		if($storageDeviceId == '' || $key == '' || $data == '') return false;
		$device = $this->get($storageDeviceId);
		if(!$device) return false;
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