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
		if($this->data['iso'] != '') {
			$where .= sprintf(" AND `iso` = '%s' ", mysql_escape_string($this->data['iso']));
		}
		if($this->data['value'] != '') {
			switch($this->data['searchFormat']) {
				case 'exact':
					$where .= sprintf(" AND `name` = '%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'left':
					$where .= sprintf(" AND `name` LIKE '%s%%' ", mysql_escape_string($this->data['value']));
					break;
				case 'right':
					$where .= sprintf(" AND `name` LIKE '%%%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'both':
				default:
					$where .= sprintf(" AND `name` LIKE '%%%s%%' ", mysql_escape_string($this->data['value']));
					break;
			}
		}
		
		if(is_array($this->data['geographyId']) && count($this->data['geographyId'])) {
			$where .= sprintf(" AND `geographyId` IN (%s) ", implode(',', $this->data['geographyId']));
		} else if($this->data['geographyId'] != '') {
			$where .= sprintf(" AND `geographyId` = '%s' ", mysql_escape_string($this->data['geographyId']));
		}

		if(is_array($this->data['advFilter']) && count($this->data['advFilter'])) {
			$advFilter = $this->data['advFilter'];
			switch($advFilter['node']){
				case 'group':
					$strArray = array();
					if(is_array($advFilter['children']) && count($advFilter['children'])) {
						$logop = $advFilter['logop'];
						foreach($advFilter['children'] as $child) {
							if($child['object'] == 'geography') {
								switch($child['condition']) {
									case '=':
									case '!=':
										$op = $child['condition'];
										$strArray[] = sprintf(" `%s` $op '%s' ", mysql_escape_string($child['key']), mysql_escape_string($child['value']));
										break;
									case 'is':
										$strArray[] = sprintf(" `%s` = '%s' ", mysql_escape_string($child['key']), mysql_escape_string($child['value']));
											break;
									case '%s':
									case 's%':
									case '%s%':
										$op = str_replace('%','%%',$child['condition']);
										$op = str_replace('s','%s',$op);
										$strArray[] = sprintf(" `%s` LIKE '$op' " , mysql_escape_string($child['key']), mysql_escape_string($child['value']));
										break;
								}
							}
						}
						if(count($strArray)) {
							$where .= ' AND ( ' . implode($logop, $strArray) . ' ) ';
						}
					}
					break;
				case 'condition':
					if($advFilter['object'] == 'geography') {
						switch($advFilter['condition']) {
							case '=':
							case '!=':
								$op = $advFilter['condition'];
								$where .= sprintf(" AND `%s` $op '%s' ", mysql_escape_string($advFilter['key']), mysql_escape_string($advFilter['value']));
								break;
							case 'is':
								$where .= sprintf(" AND `%s` = '%s' ", mysql_escape_string($advFilter['key']), mysql_escape_string($advFilter['value']));
									break;
							case '%s':
							case 's%':
							case '%s%':
								$op = str_replace('%','%%',$advFilter['condition']);
								$op = str_replace('s','%s',$op);
								$where .= sprintf(" AND `%s` LIKE '$op' " , mysql_escape_string($advFilter['key']), mysql_escape_string($advFilter['value']));
								break;
						}
					}
					break;
			}
		}
		if($this->data['rank'] != '') {
			$where .= sprintf(" AND `rank` = '%s' ", mysql_escape_string($this->data['rank']));
		}
		
		if($this->data['group'] != '' && in_array($this->data['group'], array('geographyId','iso', 'name', 'varname', 'parentId', 'rank','source')) && $this->data['dir'] != '') {
			$where .= build_order( array(array('field' => $this->data['group'], 'dir' => $this->data['dir'])), array('geographyId','parentId','rank'));
		} else {
			$where .= ' ORDER BY `geographyId` ASC ';
		}
		
		$where .= build_limit($this->data['start'], $this->data['limit']);

		if($this->data['rank'] != '') {
			$query = "SELECT SQL_CALC_FOUND_ROWS `name`, `rank`, `source` FROM `geography` " . $where;
		} else {
			$query = "SELECT SQL_CALC_FOUND_ROWS `geographyId`, `iso`, `name`, `varname`, `parentId`, `source` FROM `geography` " . $where;
		}

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
	
	public function geographyNameExists($name) {
		$query = sprintf("SELECT count(*) ct FROM `geography` WHERE `name` = '%s';", mysql_escape_string($name));
		$ret = $this->db->query_one( $query );
		if ($ret->ct) {
			return true;
		} else {
			return false;
		}
	}

	public function geographyISOExists($iso) {
		$query = sprintf("SELECT `geographyId` FROM `geography` WHERE `iso` = '%s';", mysql_escape_string($iso));
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}

	public function geographySave() {
			$query = sprintf("INSERT IGNORE INTO `geography` SET `source` = 'user', `parentId` = '%s', `name` = '%s', `varname` = '%s', `iso` = '%s', `rank` = '%s' ;"
			, mysql_escape_string($this->geographyGetProperty('parentId'))
			, mysql_escape_string($this->geographyGetProperty('name'))
			, mysql_escape_string($this->geographyGetProperty('varname'))
			, mysql_escape_string($this->geographyGetProperty('iso'))
			, mysql_escape_string($this->geographyGetProperty('rank'))
			);
		if($this->db->query($query)) {
			return $this->db->insert_id;
		}
		return false;
	}

	public function geographyUpdate() {
		if($this->geographyExists($this->geographyGetProperty('geographyId'))) {
			$query = sprintf("UPDATE `geography` SET `parentId` = '%s', `name` = '%s', `varname` = '%s', `iso` = '%s', `rank` = '%s' WHERE `geographyId` = '%s';"
			, mysql_escape_string($this->geographyGetProperty('parentId'))
			, mysql_escape_string($this->geographyGetProperty('name'))
			, mysql_escape_string($this->geographyGetProperty('varname'))
			, mysql_escape_string($this->geographyGetProperty('iso'))
			, mysql_escape_string($this->geographyGetProperty('rank'))
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
		$query = sprintf("SELECT g.`geographyId`, g.`name`, g.`iso`, g.`varname` FROM `geography` g, `events` e, `eventImages` ei WHERE e.`eventId` = ei.`eventId` AND e.`geographyId` = g.`geographyId` AND ei.`imageId` = %s", mysql_escape_string($imageId));
		$ret = $this->db->query_all($query);
		return is_null($ret) ? array() : $ret;
	}
	
}
?>