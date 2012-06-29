<?php

require_once('phpBIS.php');

$time_start = microtime(true);
$sdk = new phpBIS('{yourkey}', 'http://bis.silverbiology.com/dev/resources/api');

$result = $sdk->addSet('Leaf', 'Leaf');
$sdk->addSetValue($result['setID'], 43, null);

$result = $sdk->addSet('Lower surface', 'Lower surface');
$sdk->addSetValue($result['setID'], 44, null);

$result = $sdk->addSet('Acorn', 'Acorn');
$sdk->addSetValue($result['setID'], 45, null);

$result = $sdk->addSet('Twigs', 'Twigs');
$sdk->addSetValue($result['setID'], 46, null);

$result = $sdk->addSet('Bark', 'Bark');
$sdk->addSetValue($result['setID'], 47, null);

header('Content-type: application/json');
print(json_encode(array("success" => true, "process_time" => microtime(true) - $time_start)));

?>