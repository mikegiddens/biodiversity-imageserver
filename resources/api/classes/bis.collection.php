<?php

/**
 * @copyright SilverBiology, LLC
 * @author Michael Giddens
 * @website http://www.silverbiology.com
 */

Class Collection {

    public $db, $record;

	function __construct( $db = null ) {
		$this->db = $db;
		$this->lg = new LogClass($db);
	}

    /**
     * Set the value to Data
     * @param mixed $data : input data
     * @return bool
     */
    public function collectionSetData($data) {
        $this->data = $data;
        return( true );
    }

    /**
    * Returns a since field value
    * @return mixed
    */
    public function collectionGetProperty( $field ) {
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
    public function collectionSetProperty( $field, $value ) {
        $this->record[$field] = $value;
        return( true );
    }

	/**
	 * Loads the collection record based on the collectionId
	 * @param int $collectionId
	 * @return bool
	 */
	public function collectionLoadById( $collectionId ) {
		if($collectionId == '' || !is_numeric($collectionId) || is_null($collectionId)) return false;
		$query = sprintf("SELECT * FROM `collection` WHERE `collectionId` = %d", mysql_escape_string($collectionId) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->collectionSetProperty($field, $value);
			}
			return(true);
		} else {
			return(false);
		}
	}


	public function collectionDelete($collectionId) {
		if($collectionId == '' || !is_numeric($collectionId)) return false;
		$query = sprintf("DELETE FROM `collection` WHERE `collectionId` = %d ", mysql_escape_string($collectionId));
		if($this->db->query($query)) {
			$this->lg->logSetProperty('table', 'collection');
			$this->lg->logSetProperty('query', $query);
			$this->lg->logSave();
			return  true;
		}
		return false;
	}

	
    /**
     * Checks whether record exists in collection table
     * @param int $collectionId
     * @return bool
     */
    public function collectionRecordExists ($collectionId){
	if($collectionId == '' || !is_numeric($collectionId) || is_null($collectionId)) return false;
        $query = sprintf("SELECT `collectionId` FROM `collection` WHERE `collectionId` = %d;",  $collectionId);
        $ret = $this->db->query_one( $query );
        if ($ret == NULL) {
            return false;
        } else {
            return true;
        }
    }


	/**
	 * Inserts into or updates the collection table
	 */
	public function collectionSave() {
		if($this->collectionRecordExists($this->collectionGetProperty('collectionId'))) {
            $query = sprintf("UPDATE `collection` SET  `name` = '%s', `code` = '%s' WHERE `collectionId` = %d ;"
            , mysql_escape_string($this->collectionGetProperty('name'))
            , mysql_escape_string($this->collectionGetProperty('code'))
            , mysql_escape_string($this->collectionGetProperty('collectionId'))
            );
		} else {
            $query = sprintf("INSERT INTO `collection` SET  `name` = '%s', `code` = '%s' ;"
            , mysql_escape_string($this->collectionGetProperty('name'))
            , mysql_escape_string($this->collectionGetProperty('code'))
            );
		}
		
		if($this->db->query($query)) {
			$this->insert_id = ($this->db->insert_id == 0) ? $this->collectionGetProperty('collectionId') : $this->db->insert_id;
			$this->lg->logSetProperty('table', 'collection');
			$this->lg->logSetProperty('query', $query);
			$this->lg->logSave();
			return(true);
		}
		return (false);
	}

	/**
	 * Lists the collections
	 * @return mixed
	 */
	public function collectionList($queryFlag = true) {
		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " WHERE " . $where;
		}
		
		if(is_array($this->data['collectionId']) && count($this->data['collectionId'])) {
			$where .= sprintf(" AND `collectionId` IN (%s) ", implode(',', $this->data['collectionId']));
		} else if($this->data['collectionId'] != '' && is_numeric($this->data['collectionId'])) {
			$where .= sprintf(" AND `collectionId` = %d ", $this->data['collectionId']);
		}
		
		if($this->data['code'] != '') {
			$where .= sprintf(" AND `code` = '%s' ", mysql_escape_string($this->data['code']));
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
		if($this->data['group'] != '' && in_array($this->data['group'], array('name','code','collectionId')) && $this->data['dir'] != '') {
			$where .= build_order( array(array('field' => $this->data['group'], 'dir' => $this->data['dir'])));
		} else {
			$where .= ' ORDER BY `collectionId` ASC ';
		}

		$where .= build_limit($this->data['start'], $this->data['limit']);

		$query = "SELECT SQL_CALC_FOUND_ROWS `collectionId`, `name`, `code`, `collectionSize` FROM `collection` " . $where;

		// die($query);
		
		if($queryFlag) {
			$ret = $this->db->query_all( $query );
			return is_null($ret) ? array() : $ret;
		} else {
			$ret = $this->db->query( $query );
			return $ret;
		}
	
	}

	/**
	 * Returns the count of imaged and non-imaged images of each collection
	 */
	function collectionGetSize() {
		$output = array();
		$where = '';
		$where .= build_limit($this->data['start'],$this->data['limit']);
		$query = "SELECT * FROM `collection` " . $where;
		$rets = $this->db->query_all($query);
		if ($rets == NULL) {
			return false;
		}
		if(is_array($rets) && count($rets)) {
			foreach($rets as $ret) {
				$ar = array();
				$ar['collectionId'] = $ret->collectionId;
				$ar['collection'] = $ret->code;
				$code = $ret->code;

# logic to be changed, need to calculate from the master_log table
				$query = "SELECT count(*) ct from `image` WHERE `barcode` LIKE '$code%'";
				$re = $this->db->query_one($query);
				$ar['imaged'] = $re->ct;

				$ar['notimaged'] = $ret->collectionSize - $re->ct;
				$output[] = $ar;
			}
		}
		return $output;
	}

	public function collectionCodeExists($code) {
		if($code == '' || is_null($code)) return false;
        	$query = sprintf("SELECT `code` FROM `collection` WHERE `code` = '%s';",  $code);
        	$ret = $this->db->query_one( $query );
        	if ($ret == NULL) {
        	    	return false;
       	 	} else {
			return true;
       		 }
  	}

	public function collectionAddImage($code) {
		if($code == '') return false;
		if(!$this->collectionCodeExists($code)) return false;
		$image = new Image($this->db);
		if(is_array($this->data['advFilter']) && count($this->data['advFilter'])) {
			$qry = $image->getByCrazyFilter($this->data['advFilter']);
			$query = " UPDATE `image` SET `collectionCode` = '$code' WHERE `imageId` IN ( SELECT im.`imageId` FROM ($qry) im ) ";
			return ($this->db->query($query));
		} else if(is_array($this->data['imageId']) && count($this->data['imageId'])) {
			foreach($this->data['imageId'] as $imageId) {
				if($image->imageLoadById($imageId)) {
					$image->imageSetProperty('collectionCode', $code);
					$image->imageSave();
				}
			}
			return true;
		} else {
			return false;
		}
		
	}
		
}

?>