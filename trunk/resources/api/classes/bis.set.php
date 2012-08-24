<?php

class Set 
{
	public $db, $record;
	
	public function __construct($db) {
		$this->db = $db;
	}

	public function setSetData($data) {
		$this->data = $data;
		return(true);
	}
	
	public function setSetProperty( $field, $value ) {
		$this->record[$field] = $value;
		return( true );
	}
	
	public function setGetProperty($field) {
		if(isset($this->record[$field])) {
			return $this->record[$field];
		} else {
			return false;
		}
	}
	
	public function setLoadById( $setId ) {
		if($setId == '') return false;
		$query = sprintf("SELECT * FROM `set` WHERE `setId` = '%s'", mysql_escape_string($setId) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->setSetProperty($field, $value);
			}
			return(true);
		} else {
			return(false);
		}
	}
	
	public function setLoadByName( $name ) {
		if($name == '') return false;
		$query = sprintf("SELECT * FROM `set` WHERE `name` = '%s'", mysql_escape_string($name) );
		$ret = $this->db->query_one( $query );
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->setSetProperty($field, $value);
			}
			return(true);
		} else {
			return(false);
		}
	}
	
	public function setNameExists($name) {
		if($name == '') return false;
		$query = sprintf("SELECT * FROM `set` WHERE `name` = '%s'", mysql_escape_string($name));
		$result = $this->db->query_all($query);
		if(count($result)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function setAdd() {
		$query = sprintf("INSERT INTO `set` SET `name` = '%s', `description` = '%s'"
			, mysql_escape_string($this->setGetProperty('name'))
			, mysql_escape_string($this->setGetProperty('description'))
			);
		if($this->db->query($query)) {
			return $this->db->insert_id;
		} else {
			return false;
		}
	}
	
	public function setUpdate() {
		$query = sprintf("UPDATE `set` SET `name` = '%s', `description` = '%s' WHERE `setId` = '%s'"
			, mysql_escape_string($this->setGetProperty('name'))
			, mysql_escape_string($this->setGetProperty('description'))
			, mysql_escape_string($this->setGetProperty('setId'))
			);
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function setDelete($setId) {
		if($setId == '') return false;
		$query = sprintf("DELETE FROM `set` WHERE `setId` = '%s'", mysql_escape_string($setId));
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	
/*	
	public function setAdd($name, $description) {
		if($name == '') return false;
		$query = sprintf("INSERT INTO `set` SET `name` = '%s', `description` = '%s'"
				, mysql_escape_string($name)
				, mysql_escape_string($description)
				);
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function setUpdate($setId, $name, $description) {
		if($name == '' || $setId == '') return false;
		if($description == '') {
			$query = sprintf("UPDATE `set` SET `name` = '%s' WHERE `setId` = '%s'"
					, mysql_escape_string($name)
					, mysql_escape_string($setId)
					);
		} else {
			$query = sprintf("UPDATE `set` SET `name` = '%s', `description` = '%s' WHERE `setId` = '%s'"
				, mysql_escape_string($name)
				, mysql_escape_string($description)
				, mysql_escape_string($setId)
				);
		}
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
*/	
	
	public function setList() {
		$query = "SELECT s.setId sID, s.name sNAME, s.description sDESCRIPTION, sv.setValueId svID, iav.name iavNAME, sv.rank svRANK FROM `set` s LEFT OUTER JOIN setValues sv ON (sv.setId = s.setId) JOIN imageAttribValue iav on (iav.attributeId = sv.attributeId) WHERE 1=1 ";
		
		if(is_array($this->data['setId']) && count($this->data['setId'])) {
			$query .= sprintf(" AND s.setId IN (%s) ", implode(',', $this->data['setId']));
		}
		if($this->data['value'] != '') {
			switch($this->data['searchFormat']) {
				case 'exact':
					$query .= sprintf(" AND s.`name` = '%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'left':
					$query .= sprintf(" AND s.`name` LIKE '%s%%' ", mysql_escape_string($this->data['value']));
					break;
				case 'right':
					$query .= sprintf(" AND s.`name` LIKE '%%%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'both':
				default:
					$query .= sprintf(" AND s.`name` LIKE '%%%s%%' ", mysql_escape_string($this->data['value']));
					break;
			}
		}
		
		$query .= " ORDER BY s.name, sv.rank";
		$records = $this->db->query_all($query);
		if(count($records)) {
			$array['count'] = 0;
			$prevID = 0;
			foreach($records as $record) {
				if($prevID != $record->sID) {
					$array['count']++;
					$prevID = $record->sID;
					if(isset($tmpArray3)) {
						$tmpArray1['attributes'] = $tmpArray3;
						$array['data'][] = $tmpArray1;
						unset($tmpArray3);
					}
					$tmpArray1['id'] = $record->sID;
					$tmpArray1['name'] = $record->sNAME;
					$tmpArray1['description'] = $record->sDESCRIPTION;
				}
				$tmpArray2['id'] = $record->svID;
				$tmpArray2['attribute'] = $record->iavNAME;
				$tmpArray2['rank'] = $record->svRANK;
				$tmpArray3[] = $tmpArray2;
			}
			$tmpArray1['attributes'] = $tmpArray3;
			$array['data'][] = $tmpArray1;
			return $array;
		} else {
			return array();
		}
	}
	
	public function setValuesAdd($setId, $attributeId, $rank) {
		if($setId == '' || $attributeId == '') return false;
		$query = sprintf("INSERT INTO `setValues` SET `setId` = '%s', `attributeId` = '%s', `rank` = '%s'"
				, mysql_escape_string($setId)
				, mysql_escape_string($attributeId)
				, mysql_escape_string($rank)
				);
		if($this->db->query($query)) {
			return $this->db->insert_id;
		} else {
			return false;
		}
	}
	
	public function setValuesUpdate($setValueId, $setId, $attributeId, $rank) {
		if($setValueId == '' || $setId == '' || $attributeId == '') return false;
		if($rank == '') {
			$query = sprintf("UPDATE `setValues` SET `setId` = '%s', `attributeId` = '%s' WHERE `setValueId` = '%s'"
					, mysql_escape_string($setId)
					, mysql_escape_string($attributeId)
					, mysql_escape_string($setValueId)
					);
		} else {
			$query = sprintf("UPDATE `setValues` SET `setId` = '%s', `attributeId` = '%s', `rank` = '%s' WHERE `setValueId` = '%s'"
				, mysql_escape_string($setId)
				, mysql_escape_string($attributeId)
				, mysql_escape_string($rank)
				, mysql_escape_string($setValueId)
				);
		}
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function setValuesDelete($setValueId) {
		if($setValueId == '') return false;
		$query = sprintf("DELETE FROM `setValues` WHERE `setValueId` = '%s'", mysql_escape_string($setValueId));
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function setValuesExistsById($setValueId) {
		if($setValueId == '') return false;
		$query = sprintf("SELECT * FROM `setValues` WHERE `setValueId` = '%s'", mysql_escape_string($setValueId));
		$result = $this->db->query_all($query);
		if(count($result)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function setListImages($setId = '', $imageIds = '') {
		if($setId == '') {
			$query = "SELECT s.setId sID, s.name sNAME, s.description sDESCRIPTION, sv.setValueId svID, iav.attributeId iavID, iav.name iavNAME, sv.rank svRANK FROM `set` s LEFT OUTER JOIN setValues sv ON (sv.setId = s.setId) JOIN imageAttribValue iav on (iav.attributeId = sv.attributeId) ORDER BY s.name, sv.rank";
		} else {
			if(!$this->setLoadById($setId)) {
				return false;
			} else {
				$query = "SELECT s.setId sID, s.name sNAME, s.description sDESCRIPTION, sv.setId svID, iav.attributeId iavID, iav.name iavNAME, sv.rank svRANK FROM `set` s LEFT OUTER JOIN setValues sv ON (sv.setId = s.setId) JOIN imageAttribValue iav on (iav.attributeId = sv.attributeId) WHERE s.setId = '$setId' ORDER BY s.name, sv.rank";
			}
		}
		$records = $this->db->query_all($query);
		if(count($records)) {
			$prevID = 0;
			foreach($records as $record) {
				if($prevID != $record->sID) {
					$prevID = $record->sID;
					if(isset($tmpArray3)) {
						$tmpArray1['attributes'] = $tmpArray3;
						$array['data'][] = $tmpArray1;
						unset($tmpArray3);
					}
					$tmpArray1['id'] = $record->sID;
					$tmpArray1['name'] = $record->sNAME;
					$tmpArray1['description'] = $record->sDESCRIPTION;
				}
				$tmpArray2['id'] = $record->svID;
				$tmpArray2['attribute'] = $record->iavNAME;
				$tmpArray2['rank'] = $record->svRANK;
				if($imageIds == '') {
					$query = sprintf("SELECT imageId FROM `imageAttrib` WHERE `attributeId` = '%s'"
							, mysql_escape_string($record->iavID)
							);
				} else {
					$query = sprintf("SELECT imageId FROM `imageAttrib` WHERE `attributeId` = '%s' AND imageId IN (%s)"
							, mysql_escape_string($record->iavID)
							, implode(',', $imageIds)
							);
				}
				$results = $this->db->query_all($query);
				if(count($results)) {
					$image = new Image($this->db);
					foreach($results as $result) {
						$details = $image->imageGetUrl($result->imageId);
						$tmpArray4['id'] = $result->imageId;
						$tmpArray4['filename'] = $details['filename'];
						$tmpArray4['url'] = $details['url'];
						$tmpArray4['baseUrl'] = $details['baseUrl'];
						// $tmpArray4['urlDetails'] = $details;
						$tmpArray5[] = $tmpArray4;
					}
					$tmpArray2['images'] = $tmpArray5;
					unset($tmpArray5);
				}
				$tmpArray3[] = $tmpArray2;
			}
			$tmpArray1['attributes'] = $tmpArray3;
			$array['data'][] = $tmpArray1;
			return $array;
		} else {
			return false;
		}
	}
	
	public function setListImageByKeyValue($key, $value) {
		$query = sprintf("SELECT DISTINCT ia.imageId FROM imageAttrib ia, imageAttribValue iav, imageAttribType iat WHERE ia.categoryId=iat.categoryId AND ia.attributeId=iav.attributeId AND iat.title='%s' AND iav.name='%s'", mysql_escape_string($key), mysql_escape_string($value));
		$records = $this->db->query_all($query);
		$imageIds = array();
		if(count($records)) {
			foreach($records as $record) {
				$imageIds[] = $record->imageId;
			}
		}
			
		$query = sprintf("SELECT distinct sv.setId FROM setValues sv, imageAttrib ia WHERE sv.attributeId=ia.attributeId AND ia.imageId IN (%s)", implode(',', $imageIds));
		$records = $this->db->query_all($query);
		if(count($records)) {
			foreach($records as $record) {
				$data = $this->setListImages($record->setId, $imageIds);
				$result[] = $data['data'];
			}
			return $result;
		} else {
			return false;
		}
	}
}

?>