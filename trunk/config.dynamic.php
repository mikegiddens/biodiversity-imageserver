<?php
session_start();

date_default_timezone_set("Europe/Amsterdam");

$config['configPath'] = __DIR__ . '/projects/';
$config['configWebPath'] = 'http://{pathtobis}/projects/';
$projectArray = json_decode(trim(file_get_contents($config['configPath'] . 'projects.json')), true);

$path_parts = pathinfo($_SERVER['SCRIPT_NAME']);
$projId = strtolower($path_parts['dirname']);
$projCheck = $_SERVER['SERVER_NAME'] . $projId . '/';
$projCheckArray = array();

if (PHP_SAPI === 'cli') {
} else {
	$projectId = '';

	if (isset($_SERVER["REDIRECT_URL"]) && $_SERVER["REDIRECT_URL"] != '') {
		$projCheck = $_SERVER['SERVER_NAME'] . $_SERVER["REDIRECT_URL"];
	}

	if(is_array($projectArray) && count($projectArray)) {
		foreach($projectArray as $project) {
			foreach($project['servers'] as $server) {
				if(stristr($projCheck,$server) !== false) {
					$projectId = $project['projectId'];
					break;
				}
			}
			if($projectId != '') break;
			$projCheckArray[] = $project['projectId'];
		}
	}
	
	if($projectId == '') {
		# if domain checking failed
		$projectId = @strtolower(trim($_REQUEST['projectId']));
		if($projectId == '') {
			$projectId = @strtolower(trim($_REQUEST['projectid']));
		}
		# if not use session value
		if($projectId == '') {
			$projectId = $_SESSION['projectId'];
		}
	
		$projectId = in_array($projectId,$projCheckArray) ? $projectId : '';
	}
}
include($config['configPath'] . $projectId . '/config.php');
?>