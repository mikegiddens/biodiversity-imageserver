<?php
include('../../sdk/php/phpBIS.php');
// $config['key'] = '50d1942ed5245';
$config['key'] = '50d3f6a14e875';
$config['server'] = 'http://bis.silverbiology.com/dev/resources/api/';
$config['code'] = 'KU';

// echo '<pre>';

// print_r($_SERVER);exit;

$timeStart = time();

$sdk = new phpBIS($config['key'], $config['server']);

// $ret = $sdk->imageList(array('code' => $config['code']));
// print_r($ret);

$flag = true;
$start = 0;
// $step=5;
$step=500;
while($flag) {
	$params = array('code' => $config['code'], 'start' => $start, 'limit' => $step, 'sort' => 'imageId', 'dir' => 'desc');
	$ret = $sdk->imageList($params);
	if(!$ret['success']) {
		$flag = false;
	} else {
		$records = $ret['records'];
		$totalCount = $ret['totalCount'];
		// $totalCount = 7;
		if(is_array($records) && count($records)) {
			foreach($records as $record) {
				if($record['filename'][0] != 'V') continue; # taking only the VZ.. images now (temporary feature)
			
				$count++;
				$name = str_replace('-','_',str_replace('.jpg','',$record['filename']));
				// echo '<br>' . $record['imageId'] . ' - ' . $name;
				$ar = explode('_',$name);
				
				$countryCode = substr($ar[0],0,2);
				$year = ( '20'. substr($ar[0],2,2) ) * 1;
				$month = substr($ar[1],0,2);
				$day = substr($ar[1],2,2);
				$date = $year . '-' . $month . '-' . $day;
				$locality = substr($ar[2],0,2);
				$habitat = (strlen($ar[2]) > 2) ? substr($ar[2],2) : '';
				$general = (false !== stripos($ar[3],'GEN')) ? true : false;
				$extra = str_replace('GEN','',$ar[3]);
				
				$collectingEvent = substr($record['filename'],0,strpos($record['filename'],$ar[2])) . $ar[2];

				// echo '<br> countryCode ' . $countryCode;
				// echo '<br> year ' . $year;
				// echo '<br> date ' . $date;
				// echo '<br> locality ' . $locality;
				// echo '<br> habitat ' . $habitat;
				// echo '<br> general ' . $general;
				// echo '<br> extra ' . $extra;
				
				$rt = $sdk->imageAddAttribute($record['imageId'], 'title', 'countryCode', 'name', $countryCode, true);
				$sdk->imageAddAttribute($record['imageId'], 'title', 'Year', 'name', $year, true);
				$sdk->imageAddAttribute($record['imageId'], 'title', 'EventDate', 'name', $date, true);
				$sdk->imageAddAttribute($record['imageId'], 'title', 'CollectingEvent', 'name', $collectingEvent, true);
			}
		} else {
			$flag = false;
		}
		
		$start += $step;
		if($start > $totalCount) $flag = false;
	}
}

header('Content-type: application/json');
echo json_encode(array('success' => true, 'processTime' => time() - $timeStart, 'totalCount' => $count));


?>