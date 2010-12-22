<?php

/**
 * Wrapper class Amazon S3 class. Communictes with the amazon server
 */

if (!class_exists('S3')) require_once 'S3.php';

// Check for CURL
if (!extension_loaded('curl') && !@dl(PHP_SHLIB_SUFFIX == 'so' ? 'curl.so' : 'php_curl.dll'))
	exit("\nERROR: CURL extension not loaded\n\n");


Class Amazon extends S3 {

	public $amazonData;

	public function __construct ($amazonArray) {

		$this->amazonData = $amazonArray;
		parent::__construct($amazonArray['accessKey'],$amazonArray['secretKey']);
	}

	public function getBuckets() {
		return $this->listBuckets();
	}

	public function getDefaultBucket() {
		$ar = $this->listBuckets();
		return $ar[0];
	}

	public function getBucketInfo($bucketName,$clientName = '') {
		$output = array();
		$info = $this->getBucket($bucketName);
		if(is_array($info) && count($info)) {
			foreach($info as $key => $ar) {
				if( strpos($key,'$folder$') !== false ) continue;
				$tmp_array = explode('/', $key);
				if($clientName != '') {
					if($tmp_array[0] == $clientName){
						$output[] = $key;
					}
				} else {
					$output[] = $key;
				}
			}
		}
		return $output;
	}

	public function putBucketFile($image, $bucketName){
		$rr = $this->putObjectFile($image, $bucketName, baseName($image), S3::ACL_PUBLIC_READ);
	}

	public function getBucketFile($uri, $bucketName, $image){
		$fp = fopen($image, "wb");
		$object = $this->getObject($bucketName, $uri, $fp);
		return true;
	}

}

?>