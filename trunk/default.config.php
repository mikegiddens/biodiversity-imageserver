<?php

	$config['version'] = '1.0.0';
	$config['copyright'] = 'Copyright (&copy;) SilverBiology.com';

	# DB details
	$config['mysql']['host'] = 'localhost';
	$config['mysql']['name'] = '';
	$config['mysql']['user'] = '';
	$config['mysql']['pass'] = '';
	
	# Flicker values
	$config['flkr']['enabled'] = true; // true/false
	$config['flkr']['key'] = '';
	$config['flkr']['secret'] = '';
	$config['flkr']['email'] = '';

	# Picassa values
	$config['picassa']['lib_path'] = '/var/www/api/classes/ZendGdata/library';
	$config['picassa']['email'] = '';
	$config['picassa']['pass'] = '';
	$config['picassa']['album'] = '';

	# Site values
	$config['base_url'] = 'http://{yourdomainname for image server}.com';
	$config['path']['base'] = '{server/path/to/website}';
	$config['path']['incoming'] = '/www/incoming/';
	$config['path']['images'] = '/var/www/images/specimensheets/';
	$config['path']['files'] = '/var/www/logs/';
	$config['path']['processed_files'] = '/var/www/logs_processed/';
	$config['path']['error'] = '';
	$config['path']['imgTiles'] = '{server/path/to/website}/imgTiles/';
	$config['path']['tiles'] = '{server/path/to/website}/tiles/';
	$config['path']['tmp'] = '';
	$config['path']['metadatapackages'] = $config['path']['base'] . 'resources/metadatapackages/';

	$config['image_sequence_cache'] = '/var/www/gui/api/imageSequenceCache.txt';
	$config['storageCache'] = '/var/www/gui/api/space.json'; # to keep the storage information

	$config['disk_size'] = 10000000000; // total size on disk in bytes
	$config['image_size'] = 100000; // total size on disk
	$config['allowed_storage'] = 10; // Percentage of allowed storage capacity in the images folder

	$config['image_processing'] = 1; # 1: ImageMagik, 2: GD
	$config['image_mode'] = 'ftp'; # images existing in s3 or ftp

	$config['doc_root'] = '/var/www/';

	$config['google_key'] = '';
	$config['title'] = '';
	$config['mode'] = 'local'; # local or s3
	$config['email']['to'] = '';
	$config['reportsavail'] = true;

	# Amazon S3 details
	$config['s3']['accessKey'] = '';
	$config['s3']['secretKey'] = '';
	$config['s3']['bucket'] = '';
	$config['s3']['url'] = '';
	$config['s3']['path']['logs'] = 'logs/';
	$config['s3']['path']['processedLogs'] = 'processedLogs/';

	$config['hsUrl'] = 'http://eria.helpingscience.org/silverarchive_engine/silverarchive.php';
	$config['evernoteUrl'] = 'http://eria.helpingscience.org/evernote_engine/evernote.php';

	$path = $config['path']['base'] . 'resources/api/classes/';
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);

	$config['ratioDetect'] = false;
	$config['tesseractEnabled'] = false;
	$config['zBarImgEnabled'] = true;

	$config['boxDetectPath'] = '';
	$config['tesseractPath'] = '/usr/local/bin/tesseract';
	$config['zBarImgPath'] = '/usr/local/bin/zbarimg';

	$config['tileGenerator'] = 'http://{url-to-project}/silvertiles/api.php';
	$config['tileUrl'] = 'http://{url-to-project}/tiles/';
	
	# RSS Feed details
	$config['rssFeed']['title'] = 'Image Server Dev';
	$config['rssFeed']['webUrl'] = 'http://{WRONG!!!!!}/trt/';
	
?>