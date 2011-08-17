<?php

/**
 * @copyright SilverBiology, LLC
 * @author Balachandran Viswanathan
 * @website http://www.silverbiology.com
 */

Class EvernoteAccounts {

	public $db,$record,$records;
	
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

	public function load_by_enId( $enId ) {
		if($enId == '') return false;
		$query = sprintf("SELECT * FROM `evenote_accounts` WHERE `enAccountId` = %s", mysql_escape_string($enId) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->set($field, $value);
			}
			return(true);
		}
		return(false);
	}

/**
 * Call to this function must be preceeded with a call to the load_byenIid function
 */
	public function getEvernoteDetails() {
		return array('username' => $this->get('username')
			, 'password' => $this->get('password')
			, 'consumerKey' => $this->get('consumerKey')
			, 'consumerSecret' => $this->get('consumerSecret')
			);
	}

	public function getAccounts() {
		$query = 'SELECT `enAccountId`,`accountName`,`username`,`dateAdded` FROM `evenote_accounts`;';
		return $this->db->query_all($query);
	}
}	
?>