<?php
	ini_set('display_errors', 1);
	error_reporting(E_ALL ^ E_NOTICE);
	
	// include('config.php');
	
		// or
	
	$config['mysql']['host'] = 'localhost';
	$config['mysql']['name'] = 'silverimage';
	$config['mysql']['user'] = 'root';
	$config['mysql']['pass'] = '';
	
	$config['port']['sqlPath'] = 'G:\\wamp\\www\\test\\sqlfiles\\portSI.sql';
	$config['port']['usersSqlPath'] = 'G:\\wamp\\www\\test\\sqlfiles\\portUsersSI.sql';
	
	
	
	$link = mysql_connect($config['mysql']['host'], $config['mysql']['user'], $config['mysql']['pass']);
	if (!$link) {
		die('Not connected : ' . mysql_error());
	}
	$db_selected = mysql_select_db($config['mysql']['name'], $link);
	if (!$db_selected) {
		die ("Can\'t use {$config['mysql']['name']} : " . mysql_error());
	}

	$userTable = '';
	
	$query = " SHOW TABLES FROM {$config['mysql']['name']} ";
	$tables = array();
	$result	= mysql_query($query);
	while($row = mysql_fetch_array($result)) {
		('users' != $row[0]) ? $tables[] = $row[0] : $userTable = $row[0];
	}
	
	# Tables
	if(file_exists($config['port']['sqlPath'])){
		unlink($config['port']['sqlPath']);
	}
	mkdirRecursive(dirname($config['port']['sqlPath']));
	if(false !== $fp = fopen($config['port']['sqlPath'],'a')) {
		if(count($tables)) {
			foreach($tables as $table) {
				$columns = array();
				$cols = array();
				$query = " SHOW columns FROM {$table} ";
				$result1 = mysql_query($query);
				while($row = mysql_fetch_row($result1)) {
					$columns[] = array('value' => $row[0], 'type' => $row[1]);
					$cols[] = $row[0];
				}
				$tmpArray = array();
				$count = 0;
				$unionTerm = '';
				$dataFlag = false;
				$insertQuery = ' INSERT INTO ' . $table . ' ( "' . implode('","',$cols) . '" ) ';
				// $result = mysql_query(" SELECT * FROM {$table} LIMIT 5 ");
				$result = mysql_query(" SELECT * FROM {$table} ");
				while($row = mysql_fetch_row($result)) {
					$dataFlag = true;
					$count++;
					$tmpAr = array();
					if(is_array($row) && count($row)) {
						for($i = 0; $i < count($row); $i++) {
							$tmpAr[] = (isStringType($columns[$i]['type'])) ? "'{$row[$i]}'" : "{$row[$i]}" ;
						}
					}
					$tmpArray[] = ' SELECT ' . implode(',',$tmpAr);
					if($count > 10000) {
						$insertQuery = $insertQuery . ' ' . $unionTerm . implode(' UNION ',$tmpArray);
						fwrite($fp,$insertQuery);
						$count = 0;
						$insertQuery = '';
						$unionTerm = ' UNION ';
						$tmpArray = array();
					}
				}
				if(count($tmpArray)) {
					$insertQuery = $insertQuery . ' ' . $unionTerm . implode(' UNION ',$tmpArray);
					fwrite($fp,$insertQuery);
				}
				if($dataFlag) fwrite($fp,';');
			}
		}
		fclose($fp);
	}
	
	# Users table
	if(file_exists($config['port']['usersSqlPath'])){
		unlink($config['port']['usersSqlPath']);
	}
	mkdirRecursive(dirname($config['port']['usersSqlPath']));
	if(false !== $fp = fopen($config['port']['usersSqlPath'],'a')) {
		$columns = array();
		$cols = array();
		$query = " SHOW columns FROM `users` ";
		$result1 = mysql_query($query);
		while($row = mysql_fetch_row($result1)) {
			$columns[] = array('value' => $row[0], 'type' => $row[1]);
			$cols[] = $row[0];
		}
		$tmpArray = array();
		$count = 0;
		$unionTerm = '';
		$dataFlag = false;
		$insertQuery = ' INSERT INTO users ( "' . implode('","',$cols) . '" ) ';
		$result = mysql_query(" SELECT * FROM users ");
		while($row = mysql_fetch_row($result)) {
			$dataFlag = true;
			$count++;
			$tmpAr = array();
			if(is_array($row) && count($row)) {
				for($i = 0; $i < count($row); $i++) {
					$tmpAr[] = (isStringType($columns[$i]['type'])) ? "'{$row[$i]}'" : "{$row[$i]}" ;
				}
			}
			$tmpArray[] = ' SELECT ' . implode(',',$tmpAr);
			if($count > 10000) {
				$insertQuery = $insertQuery . ' ' . $unionTerm . implode(' UNION ',$tmpArray);
				fwrite($fp,$insertQuery);
				$count = 0;
				$insertQuery = '';
				$unionTerm = ' UNION ';
				$tmpArray = array();
			}
		}
		if(count($tmpArray)) {
			$insertQuery = $insertQuery . ' ' . $unionTerm . implode(' UNION ',$tmpArray);
			fwrite($fp,$insertQuery);
		}
		if($dataFlag) fwrite($fp,';');
		fclose($fp);
	}
	
	
	function isStringType($value) {
		$value = strtolower($value);
		if(in_array(substr($value,0,4),array('varc','char','date','enum','blob'))) {
			return true;
		} else {
			return false;
		}
	}	

	function mkdirRecursive( $pathname ) {
		is_dir(dirname($pathname)) || mkdirRecursive(dirname($pathname));
		return is_dir($pathname) || @mkdir($pathname, 0775);
	}
	
echo '<br> SQL Files created.';
echo '<br>';
echo $config['port']['sqlPath'];
echo '<br>';
echo $config['port']['usersSqlPath'];

?>