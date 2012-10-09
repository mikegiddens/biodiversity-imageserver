<?php

class LogClass
{
	public $db;

	public function __construct($db = null) {
		$this->db = $db;
	}

	/**
	* Set the value to Data
	* @param mixed $data : input data
	* @return bool
	*/
	public function logSetData($data) {
		$this->data = $data;
		return( true );
	}
	
	/**
	* Returns a since field value
	* @return mixed
	*/
	public function logGetProperty( $field ) {
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
	public function logSetProperty( $field, $value ) {
		$this->{$field} = $value;
		return( true );
	}
	
	public function logLoadById( $logId ) {
		if($geoId == '') return false;
		$query = sprintf("SELECT * FROM `log` WHERE `logId` = %s ", mysql_escape_string($logId) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->logSetProperty($field, $value);
			}
			return(true);
		} else {
			return(false);
		}
	}

	public function logListRecords($queryFlag = true) {
		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " WHERE " . $where;
		}
		if($this->data['logId'] != '') {
			$where .= sprintf(" AND `logId` = '%s' ", mysql_escape_string($this->data['logId']));
		}
		if($this->data['action'] != '') {
			$where .= sprintf(" AND `action` = '%s' ", mysql_escape_string($this->data['action']));
		}
		if($this->data['table'] != '') {
			$where .= sprintf(" AND `table` = '%s' ", mysql_escape_string($this->data['table']));
		}
		if($this->data['lastModifiedBy'] != '') {
			$where .= sprintf(" AND `lastModifiedBy` = '%s' ", mysql_escape_string($this->data['lastModifiedBy']));
		}
		if($this->data['field'] != '' && $this->data['value'] != '') {
			$where .= sprintf(" AND `%s` = '%s' ", mysql_escape_string($this->data['field']), mysql_escape_string($this->data['value']));
		}

		$where .= build_order( $this->data['order']);
		$where .= build_limit($this->data['start'], $this->data['limit']);

		$query = "SELECT SQL_CALC_FOUND_ROWS `logId`,`action`,`table`,`query`,`lastModifiedBy`,`modifiedTime` FROM  `log` " . $where;

		if($queryFlag) {
			$ret = $this->db->query_all( $query );
			return is_null($ret) ? array() : $ret;
		} else {
			$ret = $this->db->query( $query );
			return $ret;
		}
	}


	public function logRecordExists ($logId){
		if($logId == '' || is_null($logId)) return false;
		$query = sprintf("SELECT `logId` FROM `log` WHERE `logId` = %s;", mysql_escape_string($logId) );
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}

	public function logSave() {
		global $config;
		if(isset($config['log']) && in_array($config['log'],array(true,'true',1,'1'))) {
			$query = sprintf("INSERT IGNORE INTO `log` SET `action` = '%s', `table` = '%s', `query` = '%s', `lastModifiedBy` = '%s', `modifiedTime` = now() ;"
			, mysql_escape_string($this->logGetProperty('action'))
			, mysql_escape_string($this->logGetProperty('table'))
			, mysql_escape_string($this->logGetProperty('query'))
			, mysql_escape_string($this->logGetProperty('lastModifiedBy'))
			);
			if($this->db->query($query)) {
				$this->insert_id = $this->db->insert_id;
				return(true);
			}
			return (false);
		}
		return(true);
	}

}
?>