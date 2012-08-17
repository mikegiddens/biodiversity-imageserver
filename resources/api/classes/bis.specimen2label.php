<?php

/**
 * @copyright SilverBiology, LLC
 * @author Balachandran Viswanathan
 * @website http://www.silverbiology.com
 */

Class Specimen2Label {

	public $records,$data,$files,$record;
	
	function __construct( $db = null ) {
		$this->db = $db;
	}

	/**
	* Returns a since field value
	* @return mixed
	*/
	public function Specimen2LabelGetProperty( $field ) {
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
	public function Specimen2LabelSetProperty( $field, $value ) {
		$this->record[$field] = $value;
		return( true );
	}

	public function Specimen2LabelSetData ($data) {
		$this->data = $data;
	}

	public function Specimen2LabelClearData () {
		unset($this->data);
		return true;
	}

	public function Specimen2LabelClearRecords () {
		unset($this->records);
		return true;
	}

	public function Specimen2LabelGetRecords () {
		return $this->records;
	}

	public function Specimen2LabelLoadById( $labelId ) {
		if($labelId == '') return false;
		$query = sprintf("SELECT * FROM `specimen2Label` WHERE `labelId` = %s", mysql_escape_string($labelId) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->Specimen2LabelSetProperty($field, $value);
			}
			return(true);
		}
		return(false);
	}

	public function Specimen2LabelLoadByBarcode( $barcode ) {
		if($barcode == '') return false;
		$query = sprintf("SELECT * FROM `specimen2Label` WHERE `barcode` = '%s'", mysql_escape_string($barcode) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->Specimen2LabelSetProperty($field, $value);
			}
			return(true);
		}
		return(false);
	}

	public function Specimen2LabelSave() {
		$query = sprintf("INSERT IGNORE INTO `specimen2Label` SET `labelId` = '%s', `evernoteAccountId` = '%s', `barcode` = '%s', `dateAdded` = now();"
		, mysql_escape_string($this->Specimen2LabelGetProperty('labelId'))
		, mysql_escape_string($this->Specimen2LabelGetProperty('evernoteAccountId'))
		, mysql_escape_string($this->Specimen2LabelGetProperty('barcode'))
		);
		if( $this->db->query($query) ) {
			return( true );
		}
		return( false );
	}

	public function Specimen2LabelGetLatestDate() {
		$query = 'SELECT max(dateAdded) startDate FROM `specimen2Label`;';
		$ret = $this->db->query_one($query);
		return $ret->startDate;
	}

}
	
?>