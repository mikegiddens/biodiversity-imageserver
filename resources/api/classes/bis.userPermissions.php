<?php

class UserPermissions
{
	public $db;

	function __construct( $db = null ) {
		$this->db = $db;
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
	
	public function getPermission() {
		$query = sprintf(" SELECT * FROM user_permissions WHERE 0=0 AND userId = '%s' AND  event = '%s' AND C = '%s' AND R = '%s' AND U = '%s' AND D = '%s' AND G = '%s' "
		, mysql_escape_string($this->data['userId'])
		, mysql_escape_string($this->data['event'])
		, mysql_escape_string($this->data['C'])
		, mysql_escape_string($this->data['R'])
		, mysql_escape_string($this->data['U'])
		, mysql_escape_string($this->data['D'])
		, mysql_escape_string($this->data['G'])
		);
		$ret = $this->db->query_one($query);
		return (is_null($ret)) ? false : true;
	}

}
?>