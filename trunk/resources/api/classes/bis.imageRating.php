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
	public function imageRatingGetProperty( $field ) {
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
	public function imageRatingSetProperty( $field, $value ) {
		$this->{$field} = $value;
		return( true );
	}
	
	private function imageRatingProcessData() {
		$this->imageRatingSetProperty('ipAddress',str_replace('.', '', $this->imageRatingGetProperty('ipAddress')));
	}
	
	public function imageRatingSave() {
		$this->imageRatingProcessData();
		$query = sprintf(" INSERT IGNORE INTO `imageRating` SET `imageId` = '%s', `userId` = '%s', `ipAddress` = '%s', `rating` = '%s' "
		, mysql_escape_string($this->imageRatingGetProperty('imageId'))
		, mysql_escape_string($this->imageRatingGetProperty('userId'))
		, mysql_escape_string($this->imageRatingGetProperty('ipAddress'))
		, mysql_escape_string($this->imageRatingGetProperty('rating'))
		);
		
		$ret = $this->db->query($query);
		return (is_null($ret)) ? false : true;
	}

	public function imageRatingGetNonCalculatedImages($resultFlag = true) {
		$query = ' SELECT DISINCT `imageId` FROM `imageRating` WHERE `calc` = 0 ';
		return ($resultFlag) ? $this->db->query($query) : $this->db->query_all($query);
	}
	
	public function imageRatingGetAvgById($imageId = '') {
		if($imageId == '') return false;
		$query = sprintf("SELECT avg( `rating` ) AS `rating` FROM `imageRating` WHERE `imageId` = '%s'", mysql_escape_string($imageId));
		$ret = $this->db->query_one($query);
		return is_null($ret) ? false : $ret->rating;
	}
	
	public function imageRatingGetAvg($resultFlag = true) {
		$query = 'SELECT `imageId`, avg( `rating` ) AS rating FROM `imageRating` WHERE `imageId` IN ( SELECT DISTINCT `imageId` FROM `imageRating` WHERE `calc` = 0 ) GROUP BY `imageId` ';
		return ($resultFlag) ? $this->db->query($query) : $this->db->query_all($query);
	}
	
	public function imageRatingUpdateTrustedUserImages($userId = '', $trusted = true) {
		if($userId == '') return false;
		$statusType = ($trusted) ? 1 : 0;
		$query = sprintf("UPDATE `image` i, `imageRating` ir SET i.`statusType` = %d WHERE i.`imageId` = ir.`imageId` AND ir.`userId` = '%s';", $statusType, mysql_escape_string($userId));
		return ($this->db->query($query)) ? true : false;
	}
	
	public function imageRatingUpdateCalc($imageId = '') {
		if($imageId == '') return false;
		$ipAddress = str_replace('.', '', $ipAddress);
		$query = sprintf(" UPDATE `imageRating` SET `calc` = 1 WHERE `imageId` = '%s' ", mysql_escape_string($imageId));
		return ($this->db->query($query)) ? true : false;
	}
	
}