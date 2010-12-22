<?php
echo '<pre>';

$url = 'http://images.cyberfloralouisiana.com/images/specimensheets/';
$sourceUrl = 'http://namefinding.ubio.org/find?';

$barcode = trim($_REQUEST['barcode']);

if($barcode == '') {
	echo ' <br> Barcode should be given !';
	exit;
}

$url = $url . image_path($barcode) . $barcode . '.txt';
$netiParams = array('input' => $url, 'type' => 'url', 'format' => 'json', 'client' => 'neti');
$taxonParams = array('input' => $url, 'type' => 'url', 'format' => 'json', 'client' => 'taxonfinder');
$getUrl = http_build_query($netiParams);
$data = json_decode(@file_get_contents($sourceUrl . $getUrl),true);
if( !(is_array($data['names']) && count($data['names'])) ) {
// echo '<br> Taxon Finder';
	$getUrl = http_build_query($taxonParams);
	$data = json_decode(@file_get_contents($sourceUrl . $getUrl),true);
}

// $data = array(
// 'names' => array(array('scientificName' => 'Asteracae'))
// );
// print_r($data);

$family = '';
$genus = '';
$scientificName = '';

if( is_array($data['names']) && count($data['names']) ) {
	foreach($data['names'] as $dt) {
		# check 1
		$word = $dt['scientificName'];
		$word = preg_replace('/\s+/',' ',trim($word));
		$posFlag = false;
		$word = @strtolower($word);
# 		$pos = @strripos($word,'acae');
		$pos = @strripos($word,'ceae');
	
		if( ( $pos + 4 ) >= (strlen($word)) ) {
			if($family == '') {
				$family = @ucfirst($word);
			}
		} else {
			$posFlag = true;
		}
	
		$wd = explode(' ',$word);
		if(count($wd) == 2) {
			if($scientificName == '') {
				$scientificName = @ucfirst($word);
			}
			if($genus == '') {
				$genus = @ucfirst($wd[0]);
			}
		} else if (count($wd) == 1) {
			if($posFlag) {
				if($genus == '') {
					$genus = @ucfirst($word);
				}
			}
		}
		if($family != '' && $genus != '' && $scientificName != '') {
			break;
		}
	}
echo '<br> Family : ' . $family;
echo '<br> Genus : ' . $genus;
echo '<br> Sci Name : ' . $scientificName;

} else {
	echo 'no results found';
}

function image_path( $image_id ) {
        $id = $image_id;
        if ((strlen($id))>8){
		$loop_flag = true;$i = 0;
		while($loop_flag){
			if(substr($image_id,$i) * 1) {
				$loop_flag = false;
			} else {
				$i++;
			}
			if($i>8) $loop_flag = false;
		}
	        $prefix = strtolower(substr($id, 0, $i));
            	$id= substr($id, $i);
        } else {
            $prefix="";
        }
        $destPath  = $prefix . "/";
        $destPath .= (int) ($id / 1000000) . "/";
        $destPath .= (int) ( ($id % 1000000) / 10000) . "/";
        $destPath .= (int) ( ($id % 10000) / 100) . "/";
        $destPath .= (int) ( $id % 100 ) . "/";
        return( $destPath );
}
?>