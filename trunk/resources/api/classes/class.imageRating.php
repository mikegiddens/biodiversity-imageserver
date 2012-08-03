<?php

class ImageRating
{
	public $db;

	function __construct( $db = null ) {
		$this->db = $db;
	}
	
	/**
	* Returns a since field value
	* @return mixed
	*/
	public function get( $field ) {
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
	public function set( $field, $value ) {
		$this->{$field} = $value;
		return( true );
	}
	
	private function processData() {
		$this->set('ip_address',str_replace('.', '', $this->get('ip_address')));
	}
	
	public function saveRating() {
		$this->processData();
		$query = sprintf(" INSERT IGNORE INTO `image_rating` SET `image_id` = '%s', `user_id` = '%s', `ip_address` = '%s', `rating` = '%s' "
		, mysql_escape_string($this->get('image_id'))
		, mysql_escape_string($this->get('user_id'))
		, mysql_escape_string($this->get('ip_address'))
		, mysql_escape_string($this->get('rating'))
		);
		
		$ret = $this->db->query($query);
		return (is_null($ret)) ? false : true;
	}

	public function getNonCalculatedImages($resultFlag = true) {
		$query = ' SELECT DISINCT `image_id` FROM `image_rating` WHERE `calc` = 0 ';
		return ($resultFlag) ? $this->db->query($query) : $this->db->query_all($query);
	}
	
	public function getAvgRatingById($image_id = '') {
		if($image_id == '') return false;
		$query = sprintf("SELECT avg( `rating` ) AS `rating` FROM `image_rating` WHERE `image_id` = '%s'", mysql_escape_string($image_id));
		$ret = $this->db->query_one($query);
		return is_null($ret) ? false : $ret->rating;
	}
	
	public function getAvgRating($resultFlag = true) {
		$query = 'SELECT `image_id`, avg( `rating` ) AS rating FROM `image_rating` WHERE `image_id` IN ( SELECT DISTINCT `image_id` FROM `image_rating` WHERE `calc` = 0 ) GROUP BY `image_id` ';
		return ($resultFlag) ? $this->db->query($query) : $this->db->query_all($query);
	}
	
	public function updateTrustedUserImages($user_id = '', $trusted = true) {
		if($user_id == '') return false;
		$statusType = ($trusted) ? 1 : 0;
		$query = sprintf("UPDATE `image` i, `image_rating` ir SET i.`statusType` = %d WHERE i.`image_id` = ir.`image_id` AND ir.`user_id` = '%s';", $statusType, mysql_escape_string($user_id));
		return ($this->db->query($query)) ? true : false;
	}
	
	public function updateCalc($image_id = '') {
		if($image_id == '') return false;
		$ip_address = str_replace('.', '', $ip_address);
		$query = sprintf(" UPDATE `image_rating` SET `calc` = 1 WHERE `image_id` = '%s' ", mysql_escape_string($image_id));
		return ($this->db->query($query)) ? true : false;
	}
	
}