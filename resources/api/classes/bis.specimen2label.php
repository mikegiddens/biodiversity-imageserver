<?php

/**
 * @copyright SilverBiology, LLC
 * @author Balachandran Viswanathan
 * @website http://www.silverbiology.com
 */

Class Specimen2label {

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

	public function load_by_labelId( $labelId ) {
		if($labelId == '') return false;
		$query = sprintf("SELECT * FROM `specimen2label` WHERE `labelId` = %s", mysql_escape_string($labelId) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->set($field, $value);
			}
			return(true);
		}
		return(false);
	}

	public function load_by_barcode( $barcode ) {
		if($barcode == '') return false;
		$query = sprintf("SELECT * FROM `specimen2label` WHERE `barcode` = '%s'", mysql_escape_string($barcode) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->set($field, $value);
			}
			return(true);
		}
		return(false);
	}

	public function save() {
		$query = sprintf("INSERT IGNORE INTO `specimen2label` SET `labelId` = '%s', `evernoteAccountId` = '%s', `barcode` = '%s', `dateAdded` = now();"
		, mysql_escape_string($this->get('labelId'))
		, mysql_escape_string($this->get('evernoteAccountId'))
		, mysql_escape_string($this->get('barcode'))
		);
		if( $this->db->query($query) ) {
			return( true );
		}
		return( false );
	}

	public function getLatestDate() {
		$query = 'SELECT max(dateAdded) start_date FROM `specimen2label`;';
		$ret = $this->db->query_one($query);
		return $ret->start_date;
	}

}
	
?>