<?php
error_reporting(E_ALL ^ E_NOTICE);

$identified = ($_REQUEST['identified'] == 'true') ? true : false;

$baseUrl = 'http://bis.silverbiology.com/dev/resources/api/api.php?';
if($identified) {
	$url = $baseUrl . 'cmd=images&field=CollectionCode&value=ECN&useStatus=true&useRating=true&characters=[{"node_value":63}]&sort=timestamp_modified&dir=desc';
} else {
	$url = $baseUrl . 'cmd=images&field=CollectionCode&value=ECN&useStatus=true&useRating=true&characters=[{"node_value":62}]&sort=timestamp_modified&dir=desc';
}

// echo '<br>';
// echo '<br> Url : ' . $url;

// echo '<br>-------------------------------<br>';

$result = file_get_contents($url);
$result = json_decode($result);
// print_r($result);
?>
<html>
<head><title>ECN Unidentified Images</title>

</head>
<body>
<br>
<br>
<h3>The ECN Collection Unidentified Images</h3>
<br>
<br>
<br>
<?php
if($result->success) {
	if(is_array($result->data)) {
		foreach($result->data as $data){
			if($identified) {
				echo '<div style="width=200px; float: left"><img src="' . $data->path . $data->filename . '" alt="Image"></div>';
			} else {
				echo '<div style="width=200px; float: left"><a href="./page.php?id='.$data->image_id.'"><img src="' . $data->path . $data->filename . '" alt="Image"></a></div>';
			}
		}
	}
}
?>

</body>
</html>