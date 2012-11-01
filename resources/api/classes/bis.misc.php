<?php

    function getSize($dirname) {
        $handle = opendir($dirname);
        if (!$handle) return 0;
        while ($file = readdir($handle)){
            if  ($file == "." || $file == "..") continue;
                if  (is_dir($dirname."/".$file)){
                          $size += getSize($dirname.'/'.$file);
                } else {
                      $size += filesize($dirname."/".$file);
                }
        }
        closedir($handle);
        return $size ;
    }

    function getdirsize($path) {
        $result=explode("\t",exec("du -hs ".$path),2);
        return ($result[1]==$path ? $result[0] : "error");
    }

    function decodeSize( $bytes )
    {
        $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
        for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
        return( round( $bytes, 2 ) . " " . $types[$i] );
    }

/**
 *
 */
function build_where( $filter ) {

	static $cond = array();
	
	if(count($filter)){
		foreach($filter as $type => $items ){
		
//			print "..." . $type . "<br>";
			switch ($type) {
				case 'items':
					if(count($items)){
						$first = true;
						foreach($items as $item) {
							if (!$first) {							
								$tmp = array_pop( $cond );
								$condition .= ' ' . $tmp . ' ';
								array_push($cond, $tmp);
							} else {
								$first = false;
							}
							$condition .= "( " . $item[field] . getCondition( $item[condition], mysql_escape_string($item[value]) ) . ")";
//							print "..." . $condition . "<br>";
						}
					}					
					
					array_pop( $cond );		
					return( '( ' . $condition . ' )' );
					break;
					
				case 'condition':
					array_push($cond, $items);
					break;
					
				case 'group':
					
					if(count($items)){
						$first = true;
						foreach($items as $item) {
							if (!$first) {
								$tmp = array_pop( $cond );
								$str .= ' ' . $tmp . ' ';
							} else {
								$first = false;
							}
							$str .= build_where($item);
						}
					}

					return( '( ' . $str . ' )' );					
					break;
			}
		}
	}	
}

/**
 *
 */
function build_order( $tokens, $numericFields = array() ) {

	if(count($tokens) && is_array($tokens)){
		$qq = array();
		
		if(count($numericFields)) {
			foreach($tokens as $token) {
				$qq[] = (in_array($token['field'],$numericFields)) ? $token['field'] . " " . $token['dir'] : ' LOWER(' . $token['field'] . ') ' . ' ' . $token['dir'];
			}
		} else {
			foreach($tokens as $token) {
				$qq[] =  ' LOWER(' . $token['field'] . ') ' . " " . $token['dir'];
			}
		}

		return (' ORDER BY ' . implode(", ", $qq) );
	}
	return '';
}

/**
 *
 */
function build_limit( $start = '', $range = '' ) {
	$str = " LIMIT ";
	if ($range != '') {
		if ($start !== '' && $start != NULL) {
			$str .= $start . ', ';
		}
		return ($str . $range);
	}
	
}

function getCondition($condition, $value){
	$str = "";
	switch($condition){
		case 'LIKE_RIGHT' : 
			$str = " LIKE '{$value}%' ";
			break;
		case 'LIKE_LEFT' : 
			$str = " LIKE '%{$value}' ";
			break;
		case 'LIKE_BOTH' : 
			$str = " LIKE '%{$value}%' ";
			break;
		case 'EQUAL' : 
			$str = " = '{$value}' ";
			break;
		case 'NOT_EQUAL' : 
			$str = " != '{$value}'";
			break;
		case 'IN' : 
			$str = " IN ({$value}) ";
			break;
		DEFAULT :
			$str = false;
	}
	return $str;
}


// eg:-  [{"data":{"type":"numeric","value":101,"comparison":"eq"},"field":"specimen_sheet_id"},{"data":{"type":"numeric","value":11,"comparison":"eq"},"field":"label_id"}]

function buildWhere($filter = '') {
	$where = " 0 = 0 ";
	if (is_array($filter)) {
	for ($i=0;$i < count($filter);$i++){
		switch($filter[$i]['data']['type']){
		case 'string' : $qs .= " AND ".$filter[$i]['field']." LIKE '%".$filter[$i]['data']['value']."%'";
			Break;
		case 'list'   :
			if (strstr($filter[$i]['data']['value'],',')){
			$fi = explode(',',$filter[$i]['data']['value']);
			for ($q=0;$q<count($fi);$q++){
				$fi[$q] = "'".$fi[$q]."'";
			}
			$filter[$i]['data']['value'] = implode(',',$fi);
			$qs .= " AND ".$filter[$i]['field']." IN (".$filter[$i]['data']['value'].")";
			}else{
			$qs .= " AND ".$filter[$i]['field']." = '".$filter[$i]['data']['value']."'";
			}
			Break;
		case 'boolean' : $qs .= " AND ".$filter[$i]['field']." = ".($filter[$i]['data']['value']);
			Break;
		case 'numeric' :
			switch ($filter[$i]['data']['comparison']) {
			case 'eq' : $qs .= " AND ".$filter[$i]['field']." = ".$filter[$i]['data']['value'];
				Break;
			case 'lt' : $qs .= " AND ".$filter[$i]['field']." < ".$filter[$i]['data']['value'];
				Break;
			case 'gt' : $qs .= " AND ".$filter[$i]['field']." > ".$filter[$i]['data']['value'];
				Break;
			}
			Break;
		case 'date' :
			switch ($filter[$i]['data']['comparison']) {
			case 'eq' : $qs .= " AND ".$filter[$i]['field']." = '".date('Y-m-d',strtotime($filter[$i]['data']['value']))."'";
				Break;
			case 'lt' : $qs .= " AND ".$filter[$i]['field']." < '".date('Y-m-d',strtotime($filter[$i]['data']['value']))."'";
				Break;
			case 'gt' : $qs .= " AND ".$filter[$i]['field']." > '".date('Y-m-d',strtotime($filter[$i]['data']['value']))."'";
				Break;
			}
			Break;
		}
	}
	$where .= $qs;
	}
        return $where;
}

/**
 * Splits the prefix part and the number part of a given barcode
 * @param string $barcode
 * @return mixed
 */
function getBarcodePrefix($barcode) {
	$ar = str_split($barcode);
	$output = array();
	$bar = '';
	if(count($ar) && is_array($ar)) {
		foreach($ar as $a) {
			if(!is_numeric($a)) {
				$bar .= array_shift($ar);
			} else {
				$output['prefix'] = $bar;
				$output['tail'] = implode($ar);
			}
		}
	}
	$output['prefix'] = $bar;
	return $output;
}

/*
function CURL($url, $post = null, $retries = 3) {
        $curl = curl_init($url);

        if (is_resource($curl) === true) {
                curl_setopt($curl, CURLOPT_FAILONERROR, true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

                if (isset($post) === true) {
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, (is_array($post) === true) ? http_build_query($post, '', '&') : $post);
                }

                $result = false;
                while (($result === false) && (--$retries > 0)){
                        $result = curl_exec($curl);
                }
                curl_close($curl);
        }

        return $result;
}
*/
?>