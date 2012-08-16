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
	public function geographySetData($data) {
		$this->data = $data;
		return( true );
	}
	
	/**
	* Returns a since field value
	* @return mixed
	*/
	public function geographyGetProperty( $field ) {
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
	public function geographySetProperty( $field, $value ) {
		$this->{$field} = $value;
		return( true );
	}
	
	public function geographyLoadById( $geoId ) {
		if($geoId == '') return false;
		$query = sprintf("SELECT * FROM `geography` WHERE `geographyId` = %s ", mysql_escape_string($geoId) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->geographySetProperty($field, $value);
			}
			return(true);
		} else {
			return(false);
		}
	}

	public function geographyList($queryFlag = true) {
		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " WHERE " . $where;
		}
		if($this->data['countryIso'] != '') {
			$where .= sprintf(" AND `countryIso` = '%s' ", mysql_escape_string($this->data['countryIso']));
		}
		if($this->data['value'] != '') {
			switch($this->data['searchFormat']) {
				case 'exact':
					$where .= sprintf(" AND `country` = '%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'left':
					$where .= sprintf(" AND `country` LIKE '%s%%' ", mysql_escape_string($this->data['value']));
					break;
				case 'right':
					$where .= sprintf(" AND `country` LIKE '%%%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'both':
				default:
					$where .= sprintf(" AND `country` LIKE '%%%s%%' ", mysql_escape_string($this->data['value']));
					break;
			}
		}
		
		if($this->data['group'] != '' && in_array($this->data['group'], array('geographyId','country','countryIso','admin0','admin1','admin2','admin3')) && $this->data['dir'] != '') {
			$where .= build_order( array(array('field' => $this->data['group'], 'dir' => $this->data['dir'])));
		} else {
			$where .= ' ORDER BY `geographyId` ASC ';
		}
		
		$where .= build_limit($this->data['start'], $this->data['limit']);

		$query = "SELECT SQL_CALC_FOUND_ROWS `geographyId`,`country`,`countryIso`,`admin0`,`admin1`,`admin2`,`admin3` FROM  `geography` " . $where;

		if($queryFlag) {
			$ret = $this->db->query_all( $query );
			return is_null($ret) ? array() : $ret;
		} else {
			$ret = $this->db->query( $query );
			return $ret;
		}
	}

	public function geographyExists($geographyId) {
		$query = sprintf("SELECT `geographyId` FROM `geography` WHERE `geographyId` = '%s';", mysql_escape_string($geographyId));
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}
	
	public function geographyCountryExists($country) {
		$query = sprintf("SELECT `geographyId` FROM `geography` WHERE `country` = '%s';", mysql_escape_string($country));
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}

	public function geographyCountryIsoExists($countryIso) {
		$query = sprintf("SELECT `geographyId` FROM `geography` WHERE `countryIso` = '%s';", mysql_escape_string($countryIso));
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}


	public function geographySave() {
			$query = sprintf("INSERT IGNORE INTO `geography` SET `country` = '%s', `countryIso` = '%s', `admin0` = '%s', `admin1` = '%s', `admin2` = '%s', `admin3` = '%s' ;"
			, mysql_escape_string($this->geographyGetProperty('country'))
			, mysql_escape_string($this->geographyGetProperty('countryIso'))
			, mysql_escape_string($this->geographyGetProperty('admin0'))
			, mysql_escape_string($this->geographyGetProperty('admin1'))
			, mysql_escape_string($this->geographyGetProperty('admin2'))
			, mysql_escape_string($this->geographyGetProperty('admin3'))
			);
		if($this->db->query($query)) {
			return $this->db->insert_id;
		}
		return false;
	}

	public function geographyUpdate() {
		if($this->geographyExists($this->geographyGetProperty('geographyId'))) {
			$query = sprintf("UPDATE `geography` SET  `country` = '%s', `countryIso` = '%s', `admin0` = '%s', `admin1` = '%s', `admin2` = '%s', `admin3` = '%s' WHERE `geographyId` = '%s' ;"
			, mysql_escape_string($this->geographyGetProperty('country'))
			, mysql_escape_string($this->geographyGetProperty('countryIso'))
			, mysql_escape_string($this->geographyGetProperty('admin0'))
			, mysql_escape_string($this->geographyGetProperty('admin1'))
			, mysql_escape_string($this->geographyGetProperty('admin2'))
			, mysql_escape_string($this->geographyGetProperty('admin3'))
			, mysql_escape_string($this->geographyGetProperty('geographyId'))
			);
			if($this->db->query($query)) {
				return true;
			}
		} 	
		return false;
	}

	public function geographyDelete($geographyId) {
		if($geographyId == '') return false;
		if(!$this->geographyExists($geographyId)) return false;
		$query = sprintf("DELETE FROM `geography` WHERE `geographyId` = '%s' ", mysql_escape_string($geographyId));
		if($this->db->query($query)) {
			return  true;
		}
		return false;
	}
	
}
?>