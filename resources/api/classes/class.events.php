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
	public function setData($data) {
	$this->data = $data;
	return( true );
	}
	
	/**
	* Returns a since field value
	* @return mixed
	*/
	public function get( $field ) {
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
	public function set( $field, $value ) {
		$this->{$field} = $value;
		return( true );
	}
	
	public function load_by_id( $eventId ) {
		if($eventId == '') return false;
		$query = sprintf("SELECT * FROM `events` WHERE `eventId` = %s ", mysql_escape_string($eventId) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->set($field, $value);
			}
			return(true);
		} else {
			return(false);
		}
	}

	public function listRecords($queryFlag = true, $geoFlag = true) {
		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " WHERE " . $where;
		}
		if($this->data['eventId'] != '') {
			$where .= sprintf(" AND `eventId` = '%s' ", mysql_escape_string($this->data['eventId']));
		}
		if($this->data['geoId'] != '') {
			$where .= sprintf(" AND `geoId` = '%s' ", mysql_escape_string($this->data['geoId']));
		}
		if($this->data['eventTypeId'] != '') {
			$where .= sprintf(" AND `eventTypeId` = '%s' ", mysql_escape_string($this->data['eventTypeId']));
		}
		if($this->data['field'] != '' && $this->data['value'] != '') {
			$where .= sprintf(" AND `%s` = '%s' ", mysql_escape_string($this->data['field']), mysql_escape_string($this->data['value']));
		}

		$where .= build_order( $this->data['order']);
		$where .= build_limit($this->data['start'], $this->data['limit']);

		if($geoFlag) {
			$query = "SELECT SQL_CALC_FOUND_ROWS `eventId`, `geoId`, `eventDate`, `eventTypeId`, `title`, `description`, `country`,	`country_iso`, `admin_0` FROM `events` LEFT OUTER JOIN `geography` ON `events`.`geoId` = `geography`.`id` " . $where;
		} else {
			$query = "SELECT SQL_CALC_FOUND_ROWS `eventId`, `geoId`, `eventDate`, `eventTypeId`, `title`, `description` FROM `events` " . $where;
		}

		if($queryFlag) {
			$ret = $this->db->query_all( $query );
			return is_null($ret) ? array() : $ret;
		} else {
			$ret = $this->db->query( $query );
			return $ret;
		}
	}

	public function recordExists ($eventId){
		if($eventId == '' || is_null($eventId)) return false;
		$query = sprintf("SELECT `eventId` FROM `events` WHERE `eventId` = %s;", mysql_escape_string($eventId) );
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}

	public function save() {
		if($this->recordExists($this->get('eventId'))) {
			$query = sprintf("UPDATE `events` SET  `geoId` = '%s', `eventDate` = now(), `eventTypeId` = '%s', `title` = '%s', `description` = '%s', `lastModifiedBy` = '%s' WHERE `eventId` = '%s' ;"
			, mysql_escape_string($this->get('geoId'))
			, mysql_escape_string($this->get('eventTypeId'))
			, mysql_escape_string($this->get('title'))
			, mysql_escape_string($this->get('description'))
			, mysql_escape_string($this->get('lastModifiedBy'))
			, mysql_escape_string($this->get('eventId'))
			);
		} else {
			$query = sprintf("INSERT IGNORE INTO `events` SET `geoId` = '%s', `eventDate` = now(), `eventTypeId` = '%s', `title` = '%s', `description` = '%s', `lastModifiedBy` = '%s' ;"
			, mysql_escape_string($this->get('geoId'))
			, mysql_escape_string($this->get('eventTypeId'))
			, mysql_escape_string($this->get('title'))
			, mysql_escape_string($this->get('description'))
			, mysql_escape_string($this->get('lastModifiedBy'))
			);
		}
		if($this->db->query($query)) {
			$this->insert_id = ($this->db->insert_id == 0) ? $this->get('eventId') : $this->db->insert_id;
			$this->lg->set('table', 'events');
			$this->lg->set('query', $query);
			$this->lg->save();
			return(true);
		}
		return (false);
	}

	public function delete($eventId) {
		if($eventId == '') return false;
		if(!$this->recordExists($eventId)) return false;
		$query = sprintf("DELETE FROM `events` WHERE `eventId` = '%s' ", mysql_escape_string($eventId));
		if($this->db->query($query)) {
			$this->lg->set('table', 'events');
			$this->lg->set('query', $query);
			$this->lg->save();
			return  true;
		}
		return false;
	}
	
	public function addImageEvent($imageId, $eventId) {
		if($imageId == '' || $eventId == '') return false;
		if(!$this->recordExists($eventId)) return false;
		$query = sprintf("INSERT INTO event_images SET `imageId` = '%s', `eventId` = '%s'"
				, mysql_escape_string($imageId)
				, mysql_escape_string($eventId) 
				);
		if($this->db->query($query)) {
			$id = $this->db->insert_id;
			$this->lg->set('table', 'event_images');
			$this->lg->set('query', $query);
			$this->lg->save();
			return $id;
		} else {
			return false;
		}
	}
	
	public function deleteImageEvent($imageId, $eventId) {
		if($imageId == '' || $eventId == '') return false;
		if(!$this->recordExists($eventId)) return false;
		$query = sprintf("DELETE FROM `event_images` WHERE `imageId` = '%s' AND `eventId` = '%s' ", mysql_escape_string($imageId), mysql_escape_string($eventId));
		if($this->db->query($query)) {
			$this->lg->set('table', 'event_images');
			$this->lg->set('query', $query);
			$this->lg->save();
			return true;
		} else {
			return false;
		}
	}

/*	
	public function listImagesByEvent($eventId,$attributesFlag = true) {
		$query = sprintf("SELECT imageId FROM `event_images` WHERE `eventId` = '%s';", mysql_escape_string($eventId));
		$records = $this->db->query_all($query);
		if(count($records)) {
			foreach($records as $record) {
				$imageId = $record->imageId;
				$array[] = $imageId;
			}
			return $array;
		}
		return false;
	}
*/
	
	public function listImagesByEvent($eventId,$size = 'l',$attributesFlag = true) {
		$query = sprintf("SELECT e.`imageId`, i.`filename`, i.`barcode`, i.`storage_id`, i.`path`  FROM `event_images` e LEFT OUTER JOIN image i ON e.`imageId` = i.`image_id` WHERE e.`eventId` = '%s';", mysql_escape_string($eventId));
		$records = $this->db->query_all($query);
		if(count($records)) {
			$storage = new Storage($this->db);
			if($attributesFlag) {
				$image = new Image($this->db);
			}
			
			if(isset($size) && in_array($size, array('s','m','l'))) {
				$size = "_".$size;
			} else {
				$size = "";
			}
			
			foreach($records as &$record) {
				$device = $storage->get($record->storage_id);
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
					$record->attributes = $image->get_all_attributes($record->image_id);
				}
			}
			return $records;
		}
		return false;
	}
	
	public function get_all_events($imageId) {
		$query = sprintf("SELECT eventId FROM `event_images` WHERE `imageId` = '%s';", mysql_escape_string($imageId));
		$records = $this->db->query_all($query);
		if(count($records)) {
			foreach($records as $record) {
				$eventId = $record->eventId;
				$query = sprintf("SELECT title FROM `events` WHERE `eventId` = '%s';", mysql_escape_string($eventId));
				$list = $this->db->query_one($query);
				$tmpArray['id'] = $eventId;
				$tmpArray['name'] = $list->title;
				$array[] = $tmpArray;
			}
			return $array;
		}
		return false;
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
	public function setData($data) {
	$this->data = $data;
	return( true );
	}
	
	/**
	* Returns a since field value
	* @return mixed
	*/
	public function get( $field ) {
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
	public function set( $field, $value ) {
		$this->{$field} = $value;
		return( true );
	}
	
	public function load_by_id( $eventTypeId ) {
		if($eventTypeId == '') return false;
		$query = sprintf("SELECT * FROM `event_types` WHERE `eventTypeId` = %s ", mysql_escape_string($eventTypeId) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->set($field, $value);
			}
			return(true);
		} else {
			return(false);
		}
	}

	public function listRecords($queryFlag = true) {
		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " WHERE " . $where;
		}
		if($this->data['eventTypeId'] != '') {
			$where .= sprintf(" AND `eventTypeId` = '%s' ", mysql_escape_string($this->data['eventTypeId']));
		}
		if($this->data['title'] != '') {
			$where .= sprintf(" AND `title` = '%s' ", mysql_escape_string($this->data['title']));
		}
		if($this->data['field'] != '' && $this->data['value'] != '') {
			$where .= sprintf(" AND `%s` = '%s' ", mysql_escape_string($this->data['field']), mysql_escape_string($this->data['value']));
		}

		$where .= build_order( $this->data['order']);
		$where .= build_limit($this->data['start'], $this->data['limit']);

		$query = "SELECT SQL_CALC_FOUND_ROWS `eventTypeId`, `title`, `description`, `lastModifiedBy`, `modifiedTime` FROM `event_types` " . $where;

		if($queryFlag) {
			$ret = $this->db->query_all( $query );
			return is_null($ret) ? array() : $ret;
		} else {
			$ret = $this->db->query( $query );
			return $ret;
		}
	}

	public function recordExists ($eventTypeId){
		if($eventTypeId == '' || is_null($eventTypeId)) return false;
		$query = sprintf("SELECT `eventTypeId` FROM `event_types` WHERE `eventTypeId` = %s;", mysql_escape_string($eventTypeId) );
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}

	public function save() {
		if($this->recordExists($this->get('eventTypeId'))) {
			$query = sprintf("UPDATE `event_types` SET  `title` = '%s', `description` = '%s', `lastModifiedBy` = '%s', `modifiedTime` = NOW() WHERE `eventTypeId` = '%s' ;"
			, mysql_escape_string($this->get('title'))
			, mysql_escape_string($this->get('description'))
			, mysql_escape_string($this->get('lastModifiedBy'))
			, mysql_escape_string($this->get('eventTypeId'))
			);
		} else {
			$query = sprintf("INSERT IGNORE INTO `event_types` SET `title` = '%s', `description` = '%s', `lastModifiedBy` = '%s', `modifiedTime` = NOW() ;"
			, mysql_escape_string($this->get('title'))
			, mysql_escape_string($this->get('description'))
			, mysql_escape_string($this->get('lastModifiedBy'))
			);
		}
		if($this->db->query($query)) {
			$this->insert_id = ($this->db->insert_id == 0) ? $this->get('eventTypeId') : $this->db->insert_id;
			$this->lg->set('table', 'event_types');
			$this->lg->set('query', $query);
			$this->lg->save();
			return(true);
		}
		return (false);
	}

	public function delete($eventTypeId) {
		if($eventTypeId == '') return false;
		if(!$this->recordExists($eventTypeId)) return false;
		$query = sprintf("DELETE FROM `event_types` WHERE `eventTypeId` = '%s' ", mysql_escape_string($eventTypeId));
		if($this->db->query($query)) {
			$this->lg->set('table', 'event_types');
			$this->lg->set('query', $query);
			$this->lg->save();
			return  true;
		}
		return false;
	}

}

?>