<?php

class Geography
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
	
	public function load_by_id( $geoId ) {
		if($geoId == '') return false;
		$query = sprintf("SELECT * FROM `geography` WHERE `id` = %s ", mysql_escape_string($geoId) );
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
		if($this->data['geoId'] != '') {
			$where .= sprintf(" AND `id` = '%s' ", mysql_escape_string($this->data['geoId']));
		}
		if($this->data['country'] != '') {
			$where .= sprintf(" AND `country` = '%s' ", mysql_escape_string($this->data['country']));
		}
		if($this->data['country_iso'] != '') {
			$where .= sprintf(" AND `country_iso` = '%s' ", mysql_escape_string($this->data['country_iso']));
		}
		if($this->data['field'] != '' && $this->data['value'] != '') {
			$where .= sprintf(" AND `%s` = '%s' ", mysql_escape_string($this->data['field']), mysql_escape_string($this->data['value']));
		}

		$where .= build_order( $this->data['order']);
		$where .= build_limit($this->data['start'], $this->data['limit']);

		$query = "SELECT SQL_CALC_FOUND_ROWS `id`,`country`,`country_iso`,`admin_0`,`admin_1`,`admin_2`,`admin_3` FROM  `geography` " . $where;

		if($queryFlag) {
			$ret = $this->db->query_all( $query );
			return is_null($ret) ? array() : $ret;
		} else {
			$ret = $this->db->query( $query );
			return $ret;
		}
	}


}
?>