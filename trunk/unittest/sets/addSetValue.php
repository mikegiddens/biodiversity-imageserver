<?php

require_once('phpBIS.php');

$time_start = microtime(true);
$sdk = new phpBIS('{yourkey}', 'http://bis.silverbiology.com/dev/resources/api');

$result = $sdk->addSet('Whole tree (or vine)', 'Whole tree (or vine)');
$sdk->addSetValue($result['setID'], 30, null);
$sdk->addSetValue($result['setID'], 31, null);

$result = $sdk->addSet('Fruit', 'Fruit');
$sdk->addSetValue($result['setID'], 32, null);
$sdk->addSetValue($result['setID'], 33, null);
$sdk->addSetValue($result['setID'], 34, null);

$result = $sdk->addSet('Seed', 'Seed');
$sdk->addSetValue($result['setID'], 35, null);

header('Content-type: application/json');
print(json_encode(array("success" => true, "process_time" => microtime(true) - $time_start)));

?>
