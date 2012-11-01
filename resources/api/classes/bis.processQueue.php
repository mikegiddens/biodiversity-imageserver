<?php

/**
 * @copyright SilverBiology, LLC
 * @author Michael Giddens
 * @website http://www.silverbiology.com
 */

Class ProcessQueue {

	private $processs_stats;
	public $db, $record, $data, $image;

	public function __construct($db = null) {
		$this->db = $db;
		$this->image = new Image();
		$this->image->db = &$this->db;
		$this->storage = new StorageDevice($this->db);
	}

	/**
	 * Returns a since field value
	 * @return mixed
	 */
	public function processQueueGetProperty( $field ) {
		if (isset($this->record[$field])) {
			return( $this->record[$field] );
		} else {
			return( false );
		}
	}

	/**
	 * Set the value to a field
	 * @return bool
	 */
	public function processQueueSetProperty( $field, $value ) {
		$this->record[$field] = $value;
		return( true );
	}

	/**
	 * Returns all values for project
	 * @param string $set : allowed values : NEW, ORIG
	 * @return boolean|array
	 */
	public function processQueueGetAll( $set = 'NEW' ) {
		if ($set == 'NEW') {
			return( $this->record );
		}
		if ($set == 'ORIG') {
			return( $this->record_orig );
		}
		return( false );
	}

	/**
	 * Set the value to Data
	 * @param mixed $data : input data
	 * @return bool
	 */
	public function processQueueSetData($data) {
		$this->data = $data;
		return( true );
	}

	/**
	 * Loads by imageId and processType
	 * @param integer $imageId : required parameter
	 * @return boolean
	 */
	public function processQueueLoadById($imageId,$processType = '') {
		$where = ($processType != '') ? sprintf( " AND `processType` = '%s' ",$processType) : '';
			$query = sprintf("SELECT * FROM `processQueue` WHERE `imageId` = '%s' $where ", mysql_escape_string($imageId) );
			try {
				$ret = $this->db->query_one( $query );
			} catch (Exception $e) {
				trigger_error($e->getMessage(),E_USER_ERROR);
			}
			unset($this->records);
			if ($ret != NULL) {
				$records = array();
				foreach( $ret as $field => $value ) {
						$this->record_orig[$field] = $value;
						$this->record[$field] = $value;
				}
				$this->records[] = $this->record;
				return(true);
			} else {
				return(false);
			}
	}

	/**
	 * Saves the data to the db
	 */
	public function processQueueSave() {
		if($this->processQueueFieldExists($this->processQueueGetProperty('imageId'), $this->processQueueGetProperty('processType'))) {
			$query = sprintf("UPDATE `processQueue` SET  `processType` = '%s', `extra` = '%s', `dateAdded` = now(), `errors` = '%s', `errorDetails` = '%s' WHERE `imageId` = '%s' AND `processType` = '%s';"
				, mysql_escape_string($this->processQueueGetProperty('processType'))
				, mysql_escape_string($this->processQueueGetProperty('extra'))
				, mysql_escape_string($this->processQueueGetProperty('errors'))
				, mysql_escape_string($this->processQueueGetProperty('errorDetails'))
				, mysql_escape_string($this->processQueueGetProperty('imageId'))
				, mysql_escape_string($this->processQueueGetProperty('processType'))
			);
		} else {
			$query = sprintf("INSERT INTO `processQueue` SET `imageId` = '%s', `processType` = '%s', `extra` = '%s', `dateAdded` = now(), `errors` = '%s', `errorDetails` = '%s' ;"
				, mysql_escape_string($this->processQueueGetProperty('imageId'))
				, mysql_escape_string($this->processQueueGetProperty('processType'))
				, mysql_escape_string($this->processQueueGetProperty('extra'))
				, mysql_escape_string($this->processQueueGetProperty('errors'))
				, mysql_escape_string($this->processQueueGetProperty('errorDetails'))
			);
		}
		if($this->db->query($query)) {
			return(true);
		} else {
			return (false);
		}
	}

	/**
	 * checks whether field exists in processQueue table
	 */
	public function processQueueFieldExists ( $imageId, $processType = '' ){
		if($processType != '') {
			$query = sprintf("SELECT `imageId` FROM `processQueue` WHERE `imageId` = '%s' AND `processType` = '%s' ;", mysql_escape_string($imageId),  mysql_escape_string($processType));
		} else {
			$query = sprintf("SELECT `imageId` FROM `processQueue` WHERE `imageId` = '%s';", $imageId );
		}
		$ret = $this->db->query_one( $query );

		if ($ret == NULL) {
				return false;
		} else {
				return true;
		}
	}

	public function processQueueProcess() {
		$ret = array();
		unset($this->process_stats);
		$this->process_stats = array('small' => 0, 'medium' => 0, 'large' => 0, 'google_tile' => 0, 'zoomify' => 0, 'cache' => 0);
		$loop_flag = true;
		$stop = $this->data['stop'];
		$limit = $this->data['limit'];
		$this->image->db = &$this->db;
		$count = 0;

		$imageIds = $this->data['imageIds'];
		if(is_array($imageIds) && count($imageIds)) {
			$query = sprintf(" SELECT q.* FROM `processQueue` q, image i WHERE i.`imageId` = q.`imageId` AND q.`processType` NOT IN ('picassaAdd','flickrAdd') AND i.`imageId` IN (%s) ORDER BY `dateAdded` ", @implode(',',$imageIds));
			$rets = $this->db->query_all($query);
			// echo '<pre>';var_dump($rets);exit;
			if(is_array($rets) && count($rets)) {
				foreach($rets as $record) {
					if($this->processQueueProcessType($record)) {
						$count++;
						$delquery = sprintf("DELETE FROM `processQueue` WHERE `imageId` = '%s' AND `processType` = '%s' ", mysql_escape_string($record->imageId), mysql_escape_string($record->processType));
						$this->db->query($delquery);
					}
				}
			}

		} else {
			while($loop_flag) {
				if( ($stop != "") && (mktime() > $stop) ) $loop_flag = false;
				if($limit != '') {
					if($limit == 0) break;
				}
				if ($this->data['limit'] != '' && $count >= ($limit-1)) $loop_flag = false;

				$record = $this->processQueuePop();
				if($record === false) {
					$loop_flag = false;
				} else {
					if($this->processQueueProcessType($record)) {
						$count++;
					}
				}
			}
		}
		return $count;
	}

	public function processQueuePop($processType = '') {

		switch($processType) {
			case 'flickrAdd':
				$query = "SELECT * FROM `processQueue` WHERE `processType` = 'flickrAdd' ORDER BY `dateAdded` LIMIT 1";
				break;
			case 'picassaAdd':
				$query = "SELECT * FROM `processQueue` WHERE `processType` = 'picassaAdd' ORDER BY `dateAdded` LIMIT 1";
				break;
			case 'zoomify':
				$query = "SELECT * FROM `processQueue` WHERE `processType` = 'zoomify' ORDER BY `dateAdded` LIMIT 1";
				break;
			case 'google_tile':
				$query = "SELECT * FROM `processQueue` WHERE `processType` = 'google_tile' ORDER BY `dateAdded` LIMIT 1";
				break;
			case 'ocr_add':
				$query = "SELECT * FROM `processQueue` WHERE `processType` = 'ocr_add' ORDER BY `dateAdded` LIMIT 1";
				break;
			case 'box_add':
				$query = "SELECT * FROM `processQueue` WHERE `processType` = 'box_add' ORDER BY `dateAdded` LIMIT 1";
				break;
			case 'name_add':
				$query = "SELECT * FROM `processQueue` WHERE `processType` = 'name_add' ORDER BY `dateAdded` LIMIT 1";
				break;
			case 'evernote':
				$query = "SELECT * FROM `processQueue` WHERE `processType` = 'evernote' ORDER BY `dateAdded` LIMIT 1";
				break;
			case 'all':
				$query = "SELECT * FROM `processQueue` WHERE `processType` = 'all' ORDER BY `dateAdded` LIMIT 1";
				break;
			default:
				$query = "SELECT * FROM `processQueue` WHERE `processType` NOT IN ('picassaAdd','flickrAdd','ocr_add','box_add') ORDER BY `dateAdded` LIMIT 1";
				break;
		}
		$result = $this->db->query_one($query);
		if($result == NULL) {
			return false;
		} else {
			if($this->processQueueDelete($result->imageId, $result->processType)) {
				return $result;
			} else {
				return false;
			}

/*
			$delquery = sprintf("DELETE FROM `processQueue` WHERE `imageId` = '%s' AND `processType` = '%s' ", mysql_escape_string($result->imageId), mysql_escape_string($result->processType));

			if($this->db->query($delquery)) {
				return $result;
			} else {
				return false;
			}
*/

			return $result;
		}
	}

	public function processQueueDelete($imageId,$processType) {
		$delquery = sprintf("DELETE FROM `processQueue` WHERE `imageId` = '%s' AND `processType` = '%s' ", mysql_escape_string($imageId), mysql_escape_string($processType));
		if($this->db->query($delquery)) {
			return true;
		}
		return false;

	}

	public function processQueueProcessType($record) {
		global $config;
		$this->image->imageLoadById($record->imageId);
		$device = $this->storage->storageDeviceGet($this->image->record['storageDeviceId']);		
		if(!$device) return false;
/*		
		if(strtolower($this->storage->storageDeviceGetType($this->image->imageGetProperty('storageDeviceId'))) == 'local') {
			$device = $this->storage->get($this->image->imageGetProperty('storageDeviceId'));
			$image_path =  $device['basePath'] . $this->image->imageGetProperty('path');
			$image = $image_path . '/' . $this->image->imageGetProperty('filename');
			$this->image->imageSetFullPath($image);
		}
*/
		switch($record->processType) {
			case 'all':
				$this->process_stats['small']++;
				$this->process_stats['medium']++;
				$this->process_stats['large']++;

				$ar = array ('postfix' => '_s', 'width' => 100, 'height' => 100);
				$tmpPath = $this->image->imageCreateThumbnail($ar);
				$ar = array ('postfix' => '_m', 'width' => 275, 'height' => 275);
				$tmpPath = $this->image->imageCreateThumbnail($ar,$tmpPath,false);
				$ar = array ('postfix' => '_l', 'width' => 800, 'height' => 800);
				$tmpPath = $this->image->imageCreateThumbnail($ar,$tmpPath,true);

				/*
				if(strtolower($this->storage->storageDeviceGetType($this->image->imageGetProperty('storageDeviceId'))) == 's3') {
					$ar = array ('s3' => $this->data['s3'], 'obj' => $this->data['obj'], 'postfix' => '_s', 'width' => 100, 'height' => 100);
					$tmpPath = $this->image->createThumbS3($record->imageId,$ar,false);

					$ar = array ('s3' => $this->data['s3'], 'obj' => $this->data['obj'], 'postfix' => '_m', 'width' => 275, 'height' => 275);
					$tmpPath = $this->image->createFromFileS3($tmpPath,$record->imageId,$ar,false);

					$ar = array ('s3' => $this->data['s3'], 'obj' => $this->data['obj'], 'postfix' => '_l', 'width' => 800, 'height' => 800);
					$this->image->createFromFileS3($tmpPath,$record->imageId,$ar,true);

				} else {
					if($config['image_processing'] == 1) {
						$this->image->createThumbnailIMagik( $image, 100, 100, "_s");
						$this->image->createThumbnailIMagik( $image, 275, 275, "_m");
						$this->image->createThumbnailIMagik( $image, 800, 800, "_l");
					} else {
						$this->image->createThumbnail( $image, 100, 100, "_s");
						$this->image->createThumbnail( $image, 275, 275, "_m");
						$this->image->createThumbnail( $image, 800, 800, "_l");
					}
				}
				*/
				$this->image->imageSetProperty('processed',1);
				$this->image->imageSave();
				break;

			case 'small':
				$ar = array ('postfix' => '_s', 'width' => 100, 'height' => 100);
				$tmpPath = $this->image->imageCreateThumbnail($ar,'',true);
				$this->image->imageSetProperty('processed',1);
				$this->image->imageSave();
				break;

			case 'medium':
				$ar = array ('postfix' => '_m', 'width' => 275, 'height' => 275);
				$tmpPath = $this->image->imageCreateThumbnail($ar,'',true);
				$this->image->imageSetProperty('processed',1);
				$this->image->imageSave();
				break;

			case 'large':
				$ar = array ('postfix' => '_l', 'width' => 800, 'height' => 800);
				$tmpPath = $this->image->imageCreateThumbnail($ar,'',true);
				$this->image->imageSetProperty('processed',1);
				$this->image->imageSave();
				break;

			case 'cache':
				$json_data = 'test';
				$this->process_stats['cache']++;
				$filename = $$device['basePath'] . rtrim($this->image->imageGetProperty('path'),'/') . '/' . $record->imageId . '.json';
				$fp = fopen($filename, 'w');
				fwrite($fp,$json_data);
				fclose($fp);
				break;

			case 'google_tile':
			/*
				$this->process_stats['google_tile']++;
				if($this->data['mode'] == 's3') {
					$ar = array ('s3' => $this->data['s3'], 'obj' => $this->data['obj']);
					$ret = $this->image->processGTileIM_S3($record->imageId,$ar);
				} else {
					$ret = $this->image->processGTileIM($record->imageId);
//					$ret = $this->image->processGTile($record->imageId); # No image magic format uses GD but not as clear.
				}
				if($ret) {
					$this->image->imageSetProperty('gTileProcessed', 1);
					$this->image->imageSave();
				}
				*/
				break;

			case 'zoomify':
				$this->image->imageZoomify($record->imageId);
				break;
		}
		return true;
	}

	public function processQueueList() {
		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " WHERE " . $where;
		}

		if($this->data['value'] != '') {
			switch($this->data['searchFormat']) {
				case 'exact':
					$where .= sprintf(" AND `processType` = '%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'left':
					$where .= sprintf(" AND `processType` LIKE '%s%%' ", mysql_escape_string($this->data['value']));
					break;
				case 'right':
					$where .= sprintf(" AND `processType` LIKE '%%%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'both':
				default:
					$where .= sprintf(" AND `processType` LIKE '%%%s%%' ", mysql_escape_string($this->data['value']));
					break;
			}
		}
		
		if($this->data['group'] != '' && in_array($this->data['group'], array('imageId','processType','dateAdded')) && $this->data['dir'] != '') {
			$where .= build_order( array(array('field' => $this->data['group'], 'dir' => $this->data['dir'])), array('imageId'));
		} else {
			$where .= ' ORDER BY `imageId` ASC ';
		}
		
		if($this->data['limit'] != '') {
			$where .= sprintf(" LIMIT %s, %s ", mysql_escape_string($this->data['start']), mysql_escape_string($this->data['limit']));
		}
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM `processQueue` " . $where; 
		$ret = $this->db->query_all($query);
		return is_null($ret) ? array() : $ret;
	}

	public function processQueueClear() {
		if(!(is_array($this->data['processType']) || is_array($this->data['imageIds']))) return false;
		$where = '';
		if(is_array($this->data['processType']) && count($this->data['processType'])) {
			foreach($this->data['processType'] as &$type) {
				$type = mysql_escape_string($type);
			}
			$where .= sprintf(" AND `processType` IN ('%s') ",@implode("','",$this->data['processType']));
		}
		if(is_array($this->data['imageIds']) && count($this->data['imageIds'])) {
			foreach($this->data['imageIds'] as &$imageId) {
				$imageId = mysql_escape_string($imageId);
			}
			$where .= sprintf(" AND `imageId` IN ('%s') ",@implode("','",$this->data['imageIds']));
		}
		if($where != '') {
			$query = sprintf(" DELETE FROM `processQueue` WHERE 1=1 %s ",$where);
		}
		$this->db->query($query);
		$recordCount = $this->db->affected_rows;
		$recordCount = is_null($recordCount) ? 0 : $recordCount;
		return $recordCount;
	}


}
?>