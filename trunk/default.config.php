<?php

	$mysql_host = "localhost";
	$mysql_name = "";
	$mysql_user = "";
	$mysql_pass = "";
	
	# For user module
	define("DB_SERVER", "localhost");
	define("DB_NAME", "");
	define ("DB_USER", "");
	define ("DB_PASSWORD", "");
	
	# Flicker constants
	
	define('FLKR_KEY', '');
	define('FLKR_SECRET', '');
	define('FLKR_EMAIL', '');

	# Picassa constants

	define('PICASSA_LIB_PATH', '/var/www/api/classes/ZendGdata/library');
	define('PICASSA_EMAIL', '');
	define('PICASSA_PASS', '');
	define('PICASSA_ALBUM', '');

	# Site Constants
	define('BASE_URL', 'http://{yourdomainname for image server}.com');
	define('BASE_PATH', '{server/path/to/website}');
	define("PATH_INCOMING", "/www/incoming/");
	define("PATH_IMAGES", "/var/www/images/specimensheets/");
	define("PATH_FILES", "/var/www/logs/");
	define("PROCESSED_FILES", "/var/www/logs_processed/");

	define('IMAGE_SEQUENCE_CACHE', '/var/www/gui/api/imageSequenceCache.txt');
	
	define("DISK_SIZE",10000000000); // total size on disk in bytes
	define("IMAGE_SIZE",100000); // total size on disk
	define("ALLOWED_STORAGE",10); // Percentage of allowed storage capacity in the images folder

// 	define('DOC_ROOT', $_SERVER[DOCUMENT_ROOT] . '/');
	define('DOC_ROOT', '/var/www/');

// 	define("BASE_PATH", $_SERVER[DOCUMENT_ROOT] . "/");
?>