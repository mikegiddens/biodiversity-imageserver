<?php

/**
 * @copyright SilverBiology, LLC
 * @author Michael Giddens
 * @website http://www.silverbiology.com
 */

Class Logger {

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

	private function setFieldFromCsv ($data) {
// 		$field_array = array ('sc_id', 'log_id', 'image_id', 'before', 'after', 'task', 'timestamp_modified', 'user', 'station_id', 'barcode');
		$field_array = array ('sc_id', 'log_id', 'before', 'after', 'task', 'timestamp_modified', 'user', 'station_id', 'image_id', 'barcode');
		$counter = 0;
		foreach($field_array as $field) {
			$this->set($field,$data[$counter]);
			$counter++;
		}
		return true;
	}

	public function setData ($data) {
		$this->data = $data;
	}

	public function clearRecords () {
		unset($this->records);
		return true;
	}

	public function getRecords () {
		return $this->records;
	}

	public function getId() {
		$query = sprintf("SELECT MAX(`log_id`) as log_id FROM `master_log` WHERE `sc_id` = '%s'", mysql_escape_string($this->data['sc_id']));
		$record = $this->db->query_one($query);
		if($record != NULL) {
			$this->records = $record->log_id;
			return true;
		}
		return false;
	}

	public function loadLogs() {
		$ret = array();
		if(is_dir($this->data['path_files'])) {
			$handle = opendir($this->data['path_files']);
			$count = 0;
			while (false !== ($file_name = readdir($handle))) {
				if( $file_name == '.' || $file_name == '..') continue;

				$fp = fopen($this->data['path_files'] . $file_name,'r');
				while (($data = fgetcsv($fp, ",")) !== FALSE) {
					$this->setFieldFromCsv($data);
					$this->save();
				}
				fclose($fp);
				if(!file_exists($this->data['processed_files'])) {
					@mkdir($this->data['processed_files'],0775);
				}
				@rename($this->data['path_files'] . $file_name, $this->data['processed_files'] . $file_name);
				$count++;
			}
			$ret['success'] = true;
			
		} else {
			$ret['success'] = false;
		}
		$ret['total'] = $count;
		$ret['time'] = microtime(true) - $this->data['time_start'];
		return $ret;
	}

	public function loadS3Logs() {

		if(!@file_exists(PATH_TMP . 'logs/')) {
			@mkdir(PATH_TMP . 'logs/',0775);
		}
		$filename = PATH_TMP . 'logs/' . 'log.csv';
		$ret = array();

		# taking log files from the s3 logs folder
		$logArray = $this->data['obj']->getBucket($this->data['s3']['bucket'],$this->data['s3']['logPath']);

		if(count($logArray) && is_array($logArray)) {
			foreach($logArray as $log) {
				$this->data['obj']->getBucketFile($log['name'], $this->data['s3']['bucket'], $filename);

				$fp = fopen($filename,'r');
				while (($data = fgetcsv($fp, ",")) !== FALSE) {
					$this->setFieldFromCsv($data);
					$this->save();
				}
				fclose($fp);
				clearstatcache();
				$count++;

				#uploading to s3 processed logs directory and deleting from logs directory
				$this->data['obj']->putObjectFile($filename, $this->data['s3']['bucket'], $this->data['s3']['processedLogPath'] . @basename($log['name']), S3::ACL_PUBLIC_READ);
				@unlink($filename);
				$this->data['obj']->deleteObject($this->data['s3']['bucket'], $log['name']);
			}
			$ret['success'] = true;
		}


		$ret['total'] = $count;
		$ret['time'] = microtime(true) - $this->data['time_start'];
		return $ret;
	}

	public function save() {
		if(!$this->recordExists()) {
			$query = sprintf("INSERT INTO `master_log` (`sc_id`, `log_id`, `station_id`, `image_id`, `barcode`,  `before`, `after`, task, timestamp_modified, `user`) VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');", mysql_escape_string( $this->get('sc_id') ), mysql_escape_string( $this->get('log_id') ), mysql_escape_string( $this->get('station_id') ), mysql_escape_string( $this->get('image_id') ), mysql_escape_string( $this->get('barcode') ), mysql_escape_string( $this->get('before') ), mysql_escape_string( $this->get('after') ), mysql_escape_string( $this->get('task') ), mysql_escape_string( $this->get('timestamp_modified')), mysql_escape_string( $this->get('user') ) );

			if( $this->db->query($query) ) {
				return( true );
			}
		}
		return( false );
	}

	public function recordExists() {
		$query = sprintf("SELECT `id` FROM `master_log` WHERE `sc_id` = '%s' AND `log_id` = '%s' AND `station_id` = '%s' ;", mysql_escape_string( $this->get('sc_id') ), mysql_escape_string( $this->get('log_id') ), mysql_escape_string( $this->get('station_id') ) );
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}

	public function loadCollectionReport() {
		$op = array();
		switch($this->data['report_type']) {
			case 'day':
				$group_term = ' HOUR(timestamp_modified) ';
				break;
			case 'week':
				$group_term = ' DAYNAME(timestamp_modified) ';
				break;
			case 'month':
				$group_term = ' DAYOFMONTH(timestamp_modified) ';
				break;
			case 'year':
				$group_term = ' MONTHNAME(timestamp_modified) ';
				break;
		}

		$query = "SELECT count(*) ct, $group_term as dt FROM `master_log` WHERE `task` = 'IMAGE_PHOTOGRAPHED' AND user != 0 ";

		if($this->data['date'] != '' && $this->data['date2'] !='') {
			$query .= sprintf(" AND ( date( `timestamp_modified` ) BETWEEN '%s' AND '%s' ) ", mysql_escape_string($this->data['date']), mysql_escape_string($this->data['date2']));
		} else if($this->data['date'] != '' && $this->data['date2'] == '') {
			$query .= sprintf(" AND date( `timestamp_modified` ) = '%s' ", mysql_escape_string($this->data['date']));
		}


		if($this->data['year'] != '') {
			$query .= sprintf(" AND YEAR(timestamp_modified) = '%s' ", mysql_escape_string($this->data['year']));
		}

		if($this->data['month'] != '') {
			$query .= sprintf(" AND MONTH(timestamp_modified) = '%s' ", mysql_escape_string($this->data['month']));
		}

		if($this->data['collection_id'] != '') {
			$query1 = sprintf(" SELECT `code` FROM `collection` WHERE `collection_id` = '%s' ", mysql_escape_string($this->data['collection_id']));
			$rt = $this->db->query_one($query1);
			$code = $rt->code;

			$query .= sprintf(" AND `barcode` LIKE '%s%%' ", mysql_escape_string($code));

		}

		$query .= " GROUP BY  $group_term ";

// 		print $query;

		$records = $this->db->query_all($query);

		$op = array();
		$data = $this->getTemplateData($this->data['report_type']);
		if(count($records) && is_array($records)) {

			foreach($records as $record) {
				$data[$record->dt]['l1'] = $record->ct;
			}
		}

		if(count($data)) {
			foreach($data as $dt) {
				$op[] = $dt;
			}
		}

		$this->records = $op;
		return true;
		$query = 'SELECT * FROM `log`';
	}

	public function loadReportByDateRange () {
		$query = "SELECT count(*) ct, date(`timestamp_modified`) as dt FROM `master_log` WHERE `task` = 'IMAGE_PHOTOGRAPHED' ";

		if($this->data['date'] != '' && $this->data['date2'] != '') {
			$query .= sprintf(" AND ( date( `timestamp_modified` ) BETWEEN '%s' AND '%s' ) ", mysql_escape_string($this->data['date']), mysql_escape_string($this->data['date2']));
		}

		if(is_array($this->data['users']) &&  count($this->data['users'])) {
			$user_list = @implode(',', $this->data['users']);
			$query .= sprintf(" AND `user` IN (%s) ", mysql_escape_string($user_list));
		}

		if($this->data['station'] != '') {
			$this->data['station'] = json_decode( $this->data['station'] );
			if(count($this->data['station'])) {
				$user_list = @implode(',', $this->data['station']);
				$query .= sprintf(" AND `station_id` IN (%s) ", mysql_escape_string($user_list));
			}
		}

		if($this->data['user_id'] != '') {
			$query .= sprintf(" AND user = '%s' ", mysql_escape_string($this->data['user_id']));
		}
		if($this->data['sc'] != '') {
			$query .= sprintf(" AND `sc_id` = '%s' ", mysql_escape_string($this->data['sc']));
		}

// 		if($this->data['sc'] != '') {
// 			$this->data['sc'] = json_decode( $this->data['sc'] );
// 			if(count($this->data['sc'])) {
// 				$sc_list = @implode(',', $this->data['sc']);
// 				$query .= sprintf(" AND `sc_id` IN (%s) ", mysql_escape_string($user_list));
// 			}
// 		}

		$query .= " GROUP BY  date(`timestamp_modified`) ";

		$records = $this->db->query_all($query);

		$op = array();

		if(count($records)) {
			$i = 0;
			foreach($records as $record) {
				$i++;
				$temp_array = array();
				$temp_array['time'] = $record->dt;
				$temp_array['col' . $i] = $record->ct;
				$op[] = $temp_array;
			}
		}
		$this->records = $op;
		return true;
	}

	public function loadReportByDate () {

		$op = array();
		for($i=1;$i<=24;$i++) {
			$result[$i] = 0;
		}

		$query = sprintf( "SELECT count(*) ct, HOUR (`timestamp_modified`) as dt FROM `master_log` WHERE `task` = 'IMAGE_PHOTOGRAPHED' AND ( date( `timestamp_modified` ) = '%s' ) ", mysql_escape_string($this->data['date']) );
		if($this->data['users'] != '') {
			$this->data['users'] = json_decode( $this->data['users'] );

			if(count($this->data['users'])) {
				$user_list = @implode(',', $this->data['users']);
				$query .= sprintf(" AND `user` IN (%s) ", mysql_escape_string($user_list));
			}
		}

		if($this->data['station'] != '') {
			$this->data['station'] = json_decode( $this->data['station'] );
			if(count($this->data['station'])) {
				$station_list = @implode(',', $this->data['station']);
				$query .= sprintf(" AND `station_id` IN (%s) ", mysql_escape_string($station_list));
			}
		}

		if($this->data['sc'] != '') {
			$this->data['sc'] = json_decode( $this->data['sc'] );
			if(count($this->data['sc'])) {
				$sc_list = @implode(',', $this->data['sc']);
				$query .= sprintf(" AND `sc_id` IN (%s) ", mysql_escape_string($sc_list));
			}
		}

		$query .= " GROUP BY  HOUR (`timestamp_modified`) ";
// echo $query;
		$records = $this->db->query_all($query);

		$op = array();

		if(count($records)) {
			$i = 0;
			$tmp_array = array();
			foreach($records as $record) {
				if ($record->dt >= 1 && $record->dt <= 24) {
					$tmp_array[$record->dt] = $record->ct;
				}
			}
			foreach($tmp_array as $key => $value) {
				$result[$key] = $value;
			}
		}
		$i = 0;
		foreach($result as $key => $value) {
			$i++;
			$temp_array = array();
			$temp_array['name'] = $key;
			$temp_array['ds' . $i] = $value;
			$op[] = $temp_array;

		}
		$this->records = $op;
		return true;
	}

/*
	public function loadGraphReportUsers () {
		$query = sprintf( "SELECT count(*) ct, date(`timestamp_modified`) as dt, user FROM `master_log` WHERE `task` = 'IMAGE_PHOTOGRAPHED' AND ( date( `timestamp_modified` ) BETWEEN '%s' AND '%s' ) AND user != 0 ", mysql_escape_string($this->data['date']), mysql_escape_string($this->data['date2']) );

		if($this->data['users'] != '') {
			$this->data['users'] = json_decode( $this->data['users'] );
			if(count($this->data['users'])) {
				$user_list = @implode(',', $this->data['users']);
				$query .= sprintf(" AND `user` IN (%s) ", mysql_escape_string($user_list));
			}
		}

		$query .= " GROUP BY  date(`timestamp_modified`), user ";

		$records = $this->db->query_all($query);

		$op = array();

		if(count($records)) {

			$temp = '';
			$arr = array();
			foreach($records as $record) {
				if($temp != $record->dt) {
					if(count($arr)) {
						$op[] = $arr;
						$arr = array();
					}
					$arr['name'] = $record->dt;
					$temp = $record->dt;
				}
				$arr[$record->user] = $record->ct;
			}
			$op[] = $arr;
		}

		$this->records = $op;
		return true;
	}
*/

	public function loadGraphReportUsers () {
		$op = array();
		switch($this->data['report_type']) {
			case 'day':
				$group_term = ' HOUR(timestamp_modified) ';
				break;
			case 'week':
				$group_term = ' DAYNAME(timestamp_modified) ';
				break;
			case 'month':
				$group_term = ' DAYOFMONTH(timestamp_modified) ';
				break;
			case 'year':
				$group_term = ' MONTHNAME(timestamp_modified) ';
				break;
		}

		$query = "SELECT count(*) ct, $group_term as dt FROM `master_log` WHERE `task` = 'IMAGE_PHOTOGRAPHED' AND user != 0 ";

		if($this->data['date'] != '' && $this->data['date2'] !='') {
			$query .= sprintf(" AND ( date( `timestamp_modified` ) BETWEEN '%s' AND '%s' ) ", mysql_escape_string($this->data['date']), mysql_escape_string($this->data['date2']));
		} else if($this->data['date'] != '' && $this->data['date2'] == '') {
			$query .= sprintf(" AND date( `timestamp_modified` ) = '%s' ", mysql_escape_string($this->data['date']));
		}

		if(count($this->data['users']) && is_array($this->data['users'])) {
			$user_list = @implode(',', $this->data['users']);
			$query .= sprintf(" AND `user` IN (%s) ", mysql_escape_string($user_list));
		}


		if($this->data['year'] != '') {
			$query .= sprintf(" AND YEAR(timestamp_modified) = '%s' ", mysql_escape_string($this->data['year']));
		}

		if($this->data['month'] != '') {
			$query .= sprintf(" AND MONTH(timestamp_modified) = '%s' ", mysql_escape_string($this->data['month']));
		}

		if($this->data['user_id'] != '') {
			$query .= sprintf(" AND user = '%s' ", mysql_escape_string($this->data['user_id']));
		}

		$query .= " GROUP BY  $group_term ";
// print $query;
		$records = $this->db->query_all($query);

		$op = array();
		$data = $this->getTemplateData($this->data['report_type']);
		if(count($records) && is_array($records)) {

			foreach($records as $record) {
				$data[$record->dt]['l1'] = $record->ct;
			}
		}

		if(count($data)) {
			foreach($data as $dt) {
				$op[] = $dt;
			}
		}

		$this->records = $op;
		return true;
	}

	public function loadGraphReportStations () {
		$query = sprintf( "SELECT count(*) ct, date(`timestamp_modified`) as dt, station_id FROM `master_log` WHERE `task` = 'IMAGE_PHOTOGRAPHED' AND ( date( `timestamp_modified` ) BETWEEN '%s' AND '%s' ) AND station_id != 0 ", mysql_escape_string($this->data['date']), mysql_escape_string($this->data['date2']) );

		if($this->data['users'] != '') {
			$this->data['users'] = json_decode( $this->data['users'] );
			if(count($this->data['users'])) {
				$user_list = @implode(',', $this->data['users']);
				$query .= sprintf(" AND `user` IN (%s) ", mysql_escape_string($user_list));
			}
		}

		if($this->data['station'] != '') {
			$this->data['station'] = json_decode( $this->data['station'] );
			if(count($this->data['station'])) {
				$user_list = @implode(',', $this->data['station']);
				$query .= sprintf(" AND `station_id` IN (%s) ", mysql_escape_string($user_list));
			}
		}

		$query .= " GROUP BY  date(`timestamp_modified`), station_id ";

		$records = $this->db->query_all($query);


		$op = array();

		if(count($records)) {

			$temp = '';
			$arr = array();
			foreach($records as $record) {
				if($temp != $record->dt) {
					if(count($arr)) {
						$op[] = $arr;
						$arr = array();
					}
					$arr['name'] = $record->dt;
					$temp = $record->dt;
				}
				$arr[$record->station_id] = $record->ct;
			}
			$op[] = $arr;
		}
		$this->records = $op;
		return true;
	}

	public function loadGraphReportSc () {
		$query = sprintf( "SELECT count(*) ct, date(`timestamp_modified`) as dt, sc_id FROM `master_log` WHERE `task` = 'IMAGE_PHOTOGRAPHED' AND ( date( `timestamp_modified` ) BETWEEN '%s' AND '%s' ) AND station_id != 0 ", mysql_escape_string($this->data['date']), mysql_escape_string($this->data['date2']) );

		if($this->data['users'] != '') {
			$this->data['users'] = json_decode( $this->data['users'] );
			if(count($this->data['users'])) {
				$user_list = @implode(',', $this->data['users']);
				$query .= sprintf(" AND `user` IN (%s) ", mysql_escape_string($user_list));
			}
		}

		if($this->data['station'] != '') {
			$this->data['station'] = json_decode( $this->data['station'] );
			if(count($this->data['station'])) {
				$user_list = @implode(',', $this->data['station']);
				$query .= sprintf(" AND `station_id` IN (%s) ", mysql_escape_string($user_list));
			}
		}

		if($this->data['sc'] != '') {
			$this->data['sc'] = json_decode( $this->data['sc'] );
			if(count($this->data['sc'])) {
				$sc_list = @implode(',', $this->data['sc']);
				$query .= sprintf(" AND `sc_id` IN (%s) ", mysql_escape_string($sc_list));
			}
		}

		$query .= " GROUP BY  date(`timestamp_modified`), sc_id ";

		$records = $this->db->query_all($query);


		$op = array();

		if(count($records)) {

			$temp = '';
			$arr = array();
			foreach($records as $record) {
				if($temp != $record->dt) {
					if(count($arr)) {
						$op[] = $arr;
						$arr = array();
					}
					$arr['name'] = $record->dt;
					$temp = $record->dt;
				}
				$arr[$record->station_id] = $record->ct;
			}
			$op[] = $arr;
		}
		$this->records = $op;
		return true;
	}

	public function getImageStorageStats() {

		$total_size = 0;
		$dir_iterator = new RecursiveDirectoryIterator(PATH_IMAGES);
		$images_array = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
		$data['total'] = count($images_array);
		if( $data['total'] ) {
			foreach($images_array as $image) {
				$total_size += @filesize($image);
			}
		}
		$allowed_images = round((DISK_SIZE - $total_size) / IMAGE_SIZE);
		$data['allowed_images'] = $allowed_images;
		return $data;

	}

	public function getTotalImagesCount() {
		$query = "SELECT count(*) ct FROM `master_log` WHERE `task` = 'IMAGE_PHOTOGRAPHED'";
		$records = $this->db->query_one($query);
		return $records->ct;
	}

	public function getTemplateData($report_type = 'day', $extra_param = '') {
		$data = array();
		switch($report_type) {
			case 'day':
				for($i = 0; $i <= 23; $i++) {
					$data[$i]['coll'] = $i;
					$data[$i]['l1'] = 0;
				}
				break;
			case 'week':

				$data['Sunday']['coll'] = 'Sunday';
				$data['Monday']['coll'] = 'Monday';
				$data['Tuesday']['coll'] = 'Tuesday';
				$data['Wednesday']['coll'] = 'Wednesday';
				$data['Thursday']['coll'] = 'Thursday';
				$data['Friday']['coll'] = 'Friday';
				$data['Saturday']['coll'] = 'Saturday';

				$data['Sunday']['l1'] = 0;
				$data['Monday']['l1'] = 0;
				$data['Tuesday']['l1'] = 0;
				$data['Wednesday']['l1'] = 0;
				$data['Thursday']['l1'] = 0;
				$data['Friday']['l1'] = 0;
				$data['Saturday']['l1'] = 0;
				break;
			case 'month':
				$last_day = @date('t');
				for($i = 1; $i <= $last_day; $i++) {
					$data[$i]['coll'] = $i;
					$data[$i]['l1'] = 0;
				}
				break;
			case 'year':

				$data['January']['coll'] = 'January';
				$data['February']['coll'] = 'February';
				$data['March']['coll'] = 'March';
				$data['April']['coll'] = 'April';
				$data['May']['coll'] = 'May';
				$data['June']['coll'] = 'June';
				$data['July']['coll'] = 'July';
				$data['August']['coll'] = 'August';
				$data['September']['coll'] = 'September';
				$data['October']['coll'] = 'October';
				$data['November']['coll'] = 'November';
				$data['December']['coll'] = 'December';

				$data['January']['monthInt'] = 1;
				$data['February']['monthInt'] = 2;
				$data['March']['monthInt'] = 3;
				$data['April']['monthInt'] = 4;
				$data['May']['monthInt'] = 5;
				$data['June']['monthInt'] = 6;
				$data['July']['monthInt'] = 7;
				$data['August']['monthInt'] = 8;
				$data['September']['monthInt'] = 9;
				$data['October']['monthInt'] = 10;
				$data['November']['monthInt'] = 11;
				$data['December']['monthInt'] = 12;

				$data['January']['l1'] = 0;
				$data['February']['l1'] = 0;
				$data['March']['l1'] = 0;
				$data['April']['l1'] = 0;
				$data['May']['l1'] = 0;
				$data['June']['l1'] = 0;
				$data['July']['l1'] = 0;
				$data['August']['l1'] = 0;
				$data['September']['l1'] = 0;
				$data['October']['l1'] = 0;
				$data['November']['l1'] = 0;
				$data['December']['l1'] = 0;

				break;
		}
		return $data;
	}


	public function getStationUsers() {

		$query = 'SELECT DISTINCT station_id, user FROM `master_log` WHERE station_id !=0 AND user !=0 ';
		if($this->get('station_id') != '' && $this->get('station_id') != 0 && $this->get('station_id') !== false) {
			$query .= sprintf(" AND `station_id` = %s ", $this->get('station_id'));
		}
		$query .= 'ORDER BY station_id, user';
// echo $query;
		$opArray = array();
		$Ret = $this->db->query($query);
		if(is_object($Ret)) {
			$tmp = '';
			$childArray = array();
			$loopFlag = false;
			while ($record = $Ret->fetch_object()) {
				if($tmp != $record->station_id) {
					if(count($opArray)) {
					# if not the first record
						$opArray[] = array(
							  'text' => 'Station ' . $tmp
							, 'id' => $tmp
							, 'cls' => 'folder'
							, 'leaf' => false
							, 'expanded' => true
							, 'children' => $childArray
							);
						$childArray = array();
					}
					$tmp = $record->station_id;
				}
				$childArray[] = array(
						  'text' => 'User ' . $record->user
						, 'id' => $record->user
						, 'leaf' => true
						, 'cls' => 'file'
						);
				$loopFlag = true;
			}
			if($loopFlag) {
			# including the last record (or the first)
				$opArray[] = array(
						'text' => 'Station ' . $tmp
					, 'id' => $tmp
					, 'cls' => 'folder'
					, 'leaf' => false
					, 'expanded' => true
					, 'children' => $childArray
					);
			}
		} # valid result set
		return $opArray;
	}

}
	
?>