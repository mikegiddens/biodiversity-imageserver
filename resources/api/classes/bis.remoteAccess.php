<?php

Class RemoteAccess {

	public $db, $record;

	public function __construct($db = null) {
		$this->db = $db;
		$this->record['active'] = 'true';
	}
	
	public function remoteAccessSetProperty( $field, $value ) {
		$this->record[$field] = $value;
		return(true);
	}
	
	public function remoteAccessGetProperty( $field ) {
		if (isset($this->record[$field])) {
			return( $this->record[$field] );
		} else {
			return(false);
		}
	}
	
	public function remoteAccessSetData($data) {
		$this->data = $data;
		return( true );
	}

	public function remoteAccessLoadById($remoteAccessId) {
		if($remoteAccessId == '' || !is_numeric($remoteAccessId) || is_null($remoteAccessId)) return false;
		$query = sprintf("SELECT * FROM `remoteAccess` WHERE `remoteAccessId` = %d", mysql_escape_string($remoteAccessId) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->remoteAccessSetProperty($field, $value);
			}
			return(true);
		} else {
			return(false);
		}
	}
	public function remoteAccessTitleExists($title) {
		$query = sprintf("SELECT `remoteAccessId` FROM `remoteAccess` WHERE `title` = '%s';", mysql_escape_string($title));
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}
	
	public function remoteAccessKeyGenerate() {
		return uniqid();
	}
	
	public function remoteAccessSave() {
		if($this->remoteAccessCheckDuplicate($this->remoteAccessGetProperty('ip'),$this->remoteAccessGetProperty('key'))) {
			return true;
		} else {
			$query = sprintf("INSERT IGNORE INTO `remoteAccess` SET `title` = '%s', `description` = '%s', `originalIp` = '%s', `ip` = '%s', `key` = '%s', `active` = '%s' ;"
			, mysql_escape_string($this->remoteAccessGetProperty('title'))
			, mysql_escape_string($this->remoteAccessGetProperty('description'))
			, mysql_escape_string($this->remoteAccessGetProperty('originalIp'))
			, mysql_escape_string($this->remoteAccessGetProperty('ip'))
			, mysql_escape_string($this->remoteAccessGetProperty('key'))
			, mysql_escape_string($this->remoteAccessGetProperty('active'))
			);
			if($this->db->query($query)) {
				return($this->db->insert_id);
			} else {
				return (false);
			}
		}
	}
	
	public function remoteAccessUpdate() {
		$query = sprintf("UPDATE `remoteAccess` SET `title` = '%s', `description` = '%s', `originalIp` = '%s', `ip` = '%s', `key` = '%s', `active` = '%s' WHERE `remoteAccessId` = '%s' ;"
		, mysql_escape_string($this->remoteAccessGetProperty('title'))
		, mysql_escape_string($this->remoteAccessGetProperty('description'))
		, mysql_escape_string($this->remoteAccessGetProperty('originalIp'))
		, mysql_escape_string($this->remoteAccessGetProperty('ip'))
		, mysql_escape_string($this->remoteAccessGetProperty('key'))
		, mysql_escape_string($this->remoteAccessGetProperty('active'))
		, mysql_escape_string($this->remoteAccessGetProperty('remoteAccessId'))
		);
		if($this->db->query($query)) {
			return(true);
		} else {
			return (false);
		}
	}
	
	public function remoteAccessList() {
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM remoteAccess ";
		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " WHERE " . $where;
		}
		if(trim($this->data['start']) != '' && trim($this->data['limit']) != '') {
			$where .= build_limit($this->data['start'], $this->data['limit']);
		}
		$query .= $where;
		$ret = $this->db->query($query);
		return $ret;
	}

	public function remoteAccessDelete($remoteAccessId) {
			$query = sprintf("DELETE FROM `remoteAccess` WHERE `remoteAccessId` = '%s' ;"
			, mysql_escape_string($remoteAccessId));
			if($this->db->query($query)) {
				return(true);
			} else {
				return(false);
			}
	}
	
	public function remoteAccessCheck($ip, $tmpKey) {
		// return true; //To temporarly disable this check and always validate
		$query = sprintf("SELECT count(*) AS cnt FROM `remoteAccess` WHERE `ip` = '%s' AND `key` = '%s' AND `active` = '%s' ;"
		, mysql_escape_string($ip)
		, mysql_escape_string($tmpKey)
		, "true"
		);
		$ret = $this->db->query_one($query);
		if($ret->cnt) {
			return true;
		} else {
			return false;
		}
	}
	
	public function remoteAccessCheckDuplicate($ip, $tmpKey) {
		$query = sprintf("SELECT count(*) AS cnt FROM `remoteAccess` WHERE `ip` = '%s' AND `key` = '%s' ;"
		, mysql_escape_string($ip)
		, mysql_escape_string($tmpKey)
		);
		$ret = $this->db->query_one($query);
		if($ret->cnt) {
			return true;
		} else {
			return false;
		}
	}

}

?>