<?php

class Set 
{
	public $db, $record;
	
	public function __construct($db) {
		$this->db = $db;
	}
	
	public function set( $field, $value ) {
		$this->record[$field] = $value;
		return( true );
	}
	
	public function load_by_id( $setId ) {
		if($setId == '') return false;
		$query = sprintf("SELECT * FROM `set` WHERE `id` = %s ", mysql_escape_string($setId) );
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
	
	public function addSet($name, $description) {
		if($name == '' || $description == '') return false;
		$query = sprintf("INSERT INTO `set` SET `name` = '%s', `description` = '%s'"
				, mysql_escape_string($name)
				, mysql_escape_string($description)
				);
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function editSet($sId, $name, $description) {
		if($name == '' || $description == '' || $sId == '') return false;
		$query = sprintf("UPDATE `set` SET `name` = '%s', `description` = '%s' WHERE `id` = '%s'"
				, mysql_escape_string($name)
				, mysql_escape_string($description)
				, mysql_escape_string($sId)
				);
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
}

?>