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
	public function loggerGetProperty( $field ) {
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
	public function loggerSetProperty( $field, $value ) {
		$this->record[$field] = $value;
		return( true );
	}

	private function loggerSetFieldFromCsv ($data) {
		$field_array = array ('scId', 'logId', 'imageId', 'before', 'after', 'task', 'timestampModified', 'userId', 'stationId','dummyField', 'barcode');
		$counter = 0;
		foreach($field_array as $field) {
			$this->loggerSetProperty($field,$data[$counter]);
			$counter++;
		}
		return true;
	}

	public function loggerSetData ($data) {
		$this->data = $data;
	}

	public function loggerClearRecords () {
		unset($this->records);
		return true;
	}

	public function loggerGetRecords () {
		return $this->records;
	}

	public function loggerGetId() {
		$query = sprintf("SELECT MAX(`logId`) as logId FROM `masterLog` WHERE `scId` = '%s'", mysql_escape_string($this->data['scId']));
		$record = $this->db->query_one($query);
		if($record != NULL) {
			$this->records = $record->logId;
			return true;
		}
		return false;
	}

	public function loggerLoadLogs() {
		if(is_dir($this->data['path_files'])) {
			$handle = opendir($this->data['path_files']);
			$count = 0;
			while (false !== ($filename = readdir($handle))) {
				if( $filename == '.' || $filename == '..') continue;

				$fp = fopen($this->data['path_files'] . $filename,'r');
				while (($data = fgetcsv($fp, ",")) !== FALSE) {
					$this->loggerSetFieldFromCsv($data);
					$this->loggerSave();
				}
				fclose($fp);
				if(!file_exists($this->data['processed_files'])) {
					@mkdir($this->data['processed_files'],0775);
				}
				@rename($this->data['path_files'] . $filename, $this->data['processed_files'] . $filename);
				$count++;
			}
			return $count;
		}
		return false;
	}

	public function loggerLoadS3Logs() {
		if(!@file_exists(sys_get_temp_dir() . '/' . 'logs/')) {
			@mkdir(sys_get_temp_dir() . '/' . 'logs/',0775);
		}
		$filename = sys_get_temp_dir() . '/' . 'logs/' . 'log.csv';
		$ret = array();
		# taking log files from the s3 logs folder
		try {
			$logArray = $this->data['obj']->get_object_list($this->data['s3']['bucket'], array('prefix' => $this->data['s3']['path']['logs']));
		} catch (Exception $e) {
			trigger_error($e->getMessage(),E_USER_ERROR);
		}
		if(count($logArray) && is_array($logArray)) {
			foreach($logArray as $log) {
				$fp = fopen($filename,'w+');
				$res = $this->data['obj']->get_object( $this->data['s3']['bucket'], $log, array('fileDownload' => $fp));
				fclose($fp);
				$fp = fopen($filename,'r');
				while (($data = fgetcsv($fp, ",")) !== FALSE) {
					$this->setFieldFromCsv($data);
					$this->loggerSave();
				}
				fclose($fp);
				clearstatcache();
				$count++;

				#uploading to s3 processed logs directory and deleting from logs directory
				$this->data['obj']->create_object( $this->data['s3']['bucket'], $this->data['s3']['path']['processedLogs'] . @basename($log), array('fileUpload' => $filename, 'acl' => AmazonS3::ACL_PUBLIC));
				$this->data['obj']->delete_object($this->data['s3']['bucket'], $log);
			}
			@unlink($filename);
			return $count;
		}
		return false;
	}

	public function loggerSave() {
		if(!$this->recordExists()) {
			$query = sprintf("INSERT INTO `masterLog` (`scId`, `logId`, `stationId`, `image_id`, `barcode`,  `before`, `after`, task, timestampModified, `user`) VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');", mysql_escape_string( $this->loggerGetProperty('scId') ), mysql_escape_string( $this->loggerGetProperty('logId') ), mysql_escape_string( $this->loggerGetProperty('stationId') ), mysql_escape_string( $this->loggerGetProperty('image_id') ), mysql_escape_string( $this->loggerGetProperty('barcode') ), mysql_escape_string( $this->loggerGetProperty('before') ), mysql_escape_string( $this->loggerGetProperty('after') ), mysql_escape_string( $this->loggerGetProperty('task') ), mysql_escape_string( $this->loggerGetProperty('timestampModified')), mysql_escape_string( $this->loggerGetProperty('user') ) );

			if( $this->db->query($query) ) {
				return( true );
			}
		}
		return( false );
	}

	public function recordExists() {
		$query = sprintf("SELECT `masterLogId` FROM `masterLog` WHERE `scId` = '%s' AND `logId` = '%s' AND `stationId` = '%s' ;", mysql_escape_string( $this->loggerGetProperty('scId') ), mysql_escape_string( $this->loggerGetProperty('logId') ), mysql_escape_string( $this->loggerGetProperty('stationId') ) );
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}

	public function loggerLoadCollectionReport() {
		$op = array();
		switch($this->data['report_type']) {
			case 'day':
				$group_term = ' HOUR(timestampModified) ';
				break;
			case 'week':
				$group_term = ' DAYNAME(timestampModified) ';
				break;
			case 'month':
				$group_term = ' DAYOFMONTH(timestampModified) ';
				break;
			case 'year':
				$group_term = ' MONTHNAME(timestampModified) ';
				break;
		}

		$query = "SELECT count(*) ct, $group_term as dt FROM `masterLog` WHERE `task` = 'IMAGE_PHOTOGRAPHED' AND user != 0 ";

		if($this->data['date'] != '' && $this->data['date2'] !='') {
			$query .= sprintf(" AND ( date( `timestampModified` ) BETWEEN '%s' AND '%s' ) ", mysql_escape_string($this->data['date']), mysql_escape_string($this->data['date2']));
		} else if($this->data['date'] != '' && $this->data['date2'] == '') {
			$query .= sprintf(" AND date( `timestampModified` ) = '%s' ", mysql_escape_string($this->data['date']));
		}


		if($this->data['year'] != '') {
			$query .= sprintf(" AND YEAR(timestampModified) = '%s' ", mysql_escape_string($this->data['year']));
		}

		if($this->data['month'] != '') {
			$query .= sprintf(" AND MONTH(timestampModified) = '%s' ", mysql_escape_string($this->data['month']));
		}

		if($this->data['collectionId'] != '') {
			$query1 = sprintf(" SELECT `code` FROM `collection` WHERE `collectionId` = '%s' ", mysql_escape_string($this->data['collectionId']));
			$rt = $this->db->query_one($query1);
			$code = $rt->code;

			$query .= sprintf(" AND `barcode` LIKE '%s%%' ", mysql_escape_string($code));

		}

		$query .= " GROUP BY  $group_term ";

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
		// $query = 'SELECT * FROM `log`';
	}

	public function loggerLoadReportByDateRange () {
		$query = "SELECT count(*) ct, date(`timestampModified`) as dt FROM `masterLog` WHERE `task` = 'IMAGE_PHOTOGRAPHED' ";

		if($this->data['date'] != '' && $this->data['date2'] != '') {
			$query .= sprintf(" AND ( date( `timestampModified` ) BETWEEN '%s' AND '%s' ) ", mysql_escape_string($this->data['date']), mysql_escape_string($this->data['date2']));
		}

		if(is_array($this->data['users']) &&  count($this->data['users'])) {
			$user_list = @implode(',', $this->data['users']);
			$query .= sprintf(" AND `user` IN (%s) ", mysql_escape_string($user_list));
		}

		if($this->data['station'] != '') {
			$this->data['station'] = json_decode( $this->data['station'] );
			if(count($this->data['station'])) {
				$user_list = @implode(',', $this->data['station']);
				$query .= sprintf(" AND `stationId` IN (%s) ", mysql_escape_string($user_list));
			}
		}

		if($this->data['userId'] != '') {
			$query .= sprintf(" AND user = '%s' ", mysql_escape_string($this->data['userId']));
		}
		if($this->data['sc'] != '') {
			$query .= sprintf(" AND `scId` = '%s' ", mysql_escape_string($this->data['sc']));
		}

		$query .= " GROUP BY  date(`timestampModified`) ";

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

	public function loggerLoadReportByDate () {

		$op = array();
		for($i=1;$i<=24;$i++) {
			$result[$i] = 0;
		}

		$query = sprintf( "SELECT count(*) ct, HOUR (`timestampModified`) as dt FROM `masterLog` WHERE `task` = 'IMAGE_PHOTOGRAPHED' AND ( date( `timestampModified` ) = '%s' ) ", mysql_escape_string($this->data['date']) );
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
				$query .= sprintf(" AND `stationId` IN (%s) ", mysql_escape_string($station_list));
			}
		}

		if($this->data['sc'] != '') {
			$this->data['sc'] = json_decode( $this->data['sc'] );
			if(count($this->data['sc'])) {
				$sc_list = @implode(',', $this->data['sc']);
				$query .= sprintf(" AND `scId` IN (%s) ", mysql_escape_string($sc_list));
			}
		}

		$query .= " GROUP BY  HOUR (`timestampModified`) ";
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

	public function loggerLoadGraphReportUsers () {
		$op = array();
		switch($this->data['report_type']) {
			case 'day':
				$group_term = ' HOUR(timestampModified) ';
				break;
			case 'week':
				$group_term = ' DAYNAME(timestampModified) ';
				break;
			case 'month':
				$group_term = ' DAYOFMONTH(timestampModified) ';
				break;
			case 'year':
				$group_term = ' MONTHNAME(timestampModified) ';
				break;
		}

		$query = "SELECT count(*) ct, $group_term as dt FROM `masterLog` WHERE `task` = 'IMAGE_PHOTOGRAPHED' AND user != 0 ";

		if($this->data['date'] != '' && $this->data['date2'] !='') {
			$query .= sprintf(" AND ( date( `timestampModified` ) BETWEEN '%s' AND '%s' ) ", mysql_escape_string($this->data['date']), mysql_escape_string($this->data['date2']));
		} else if($this->data['date'] != '' && $this->data['date2'] == '') {
			$query .= sprintf(" AND date( `timestampModified` ) = '%s' ", mysql_escape_string($this->data['date']));
		}

		if(count($this->data['users']) && is_array($this->data['users'])) {
			$user_list = @implode(',', $this->data['users']);
			$query .= sprintf(" AND `user` IN (%s) ", mysql_escape_string($user_list));
		}


		if($this->data['year'] != '') {
			$query .= sprintf(" AND YEAR(timestampModified) = '%s' ", mysql_escape_string($this->data['year']));
		}

		if($this->data['month'] != '') {
			$query .= sprintf(" AND MONTH(timestampModified) = '%s' ", mysql_escape_string($this->data['month']));
		}

		if($this->data['userId'] != '') {
			$query .= sprintf(" AND user = '%s' ", mysql_escape_string($this->data['userId']));
		}

		$query .= " GROUP BY  $group_term ";

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

	public function loggerLoadGraphReportStations () {
		$query = sprintf( "SELECT count(*) ct, date(`timestampModified`) as dt, stationId FROM `masterLog` WHERE `task` = 'IMAGE_PHOTOGRAPHED' AND ( date( `timestampModified` ) BETWEEN '%s' AND '%s' ) AND stationId != 0 ", mysql_escape_string($this->data['date']), mysql_escape_string($this->data['date2']) );

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
				$query .= sprintf(" AND `stationId` IN (%s) ", mysql_escape_string($user_list));
			}
		}

		$query .= " GROUP BY  date(`timestampModified`), stationId ";

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
				$arr[$record->stationId] = $record->ct;
			}
			$op[] = $arr;
		}
		$this->records = $op;
		return true;
	}

	public function loggerLoadGraphReportSc () {
		$query = sprintf( "SELECT count(*) ct, date(`timestampModified`) as dt, scId FROM `masterLog` WHERE `task` = 'IMAGE_PHOTOGRAPHED' AND ( date( `timestampModified` ) BETWEEN '%s' AND '%s' ) AND stationId != 0 ", mysql_escape_string($this->data['date']), mysql_escape_string($this->data['date2']) );

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
				$query .= sprintf(" AND `stationId` IN (%s) ", mysql_escape_string($user_list));
			}
		}

		if($this->data['sc'] != '') {
			$this->data['sc'] = json_decode( $this->data['sc'] );
			if(count($this->data['sc'])) {
				$sc_list = @implode(',', $this->data['sc']);
				$query .= sprintf(" AND `scId` IN (%s) ", mysql_escape_string($sc_list));
			}
		}

		$query .= " GROUP BY  date(`timestampModified`), scId ";

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
				$arr[$record->stationId] = $record->ct;
			}
			$op[] = $arr;
		}
		$this->records = $op;
		return true;
	}

	public function loggerGetImageStorageStats() {
		global $config;
		$total_size = 0;
		$dir_iterator = new RecursiveDirectoryIterator($config['path']['images']);
		$images_array = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
		$data['total'] = count($images_array);
		if( $data['total'] ) {
			foreach($images_array as $image) {
				$total_size += @filesize($image);
			}
		}
		$allowed_images = round(($config['disk_size'] - $total_size) / $config['image_size']);
		$data['allowed_images'] = $allowed_images;
		return $data;

	}

	public function loggerGetTotalImagesCount() {
		$query = "SELECT count(*) ct FROM `masterLog` WHERE `task` = 'IMAGE_PHOTOGRAPHED'";
		$records = $this->db->query_one($query);
		return $records->ct;
	}

	public function loggerGetTemplateData($report_type = 'day', $extra_param = '') {
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


	public function loggerGetStationUsers() {

		$query = 'SELECT DISTINCT stationId, user FROM `masterLog` WHERE stationId !=0 AND user !=0 ';
		if($this->loggerGetProperty('stationId') != '' && $this->loggerGetProperty('stationId') != 0 && $this->loggerGetProperty('stationId') !== false) {
			$query .= sprintf(" AND `stationId` = %s ", $this->loggerGetProperty('stationId'));
		}
		$query .= 'ORDER BY stationId, user';
// echo $query;
		$opArray = array();
		$Ret = $this->db->query($query);
		if(is_object($Ret)) {
			$tmp = '';
			$childArray = array();
			$loopFlag = false;
			while ($record = $Ret->fetch_object()) {
				if($tmp != $record->stationId) {
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
					$tmp = $record->stationId;
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