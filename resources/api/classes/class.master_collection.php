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
    public function set( $field, $value ) {
        $this->record[$field] = $value;
        return( true );
    }

	/**
	 * Loads the collection record based on the collection_id
	 * @param int $collectionId
	 * @return bool
	 */
	public function load_by_id( $collectionId ) {
		if($collectionId == '' || !is_numeric($collectionId) || is_null($collectionId)) return false;
		$query = sprintf("SELECT * FROM `collection` WHERE `collection_id` = %d", mysql_escape_string() );
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

    /**
     * Checks whether record exists in collection table
     * @param int $collectionId
     * @return bool
     */
    public function record_exists ($collectionId){
	if($collectionId == '' || !is_numeric($collectionId) || is_null($collectionId)) return false;
        $query = sprintf("SELECT `collection_id` FROM `collection` WHERE `collection_id` = %d;",  $collectionId);
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
	public function save() {
		if($this->record_exists($this->get('collection_id'))) {
            $query = sprintf("UPDATE `collection` SET  `name` = '%s', `code` = '%s' WHERE `collection_id` = %d ;"
            , mysql_escape_string($this->get('name'))
            , mysql_escape_string($this->get('code'))
            , mysql_escape_string($this->get('collection_id'))
            );
		} else {
            $query = sprintf("INSERT INTO `collection` SET  `name` = '%s', `code` = '%s' ;"
            , mysql_escape_string($this->get('name'))
            , mysql_escape_string($this->get('code'))
            );
		}
		if($this->db->query($query)) {
			return(true);
		} else {
			return (false);
		}
	}

	/**
	 * Lists the collections
	 * @return mixed
	 */
	public function listCollection() {
		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " WHERE " . $where;
		}
		$where .= build_order( $this->data['order']);

		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM `collection` " . $where;

		$ret = $this->db->query_page_all( $query, $this->data['limit'], $this->data['start'] );

		return is_null($ret) ? array() : $ret;

	}

	/**
	 * Returns the count of imaged and non-imaged images of each collection
	 */
	function getSizeOfCollection() {
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
				$ar['collection_id'] = $ret->collection_id;
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

	public function exist_collectionCode($collectionCode) {
		if($collectionCode == '' || is_null($collectionCode)) return false;
        	$query = sprintf("SELECT `code` FROM `collection` WHERE `code` = '%s';",  $collectionCode);
        	$ret = $this->db->query_one( $query );
        	if ($ret == NULL) {
        	    	return false;
       	 	} else {
			return true;
       		 }
  	}

}

?>