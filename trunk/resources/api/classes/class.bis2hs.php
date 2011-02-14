<?php

/**
 * @copyright SilverBiology, LLC
 * @author Michael Giddens
 * @website http://www.silverbiology.com
 */

Class Bis2hs {

	public $records,$data,$files,$record;
	
	function __construct( $db = null ) {
		$this->db = $db;
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

	public function setData ($data) {
		$this->data = $data;
	}

	public function clearData () {
		unset($this->data);
		return true;
	}

	public function clearRecords () {
		unset($this->records);
		return true;
	}

	public function getRecords () {
		return $this->records;
	}

	public function getId() {
		$where = '';
		$query = "SELECT MAX(`image_id`) as `image_id` FROM `bis2hs` WHERE 1=1 ";
		if($this->data['client_id'] != '') {
			$where .= sprintf(" AND `client_id` = '%s' ", mysql_escape_string($this->data['client_id']));
		}

		if($this->data['client_id'] != '') {
			$where .= sprintf(" AND `client_id` = '%s' ", mysql_escape_string($this->data['client_id']));
		}

		$query .= $where;

		$record = $this->db->query_one($query);
		if($record != NULL) {
			return $record->image_id;
		}
		return false;
	}

	public function load_by_id( $image_id ) {
		if($image_id == '') return false;
		$query = sprintf("SELECT * FROM `bis2hs` WHERE `image_id` = %s", mysql_escape_string($image_id) );
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

	public function save() {
		if($this->recordExists($this->get('image_id'))) {
			$query = sprintf("UPDATE `bis2hs` SET `filename` = '%s', `barcode` = '%s', `client_id` = '%s', `collection_id` = '%s', `imageserver_id` = '%s', `timestamp_modified` = now() WHERE `image_id` = '%s' ;"
			, mysql_escape_string($this->get('filename'))
			, mysql_escape_string($this->get('barcode'))
			, mysql_escape_string($this->get('client_id'))
			, mysql_escape_string($this->get('collection_id'))
			, mysql_escape_string($this->get('imageserver_id'))
			, mysql_escape_string($this->get('image_id'))
			);
		} else {
			$query = sprintf("INSERT INTO `bis2hs` SET `image_id` = '%s', `filename` = '%s', `barcode` = '%s', `client_id` = '%s', `collection_id` = '%s', `imageserver_id` = '%s', `timestamp_modified` = now();"
			, mysql_escape_string($this->get('image_id'))
			, mysql_escape_string($this->get('filename'))
			, mysql_escape_string($this->get('barcode'))
			, mysql_escape_string($this->get('client_id'))
			, mysql_escape_string($this->get('collection_id'))
			, mysql_escape_string($this->get('imageserver_id'))
			);
		}
		if( $this->db->query($query) ) {
			return( true );
		}
		return( false );
	}

	public function recordExists($image_id) {
		$query = sprintf("SELECT `image_id` FROM `bis2hs` WHERE `image_id` = '%s' ;", mysql_escape_string( $image_id ) );
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}
}
	
?>