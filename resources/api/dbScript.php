<?php

# DB script test

require_once("../config.php");
require_once("classes/class.master.php");

$expected=array('limit');
foreach ($expected as $formvar)
	$$formvar = (isset(${"_$_SERVER[REQUEST_METHOD]"}[$formvar])) ? ${"_$_SERVER[REQUEST_METHOD]"}[$formvar]:NULL;

$limit = ($limit == '')?5:$limit;
$count = 0;

$si = new SilverImage;
$si->load( $mysql_name );
$query =  sprintf(" SELECT * FROM `image` WHERE `processed` = 1 LIMIT %d ", mysql_escape_string($limit));

$startTime = time();
$Ret = $si->db->query($query);
// print '<pre>';
if (is_object($Ret)){
	while ($Row = $Ret->fetch_object())
	{
		$si->image->load_by_id($Row->image_id);
		$si->image->set('flickr_PlantID',0);
		$si->image->set('picassa_PlantID',0);
		$si->image->set('gTileProcessed',0);
		$si->image->set('zoomEnabled',0);
		$si->image->set('processed',0);
		$si->image->save();
/*print '<br>';
print_r($si->image);*/
	
		$si->pqueue->set('image_id',$si->image->get('barcode'));
		$si->pqueue->set('process_type','all');
		$si->pqueue->save();
		$count++;

	}
} # if object

$time = time() - $startTime;

header('Content-type: application/json');
print( json_encode( array( 'success' => true, 'process_time' => $time, 'total_images' => $count ) ) );

?>