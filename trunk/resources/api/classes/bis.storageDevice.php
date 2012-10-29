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
			$this->devices[$cnt]['userName']  =  $item->userName;
			$this->devices[$cnt]['password']  =  $item->password;
			$this->devices[$cnt]['key']  =  $item->key;
			$this->devices[$cnt]['active']  =  ($item->active) ? true : false;
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
		$query = sprintf("INSERT IGNORE INTO `storageDevice` SET `name` = '%s', `description` = '%s', `type` = '%s', `baseUrl` = '%s', `basePath` = '%s', `userName` = '%s', `password` = '%s', `key` = '%s', `active` = '%s', `defaultStorage` = '%s', `extra2` = '%s' ;"
		, mysql_escape_string($this->storageDeviceGetProperty('name'))
		, mysql_escape_string($this->storageDeviceGetProperty('description'))
		, mysql_escape_string($this->storageDeviceGetProperty('type'))
		, mysql_escape_string($this->storageDeviceGetProperty('baseUrl'))
		, mysql_escape_string($this->storageDeviceGetProperty('basePath'))
		, mysql_escape_string($this->storageDeviceGetProperty('userName'))
		, mysql_escape_string($this->storageDeviceGetProperty('password'))
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
	
	public function storageDeviceUpdate() {
		$query = sprintf("UPDATE `storageDevice` SET `name` = '%s', `description` = '%s', `type` = '%s', `baseUrl` = '%s', `basePath` = '%s', `userName` = '%s', `password` = '%s', `key` = '%s', `active` = '%s', `defaultStorage` = '%s', `extra2` = '%s' WHERE `storageDeviceId` = '%s' ;"
		, mysql_escape_string($this->storageDeviceGetProperty('name'))
		, mysql_escape_string($this->storageDeviceGetProperty('description'))
		, mysql_escape_string($this->storageDeviceGetProperty('type'))
		, mysql_escape_string($this->storageDeviceGetProperty('baseUrl'))
		, mysql_escape_string($this->storageDeviceGetProperty('basePath'))
		, mysql_escape_string($this->storageDeviceGetProperty('userName'))
		, mysql_escape_string($this->storageDeviceGetProperty('password'))
		, mysql_escape_string($this->storageDeviceGetProperty('key'))
		, mysql_escape_string($this->storageDeviceGetProperty('active'))
		, mysql_escape_string($this->storageDeviceGetProperty('defaultStorage'))
		, mysql_escape_string($this->storageDeviceGetProperty('extra2'))
		, mysql_escape_string($this->storageDeviceGetProperty('storageDeviceId'))
		);
		if($this->db->query($query)) {
			$this->storageDeviceGetAll();
			return(true);
		} else {
			return (false);
		}
	}
	
	public function storageDeviceDelete($storageDeviceId) {
		$query = sprintf(" DELETE FROM `storageDevice` WHERE `storageDeviceId` = %s ", mysql_escape_string($storageDeviceId));
		if($this->db->query($query)) {
			$this->storageDeviceGetAll();
			return(true);
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
					$amazon = new AmazonS3(array('key' => $device['password'],'secret' => $device['key']));
					$storageFilePath1 = substr($storageFilePath,0,1)=='/' ? substr($storageFilePath,1,strlen($storageFilePath)-1) : $storageFilePath;
					$response = $amazon->create_object ($device['basePath'], $storageFilePath1 . '/' .  $storageFileName, array('fileUpload' => $tmpFile,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
					if($response->isOK()) {
						$result['imageId'] = $img->imageGetId($storageFileName, $storageFilePath, $storageDeviceId);
						if(!$result['imageId']) {
							$img->imageSetProperty('filename',$storageFileName);
							$img->imageSetProperty('storageDeviceId', $storageDeviceId);
							$img->imageSetProperty('path', $storageFilePath);
							$img->imageSetProperty('originalFilename', $storageFileName);
							$img->imageSetProperty('remoteAccessKey', $remoteAccesskey);
							$img->imageSave();
							$result['imageId'] = $img->imageGetId($storageFileName, $storageFilePath, $storageDeviceId);
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
						$result['imageId'] = $img->imageGetId($storageFileName, $storageFilePath, $storageDeviceId);
						if(!$result['imageId']) {
							$ar = @getimagesize($device['basePath'].$storageFilePath.'/'.$storageFileName);
							$img->imageSetProperty('width',$ar[0]);
							$img->imageSetProperty('height',$ar[1]);
							$img->imageSetProperty('filename',$storageFileName);
							$img->imageSetProperty('storageDeviceId', $storageDeviceId);
							$img->imageSetProperty('path', $storageFilePath);
							$img->imageSetProperty('originalFilename', $storageFileName);
							$img->imageSetProperty('remoteAccessKey', $remoteAccesskey);
							$img->imageSave();
							$result['imageId'] = $img->imageGetId($storageFileName, $storageFilePath, $storageDeviceId);
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
		
	public function storageDeviceDeleteFile($storageDeviceId, $storageFileName, $storageFilePath='') {
		if($storageDeviceId == '' || $storageFileName == '') {
			return false;
		} else {
			$device = $this->storageDeviceGet($storageDeviceId);
			$ar = @explode('.',$storageFileName);
			$ext = @array_pop($ar);
			$filename = @implode('.',$ar);
			switch(strtolower($this->storageDeviceGetType($storageDeviceId))) {
				case 's3':
					$amazon = new AmazonS3(array('key' => $device['password'],'secret' => $device['key']));
					$storageFilePath1 = substr($storageFilePath,0,1)=='/' ? substr($storageFilePath,1,strlen($storageFilePath)-1) : $storageFilePath;
					$response = $amazon->delete_object ($device['basePath'], $storageFilePath1 . '/' .  $storageFileName);
					foreach(array('_s','_m','_l','') as $postfix) {
						$response = @$amazon->delete_object($device['basePath'], $storageFilePath1 . '/' .  $filename . $postfix .'.'. $ext);
					}
					break;
				case 'local':
					@unlink($device['basePath']. rtrim($storageFilePath,'/') . '/' .  $storageFileName);
					foreach(array('_s','_m','_l','') as $postfix) {
						if(file_exists($device['basePath']. rtrim($storageFilePath,'/') . '/' .$filename.$postfix.'.'.$ext)) {
							@unlink($device['basePath']. rtrim($storageFilePath,'/') . '/' .$filename.$postfix.'.'.$ext);
						}
					}
					break;
				default:
					return false;
					break;
			}
			return true;
		}
	}
	
	public function storageDeviceMoveImage($imageId, $newStorageId, $newImagePath) {
		$img = new Image($this->db);
		if($imageId == '' || $newStorageId == '' || $newImagePath == '') {
			return false;
		}
		if($img->imageLoadById($imageId)) {
			if(($img->imageGetProperty('storageDeviceId') == $newStorageId) && ($img->imageGetProperty('path') == $newImagePath)) {
				return true;
			} else {
				$device1 = $this->storageDeviceGet($img->imageGetProperty('storageDeviceId'));
				$device2 = $this->storageDeviceGet($newStorageId);
				
				switch(strtolower($device2['type'])) {
					case 's3':
						$amazon = new AmazonS3(array('key' => $device2['password'],'secret' => $device2['key']));
						switch(strtolower($device1['type'])) {
							case 's3':
								$tmp = $img->imageGetProperty('path');
								$tmp = substr($tmp,0,1)=='/' ? substr($tmp,1,strlen($tmp)-1) : $tmp;
								$source = $tmp . '/' . $img->imageGetProperty('filename');
								$tmpImage = $img->imageGetProperty('filename');
								$fp = fopen($tmpImage, "w+b");
								$amazon->get_object($device1['basePath'], $source, array('fileDownload' => $tmpImage));
								fclose($fp);
								$tmp = $newImagePath;
								$tmp = substr($tmp,0,1)=='/' ? substr($tmp,1,strlen($tmp)-1) : $tmp;
								$response = $amazon->create_object ($device2['basePath'], $tmp . '/' .  $img->imageGetProperty('filename'), array('fileUpload' => $tmpImage,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
								unlink($tmpImage);
								break;
							
							case 'local':
								$source = $device1['basePath'] . rtrim($img->imageGetProperty('path'),'/') . '/' . $img->imageGetProperty('filename');
								$tmp = $newImagePath;
								$tmp = substr($tmp,0,1)=='/' ? substr($tmp,1,strlen($tmp)-1) : $tmp;
								$response = $amazon->create_object ($device2['basePath'], $tmp . '/' .  $img->imageGetProperty('filename'), array('fileUpload' => $source,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
								break;
						}
						if($response->isOK()) {
							$this->storageDeviceDeleteFile($img->imageGetProperty('storageDeviceId'), $img->imageGetProperty('filename'), $img->imageGetProperty('path'));
							$img->imageSetProperty('storageDeviceId', $newStorageId);
							$img->imageSetProperty('path', $newImagePath);
							$img->imageSave();
							return true;
						} else {
							return false;
						}
						break;
					
					case 'local':
						switch(strtolower($device1['type'])) {
							case 's3':
								$amazon = new AmazonS3(array('key' => $device1['password'],'secret' => $device1['key']));
								$tmp = $img->imageGetProperty('path');
								$tmp = substr($tmp,0,1)=='/' ? substr($tmp,1,strlen($tmp)-1) : $tmp;
								$source = $tmp . '/' . $img->imageGetProperty('filename');
								$tmpImage = $img->imageGetProperty('filename');
								$fp = fopen($tmpImage, "w+b");
								$amazon->get_object($device1['basePath'], $source, array('fileDownload' => $tmpImage));
								fclose($fp);
								$img->imageMkdirRecursive($device2['basePath'].$newImagePath);
								$fp = fopen($tmpImage, "r");
								$response = file_put_contents($device2['basePath'].$newImagePath . '/' .  $img->imageGetProperty('filename'), $fp);
								fclose($fp);
								unlink($tmpImage);
								break;
							
							case 'local':
								$source = $device1['basePath'] . $img->imageGetProperty('path') . '/' . $img->imageGetProperty('filename');
								$img->imageMkdirRecursive($device2['basePath'].$newImagePath);
								$fp = fopen($source, "r");
								$response = file_put_contents($device2['basePath'].$newImagePath . '/' .  $img->imageGetProperty('filename'), $fp);
								fclose($fp);
								break;
						}
						$this->storageDeviceDeleteFile($img->imageGetProperty('storageDeviceId'), $img->imageGetProperty('filename'), $img->imageGetProperty('path'));
						$img->imageSetProperty('storageDeviceId', $newStorageId);
						$img->imageSetProperty('path', $newImagePath);
						$img->imageSave();
						return true;
						break;
				}
			}
		} else {
			return false;
		}
	}
	
	public function storageDeviceFileExists($storageDeviceId, $key) {
		if($storageDeviceId == '') return false;
		$device = $this->storageDeviceGet($storageDeviceId);
		if(!$device) return false;
		switch(strtolower($device['type'])) {
			case 's3':
				$key = substr($key,0,1)=='/' ? substr($key,1,strlen($key)-1) : $key;
				$amazon = new AmazonS3(array('key' => $device['password'],'secret' => $device['key']));
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
	
	public function storageDeviceFileGetContents($storageDeviceId, $key) {
		if($storageDeviceId == '') return false;
		$device = $this->storageDeviceGet($storageDeviceId);
		if(!$device) return false;
		switch(strtolower($device['type'])) {
			case 's3':
				$amazon = new AmazonS3(array('key' => $device['password'],'secret' => $device['key']));
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
	
	public function storageDeviceFileDownload($storageDeviceId , $key) {
		if($storageDeviceId == '') return false;
		$device = $this->storageDeviceGet($storageDeviceId);
		if(!$device) return false;
		switch(strtolower($device['type'])) {
			case 's3':
				$amazon = new AmazonS3(array('key' => $device['password'],'secret' => $device['key']));
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
				$key = (substr($key,0,1)!='/') ? '/'.$key : $key;
				return ($device['basePath'] . $key);
				break;
			default:
				return false;
				break;
		}
	}
	
	public function storageDeviceCreateFile($storageDeviceId, $key, $data) {
		if($storageDeviceId == '' || $data == '') return false;
		$device = $this->storageDeviceGet($storageDeviceId);
		if(!$device) return false;
		switch(strtolower($device['type'])) {
			case 's3':
				$amazon = new AmazonS3(array('key' => $device['password'],'secret' => $device['key']));
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
	
	public function storageDeviceFileUpload($storageDeviceId, $key, $source, $delFlag = true) {
		if($storageDeviceId == '' || $source == '') return false;
		$device = $this->storageDeviceGet($storageDeviceId);
		if(!$device) return false;
		switch(strtolower($device['type'])) {
			case 's3':
				$amazon = new AmazonS3(array('key' => $device['password'],'secret' => $device['key']));
				$key = substr($key,0,1)=='/' ? substr($key,1,strlen($key)-1) : $key;
				$response = $amazon->create_object ($device['basePath'], $key, array('fileUpload' => $source,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
				if($delFlag == true) {
					@unlink($source);
				}
				if($response->isOK()) {
					return true;
				} else {
					return false;
				}
				break;
			case 'local':
				return true;
				break;
			default:
				return false;
				break;
		}
	}
	
}

?>