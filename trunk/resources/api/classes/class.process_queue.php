<?php

/**
 * @copyright SilverBiology, LLC
 * @author Michael Giddens
 * @website http://www.silverbiology.com
 */

Class ProcessQueue {

	private $processs_stats;
	public $db, $record, $data, $image;

	public function __construct() {
		$this->image = new Image();
		$this->image->db = &$this->db;
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

	/**
	 * Returns all values for project
	 * @param string $set : allowed values : NEW, ORIG
	 * @return boolean|array
	 */
	public function get_all( $set = 'NEW' ) {
		if ($set == 'NEW') {
			return( $this->record );
		}
		if ($set == 'ORIG') {
			return( $this->record_orig );
		}
		return( false );
	}

	/**
	 * Set the value to Data
	 * @param mixed $data : input data
	 * @return bool
	 */
	public function setData($data) {
		$this->data = $data;
		return( true );
	}

	/**
	 * Loads by image_id
	 * @param integer $image_id : required parameter
	 * @return boolean
	 */
	public function load_by_id($image_id,$process_type = '') {
		$where = ($process_type != '') ? sprintf( " AND `process_type` = '%s' ",$process_type) : '';
			$query = sprintf("SELECT * FROM `process_queue` WHERE `image_id` = '%s' $where ", mysql_escape_string($image_id) );
			try {
				$ret = $this->db->query_one( $query );
			} catch (Exception $e) {
				trigger_error($e->getMessage(),E_USER_ERROR);
			}
			unset($this->records);
			if ($ret != NULL) {
				$records = array();
				foreach( $ret as $field => $value ) {
						$this->record_orig[$field] = $value;
						$this->record[$field] = $value;
				}
				$this->records[] = $this->record;
				return(true);
			} else {
				return(false);
			}
	}

	/**
	 * Saves the data to the db
	 */
	public function save() {
		if($this->field_exists($this->get('image_id'), $this->get('process_type'))) {
			$query = sprintf("UPDATE `process_queue` SET  `process_type` = '%s', `extra` = '%s', `date_added` = now(), `errors` = '%s', `error_details` = '%s' WHERE `image_id` = '%s' AND `process_type` = '%s';"
				, mysql_escape_string($this->get('process_type'))
				, mysql_escape_string($this->get('extra'))
				, mysql_escape_string($this->get('errors'))
				, mysql_escape_string($this->get('error_details'))
				, mysql_escape_string($this->get('image_id'))
				, mysql_escape_string($this->get('process_type'))
			);
		} else {
			$query = sprintf("INSERT INTO `process_queue` SET `image_id` = '%s', `process_type` = '%s', `extra` = '%s', `date_added` = now(), `errors` = '%s', `error_details` = '%s' ;"
				, mysql_escape_string($this->get('image_id'))
				, mysql_escape_string($this->get('process_type'))
				, mysql_escape_string($this->get('extra'))
				, mysql_escape_string($this->get('errors'))
				, mysql_escape_string($this->get('error_details'))
			);
		}
// echo $query;
		if($this->db->query($query)) {
			return(true);
		} else {
			return (false);
		}
	}

	/**
	 * checks whether field exists in process_queue table
	 */
	public function field_exists ( $image_id, $process_type = '' ){
		if($process_type != '') {
			$query = sprintf("SELECT `image_id` FROM `process_queue` WHERE `image_id` = '%s' AND `process_type` = '%s' ;", mysql_escape_string($image_id),  mysql_escape_string($process_type));
		} else {
			$query = sprintf("SELECT `image_id` FROM `process_queue` WHERE `image_id` = '%s';", $image_id );
		}
		$ret = $this->db->query_one( $query );

		if ($ret == NULL) {
				return false;
		} else {
				return true;
		}
	}

	public function process_queue() {
		$ret = array();
		unset($this->process_stats);
		$this->process_stats = array('small' => 0, 'medium' => 0, 'large' => 0, 'google_tile' => 0, 'zoomify' => 0, 'cache' => 0);
		$loop_flag = true;
		$stop = $this->data['stop'];
		$limit = $this->data['limit'];
		$this->image->db = &$this->db;
		$count = 0;

		while($loop_flag) {
			if( ($stop != "") && (mktime() > $stop) ) $loop_flag = false;
			if ($this->data['limit'] != '' && $count >= $limit) $loop_flag = false;
			
			$record = $this->popQueue();
			if($record === false) {
				$loop_flag = false;
			} else {
# $loop_flag = false;
				$this->processType($record);
				$count++;
			}
		}

		$subject = 'Cyberflora Image Server - Processed';
		$message = "Processed\r\n----------------";
		$message .= "\r\nSmall : " . $this->process_stats['small'];
		$message .= "\r\nMedium : " . $this->process_stats['medium'];
		$message .= "\r\nLarge : " . $this->process_stats['large'];
		$message .= "\r\nGoogle Tile : " . $this->process_stats['google_tile'];
		$message .= "\r\nZoomify : " . $this->process_stats['zoomify'];
		$message .= "\r\nCache : " . $this->process_stats['cache'];
		$to = $config['email']['to'];
/*
		if( mail($to, $subject, $message) ) {
			return true;
		} else {
			return false;
		}
*/
		$ret['success'] = true;
		$ret['time'] = microtime(true) - $this->data['time_start'];
		$ret['total'] = $count;
		return $ret;
	}

	public function popQueue($process_type = '') {

		switch($process_type) {
			case 'flickr_add':
				$query = "SELECT * FROM `process_queue` WHERE `process_type` = 'flickr_add' ORDER BY `date_added` LIMIT 1";
				break;
			case 'picassa_add':
				$query = "SELECT * FROM `process_queue` WHERE `process_type` = 'picassa_add' ORDER BY `date_added` LIMIT 1";
				break;
			case 'zoomify':
				$query = "SELECT * FROM `process_queue` WHERE `process_type` = 'zoomify' ORDER BY `date_added` LIMIT 1";
				break;
			case 'google_tile':
				$query = "SELECT * FROM `process_queue` WHERE `process_type` = 'google_tile' ORDER BY `date_added` LIMIT 1";
				break;
			case 'ocr_add':
				$query = "SELECT * FROM `process_queue` WHERE `process_type` = 'ocr_add' ORDER BY `date_added` LIMIT 1";
				break;
			case 'name_add':
				$query = "SELECT * FROM `process_queue` WHERE `process_type` = 'name_add' ORDER BY `date_added` LIMIT 1";
				break;
			case 'all':
				$query = "SELECT * FROM `process_queue` WHERE `process_type` = 'all' ORDER BY `date_added` LIMIT 1";
				break;
			default:
				$query = "SELECT * FROM `process_queue` WHERE `process_type` NOT IN ('picassa_add','flickr_add') ORDER BY `date_added` LIMIT 1";
				break;
		}
		$result = $this->db->query_one($query);
		if($result == NULL) {
			return false;
		} else {
			$delquery = sprintf("DELETE FROM `process_queue` WHERE `image_id` = '%s' AND `process_type` = '%s' ", mysql_escape_string($result->image_id), mysql_escape_string($result->process_type));

			if($this->db->query($delquery)) {
					return $result;
			} else {
					return false;
			}

			return $result;
		}
	}

	public function processType($record) {
		$this->image->load_by_barcode($record->image_id);
		if($this->data['mode'] != 's3') {
			$image_path = PATH_IMAGES . $this->image->barcode_path( $record->image_id );
			$image = $image_path . $this->image->get('filename');
			$this->image->set_fullpath($image);
		}
		switch($record->process_type) {
			case 'all':
				$this->process_stats['small']++;
				$this->process_stats['medium']++;
				$this->process_stats['large']++;

				if($this->data['mode'] == 's3') {
					$ar = array ('s3' => $this->data['s3'], 'obj' => $this->data['obj'], 'postfix' => '_s', 'width' => 100, 'height' => 100);
					$tmpPath = $this->image->createThumbS3($record->image_id,$ar,false);

					$ar = array ('s3' => $this->data['s3'], 'obj' => $this->data['obj'], 'postfix' => '_m', 'width' => 275, 'height' => 275);
// 					$this->image->createThumbS3($record->image_id,$ar);
					$tmpPath = $this->image->createFromFileS3($tmpPath,$record->image_id,$ar,false);

					$ar = array ('s3' => $this->data['s3'], 'obj' => $this->data['obj'], 'postfix' => '_l', 'width' => 800, 'height' => 800);
// 					$this->image->createThumbS3($record->image_id,$ar);
					$this->image->createFromFileS3($tmpPath,$record->image_id,$ar,true);

				} else {
					if(IMAGE_PROCESSING == 1) {
						$this->image->createThumbnailIMagik( $image, 100, 100, "_s");
						$this->image->createThumbnailIMagik( $image, 275, 275, "_m");
						$this->image->createThumbnailIMagik( $image, 800, 800, "_l");
					} else {
						$this->image->createThumbnail( $image, 100, 100, "_s");
						$this->image->createThumbnail( $image, 275, 275, "_m");
						$this->image->createThumbnail( $image, 800, 800, "_l");
					}
				}
				$this->image->load_by_barcode($record->image_id);
				$this->image->set('processed',1);
				$this->image->save();
				break;

			case 'small':
				$postFix = '_s';
				$width = 100;
				$height = 100;
				$this->process_stats['small']++;
				if($this->data['mode'] == 's3') {
					$ar = array ('s3' => $this->data['s3'], 'obj' => $this->data['obj'], 'postfix' => $postFix, 'width' => $width, 'height' => $height);
					$rr = $this->image->createThumbS3($record->image_id,$ar);
				} else {
					if(IMAGE_PROCESSING == 1) {
						$this->image->createThumbnailIMagik( $image, $width, $height, $postFix);
					} else {
						$this->image->createThumbnail( $image, $width, $height, $postFix);
					}
				}
				$this->image->load_by_barcode($record->image_id);
				$this->image->set('processed',1);
				$this->image->save();
				break;

			case 'medium':
				$postFix = '_m';
				$width = 275;
				$height = 275;
				$this->process_stats['medium']++;
				if($this->data['mode'] == 's3') {
					$ar = array ('s3' => $this->data['s3'], 'obj' => $this->data['obj'], 'postfix' => $postFix, 'width' => $width, 'height' => $height);
					$this->image->createThumbS3($record->image_id,$ar);
				} else {
					if(IMAGE_PROCESSING == 1) {
						$this->image->createThumbnailIMagik( $image, $width, $height, $height);
					} else {
						$this->image->createThumbnail( $image, $width, $height, $height);
					}
				}
				$this->image->load_by_barcode($record->image_id);
				$this->image->set('processed',1);
				$this->image->save();
				break;

			case 'large':
				$postFix = '_l';
				$width = 800;
				$height = 800;
				$this->process_stats['large']++;

				if($this->data['mode'] == 's3') {
					$ar = array ('s3' => $this->data['s3'], 'obj' => $this->data['obj'], 'postfix' => $postFix, 'width' => $width, 'height' => $height);
					$this->image->createThumbS3($record->image_id,$ar);
				} else {
					if(IMAGE_PROCESSING == 1) {
						$this->image->createThumbnailIMagik( $image, $width, $height, $postFix);
					} else {
						$this->image->createThumbnail( $image, $width, $height, $postFix);
					}
				}
				$this->image->load_by_barcode($record->image_id);
				$this->image->set('processed',1);
				$this->image->save();
				break;

			case 'cache':
				$json_data = 'test';
				$this->process_stats['cache']++;
				$filename = $image_path . $record->image_id . '.json';
				$fp = fopen($filename, 'w');
				fwrite($fp,$json_data);
				fclose($fp);
				break;

			case 'google_tile':
				$this->process_stats['google_tile']++;
				if($this->data['mode'] == 's3') {
					$ar = array ('s3' => $this->data['s3'], 'obj' => $this->data['obj']);
					$ret = $this->image->processGTileIM_S3($record->image_id,$ar);
				} else {
					$ret = $this->image->processGTileIM($record->image_id);
//					$ret = $this->image->processGTile($record->image_id); # No image magic format uses GD but not as clear.
				}
				if($ret) {
					$this->image->load_by_barcode($record->image_id);
					$this->image->set('gTileProcessed', 1);
					$this->image->save();
				}
				break;

			case 'zoomify':
				$this->image->zoomifyImage($record->image_id);
				break;
		}
	}

	public function listQueue() {
// 		$where = build_where($this->data['filter']);
		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " WHERE " . $where;
		}
		$where .= build_order( $this->data['order']);
// 		$where .= build_limit($this->data['start'], $this->data['limit']);


# $query = "SELECT SQL_CALC_FOUND_ROWS * FROM `process_queue` " . $where; - query if paging not used
		$query = "SELECT * FROM `process_queue` " . $where; # query for paging

		$page = ($this->data['limit'] != 0) ? floor($this->data['start']/$this->data['limit']) : 1;
# 		$page = ($page == 0) ? 1 : $page;

# 		$ret = $this->db->query_all( $query );
		$ret = $this->db->query_page_all( $query, $this->data['limit'],$page );

		return is_null($ret) ? array() : $ret;
	}

	public function clearQueue() {
		if(!(is_array($this->data['processType']) || is_array($this->data['imageIds']))) return array('success' => false, 'recordCount' => 0);
		$where = '';
		if(is_array($this->data['processType']) && count($this->data['processType'])) {
			foreach($this->data['processType'] as &$type) {
				$type = mysql_escape_string($type);
			}
			$where .= sprintf(" AND `process_type` IN ('%s') ",@implode("','",$this->data['processType']));
		}
		if(is_array($this->data['imageIds']) && count($this->data['imageIds'])) {
			foreach($this->data['imageIds'] as &$imageId) {
				$imageId = mysql_escape_string($imageId);
			}
			$where .= sprintf(" AND `image_id` IN ('%s') ",@implode("','",$this->data['imageIds']));
		}
		if($where != '') {
			$query = sprintf(" DELETE FROM `process_queue` WHERE 1=1 %s ",$where);
		}

// echo $query;

		$this->db->query($query);
		$recordCount = $this->db->affected_rows;
		$recordCount = is_null($recordCount) ? 0 : $recordCount;

		return array('success' => true, 'recordCount' => $recordCount);
	}


}
?>