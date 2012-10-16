<?php

class AdvFilter
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
	public function advFilterSetData($data) {
		$this->data = $data;
		return( true );
	}
	
	/**
	* Returns a since field value
	* @return mixed
	*/
	public function advFilterGetProperty( $field ) {
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
	public function advFilterSetProperty( $field, $value ) {
		$this->{$field} = $value;
		return( true );
	}
	
	public function advFilterLoadById( $advFilterId ) {
		if($advFilterId == '') return false;
		$query = sprintf("SELECT * FROM `advFilter` WHERE `advFilterId` = %s ", mysql_escape_string($advFilterId) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->advFilterSetProperty($field, $value);
			}
			return(true);
		} else {
			return(false);
		}
	}

	public function advFilterList($queryFlag = true) {
		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " WHERE " . $where;
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
		
		if($this->data['group'] != '' && in_array($this->data['group'], array('advFilterId','name','dateCreated','dateModified','lastModifiedBy')) && $this->data['dir'] != '') {
			$where .= build_order( array(array('field' => $this->data['group'], 'dir' => $this->data['dir'])));
		} else {
			$where .= ' ORDER BY `advFilterId` ASC ';
		}
		
		$where .= build_limit($this->data['start'], $this->data['limit']);

		$query = "SELECT SQL_CALC_FOUND_ROWS `advFilterId`,`name`,`description`,`filter`,`dateCreated`,`dateModified`,`lastModifiedBy` FROM  `advFilter` " . $where;

		if($queryFlag) {
			$ret = $this->db->query_all( $query );
			return is_null($ret) ? array() : $ret;
		} else {
			$ret = $this->db->query( $query );
			return $ret;
		}
	}

	public function advFilterExists($advFilterId) {
		$query = sprintf("SELECT `advFilterId` FROM `advFilter` WHERE `advFilterId` = '%s';", mysql_escape_string($advFilterId));
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}
	
	public function advFilterNameExists($name) {
		$query = sprintf("SELECT `advFilterId` FROM `advFilter` WHERE `name` = '%s';", mysql_escape_string($name));
		// echo $query;exit;
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}

	public function advFilterSave() {
			$query = sprintf("INSERT IGNORE INTO `advFilter` SET `name` = '%s', `description` = '%s', `filter` = '%s', `dateCreated` = NOW(), `dateModified` = NOW(), `lastModifiedBy` = '%s' ;"
			, mysql_escape_string($this->advFilterGetProperty('name'))
			, mysql_escape_string($this->advFilterGetProperty('description'))
			, mysql_escape_string($this->advFilterGetProperty('filter'))
			, mysql_escape_string($this->advFilterGetProperty('lastModifiedBy'))
			);
		if($this->db->query($query)) {
			return $this->db->insert_id;
		}
		return false;
	}

	public function advFilterUpdate() {
		if($this->advFilterExists($this->advFilterGetProperty('advFilterId'))) {
			$query = sprintf("UPDATE `advFilter` SET `name` = '%s', `description` = '%s', `filter` = '%s', `dateModified` = NOW(), `lastModifiedBy` = '%s' WHERE `advFilterId` = '%s' ;"
			, mysql_escape_string($this->advFilterGetProperty('name'))
			, mysql_escape_string($this->advFilterGetProperty('description'))
			, mysql_escape_string($this->advFilterGetProperty('filter'))
			, mysql_escape_string($this->advFilterGetProperty('lastModifiedBy'))
			, mysql_escape_string($this->advFilterGetProperty('advFilterId'))
			);
			if($this->db->query($query)) {
				return true;
			}
		} 	
		return false;
	}

	public function advFilterDelete($advFilterId) {
		if($advFilterId == '') return false;
		if(!$this->advFilterExists($advFilterId)) return false;
		$query = sprintf("DELETE FROM `advFilter` WHERE `advFilterId` = '%s' ", mysql_escape_string($advFilterId));
		if($this->db->query($query)) {
			return  true;
		}
		return false;
	}
}
?>