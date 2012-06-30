<?php

Class RemoteAccess {

	public $db, $record;

	public function __construct($db = null) {
		$this->db = $db;
		$this->record['active'] = 'true';
	}
	
	public function set( $field, $value ) {
		$this->record[$field] = $value;
		return(true);
	}
	
	public function get( $field ) {
		if (isset($this->record[$field])) {
			return( $this->record[$field] );
		} else {
			return(false);
		}
	}
	
	public function save() {
		if($this->check_duplicate($this->get('ip'),$this->get('key'))) {
			return true;
		} else {
			$query = sprintf("INSERT IGNORE INTO `remoteaccess` SET `ip` = '%s', `key` = '%s', `active` = '%s' ;"
			, mysql_escape_string($this->get('ip'))
			, mysql_escape_string($this->get('key'))
			, mysql_escape_string($this->get('active'))
			);
			if($this->db->query($query)) {
				return(true);
			} else {
				return (false);
			}
		}
	}
	
	public function list_all() {
		$query = "SELECT * FROM remoteaccess";
		$ret = $this->db->query($query);
		return $ret;
	}
	
	public function checkRemoteAccess($ip, $tmpKey) {
		return true; //To temporarly disable this check and always validate
		$query = sprintf("SELECT count(*) AS cnt FROM `remoteaccess` WHERE `ip` = '%s' AND `key` = '%s' AND `active` = '%s' ;"
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
	
	public function check_duplicate($ip, $tmpKey) {
		$query = sprintf("SELECT count(*) AS cnt FROM `remoteaccess` WHERE `ip` = '%s' AND `key` = '%s' ;"
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