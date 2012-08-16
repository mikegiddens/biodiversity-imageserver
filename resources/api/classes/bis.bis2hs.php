<?php

/**
 * @copyright SilverBiology, LLC
 * @author Michael Giddens
 * @website http://www.silverbiology.com
 */

Class Bis2Hs {

	public $records,$data,$files,$record;
	
	function __construct( $db = null ) {
		$this->db = $db;
	}

	/**
	* Returns a since field value
	* @return mixed
	*/
	public function bis2HsGetProperty( $field ) {
		if (isset($this->record[$field])) {
			return( $this->record[$field] );
		} else {
			return( false );
		}
	}

	/**
	* Set the value to a field
	* @return bool
	*/
	public function bis2HsSetProperty( $field, $value ) {
		$this->record[$field] = $value;
		return( true );
	}

	public function bis2HsSetData ($data) {
		$this->data = $data;
	}

	public function bis2HsClearData () {
		unset($this->data);
		return true;
	}

	public function bis2HsClearRecords () {
		unset($this->records);
		return true;
	}

	public function bis2HsGetRecords () {
		return $this->records;
	}

	public function bis2HsGetId() {
		$where = '';
		$query = "SELECT MAX(`imageId`) as `imageId` FROM `bis2Hs` WHERE 1=1 ";
		if($this->data['clientId'] != '') {
			$where .= sprintf(" AND `clientId` = '%s' ", mysql_escape_string($this->data['clientId']));
		}

		$query .= $where;

		$record = $this->db->query_one($query);
		if($record != NULL) {
			return $record->imageId;
		}
		return false;
	}

	public function bis2HsLoadById( $imageId ) {
		if($imageId == '') return false;
		$query = sprintf("SELECT * FROM `bis2Hs` WHERE `imageId` = %s", mysql_escape_string($imageId) );
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

	public function bis2HsSave() {
		if($this->bis2HsRecordExists($this->bis2HsGetProperty('imageId'))) {
			$query = sprintf("UPDATE `bis2Hs` SET `filename` = '%s', `barcode` = '%s', `clientId` = '%s', `collectionId` = '%s', `imageserverId` = '%s', `timestamp_modified` = now() WHERE `imageId` = '%s' ;"
			, mysql_escape_string($this->bis2HsGetProperty('filename'))
			, mysql_escape_string($this->bis2HsGetProperty('barcode'))
			, mysql_escape_string($this->bis2HsGetProperty('clientId'))
			, mysql_escape_string($this->bis2HsGetProperty('collectionId'))
			, mysql_escape_string($this->bis2HsGetProperty('imageserverId'))
			, mysql_escape_string($this->bis2HsGetProperty('imageId'))
			);
		} else {
			$query = sprintf("INSERT INTO `bis2Hs` SET `imageId` = '%s', `filename` = '%s', `barcode` = '%s', `clientId` = '%s', `collectionId` = '%s', `imageserverId` = '%s', `timestamp_modified` = now();"
			, mysql_escape_string($this->bis2HsGetProperty('imageId'))
			, mysql_escape_string($this->bis2HsGetProperty('filename'))
			, mysql_escape_string($this->bis2HsGetProperty('barcode'))
			, mysql_escape_string($this->bis2HsGetProperty('clientId'))
			, mysql_escape_string($this->bis2HsGetProperty('collectionId'))
			, mysql_escape_string($this->bis2HsGetProperty('imageserverId'))
			);
		}
		if( $this->db->query($query) ) {
			return( true );
		}
		return( false );
	}

	public function bis2HsRecordExists($imageId) {
		$query = sprintf("SELECT `imageId` FROM `bis2Hs` WHERE `imageId` = '%s' ;", mysql_escape_string( $imageId ) );
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}
	public function bis2HsList() {
		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " WHERE " . $where;
		}
		if(is_array($this->data['clientId']) && count($this->data['clientId'])) {
			$where .= sprintf(" AND `clientId` IN  (%s) ", @implode(',', $this->data['clientId']));
		} else if(is_numeric($this->data['clientId'])) {
			$where .= sprintf(" AND `clientId` = '%s' ", mysql_escape_string($this->data['clientId']));
		}
		if(is_array($this->data['collectionId']) && count($this->data['collectionId'])) {
			$where .= sprintf(" AND `collectionId` IN  (%s) ", @implode(',', $this->data['collectionId']));
		} else if(is_numeric($this->data['collectionId'])) {
			$where .= sprintf(" AND `collectionId` = '%s' ", mysql_escape_string($this->data['collectionId']));
		}
		if(is_array($this->data['imageServerId']) && count($this->data['imageServerId'])) {
			$where .= sprintf(" AND `imageServerId` IN  (%s) ", @implode(',', $this->data['imageServerId']));
		} else if(is_numeric($this->data['imageServerId'])) {
			$where .= sprintf(" AND `imageServerId` = '%s' ", mysql_escape_string($this->data['imageServerId']));
		}
		if($this->data['value'] != '') {
			switch($this->data['searchFormat']) {
				case 'exact':
					$where .= sprintf(" AND `barcode` = '%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'left':
					$where .= sprintf(" AND `barcode` LIKE '%s%%' ", mysql_escape_string($this->data['value']));
					break;
				case 'right':
					$where .= sprintf(" AND `barcode` LIKE '%%%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'both':
				default:
					$where .= sprintf(" AND `barcode` LIKE '%%%s%%' ", mysql_escape_string($this->data['value']));
					break;
			}
		}
		if($this->data['group'] != '' && in_array($this->data['group'], array('fileName','barcode','clientId','collectionId','imageServerId','timestampModified')) && $this->data['dir'] != '') {
			$where .= build_order( array(array('field' => $this->data['group'], 'dir' => $this->data['dir'])));
		} else {
			$where .= ' ORDER BY `imageId` ASC ';
		}
		$where .= build_limit($this->data['start'], $this->data['limit']);
		$query = 'SELECT SQL_CALC_FOUND_ROWS * FROM `bis2Hs` ' . $where; 
		$ret = $this->db->query_all( $query );
		return is_null($ret) ? array() : $ret;
		
		
		// $page = ($this->data['limit'] != 0 && $this->data['limit'] != '') ? floor($this->data['start']/$this->data['limit']) : 1;
		// $ret = $this->db->query_page_all( $query, $this->data['limit'],$page );
		// return is_null($ret) ? array() : $ret;
	}

}
	
?>