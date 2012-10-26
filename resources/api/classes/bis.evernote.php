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
	public function evernoteAccountsGetProperty( $field ) {
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
	public function evernoteAccountsSetProperty( $field, $value ) {
		$this->record[$field] = $value;
		return( true );
	}
	
	public function evernoteAccountsSetAllData ($data) {
		foreach($data as $field => $value) {
			$this->record[$field] = $value;
		}
	}

	public function evernoteAccountsSetData ($data) {
		$this->evernoteAccountsClearData();
		$this->data = $data;
	}

	public function evernoteAccountsClearData () {
		unset($this->data);
		return true;
	}

	public function evernoteAccountsClearRecords () {
		unset($this->records);
		return true;
	}

	public function evernoteAccountsGetRecords () {
		return $this->records;
	}

	public function evernoteAccountsLoadById( $enId ) {
		if($enId == '') return false;
		$query = sprintf("SELECT * FROM `evenoteAccounts` WHERE `enAccountId` = %s", mysql_escape_string($enId) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->evernoteAccountsSetProperty($field, $value);
			}
			return(true);
		}
		return(false);
	}

/**
 * Call to this function must be preceeded with a call to the evernoteAccountsLoadById function
 */
	public function evernoteAccountsGetDetails() {
		return array('username' => $this->evernoteAccountsGetProperty('userName')
			, 'password' => $this->evernoteAccountsGetProperty('password')
			, 'consumerKey' => $this->evernoteAccountsGetProperty('consumerKey')
			, 'consumerSecret' => $this->evernoteAccountsGetProperty('consumerSecret')
			, 'notebookGuid' => $this->evernoteAccountsGetProperty('notebookGuid')
			);
	}

	public function evernoteAccountsGet($enAccountId='') {
		$accounts = array();
		if($enAccountId=='') {
			$query = 'SELECT * FROM `evenoteAccounts`;';
		} else {
			$query = sprintf("SELECT * FROM `evenoteAccounts` WHERE `enAccountId` = '%s';", mysql_escape_string($enAccountId));
		}
		$acnts = $this->db->query_all($query);
		if(is_array($acnts) && count($acnts)) {
			foreach($acnts as $acnt) {
				$accounts[] = array('username' => $acnt->userName
						, 'password' => $acnt->password
						, 'consumerKey' => $acnt->consumerKey
						, 'consumerSecret' => $acnt->consumerSecret
						, 'notebookGuid' => $acnt->notebookGuid
						);
			}
		}
		return $accounts;
	}
	
	public function evernoteAccountsExists($accountName) {
		$accounts = $this->evernoteAccountsList();
		for($i=0; $i<count($accounts); $i++) {
			if($accounts[$i]['accountName'] == $accountName) {
				return $accounts[$i]['enAccountId'];
			}
		}
		return false;
	}
	
	public function evernoteAccountsAdd() {
		$query = sprintf("INSERT INTO `evenoteAccounts` SET `accountName` = '%s', `userName` = '%s', `password` = '%s', `consumerKey` = '%s', `consumerSecret` = '%s', `notebookGuid` = '%s', `rank` = '%s', `dateAdded` = now(), `dateModified` = now();"
				, mysql_escape_string($this->evernoteAccountsGetProperty('accountName'))
				, mysql_escape_string($this->evernoteAccountsGetProperty('userName'))
				, mysql_escape_string($this->evernoteAccountsGetProperty('password'))
				, mysql_escape_string($this->evernoteAccountsGetProperty('consumerKey'))
				, mysql_escape_string($this->evernoteAccountsGetProperty('consumerSecret'))
				, mysql_escape_string($this->evernoteAccountsGetProperty('notebookGuid'))
				, mysql_escape_string($this->evernoteAccountsGetProperty('rank'))
			);
		if($this->db->query($query)) {
			return($this->db->insert_id);
		} else {
			return (false);
		}
	}
	
	public function evernoteAccountsFieldExists ($enAccountId){
		if($enAccountId == '' || is_null($enAccountId)) return(false);

		$query = sprintf("SELECT `enAccountId` FROM `evenoteAccounts` WHERE `enAccountId` = %s;", $enAccountId);
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}
	
	
	public function evernoteAccountsUpdate() {
		$query = sprintf("UPDATE `evenoteAccounts` SET `accountName` = '%s', `userName` = '%s', `password` = '%s', `consumerKey` = '%s', `consumerSecret` = '%s', `notebookGuid` = '%s', `rank` = '%s', `dateModified` = now() WHERE `enAccountId` = '%s';"
				, mysql_escape_string($this->evernoteAccountsGetProperty('accountName'))
				, mysql_escape_string($this->evernoteAccountsGetProperty('userName'))
				, mysql_escape_string($this->evernoteAccountsGetProperty('password'))
				, mysql_escape_string($this->evernoteAccountsGetProperty('consumerKey'))
				, mysql_escape_string($this->evernoteAccountsGetProperty('consumerSecret'))
				, mysql_escape_string($this->evernoteAccountsGetProperty('notebookGuid'))
				, mysql_escape_string($this->evernoteAccountsGetProperty('rank'))
				, mysql_escape_string($this->evernoteAccountsGetProperty('enAccountId'))
				);
		if($this->db->query($query)) {
			return(true);
		} else {
			return (false);
		}
	}
	
	public function evernoteAccountsDelete($enAccountId) {
		$query = sprintf("DELETE FROM `evenoteAccounts` WHERE `enAccountId` = '%s'", mysql_escape_string($enAccountId));
		if($this->db->query($query)) {
			return(true);
		} else {
			return (false);
		}
	}
	
	public function evernoteAccountsList() {
		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " WHERE " . $where;
		}
		
		if(is_array($this->data['enAccountId']) && count($this->data['enAccountId'])) {
			$where .= sprintf(" AND `enAccountId` IN (%s) ", implode(',', $this->data['enAccountId']));
		} else if($this->data['enAccountId'] != '' && is_numeric($this->data['enAccountId'])) {
			$where .= sprintf(" AND `enAccountId` = %d ", $this->data['enAccountId']);
		}
		if($this->data['value'] != '') {
			switch($this->data['searchFormat']) {
				case 'exact':
					$where .= sprintf(" AND `accountName` = '%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'left':
					$where .= sprintf(" AND `accountName` LIKE '%s%%' ", mysql_escape_string($this->data['value']));
					break;
				case 'right':
					$where .= sprintf(" AND `accountName` LIKE '%%%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'both':
				default:
					$where .= sprintf(" AND `accountName` LIKE '%%%s%%' ", mysql_escape_string($this->data['value']));
					break;
			}
		}
		if($this->data['group'] != '' && in_array($this->data['group'], array('accountName','userName','dateAdded','dateModified')) && $this->data['dir'] != '') {
			$where .= build_order( array(array('field' => $this->data['group'], 'dir' => $this->data['dir'])));
		} else {
			$where .= ' ORDER BY `enAccountId` ASC ';
		}

		$where .= build_limit($this->data['start'], $this->data['limit']);
	
	
		$accounts = array();
		$query = 'SELECT SQL_CALC_FOUND_ROWS * FROM `evenoteAccounts` ' . $where;
		
		// die($query);
		
		$acnts = $this->db->query_all($query);
		if(is_array($acnts) && count($acnts)) {
			foreach($acnts as $acnt) {
				$accounts[] = array(
						'enAccountId' => $acnt->enAccountId
						,'accountName' => $acnt->accountName
						,'username' => $acnt->userName
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

	public function evernoteTagsGetAll() {
		$query = "SELECT * FROM `evernoteTags` ORDER BY `tagName`";
		$tags = $this->db->query_all($query);
		if(is_array($tags) && count($tags)) {
			foreach($tags as $tag) {
				$tagList[] = array('tagName' => $tag->$tagName, 'tagGuid' => $tag->$tagGuid);
			}
		}
		return $tagList;
	}
	
	public function evernoteTagsExistGuid($tagGuid) {
		if($tagGuid=='') return false;
		$query = sprintf("SELECT * FROM `evernoteTags` WHERE `tagGuid` = '%s'", mysql_escape_string($tagGuid));
		$ret = $this->db->query_one( $query );
		if ($ret != NULL)
			return(true);
		else
			return(false);
	}
	
	public function evernoteTagsExist($tagName) {
		if($tagName=='') return false;
		$query = sprintf("SELECT * FROM `evernoteTags` WHERE `tagName` = '%s'", mysql_escape_string($tagName));
		$ret = $this->db->query_one( $query );
		if ($ret != NULL)
			return(true);
		else
			return(false);
	}
	
	public function evernoteTagsAdd($tagName, $tagGuid) {
		if($tagName=='' || $tagGuid=='') return false;
		if(!$this->evernoteTagsExistGuid($tagGuid)) {
			$query = sprintf("INSERT INTO `evernoteTags` SET `tagName` = '%s', `tagGuid` = '%s'"
					, mysql_escape_string($tagName)
					, mysql_escape_string($tagGuid)
					);
			return ($this->db->query($query)) ? true : false;
		}
		return true;
	}
	
	public function evernoteTagsLoadByTagName($tagName) {
		if($tagName == '') return false;
		$query = sprintf("SELECT * FROM `evernoteTags` WHERE `tagName` = '%s'", mysql_escape_string($tagName) );
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