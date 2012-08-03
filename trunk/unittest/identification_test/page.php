<?php
error_reporting(E_ALL ^ E_NOTICE);
$baseUrl = 'http://bis.silverbiology.com/dev/resources/api/api.php?';

if($_REQUEST['id']=='') {
	die('Invalid Id');
}
if($_POST['Go'] == 'Go') {
	if($_POST['sciname'] != '') {
		$categoryID = 46;
		$identifiedCategoryID = 59;
		$unIdentifiedValueId = 62;
		$identifiedValueId = 63;
		$scinames = array();
		$rets = json_decode(file_get_contents($baseUrl . "cmd=list_attributes&categoryID=$categoryID"));
		if(is_array($rets->data) && count($rets->data)) {
			foreach($rets->data as $data){
				$scinames[$data->valueID] = $data->name;
			}
		}
		$valueID = array_search($_POST['sciname'],$scinames);
		
		if(false === $valueID) {
			$url = $baseUrl . "cmd=add_attribute&categoryID=$categoryID&name={$_POST['sciname']}";
			$ret = json_decode(file_get_contents($url));
			$valueID = ($ret->success) ? $ret->new_id : '';
		}
		$url = $baseUrl . "cmd=add_image_attribute&imageID={$_POST['id']}&categoryID=$categoryID&valueID=$valueID";
		$ret = json_decode(file_get_contents($url));
		if($ret->success) {
			$ret = json_decode(file_get_contents($baseUrl . "cmd=delete_image_attribute&imageID={$_POST['id']}&valueID=$unIdentifiedValueId"));
			$url = $baseUrl . "cmd=add_image_attribute&imageID={$_POST['id']}&categoryID=$identifiedCategoryID&valueID=$identifiedValueId";
			$ret = json_decode(file_get_contents($url));
		}
		
		
		header('Content-type: application/json');
		echo json_encode($ret);
	}
} else {
?>
<html>
<head><title>Page</title></head>
<body>
<form method='POST'>
<label> Enter Scientific Name: </label><input type='text' id='sciname' name='sciname'><input type='hidden' id='id' name='id' value='<?php echo $_REQUEST['id'];?>'>
<br>
<input type='submit' id='Go' name='Go' value='Go'>
</form>
</body>
</html>
<?php
}
?>