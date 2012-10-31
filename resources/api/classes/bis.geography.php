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

	// public function geographyList($queryFlag = true) {
		// $where = buildWhere($this->data['filter']);
		// if ($where != '') {
			// $where = " WHERE " . $where;
		// }
		// if($this->data['countryIso'] != '') {
			// $where .= sprintf(" AND `countryIso` = '%s' ", mysql_escape_string($this->data['countryIso']));
		// }
		// if($this->data['value'] != '') {
			// switch($this->data['searchFormat']) {
				// case 'exact':
					// $where .= sprintf(" AND `country` = '%s' ", mysql_escape_string($this->data['value']));
					// break;
				// case 'left':
					// $where .= sprintf(" AND `country` LIKE '%s%%' ", mysql_escape_string($this->data['value']));
					// break;
				// case 'right':
					// $where .= sprintf(" AND `country` LIKE '%%%s' ", mysql_escape_string($this->data['value']));
					// break;
				// case 'both':
				// default:
					// $where .= sprintf(" AND `country` LIKE '%%%s%%' ", mysql_escape_string($this->data['value']));
					// break;
			// }
		// }
		
		// if($this->data['group'] != '' && in_array($this->data['group'], array('geographyId','country','countryIso','admin0','admin1','admin2','admin3')) && $this->data['dir'] != '') {
			// $where .= build_order( array(array('field' => $this->data['group'], 'dir' => $this->data['dir'])));
		// } else {
			// $where .= ' ORDER BY `geographyId` ASC ';
		// }
		
		// $where .= build_limit($this->data['start'], $this->data['limit']);

		// $query = "SELECT SQL_CALC_FOUND_ROWS `geographyId`,`country`,`countryIso`,`admin0`,`admin1`,`admin2`,`admin3` FROM  `geography` " . $where;

		// if($queryFlag) {
			// $ret = $this->db->query_all( $query );
			// return is_null($ret) ? array() : $ret;
		// } else {
			// $ret = $this->db->query( $query );
			// return $ret;
		// }
	// }

	public function geographyList($queryFlag = true) {
		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " WHERE " . $where;
		}
		if($this->data['ISO'] != '') {
			$where .= sprintf(" AND `ISO` = '%s' ", mysql_escape_string($this->data['ISO']));
		}
		if($this->data['value'] != '') {
			switch($this->data['searchFormat']) {
				case 'exact':
					$where .= sprintf(" AND `NAME_0` = '%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'left':
					$where .= sprintf(" AND `NAME_0` LIKE '%s%%' ", mysql_escape_string($this->data['value']));
					break;
				case 'right':
					$where .= sprintf(" AND `NAME_0` LIKE '%%%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'both':
				default:
					$where .= sprintf(" AND `NAME_0` LIKE '%%%s%%' ", mysql_escape_string($this->data['value']));
					break;
			}
		}
		
		if(is_array($this->data['geographyId']) && count($this->data['geographyId'])) {
			$where .= sprintf(" AND `geographyId` IN (%s) ", implode(',', $this->data['geographyId']));
		} else if($this->data['geographyId'] != '') {
			$where .= sprintf(" AND `geographyId` = '%s' ", mysql_escape_string($this->data['geographyId']));
		}
		
		if($this->data['group'] != '' && in_array($this->data['group'], array('geographyId','ISO', 'NAME_0', 'NAME_1', 'VARNAME_1', 'ENGTYPE_1', 'NAME_2', 'VARNAME_2', 'NAME_3', 'VARNAME_3', 'NAME_4', 'VARNAME_4', 'NAME_5', 'source')) && $this->data['dir'] != '') {
			$where .= build_order( array(array('field' => $this->data['group'], 'dir' => $this->data['dir'])));
		} else {
			$where .= ' ORDER BY `geographyId` ASC ';
		}
		
		$where .= build_limit($this->data['start'], $this->data['limit']);

		$query = "SELECT SQL_CALC_FOUND_ROWS `ISO`, `NAME_0`, `NAME_1`, `VARNAME_1`, `ENGTYPE_1`, `NAME_2`, `VARNAME_2`, `NAME_3`, `VARNAME_3`, `NAME_4`, `VARNAME_4`, `NAME_5`, `source` FROM `geography` " . $where;

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
	
	// public function geographyCountryExists($country) {
		// $query = sprintf("SELECT `geographyId` FROM `geography` WHERE `country` = '%s';", mysql_escape_string($country));
		// $ret = $this->db->query_one( $query );
		// if ($ret == NULL) {
			// return false;
		// } else {
			// return true;
		// }
	// }

	// public function geographyCountryIsoExists($countryIso) {
		// $query = sprintf("SELECT `geographyId` FROM `geography` WHERE `countryIso` = '%s';", mysql_escape_string($countryIso));
		// $ret = $this->db->query_one( $query );
		// if ($ret == NULL) {
			// return false;
		// } else {
			// return true;
		// }
	// }

	public function geographyISOExists($countryIso) {
		$query = sprintf("SELECT `geographyId` FROM `geography` WHERE `countryIso` = '%s';", mysql_escape_string($countryIso));
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}

	public function geographySave() {
			// $query = sprintf("INSERT IGNORE INTO `geography` SET `country` = '%s', `countryIso` = '%s', `admin0` = '%s', `admin1` = '%s', `admin2` = '%s', `admin3` = '%s' ;"
			// , mysql_escape_string($this->geographyGetProperty('country'))
			// , mysql_escape_string($this->geographyGetProperty('countryIso'))
			// , mysql_escape_string($this->geographyGetProperty('admin0'))
			// , mysql_escape_string($this->geographyGetProperty('admin1'))
			// , mysql_escape_string($this->geographyGetProperty('admin2'))
			// , mysql_escape_string($this->geographyGetProperty('admin3'))
			// );
			$query = sprintf("INSERT IGNORE INTO `geography` SET `ISO` = '%s', `NAME_0` = '%s', `NAME_1` = '%s', `VARNAME_1` = '%s', `ENGTYPE_1` = '%s', `NAME_2` = '%s', `VARNAME_2` = '%s', `NAME_3` = '%s', `VARNAME_3` = '%s', `NAME_4` = '%s', `VARNAME_4` = '%s', `NAME_5` = '%s', `source` = 'user' ;"
			, mysql_escape_string($this->geographyGetProperty('ISO'))
			, mysql_escape_string($this->geographyGetProperty('NAME_0'))
			, mysql_escape_string($this->geographyGetProperty('NAME_1'))
			, mysql_escape_string($this->geographyGetProperty('VARNAME_1'))
			, mysql_escape_string($this->geographyGetProperty('ENGTYPE_1'))
			, mysql_escape_string($this->geographyGetProperty('NAME_2'))
			, mysql_escape_string($this->geographyGetProperty('VARNAME_2'))
			, mysql_escape_string($this->geographyGetProperty('NAME_3'))
			, mysql_escape_string($this->geographyGetProperty('VARNAME_3'))
			, mysql_escape_string($this->geographyGetProperty('NAME_4'))
			, mysql_escape_string($this->geographyGetProperty('VARNAME_4'))
			, mysql_escape_string($this->geographyGetProperty('NAME_5'))
			);
		if($this->db->query($query)) {
			return $this->db->insert_id;
		}
		return false;
	}

	public function geographyUpdate() {
		if($this->geographyExists($this->geographyGetProperty('geographyId'))) {
			$query = sprintf("UPDATE `geography` SET  `ISO` = '%s', `NAME_0` = '%s', `NAME_1` = '%s', `VARNAME_1` = '%s', `ENGTYPE_1` = '%s', `NAME_2` = '%s', `VARNAME_2` = '%s', `NAME_3` = '%s', `VARNAME_3` = '%s', `NAME_4` = '%s', `VARNAME_4` = '%s', `NAME_5` = '%s', `source` = 'user' WHERE `geographyId` = '%s' ;"
			, mysql_escape_string($this->geographyGetProperty('ISO'))
			, mysql_escape_string($this->geographyGetProperty('NAME_0'))
			, mysql_escape_string($this->geographyGetProperty('NAME_1'))
			, mysql_escape_string($this->geographyGetProperty('VARNAME_1'))
			, mysql_escape_string($this->geographyGetProperty('ENGTYPE_1'))
			, mysql_escape_string($this->geographyGetProperty('NAME_2'))
			, mysql_escape_string($this->geographyGetProperty('VARNAME_2'))
			, mysql_escape_string($this->geographyGetProperty('NAME_3'))
			, mysql_escape_string($this->geographyGetProperty('VARNAME_3'))
			, mysql_escape_string($this->geographyGetProperty('NAME_4'))
			, mysql_escape_string($this->geographyGetProperty('VARNAME_4'))
			, mysql_escape_string($this->geographyGetProperty('NAME_5'))
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

	public function geographyByImage($imageId = '') {
		if($imageId == '' || !is_numeric($imageId) ) return false;
		// $query = sprintf("SELECT g.`geographyId`, g.`country`, g.`countryIso`, g.`admin0` FROM `geography` g, `events` e, `eventImages` ei WHERE e.`eventId` = ei.`eventId` AND e.`geographyId` = g.`geographyId` AND ei.`imageId` = %s", mysql_escape_string($imageId));
		$query = sprintf("SELECT g.`geographyId`, g.`NAME_0`, g.`ISO`, g.`NAME_1`, g.`NAME_2` FROM `geography` g, `events` e, `eventImages` ei WHERE e.`eventId` = ei.`eventId` AND e.`geographyId` = g.`geographyId` AND ei.`imageId` = %s", mysql_escape_string($imageId));
		$ret = $this->db->query_all($query);
		return is_null($ret) ? array() : $ret;
	}
	
}
?>