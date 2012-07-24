<?php

class Set 
{
	public $db, $record;
	
	public function __construct($db) {
		$this->db = $db;
	}
	
	public function set( $field, $value ) {
		$this->record[$field] = $value;
		return( true );
	}
	
	public function get($field) {
		if(isset($this->record[$field])) {
			return $this->record[$field];
		} else {
			return false;
		}
	}
	
	public function load_by_id( $setId ) {
		if($setId == '') return false;
		$query = sprintf("SELECT * FROM `set` WHERE `id` = '%s'", mysql_escape_string($setId) );
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
	
	public function load_by_set_name( $name ) {
		if($name == '') return false;
		$query = sprintf("SELECT * FROM `set` WHERE `name` = '%s'", mysql_escape_string($name) );
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
	
	public function exists($name) {
		if($name == '') return false;
		$query = sprintf("SELECT * FROM `set` WHERE `name` = '%s'", mysql_escape_string($name));
		$result = $this->db->query_all($query);
		if(count($result)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function addSet($name, $description) {
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
	
	public function editSet($sId, $name, $description) {
		if($name == '' || $sId == '') return false;
		if($description == '') {
			$query = sprintf("UPDATE `set` SET `name` = '%s' WHERE `id` = '%s'"
					, mysql_escape_string($name)
					, mysql_escape_string($sId)
					);
		} else {
			$query = sprintf("UPDATE `set` SET `name` = '%s', `description` = '%s' WHERE `id` = '%s'"
				, mysql_escape_string($name)
				, mysql_escape_string($description)
				, mysql_escape_string($sId)
				);
		}
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function deleteSet($sId) {
		if($sId == '') return false;
		$query = sprintf("DELETE FROM `set` WHERE `id` = '%s'", mysql_escape_string($sId));
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function listSet() {
		$query = "SELECT s.id sID, s.name sNAME, s.description sDESCRIPTION, sv.id svID, iav.name iavNAME, sv.rank svRANK FROM `set` s LEFT OUTER JOIN set_values sv ON (sv.sId = s.id) JOIN image_attrib_value iav on (iav.valueID = sv.valueID) ORDER BY s.name, sv.rank";
		$records = $this->db->query_all($query);
		if(count($records)) {
			$array['count'] = 0;
			$prevID = 0;
			foreach($records as $record) {
				if($prevID != $record->sID) {
					$array['count']++;
					$prevID = $record->sID;
					if(isset($tmpArray3)) {
						$tmpArray1['values'] = $tmpArray3;
						$array['data'][] = $tmpArray1;
						unset($tmpArray3);
					}
					$tmpArray1['id'] = $record->sID;
					$tmpArray1['name'] = $record->sNAME;
					$tmpArray1['description'] = $record->sDESCRIPTION;
				}
				$tmpArray2['id'] = $record->svID;
				$tmpArray2['value'] = $record->iavNAME;
				$tmpArray2['rank'] = $record->svRANK;
				$tmpArray3[] = $tmpArray2;
			}
			$tmpArray1['values'] = $tmpArray3;
			$array['data'][] = $tmpArray1;
			return $array;
		} else {
			return false;
		}
	}
	
	public function addSetValue($sId, $valueId, $rank) {
		if($sId == '' || $valueId == '') return false;
		$query = sprintf("INSERT INTO `set_values` SET `sId` = '%s', `valueId` = '%s', `rank` = '%s'"
				, mysql_escape_string($sId)
				, mysql_escape_string($valueId)
				, mysql_escape_string($rank)
				);
		if($this->db->query($query)) {
			return $this->db->insert_id;
		} else {
			return false;
		}
	}
	
	public function editSetValue($id, $sId, $valueId, $rank) {
		if($id == '' || $sId == '' || $valueId == '') return false;
		if($rank == '') {
			$query = sprintf("UPDATE `set_values` SET `sId` = '%s', `valueId` = '%s' WHERE `id` = '%s'"
					, mysql_escape_string($sId)
					, mysql_escape_string($valueId)
					, mysql_escape_string($id)
					);
		} else {
			$query = sprintf("UPDATE `set_values` SET `sId` = '%s', `valueId` = '%s', `rank` = '%s' WHERE `id` = '%s'"
				, mysql_escape_string($sId)
				, mysql_escape_string($valueId)
				, mysql_escape_string($rank)
				, mysql_escape_string($id)
				);
		}
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function deleteSetValue($id) {
		if($id == '') return false;
		$query = sprintf("DELETE FROM `set_values` WHERE `id` = '%s'", mysql_escape_string($id));
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function exists_set_values_by_id($id) {
		if($id == '') return false;
		$query = sprintf("SELECT * FROM `set_values` WHERE `id` = '%s'", mysql_escape_string($id));
		$result = $this->db->query_all($query);
		if(count($result)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function listImageBySet($sId = '', $imageIds = '') {
		if($sId == '') {
			$query = "SELECT s.id sID, s.name sNAME, s.description sDESCRIPTION, sv.id svID, iav.valueID iavID, iav.name iavNAME, sv.rank svRANK FROM `set` s LEFT OUTER JOIN set_values sv ON (sv.sId = s.id) JOIN image_attrib_value iav on (iav.valueID = sv.valueID) ORDER BY s.name, sv.rank";
		} else {
			if(!$this->load_by_id($sId)) {
				return false;
			} else {
				$query = "SELECT s.id sID, s.name sNAME, s.description sDESCRIPTION, sv.id svID, iav.valueID iavID, iav.name iavNAME, sv.rank svRANK FROM `set` s LEFT OUTER JOIN set_values sv ON (sv.sId = s.id) JOIN image_attrib_value iav on (iav.valueID = sv.valueID) WHERE s.id = '$sId' ORDER BY s.name, sv.rank";
			}
		}
		$records = $this->db->query_all($query);
		if(count($records)) {
			$prevID = 0;
			foreach($records as $record) {
				if($prevID != $record->sID) {
					$prevID = $record->sID;
					if(isset($tmpArray3)) {
						$tmpArray1['values'] = $tmpArray3;
						$array['data'][] = $tmpArray1;
						unset($tmpArray3);
					}
					$tmpArray1['id'] = $record->sID;
					$tmpArray1['name'] = $record->sNAME;
					$tmpArray1['description'] = $record->sDESCRIPTION;
				}
				$tmpArray2['id'] = $record->svID;
				$tmpArray2['value'] = $record->iavNAME;
				$tmpArray2['rank'] = $record->svRANK;
				if($imageIds == '') {
					$query = sprintf("SELECT imageID FROM `image_attrib` WHERE `valueID` = '%s'"
							, mysql_escape_string($record->iavID)
							);
				} else {
					$query = sprintf("SELECT imageID FROM `image_attrib` WHERE `valueID` = '%s' AND imageID IN (%s)"
							, mysql_escape_string($record->iavID)
							, implode(',', $imageIds)
							);
				}
				$results = $this->db->query_all($query);
				if(count($results)) {
					$image = new Image($this->db);
					foreach($results as $result) {
						$details = $image->getUrl($result->imageID);
						$tmpArray4['id'] = $result->imageID;
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
			$tmpArray1['values'] = $tmpArray3;
			$array['data'][] = $tmpArray1;
			return $array;
		} else {
			return false;
		}
	}
	
	public function listImageBySetKeyValue($key, $value) {
		$query = sprintf("SELECT DISTINCT ia.imageID FROM image_attrib ia, image_attrib_value iav, image_attrib_type iat WHERE ia.typeID=iat.typeID AND ia.valueID=iav.valueID AND iat.title='%s' AND iav.name='%s'", mysql_escape_string($key), mysql_escape_string($value));
		$records = $this->db->query_all($query);
		$imageIds = array();
		if(count($records)) {
			foreach($records as $record) {
				$imageIds[] = $record->imageID;
			}
		}
			
		$query = sprintf("SELECT distinct sv.sId FROM set_values sv, image_attrib ia WHERE sv.valueId=ia.valueID AND ia.imageID IN (%s)", implode(',', $imageIds));
		$records = $this->db->query_all($query);
		if(count($records)) {
			foreach($records as $record) {
				$data = $this->listImageBySet($record->sId, $imageIds);
				$result[] = $data['data'];
			}
			return $result;
		} else {
			return false;
		}
	}
}

?>