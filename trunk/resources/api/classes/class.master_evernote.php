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
	
	public function setAllData ($data) {
		foreach($data as $field => $value) {
			$this->record[$field] = $value;
		}
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
			, 'notebookGuid' => $this->get('notebookGuid')
			);
	}

	public function getAccounts() {
		$accounts = array();
		$query = 'SELECT * FROM `evenote_accounts`;';
		$acnts = $this->db->query_all($query);
		if(is_array($acnts) && count($acnts)) {
			foreach($acnts as $acnt) {
				$accounts[] = array('username' => $acnt->username
						, 'password' => $acnt->password
						, 'consumerKey' => $acnt->consumerKey
						, 'consumerSecret' => $acnt->consumerSecret
						, 'notebookGuid' => $acnt->notebookGuid
						);
			}
		}
		return $accounts;
	}
	
	public function accountNameExist($accountName) {
		$accounts = $this->listAccounts();
		for($i=0; $i<count($accounts); $i++) {
			if($accounts[$i]['accountName'] == $accountName) {
				return $accounts[$i]['enAccountId'];
			}
		}
		return false;
	}
	
	public function addAccount() {
		$query = sprintf("INSERT INTO `evenote_accounts` SET `accountName` = '%s', `username` = '%s', `password` = '%s', `consumerKey` = '%s', `consumerSecret` = '%s', `notebookGuid` = '%s', `rank` = '%s', `dateAdded` = now(), `dateModified` = now();"
				, mysql_escape_string($this->get('accountName'))
				, mysql_escape_string($this->get('username'))
				, mysql_escape_string($this->get('password'))
				, mysql_escape_string($this->get('consumerKey'))
				, mysql_escape_string($this->get('consumerSecret'))
				, mysql_escape_string($this->get('notebookGuid'))
				, mysql_escape_string($this->get('rank'))
			);
		if($this->db->query($query)) {
			return($this->db->insert_id);
		} else {
			return (false);
		}
	}
	
	public function field_exists ($enAccountId){
		if($enAccountId == '' || is_null($enAccountId)) return(false);

		$query = sprintf("SELECT `enAccountId` FROM `evenote_accounts` WHERE `enAccountId` = %s;", $enAccountId);
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}
	
	
	public function editAccount() {
		$query = sprintf("UPDATE `evenote_accounts` SET `accountName` = '%s', `username` = '%s', `password` = '%s', `consumerKey` = '%s', `consumerSecret` = '%s', `notebookGuid` = '%s', `rank` = '%s', `dateModified` = now() WHERE `enAccountId` = '%s';"
				, mysql_escape_string($this->get('accountName'))
				, mysql_escape_string($this->get('username'))
				, mysql_escape_string($this->get('password'))
				, mysql_escape_string($this->get('consumerKey'))
				, mysql_escape_string($this->get('consumerSecret'))
				, mysql_escape_string($this->get('notebookGuid'))
				, mysql_escape_string($this->get('rank'))
				, mysql_escape_string($this->get('enAccountId'))
				);
		if($this->db->query($query)) {
			return(true);
		} else {
			return (false);
		}
	}
	
	public function deleteAccount($enAccountId) {
		$query = sprintf("DELETE FROM `evenote_accounts` WHERE `enAccountId` = '%s'", mysql_escape_string($enAccountId));
		if($this->db->query($query)) {
			return(true);
		} else {
			return (false);
		}
	}
	
	public function listAccounts() {
		$accounts = array();
		$query = 'SELECT * FROM `evenote_accounts`;';
		$acnts = $this->db->query_all($query);
		if(is_array($acnts) && count($acnts)) {
			foreach($acnts as $acnt) {
				$accounts[] = array(
						'enAccountId' => $acnt->enAccountId
						,'accountName' => $acnt->accountName
						,'username' => $acnt->username
						, 'password' => $acnt->password
						, 'consumerKey' => $acnt->consumerKey
						, 'consumerSecret' => $acnt->consumerSecret
						, 'notebookGuid' => $acnt->notebookGuid
						   );
			}
		}
		return $accounts;
	}

/**
 *  Functions related to Evernote tags
 */

	public function getAllTags() {
		$query = "SELECT * FROM `evernote_tags` ORDER BY `tagName`";
		$tags = $this->db->query_all($query);
		if(is_array($tags) && count($tags)) {
			foreach($tags as $tag) {
				$tagList[] = array('tagName' => $tag->$tagName, 'tagGuid' => $tag->$tagGuid);
			}
		}
		return $tagList;
	}
	
	public function existTagGuid($tagGuid) {
		if($tagGuid=='') return false;
		$query = sprintf("SELECT * FROM `evernote_tags` WHERE `tagGuid` = '%s'", mysql_escape_string($tagGuid));
		$ret = $this->db->query_one( $query );
		if ($ret != NULL)
			return(true);
		else
			return(false);
	}
	
	public function existTagName($tagName) {
		if($tagName=='') return false;
		$query = sprintf("SELECT * FROM `evernote_tags` WHERE `tagName` = '%s'", mysql_escape_string($tagName));
		$ret = $this->db->query_one( $query );
		if ($ret != NULL)
			return(true);
		else
			return(false);
	}
	
	public function addTag($tagName, $tagGuid) {
		if($tagName=='' || $tagGuid=='') return false;
		if(!$this->existTagGuid($tagGuid)) {
			$query = sprintf("INSERT INTO `evernote_tags` SET `tagName` = '%s', `tagGuid` = '%s'"
					, mysql_escape_string($tagName)
					, mysql_escape_string($tagGuid)
					);
			$this->db->query($query);
		}
		return true;
	}
	
	public function load_by_tagName($tagName) {
		if($tagName == '') return false;
		$query = sprintf("SELECT * FROM `evernote_tags` WHERE `tagName` = '%s'", mysql_escape_string($tagName) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$tag[$field] = $value;
			}
			return($tag);
		}
		return(false);
	}
}	
?>