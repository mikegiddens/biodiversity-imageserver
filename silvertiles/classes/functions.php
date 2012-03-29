<?php
function barcode_path( $barcode ) {
	$id = $barcode;
	if ((strlen($id))>8){
		$loop_flag = true;$i = 0;
		while($loop_flag){
			if(substr($barcode,$i) * 1) {
				$loop_flag = false;
			} else {
				$i++;
			}
			if($i>8) $loop_flag = false;
		}
		$prefix = strtolower(substr($id, 0, $i));
		$id= substr($id, $i);
	} else {
		$this->prefix="";
	}
	$destPath  = $prefix . "/";
	$destPath .= (int) ($id / 1000000) . "/";
	$destPath .= (int) ( ($id % 1000000) / 10000) . "/";
	$destPath .= (int) ( ($id % 10000) / 100) . "/";
	$destPath .= (int) ( $id % 100 ) . "/";
	return( $destPath );
}
?>