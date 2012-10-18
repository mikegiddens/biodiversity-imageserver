<?php

class Event
{
	public $db;

	public function __construct($db) {
		$this->db = $db;
		$this->lg = new LogClass($db);
	}
	
	/**
	* Set the value to Data
	* @param mixed $data : input data
	* @return bool
	*/
	public function eventsSetData($data) {
		$this->data = $data;
		return( true );
	}
	
	/**
	* Returns a since field value
	* @return mixed
	*/
	public function eventsGetProperty( $field ) {
		if (isset($this->{$field})) {
			return( $this->{$field} );
		} else {
			return( false );
		}
	}
	
	/**
	* Set the value to a field
	* @return bool
	*/
	public function eventsSetProperty( $field, $value ) {
		$this->{$field} = $value;
		return( true );
	}
	
	public function eventsLoadById( $eventId ) {
		if($eventId == '') return false;
		$query = sprintf("SELECT * FROM `events` WHERE `eventId` = %s ", mysql_escape_string($eventId) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->eventsSetProperty($field, $value);
			}
			return(true);
		} else {
			return(false);
		}
	}

	public function eventsListRecords($queryFlag = true) {
		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " WHERE " . $where;
		}
		if(is_array($this->data['eventId']) && count($this->data['eventId'])) {
			$where .= sprintf(" AND `eventId` IN (%s) ", implode(',', $this->data['eventId']));
		} else if($this->data['eventId'] != '') {
			$where .= sprintf(" AND `eventId` = %s ", mysql_escape_string($this->data['eventId']));
		}
		if(is_array($this->data['geographyId']) && count($this->data['geographyId'])) {
			$where .= sprintf(" AND `geographyId` IN (%s) ", implode(',', $this->data['geographyId']));
		} else if($this->data['geographyId'] != '') {
			$where .= sprintf(" AND `geographyId` = %s ", mysql_escape_string($this->data['geographyId']));
		}
		if(is_array($this->data['eventTypeId']) && count($this->data['eventTypeId'])) {
			$where .= sprintf(" AND `eventTypeId` IN (%s) ", implode(',', $this->data['eventTypeId']));
		} else if($this->data['eventTypeId'] != '') {
			$where .= sprintf(" AND `eventTypeId` = %s ", mysql_escape_string($this->data['eventTypeId']));
		}
		if($this->data['value'] != '') {
			switch($this->data['searchFormat']) {
				case 'exact':
					$where .= sprintf(" AND `title` = '%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'left':
					$where .= sprintf(" AND `title` LIKE '%s%%' ", mysql_escape_string($this->data['value']));
					break;
				case 'right':
					$where .= sprintf(" AND `title` LIKE '%%%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'both':
				default:
					$where .= sprintf(" AND `title` LIKE '%%%s%%' ", mysql_escape_string($this->data['value']));
					break;
			}
		}
		if($this->data['group'] != '' && in_array($this->data['group'], array('eventId','geographyId','eventDate','eventTypeId')) && $this->data['dir'] != '') {
			$where .= build_order( array(array('field' => $this->data['group'], 'dir' => $this->data['dir'])));
		} else {
			$where .= ' ORDER BY `eventId` ASC ';
		}
		
		$where .= build_limit($this->data['start'], $this->data['limit']);

		if($geoFlag) {
			$query = "SELECT SQL_CALC_FOUND_ROWS `eventId`, `geographyId`, `eventDate`, `eventTypeId`, `title`, `description`, `country`,	`countryIso`, `admin0` FROM `events` LEFT OUTER JOIN `geography` ON `events`.`geographyId` = `geography`.`geographyId` " . $where;
		} else {
			$query = "SELECT SQL_CALC_FOUND_ROWS `eventId`, `geographyId`, `eventDate`, `eventTypeId`, `title`, `description` FROM `events` " . $where;
		}

		if($queryFlag) {
			$ret = $this->db->query_all( $query );
			return is_null($ret) ? array() : $ret;
		} else {
			$ret = $this->db->query( $query );
			return $ret;
		}
	}

	public function eventsRecordExists ($eventId){
		if($eventId == '' || is_null($eventId)) return false;
		$query = sprintf("SELECT `eventId` FROM `events` WHERE `eventId` = %s;", mysql_escape_string($eventId) );
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}

	public function eventsSave() {
		if($this->eventsRecordExists($this->eventsGetProperty('eventId'))) {
			$query = sprintf("UPDATE `events` SET  `geographyId` = '%s', `eventDate` = now(), `eventTypeId` = '%s', `title` = '%s', `description` = '%s', `lastModifiedBy` = '%s' WHERE `eventId` = '%s' ;"
			, mysql_escape_string($this->eventsGetProperty('geographyId'))
			, mysql_escape_string($this->eventsGetProperty('eventTypeId'))
			, mysql_escape_string($this->eventsGetProperty('title'))
			, mysql_escape_string($this->eventsGetProperty('description'))
			, mysql_escape_string($this->eventsGetProperty('lastModifiedBy'))
			, mysql_escape_string($this->eventsGetProperty('eventId'))
			);
		} else {
			$query = sprintf("INSERT IGNORE INTO `events` SET `geographyId` = '%s', `eventDate` = now(), `eventTypeId` = '%s', `title` = '%s', `description` = '%s', `lastModifiedBy` = '%s' ;"
			, mysql_escape_string($this->eventsGetProperty('geographyId'))
			, mysql_escape_string($this->eventsGetProperty('eventTypeId'))
			, mysql_escape_string($this->eventsGetProperty('title'))
			, mysql_escape_string($this->eventsGetProperty('description'))
			, mysql_escape_string($this->eventsGetProperty('lastModifiedBy'))
			);
		}
		if($this->db->query($query)) {
			$this->insert_id = ($this->db->insert_id == 0) ? $this->eventsGetProperty('eventId') : $this->db->insert_id;
			$this->lg->logSetProperty('table', 'events');
			$this->lg->logSetProperty('query', $query);
			$this->lg->logSave();
			return(true);
		}
		return (false);
	}

	public function eventsDelete($eventId) {
		if($eventId == '') return false;
		if(!$this->eventsRecordExists($eventId)) return false;
		$query = sprintf("DELETE FROM `eventImages` WHERE `eventId` = '%s' ", mysql_escape_string($eventId));
		if($this->db->query($query)) {
			$query = sprintf("DELETE FROM `events` WHERE `eventId` = '%s' ", mysql_escape_string($eventId));
			if($this->db->query($query)) {
				$this->lg->logSetProperty('table', 'events');
				$this->lg->logSetProperty('query', $query);
				$this->lg->logSave();
				return  true;
			}
		}
		return false;
	}
	
	public function eventsAddImage($eventId) {
		if($eventId == '') return false;
		if(!$this->eventsRecordExists($eventId)) return false;

		if(is_array($this->data['advFilter']) && count($this->data['advFilter'])) {
			$image = new Image($this->db);
			$qry = $image->getByCrazyFilter($this->data['advFilter']);
			$query = " INSERT INTO eventImages (imageId, eventId) SELECT im.imageId, $eventId FROM ($qry) im ";
			// echo $query; exit;
		} else if($this->data['imageId'] != '') {
			$query = sprintf("INSERT INTO eventImages SET `imageId` = '%s', `eventId` = '%s'"
					, mysql_escape_string($this->data['imageId'])
					, mysql_escape_string($eventId) 
					);
		} else {
			return false;
		}
		
		if($this->db->query($query)) {
			$id = $this->db->insert_id;
			$this->lg->logSetProperty('table', 'eventImages');
			$this->lg->logSetProperty('query', $query);
			$this->lg->logSave();
			return $id;
		} else {
			return false;
		}
	}
	
	public function eventsDeleteImage($imageId, $eventId) {
		if($imageId == '' || $eventId == '') return false;
		if(!$this->eventsRecordExists($eventId)) return false;
		$query = sprintf("DELETE FROM `eventImages` WHERE `imageId` = '%s' AND `eventId` = '%s' ", mysql_escape_string($imageId), mysql_escape_string($eventId));
		if($this->db->query($query)) {
			$this->lg->logSetProperty('table', 'eventImages');
			$this->lg->logSetProperty('query', $query);
			$this->lg->logSave();
			return true;
		} else {
			return false;
		}
	}
	
	public function eventsListImages($eventId,$size = 'l',$attributesFlag = true) {
		$query = sprintf("SELECT e.`imageId`, i.`filename`, i.`barcode`, i.`storageDeviceId`, i.`path`  FROM `eventImages` e LEFT OUTER JOIN image i ON e.`imageId` = i.`imageId` WHERE e.`eventId` = '%s';", mysql_escape_string($eventId));
		$records = $this->db->query_all($query);
		if(count($records)) {
			$storage = new StorageDevice($this->db);
			if($attributesFlag) {
				$image = new Image($this->db);
			}
			
			if(isset($size) && in_array($size, array('s','m','l'))) {
				$size = "_".$size;
			} else {
				$size = "";
			}
			
			foreach($records as &$record) {
				$device = $storage->storageDeviceGet($record->storageDeviceId);
				$tmpFilename = explode(".",$record->filename);
				$tmpFilename[0] .= $size;
				$record->filename = implode(".", $tmpFilename);

				$record->url = $device['baseUrl'];
				switch(strtolower($device['type'])) {
					case 's3':
						$record->path = substr($record->path, 0, 1) == '/' ? substr($record->path, 1, strlen($record->path)-1) : $record->path;
						$record->baseUrl = $device['baseUrl'] . $record->path . '/';
						$record->url = $device['baseUrl'] . $record->path . '/' . $record->filename;
						break;
					case 'local':
						if(substr($device['baseUrl'], strlen($url['url'])-1, 1) == '/') {
							$record->url = substr($device['baseUrl'],0,strlen($device['baseUrl'])-1);
						}
						$record->baseUrl = $record->url . $record->path . '/';
						$record->url .= $record->path . '/' . $record->filename;
						break;
				}
				if($attributesFlag) {
					$record->attributes = $image->imageGetAttributes($record->imageId);
				}
			}
			return $records;
		}
		return false;
	}
	
	public function eventsByImage($imageId = '') {
		if($imageId == '' || !is_numeric($imageId) ) return false;
		$query = sprintf("SELECT e.*,g.* FROM `events` e JOIN `eventImages` ei ON e.`eventId` = ei.`eventId` LEFT OUTER JOIN `geography` g ON e.`geographyId` = g.`geographyId` WHERE ei.`imageId` = %s ", mysql_escape_string($imageId));
		$ret = $this->db->query_all($query);
		return is_null($ret) ? array() : $ret;
	}
	
}


class EventTypes
{
	public $db;

	public function __construct($db) {
		$this->db = &$db;
		$this->lg = new LogClass($db);
	}

	/**
	* Set the value to Data
	* @param mixed $data : input data
	* @return bool
	*/
	public function eventTypesSetData($data) {
		$this->data = $data;
		return( true );
	}
	
	/**
	* Returns a since field value
	* @return mixed
	*/
	public function eventTypesGetProperty( $field ) {
		if (isset($this->{$field})) {
			return( $this->{$field} );
		} else {
			return( false );
		}
	}
	
	/**
	* Set the value to a field
	* @return bool
	*/
	public function eventTypesSetProperty( $field, $value ) {
		$this->{$field} = $value;
		return( true );
	}
	
	public function eventTypesLoadById( $eventTypeId ) {
		if($eventTypeId == '') return false;
		$query = sprintf("SELECT * FROM `eventTypes` WHERE `eventTypeId` = %s ", mysql_escape_string($eventTypeId) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->eventTypesSetProperty($field, $value);
			}
			return(true);
		} else {
			return(false);
		}
	}
	
	public function eventTypesTitleExists($title) {
		$query = sprintf("SELECT `eventTypeId` FROM `eventTypes` WHERE `title` = '%s';", mysql_escape_string($title));
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}

	public function eventTypesListRecords($queryFlag = true) {
		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " WHERE " . $where;
		}
		
		if(is_array($this->data['eventTypeId']) && count($this->data['eventTypeId'])) {
			$where .= sprintf(" AND `eventTypeId` IN (%s) ", implode(',', $this->data['eventTypeId']));
		} else if($this->data['eventTypeId'] != '') {
			$where .= sprintf(" AND `eventTypeId` = %s ", mysql_escape_string($this->data['eventTypeId']));
		}
		if($this->data['value'] != '') {
			switch($this->data['searchFormat']) {
				case 'exact':
					$where .= sprintf(" AND `title` = '%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'left':
					$where .= sprintf(" AND `title` LIKE '%s%%' ", mysql_escape_string($this->data['value']));
					break;
				case 'right':
					$where .= sprintf(" AND `title` LIKE '%%%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'both':
				default:
					$where .= sprintf(" AND `title` LIKE '%%%s%%' ", mysql_escape_string($this->data['value']));
					break;
			}
		}
		if($this->data['group'] != '' && in_array($this->data['group'], array('eventTypeId','title')) && $this->data['dir'] != '') {
			$where .= build_order( array(array('field' => $this->data['group'], 'dir' => $this->data['dir'])));
		} else {
			$where .= ' ORDER BY `eventTypeId` ASC ';
		}

		$where .= build_limit($this->data['start'], $this->data['limit']);

		$query = "SELECT SQL_CALC_FOUND_ROWS `eventTypeId`, `title`, `description`, `lastModifiedBy`, `modifiedTime` FROM `eventTypes` " . $where;

		// die($query);
		
		if($queryFlag) {
			$ret = $this->db->query_all( $query );
			return is_null($ret) ? array() : $ret;
		} else {
			$ret = $this->db->query( $query );
			return $ret;
		}
	}

	public function eventTypesRecordExists ($eventTypeId){
		if($eventTypeId == '' || is_null($eventTypeId)) return false;
		$query = sprintf("SELECT `eventTypeId` FROM `eventTypes` WHERE `eventTypeId` = %s;", mysql_escape_string($eventTypeId) );
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}

	public function eventTypesSave() {
		if($this->eventTypesRecordExists($this->eventTypesGetProperty('eventTypeId'))) {
			$query = sprintf("UPDATE `eventTypes` SET  `title` = '%s', `description` = '%s', `lastModifiedBy` = '%s', `modifiedTime` = NOW() WHERE `eventTypeId` = '%s' ;"
			, mysql_escape_string($this->eventTypesGetProperty('title'))
			, mysql_escape_string($this->eventTypesGetProperty('description'))
			, mysql_escape_string($this->eventTypesGetProperty('lastModifiedBy'))
			, mysql_escape_string($this->eventTypesGetProperty('eventTypeId'))
			);
		} else {
			$query = sprintf("INSERT IGNORE INTO `eventTypes` SET `title` = '%s', `description` = '%s', `lastModifiedBy` = '%s', `modifiedTime` = NOW() ;"
			, mysql_escape_string($this->eventTypesGetProperty('title'))
			, mysql_escape_string($this->eventTypesGetProperty('description'))
			, mysql_escape_string($this->eventTypesGetProperty('lastModifiedBy'))
			);
		}
		if($this->db->query($query)) {
			$this->insert_id = ($this->db->insert_id == 0) ? $this->eventTypesGetProperty('eventTypeId') : $this->db->insert_id;
			$this->lg->logSetProperty('table', 'eventTypes');
			$this->lg->logSetProperty('query', $query);
			$this->lg->logSave();
			return(true);
		}
		return (false);
	}

	public function eventTypesDelete($eventTypeId) {
		if($eventTypeId == '') return false;
		if(!$this->eventTypesRecordExists($eventTypeId)) return false;
		$query = sprintf("DELETE e, et, em FROM eventTypes et LEFT OUTER JOIN events e ON et.eventTypeId = e.eventTypeId LEFT OUTER JOIN eventImages em ON  e.eventId = em.eventId WHERE et.eventTypeId = '%s' ", mysql_escape_string($eventTypeId));
		if($this->db->query($query)) {
			$this->lg->logSetProperty('table', 'eventTypes');
			$this->lg->logSetProperty('query', $query);
			$this->lg->logSave();
			return  true;
		}
		return false;
	}

}

?>