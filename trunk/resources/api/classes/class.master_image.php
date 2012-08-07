<?php
/**
 * @author SilverBiology
 * @website http://www.silverbiology.com
 */

Class Image {

	public $db, $record;

	public function __construct($db = null) {
		$this->db = $db;
	}

	public function set_fullpath( $file ){
		$parts = explode('/', $file);
		if ( count($parts) == 1 ) {
			$parts = explode('\\', $file);
		}
		$filename = $parts[count($parts) - 1];
		unset($parts[count($parts) - 1]);
		$path = implode('/', $parts) . "/";
		$this->set('filename', $filename);
		$this->set('path', $path);
	}

	public function getName( $field = 'name' ) {
		if ($field == 'name' || $field == 'ext') {
			$ext = explode('.', $this->get('filename'));
			return($field == 'name') ? $ext[0] : $ext[1];
		} else {
			return($this->$field);
		}
	}

	/**
	 * Set the value to Data
	 * @param mixed $data : input data
	 * @return bool
	 */
	public function setData($data) {
		$this->data = $data;
		return(true);
	}

	/**
	* Returns all the values in the record
	* @return mixed
	*/
	public function get_all() {
		if (isset($this->record)) {
			return( $this->record );
		} else {
			return(false);
		}
	}

	/**
	* Returns a since field value
	* @return mixed
	*/
	public function get( $field ) {
		if (isset($this->record[$field])) {
			return( $this->record[$field] );
		} else {
			return(false);
		}
	}

	/**
	* Set the value to a field
	* @return bool
	*/
	public function set( $field, $value ) {
		$this->record[$field] = $value;
		return(true);
	}

	public function load_by_barcode( $barcode ) {
		if($barcode == '') return(false);
		$query = sprintf("SELECT * FROM `image` WHERE `barcode` = '%s'", mysql_escape_string($barcode) );
		try {
		$ret = $this->db->query_one( $query );
		} catch (Exception $e) {
			trigger_error($e->getMessage(),E_USER_ERROR);
		}
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->set($field, $value);
			}
			return(true);
		} else {
			return(false);
		}
	}

	public function load_by_id( $image_id ) {
		if($image_id == '') return false;
		$query = sprintf("SELECT * FROM `image` WHERE `image_id` = %s", mysql_escape_string($image_id) );
		try {
		$ret = $this->db->query_one( $query );
		} catch (Exception $e) {
			trigger_error($e->getMessage(),E_USER_ERROR);
		}
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->set($field, $value);
			}
			return(true);
		} else {
			return(false);
		}
	}

	public function moveToImages() {
		global $config;
		$barcode = $this->getName();
		$tmpPath = $config['path']['images'] . $this->barcode_path( $barcode );
		$this->mkdir_recursive( $tmpPath );
		$flsz = @filesize($this->get('path') . $this->get('filename'));
		if(!$flsz) {
			if(!@rename( $this->get('path') . $this->get('filename'), $config['path']['error'] . $this->get('filename') )) {
				return array('success' => false, 'code' => 140);
			}
			return array('success' => false);
		}
		if(@rename( $this->get('path') . $this->get('filename'), $tmpPath . $this->get('filename') )) {
			$this->set('path',$tmpPath);
			return array('success' => true);
		} else {
			return array('success' => false, 'code' => 141);
		}
	}

	public function barcode_path( $barcode ) {
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
			$this->prefix = strtolower(substr($id, 0, $i));
			$id= substr($id, $i);
		} else {
			$this->prefix="";
		}
		$destPath  = $this->prefix . "/";
		$destPath .= (int) ($id / 1000000) . "/";
		$destPath .= (int) ( ($id % 1000000) / 10000) . "/";
		$destPath .= (int) ( ($id % 10000) / 100) . "/";
		$destPath .= (int) ( $id % 100 ) . "/";
		return( $destPath );
	}

	public function mkdir_recursive( $pathname ) {
		is_dir(dirname($pathname)) || $this->mkdir_recursive(dirname($pathname));
		return is_dir($pathname) || @mkdir($pathname, 0775);
	}

	function rmdir_recursive($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") $this->rmdir_recursive($dir."/".$object); else unlink($dir."/".$object);
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}

	function createThumbnail( $tmp_path, $new_width, $new_height, $postfix = '', $display_flag=false ) {
		$extension = '.' . $this->getName('ext');
		$func = 'imagecreatefrom' . (@strtolower($this->getName('ext')) == 'jpg' ? 'jpeg' : @strtolower($this->getName('ext')));
		$im = @$func($tmp_path);
		if($im !== false) {
			$image_file = $this->get("path") . $this->getName() . $postfix . $extension;
			$width = imageSX($im);
			$height = imageSY($im);
			$image_file = ($display_flag)?NULL:$image_file;
			$this->resizeImage($new_width, $new_height, $im, $image_file, $width, $height);
			ImageDestroy($im); // Remove tmp Image Object
		}
	}

	function createThumbnailIMagik( $tmp_path, $new_width, $new_height, $postfix = '' ) {
		$extension = '.' . $this->getName('ext');
		$destination = $this->get("path") . $this->getName() . $postfix . $extension;
		$tmp = sprintf("convert %s -resize %sx%s %s", $tmp_path,$new_width,$new_height,$destination);
		$res = system($tmp);
	}

	function createThumb( $tmp_path, $new_width, $new_height, $postfix = '', $display_flag=false, $type='jpg') {
		global $config;
		$dtls = @pathinfo($tmp_path);
		$extension = '.' . $dtls['extension'];
		$content_type = 'image/' . ($dtls['extension'] == 'jpg' ? 'jpeg' : $dtls['extension']);
		
		if($config['image_processing'] == 1) {
			$destination =  $dtls['dirname'] . '/' . $dtls['filename'] . $postfix . $extension;
#			$tmp = sprintf("convert %s -thumbnail %sx%s %s", $tmp_path,$new_width,$new_height,$destination);
			$tmp = sprintf("convert -limit memory 16MiB -limit map 32MiB %s -thumbnail %sx%s %s", $tmp_path,$new_width,$new_height,$destination);
			$res = exec($tmp);
			$tmp_path = $destination;
			$extension = '.' . $type;
			$content_type = 'image/' . ($type == 'jpg' ? 'jpeg' : $type);
			$destination =  $dtls['dirname'] . '/' . $dtls['filename'] . $postfix . $extension;

			$tmp = sprintf("convert %s %s",$tmp_path,$destination);
			$res = exec($tmp);
			
			if($display_flag) {
				
				$fp = fopen($destination, 'rb');
				header("Content-Type: $content_type");
				header("Content-Length: " . filesize($destination));
				fpassthru($fp);
				fclose($fp);
				unlink($destination);
				exit;
			}
		} else {
			$func = 'imagecreatefrom' . (@strtolower($dtls['extension']) == 'jpg' ? 'jpeg' : @strtolower($dtls['extension']));
			$im = @$func($tmp_path);
			if($im !== false) {
				$image_file = $dtls['dirname'] . $dtls['filename'] . $postfix . $extension;
				$width = imageSX($im);
				$height = imageSY($im);
				$image_file = ($display_flag)?NULL:$image_file;
				$this->resizeImage($new_width, $new_height, $im, $image_file, $width, $height);
				ImageDestroy($im); // Remove tmp Image Object
			}
		}
	}

	/**
	 * Creates the Thumbnails for the image using IM/GD for s3 mode
	 * @param string barcode
	 * @param mixed s3 details and object
	 */
	function createThumbS3($image_id,$arr,$deleteFlag = true) {
		global $config;
		$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';

		if($this->load_by_id($image_id)) {
			$filName = 'Img_' . time();
			$fname = explode(".", $this->get('filename'));
			$tmpThumbPath = $_TMP . $filName . $arr['postfix'] . '.' . $fname[1];
			$thumbName = $this->get('path') .'/'. $fname[0] . $arr['postfix'] . '.' . $fname[1];
			$thumbName = (substr($thumbName,0,1)=='/')? substr($thumbName,1,strlen($thumbName)-1) : $thumbName;
			$tmpPath = $_TMP . $filName . '.' . $fname[1];

			$fp = fopen($tmpPath, "w+b");

			# getting the image from s3
			$bucket = $arr['s3']['bucket'];
			$key = $this->get('path') .'/'. $this->get('filename');
			$key = (substr($key,0,1)=='/')? substr($key,1,strlen($key)-1) : $key;
			$rr = $arr['obj']->get_object($bucket, $key, array('fileDownload' => $tmpPath));

			$this->createThumb($tmpPath, $arr['width'], $arr['height'], $arr['postfix']);
 			
			# uploading thumb to s3
			$response = $arr['obj']->create_object ( $bucket, $thumbName, array('fileUpload' => $tmpThumbPath,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
			
			@unlink($tmpThumbPath);
			if($deleteFlag) {
				@unlink($tmpPath);
				return true;
			}
			return $tmpPath;
		}
		return false;
	}

	function createFromFileS3($tmpPath,$image_id,$arr,$deleteFlag = false) {
		if(!@file_exists($tmpPath)) return false;
		$dtls = @pathinfo($tmpPath);
		$extension = '.' . $dtls['extension'];
		$tmpThumbPath =  $dtls['dirname'] . '/' . $dtls['filename'] . $arr['postfix'] . $extension;
		$fname = explode(".", $this->get('filename'));
		$thumbName = $this->get('path') . '/' . $fname[0] . $arr['postfix'] . '.' . $fname[1];
		$thumbName = (substr($thumbName,0,1)=='/')? substr($thumbName,1,strlen($thumbName)-1) : $thumbName;

		# uploading thumb to s3
		$this->createThumb($tmpPath, $arr['width'], $arr['height'],$arr['postfix']);
		$response = $arr['obj']->create_object ( $arr['s3']['bucket'], $thumbName, array('fileUpload' => $tmpThumbPath,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );

		@unlink($tmpThumbPath);
		if($deleteFlag) {
			@unlink($tmpPath);
			return true;
		}
		return $tmpPath;
	}


    ///////////////////////////////////////////////
    // Type: Function
    // Description:
    //    Recieves original image and resized it to
    //     desired size and save it to assigned path
    // Vars:
    //    x - Desired Max Width
    //    y - Desired Max Height
    //    im - original image
    //     path - path for file to be saved
    ///////////////////////////////////////////////           
    function resizeImage($x,$y,$im,$path=NULL,$width,$height) {
        // Ratioi Resizing
        if ($width > $height) {
            $ratio = $height / $width;
            $y *= $ratio;
        } else {
            $ratio = $width / $height;
            $x *= $ratio;
        }

        $newImage=ImageCreateTrueColor($x,$y);
        imagecopyresized($newImage,$im,0,0,0,0,$x,$y,$width,$height);
        imagejpeg($newImage,$path,90);
        ImageDestroy($newImage);
    }


	public function getImage() {
		global $config;
		$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';
		$flag = false;
		if($this->data['image_id'] != '') {
			$flag = $this->load_by_id($this->data['image_id']);
		}
		if(!$flag && $this->data['barcode'] != '') {
			$flag = $this->load_by_barcode($this->data['barcode']);
		}
		if(!$flag) return array('success' => false, 'code' => 135);
		
		$fname = explode(".", $this->get('filename'));
		$ext = ($this->data['type']==''?@strtolower($this->getName('ext')):$this->data['type']);
		$extension = '.' . $ext;
		$func1 = 'image' . ($ext == 'jpg' ? 'jpeg' : $ext);
		$content_type = 'image/' . ($ext == 'jpg' ? 'jpeg' : $ext);
		$size = @strtolower($this->data['size']);
		//$path = $config['path']['images'] . substr($this->get('path'), 1, strlen($this->get('path'))-1);
		//$image =  $path . $fname[0] . $extension;
		$existsFlag = false;
		//$bucket = $config['s3']['bucket'];
		$tmpPath = $_TMP . $this->get('filename');
		
		$storage = new Storage($this->db);
		$device = $storage->get($this->get('storage_id'));
		$bucket = $device['basePath'];
		$path = $device['basePath'] . $this->get('path');
		$image =  $path .'/'. $this->get('filename');
		
		# checking if exists
		if(in_array(strtolower($size),array('s', 'm', 'l'))) {
			if(strtolower($device['type']) == 's3') {
				$key = $this->get('path') .'/'. $fname[0] . '_' . $size . $extension;
				$key = (substr($key, 0, 1) == '/') ? substr($key, 1, strlen($key)-1) : $key;
				$existsFlag = $this->data['obj']->if_object_exists($bucket,$key);
			} else {
				$existsFlag = @file_exists($path .'/'. $fname[0] . '_' . $size . $extension);
			}
		}
		
		# if exists
		if($existsFlag) {
			if(strtolower($device['type']) == 's3') {
				$fp = fopen($tmpPath, "w+b");
				$this->data['obj']->get_object($bucket, $key, array('fileDownload' => $tmpPath));
				fclose($fp);
			} else {
				$tmpPath = $path .'/'. $fname[0] . '_' . $size . $extension;
			}

			$fp = fopen($tmpPath, 'rb');
// TODO THIS NEED to be the content type based on the data["type"] set
			header("Content-Type: " . $content_type);
			header("Content-Length: " . filesize($tmpPath));
			fpassthru($fp);
			fclose($fp);
			if(strtolower($device['type']) == 's3') {
				@unlink($tmpPath);
			}
			exit;
		}

		$ext = @strtolower($this->getName('ext'));
		$extension = '.' . $ext;
		
		# Image variation does not exist
		if(strtolower($device['type']) == 's3') {
			# downloading original image
			$key = $this->get('path') .'/'. $fname[0] . $extension;
			$key = (substr($key, 0, 1) == '/') ? substr($key, 1, strlen($key)-1) : $key;
			$fp = fopen($tmpPath, "w+b");
			$this->data['obj']->get_object($bucket, $key, array('fileDownload' => $tmpPath));
			fclose($fp);
		} else {
			$tmpPath =  $image;
		}
		if(in_array(strtolower($size),array('s', 'm', 'l'))) {
			switch (strtolower($size)) {
				case 's':
					$this->data['width'] = 100;
					$this->data['height'] = 100;
					break;
				case 'm':
					$this->data['width'] = 275;
					$this->data['height'] = 275;
					break;
				case 'l':
					$this->data['width'] = 800;
					$this->data['height'] = 800;
					break;
			}
		}

		if($this->data['width'] != '' || $this->data['height'] != "") {
			$size = 'custom';
		}
		
		if(in_array(strtolower($size), array('s', 'm', 'l', 'custom'))){
			$dtls = @pathinfo($tmpPath);
			$extension = '.' . $dtls['extension'];
			//$file_name =  $dtls['dirname'] . '/' . $dtls['filename'] . '_' . $size . $extension;
// TODO you need to add the type param at the end of this and add it as an optiona argument in the createThumb
			$type = ($this->data['type']==''?@strtolower($this->getName('ext')):$this->data['type']);
			$extension = '.' . ($type == 'jpg' ? 'jpeg' : $type);
			$file_name =  $dtls['dirname'] . '/' . $dtls['filename'] . '_' . $size . $extension;

			switch($size) {
				case 's':
					$this->createThumb( $tmpPath, 100, 100, '_s', false, $type);
					break;
				case 'm':
					$this->createThumb( $tmpPath, 275, 275, "_m", false, $type);
					break;
				case 'l':
					$this->createThumb( $tmpPath, 800, 800, "_l", false, $type);
					break;
				case 'custom':
					$width = ($this->data['width']!='') ? $this->data['width'] : $this->data['height'];
					$height = ($this->data['height']!='') ? $this->data['height'] : $this->data['width'];
					$this->createThumb( $tmpPath, $width, $height, 'tmp', true, $type);
					break;
			}
			
			if(strtolower($device['type']) == 's3') {
				# putting the image to s3
				$key = $this->get('path') .'/'. $fname[0] . '_' . $size . $extension;
				$key = (substr($key, 0, 1) == '/') ? substr($key, 1, strlen($key)-1) : $key;
				$response = $this->data['obj']->create_object ( $bucket, $key, array('fileUpload' => $file_name,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
				
			}
			
			$fp = fopen($file_name, 'rb');
			header("Content-Type: $content_type");
			header("Content-Length: " . filesize($file_name));
			fpassthru($fp);
			if(strtolower($device['type']) == 's3') {
				@unlink($file_name);
				@unlink($tmpPath);
			}
			exit;
		} else {
			return array('success' => false, 'code' => 138);
		}

	}

	public function getImageId($filename, $filepath, $storage_id) {
		if($filename == '' || $filepath == '' || $storage_id == '') return false;
		$query = sprintf("SELECT `image_id` FROM `image` WHERE `originalFilename` = '%s' AND `path` = '%s' AND `storage_id` = '%s';", $filename, $filepath, $storage_id);
		$ret = $this->db->query_one($query);
		if($ret->image_id == NULL) {
			return false;
		} else {
			return $ret->image_id;
		}
	}

	/**
	 * checks whether field exists in image table
	 */
	public function field_exists ($image_id){
		if($image_id == '' || is_null($image_id)) return(false);

		$query = sprintf("SELECT `image_id` FROM `image` WHERE `image_id` = %s;", $image_id );
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * checks whether field exists in image table
	 */
	public function barcode_exists ($barcode,$returnFlag = false){
		$query = sprintf("SELECT `image_id` FROM `image` WHERE `barcode` = '%s';", $barcode );
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			if($returnFlag) {
				return $ret->image_id;
			} else {
				return true;
			}
		}
	}

	public function save() {
		if($this->field_exists($this->get('image_id'))) {
			$query = sprintf("UPDATE `image` SET  `filename` = '%s', `timestamp_modified` = now(), `barcode` = '%s', `width` = '%s', `height` = '%s', `Family` = '%s', `Genus` = '%s', `SpecificEpithet` = '%s', `rank` = '%s', `author` = '%s', `title` = '%s', `description` = '%s', `GlobalUniqueIdentifier` = '%s', `creative_commons` = '%s', `characters` = '%s', `flickr_PlantID` = '%s', `flickr_modified` = '%s', `flickr_details` = '%s', `picassa_PlantID` = '%s', `picassa_modified` = '%s', `gTileProcessed` = '%s', `zoomEnabled` = '%s', `processed` = '%s', `box_flag` = '%s', `ocr_flag` = '%s', `ocr_value` = '%s', `namefinder_flag` = '%s', `namefinder_value` = '%s', `ScientificName` = '%s', `CollectionCode` = '%s', `tmpFamily` = '%s', `tmpFamilyAccepted` = '%s', `tmpGenus` = '%s', `tmpGenusAccepted` = '%s', `guess_flag` = '%s', `storage_id` = '%s', `path` = '%s', `originalFilename` = '%s', `remoteAccessKey` = '%s', `statusType` = '%s', `rating` = '%s'  WHERE image_id = '%s' ;"
				, mysql_escape_string($this->get('filename'))
				, mysql_escape_string($this->get('barcode'))
				, mysql_escape_string($this->get('width'))
				, mysql_escape_string($this->get('height'))
				, mysql_escape_string($this->get('Family'))
				, mysql_escape_string($this->get('Genus'))
				, mysql_escape_string($this->get('SpecificEpithet'))
				, mysql_escape_string($this->get('rank'))
				, mysql_escape_string($this->get('author'))
				, mysql_escape_string($this->get('title'))
				, mysql_escape_string($this->get('description'))
				, mysql_escape_string($this->get('GlobalUniqueIdentifier'))
				, mysql_escape_string($this->get('creative_commons'))
				, mysql_escape_string($this->get('characters'))
				, mysql_escape_string($this->get('flickr_PlantID'))
				, mysql_escape_string($this->get('flickr_modified'))
				, mysql_escape_string($this->get('flickr_details'))
				, mysql_escape_string($this->get('picassa_PlantID'))
				, mysql_escape_string($this->get('picassa_modified'))
				, mysql_escape_string($this->get('gTileProcessed'))
				, mysql_escape_string($this->get('zoomEnabled'))
				, mysql_escape_string($this->get('processed'))
				, mysql_escape_string($this->get('box_flag'))
				, mysql_escape_string($this->get('ocr_flag'))
				, mysql_escape_string($this->get('ocr_value'))
				, mysql_escape_string($this->get('namefinder_flag'))
				, mysql_escape_string($this->get('namefinder_value'))
				, mysql_escape_string($this->get('ScientificName'))
				, mysql_escape_string($this->get('CollectionCode'))
				, mysql_escape_string($this->get('tmpFamily'))
				, mysql_escape_string($this->get('tmpFamilyAccepted'))
				, mysql_escape_string($this->get('tmpGenus'))
				, mysql_escape_string($this->get('tmpGenusAccepted'))
				, mysql_escape_string($this->get('guess_flag'))
				, mysql_escape_string($this->get('storage_id'))
				, mysql_escape_string($this->get('path'))
				, mysql_escape_string($this->get('originalFilename'))
				, mysql_escape_string($this->get('remoteAccessKey'))
				, mysql_escape_string($this->get('statusType'))
				, mysql_escape_string($this->get('rating'))
				, mysql_escape_string($this->get('image_id'))
			);
		} else {
			$query = sprintf("INSERT IGNORE INTO `image` SET `filename` = '%s', `timestamp_modified` = now(), `barcode` = '%s', `width` = '%s', `height` = '%s', `Family` = '%s', `Genus` = '%s', `SpecificEpithet` = '%s', `rank` = '%s', `author` = '%s', `title` = '%s', `description` = '%s', `GlobalUniqueIdentifier` = '%s', `creative_commons` = '%s', `characters` = '%s', `flickr_PlantID` = '%s', `flickr_modified` = '%s', `flickr_details` = '%s', `picassa_PlantID` = '%s', `picassa_modified` = '%s', `gTileProcessed` = '%s', `zoomEnabled` = '%s', `processed` = '%s', `box_flag` = '%s', `ocr_flag` = '%s', `ocr_value` = '%s', `namefinder_flag` = '%s', `namefinder_value` = '%s', `ScientificName` = '%s', `CollectionCode` = '%s', `tmpFamily` = '%s', `tmpFamilyAccepted` = '%s', `tmpGenus` = '%s', `tmpGenusAccepted` = '%s', `guess_flag` = '%s', `storage_id` = '%s', `path` = '%s', `originalFilename` = '%s', `remoteAccessKey` = '%s', `statusType` = '%s', `rating` = '%s' ;"
				, mysql_escape_string($this->get('filename'))
				, mysql_escape_string($this->get('barcode'))
				, mysql_escape_string($this->get('width'))
				, mysql_escape_string($this->get('height'))
				, mysql_escape_string($this->get('Family'))
				, mysql_escape_string($this->get('Genus'))
				, mysql_escape_string($this->get('SpecificEpithet'))
				, mysql_escape_string($this->get('rank'))
				, mysql_escape_string($this->get('author'))
				, mysql_escape_string($this->get('title'))
				, mysql_escape_string($this->get('description'))
				, mysql_escape_string($this->get('GlobalUniqueIdentifier'))
				, mysql_escape_string($this->get('creative_commons'))
				, mysql_escape_string($this->get('characters'))
				, mysql_escape_string($this->get('flickr_PlantID'))
				, mysql_escape_string($this->get('flickr_modified'))
				, mysql_escape_string($this->get('flickr_details'))
				, mysql_escape_string($this->get('picassa_PlantID'))
				, mysql_escape_string($this->get('picassa_modified'))
				, mysql_escape_string($this->get('gTileProcessed'))
				, mysql_escape_string($this->get('zoomEnabled'))
				, mysql_escape_string($this->get('processed'))
				, mysql_escape_string($this->get('box_flag'))
				, mysql_escape_string($this->get('ocr_flag'))
				, mysql_escape_string($this->get('ocr_value'))
				, mysql_escape_string($this->get('namefinder_flag'))
				, mysql_escape_string($this->get('namefinder_value'))
				, mysql_escape_string($this->get('ScientificName'))
				, mysql_escape_string($this->get('CollectionCode'))
				, mysql_escape_string($this->get('tmpFamily'))
				, mysql_escape_string($this->get('tmpFamilyAccepted'))
				, mysql_escape_string($this->get('tmpGenus'))
				, mysql_escape_string($this->get('tmpGenusAccepted'))
				, mysql_escape_string($this->get('guess_flag'))
				, mysql_escape_string($this->get('storage_id'))
				, mysql_escape_string($this->get('path'))
				, mysql_escape_string($this->get('originalFilename'))
				, mysql_escape_string($this->get('remoteAccessKey'))
				, mysql_escape_string($this->get('statusType'))
				, mysql_escape_string($this->get('rating'))
			);
		}
// echo '<br> Query : ' . $query;
		if($this->db->query($query)) {
			return(true);
		} else {
			return (false);
		}
	}

	public function getNameFinderRecords($filter = '') {
		$query = " SELECT * FROM `image` WHERE ( `namefinder_flag` = 0 OR `namefinder_flag` IS NULL ) AND `ocr_flag` = 1 ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		$ret = $this->db->query($query);
		return($ret);
	}

	public function getOcrRecords($filter = '') {
		$query = " SELECT * FROM `image` WHERE ( `ocr_flag` = 0 OR `ocr_flag` IS NULL ) AND `processed` = 1 ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		$ret = $this->db->query($query);
		return($ret);
	}

	public function getGuessTaxaRecords($filter = '') {
		$query = " SELECT * FROM `image` WHERE ( `guess_flag` = 0 OR `guess_flag` IS NULL ) AND `processed` = 1 ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		$ret = $this->db->query($query);
		return($ret);
	}

	public function getBoxRecords($filter = '') {
		$query = " SELECT * FROM `image` WHERE ( `box_flag` = 0 OR `box_flag` IS NULL ) ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		$ret = $this->db->query($query);
		return($ret);
	}

	public function getFlickrRecords($filter = '') {
		$query = " SELECT * FROM `image` WHERE `flickr_PlantID` = 0 OR `flickr_PlantID` IS NULL ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		$ret = $this->db->query($query);
		return($ret);
	}

	public function getPicassaRecords($filter = '') {
		$query = " SELECT * FROM `image` WHERE `picassa_PlantID` = 0 OR `picassa_PlantID` IS NULL ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		return ($this->db->query($query));
	}

	/**
	 * Return image records yet to be gTileProcessed
	 * @return mysql resultset
	 */
	public function getGTileRecords($filter='') {
		$query = " SELECT * FROM `image` WHERE `gTileProcessed` = 0 OR `gTileProcessed` IS NULL ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		return ($this->db->query($query));
	}

	public function getZoomifyRecords($filter='') {
		$query = " SELECT * FROM `image` WHERE `zoomEnabled` = 0 OR `zoomEnabled` IS NULL ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		return ($this->db->query($query));
	}

	public function getNonProcessedRecords($filter='') {
		$query = " SELECT * FROM `image` WHERE `processed` = 0 OR `processed` IS NULL ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		return ($this->db->query($query));
	}

	/**
	 * Creates the GoogleMap Tiles for the image
	 * @param string barcode
	 */
	public function processGTile($barcode) {
		global $config;
		if($this->load_by_barcode($barcode)) {

		$ext = @strtolower($this->getName('ext'));
		$func = 'imagecreatefrom' . ($ext == 'jpg' ? 'jpeg' : $ext);
		$func1 = 'image' . ($ext == 'jpg' ? 'jpeg' : $ext);

		$outputPath = $config['path']['images'] . $this->barcode_path( $barcode ) . 'google_tiles/';
		$image = $config['path']['images'] . $this->barcode_path( $barcode ) . $this->get('filename');

// 			$src = imagecreatefromjpeg( $image );
		$src = $func( $image );
		$dest = imagecreatetruecolor(256, 256);

// 2x Zoom
			$zoomfactor = 2;
			$tmp = imagecreatetruecolor( imagesx( $src ) * $zoomfactor, imagesy( $src ) * $zoomfactor );
			imagecopyresized($tmp, $src, 0, 0, 0, 0, imagesx( $src ) * $zoomfactor, imagesy( $src ) * $zoomfactor, imagesx( $src ), imagesy( $src ));
			$src = $tmp;

			for ($k = 0; $k <= 5; $k++) {
				$width = imagesx( $src );
				$height = imagesy( $src );
				if ($k == 0) {
					$sample = $src;
				} else {
			
					$percent = 1 / pow(2, $k);
					$newwidth = $width * $percent;
					$newheight = $height * $percent;
					$sample = imagecreatetruecolor($newwidth, $newheight);
					imagecopyresized($sample, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
					$width = $newwidth;
					$height = $newheight;
				}

				for ($i = 0; $i <= (int) ( $width / 256 ); $i++) {
					for ($j = 0; $j <= (int) ( $height / 256 ); $j++) {
						$x = $i;
						$y = $j;
						$z = 1;

						$this->mkdir_recursive($outputPath . $k . '/');
						imagecopy($dest, $sample, 0, 0, ($i * 256), ($j * 256), 256, 256);
// 						imagejpeg($dest, sprintf( $outputPath . '%s/tile_%s_%s_%s.jpg', $k, $z, $x, $y) );
						$func1($dest, sprintf( $outputPath . '%s/tile_%s_%s_%s.' . $ext, $k, $z, $x, $y) );
				
					}
				}
				
			}
			
			imagedestroy($dest);
			imagedestroy($src);
			imagedestroy($sample);

			$this->set('gTileProcessed',1);
			$this->save();

			return true;
		} # if barcode present
		return false;
	}

	/**
	 * Creates the GoogleMap Tiles for the image using IM for S3 mode
	 * @param string barcode
	 * @param mixed s3 details and object
	 */
	public function processGTileIM($barcode) {
		global $config;
		if($this->load_by_barcode($barcode)) {
			$tilepath = $config['path']['images'] . $this->barcode_path( $barcode ) . 'google_tiles/';
			$filename = $config['path']['images'] . $this->barcode_path( $barcode ) . $this->get('filename');
			$this->mkdir_recursive($tilepath);
			# creating tiles using Image Magik
			$gTileRes = $this->createGTileIM($filename, $tilepath);
			return true;
		}
		return false;
	}

	/**
	 * Creates the GoogleMap Tiles for the image using IM for s3 mode
	 * @param string barcode
	 * @param mixed s3 details and object
	 */
	public function processGTileIM_S3($barcode, $arr) {
		global $config;
		$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';		if($this->load_by_barcode($barcode)) {

		$tmpPath = $_TMP . 'tiles/';
		if(!@file_exists($tmpPath)) {
			@mkdir($tmpPath,0775);
		}
		$tilepath = $tmpPath;

		# getting the image from s3
		$filename = $_TMP . $this->get('filename');

		$bucket = $arr['s3']['bucket'];
		$key = $this->barcode_path($barcode) . $this->get('filename');
		$arr['obj']->get_object($bucket, $key, array('fileDownload' => $filename));

		# creating tiles using Image Magik
		$gTileRes = $this->createGTileIM($filename,$tilepath);

		# uploading to s3 and deleting the files
		$tiles3path = $this->barcode_path($barcode) . 'google_tiles/';
		
		if ($handle = @opendir($tilepath)) {
			while (false !== ($file = @readdir($handle))) {
				if ($file != '.' && $file != '..') {
					$file = $tilepath . $file;
					if(is_dir($file)) {		
						if ($tempHandle = @opendir($file)) {
							while (false !== ($tile = @readdir($tempHandle))) {
								if ($tile != '.' && $tile != '..') {
									$tmpThumbPath = $tilepath . @basename($file) . '/' . @basename($tile);
									$tmpS3Path = $tiles3path . @basename($file) . '/' . @basename($tile);
									$response = $arr['obj']->create_object ( $bucket, $tmpS3Path, array('fileUpload' => $tmpThumbPath,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
									@unlink($tilepath . @basename($file) . '/' . @basename($tile));
								} # not . or ..
							} # while tile
							@closedir($tempHandle);
						} # temp handle
						rmdir($file);
					} # is dir
				} # not . or ..
			} # while file
			@closedir($handle);
		} # handle

			@unlink($filename);
			@unlink($tmpPath);
			return true;
		}
		return false;
	}

	/**
	 * Creates gTiles using IM
	 * @param string $filename : input filename
	 * @param string $outputPath : location for creating tiles
	 */
	function createGTileIM($filename, $outputPath) {
	
		if (!file_exists($filename)) {
			return( array("success" => false, "error" => array("code" => 100, "msg" => "File does not exist.") ) );
		}
	
		$filePath = @dirname($filename) . '/';
		$dimensions = exec('identify -format "%w,%h" ' . $filename);
		list($owidth,$oheight) = explode(',',$dimensions);
		
		if(!file_exists($outputPath)) {
			@mkdir($outputPath, 0777);
		}
	
		$zoomLevels = round(sqrt($oheight / 256));
		for ($z = 0; $z < $zoomLevels; $z++) {
			if ($z == 0) {
				$width = $owidth;
				$height = $oheight;
				$tmpFile = $filename;
			} else {
				$tmpFile = $filePath . $z . "tmp" . @basename($filename);
				$percent = 1 / pow(2, $z);
				$width = $owidth * $percent;
				$height = $oheight * $percent;
				$cmd = sprintf("convert %s -resize %sx%s %s"
					,	$filename
					,	$width
					,	$height
					,	$tmpFile
				);
				$res = system($cmd);
			}
	
			$iLimit = (int) ( $width / 256 );
			$jLimit = (int) ( $height / 256 );
	
			for ($i = 0; $i <= $iLimit; $i++) {
				for ($j = 0; $j <= $jLimit; $j++) {
				
					$x = $i;
					$y = $j;
	//				$z = 1;
					
					$this->mkdir_recursive($outputPath . $z . '/');
		
					$cmd = sprintf("convert %s -crop %sx%s+%s+%s\! %s%s/tile_%s_%s_%s.jpg"
						, $tmpFile
						,	256
						,	256
						,	($i * 256)
						,	($j * 256)
						,	$outputPath
						,	$z
						,	$z
						,	$x
						,	$y
					);
					$res = system($cmd);
					if($i == $iLimit || $j == $jLimit) {
						$tmpImage = sprintf("%s%s/tile_%s_%s_%s.jpg",$outputPath,$z,$z,$x,$y);
						$cmd = sprintf("convert %s -background white -extent 256x256 +repage %s", $tmpImage, $tmpImage);
						$res = system($cmd);
					}
				}
			}
			if($tmpFile != $filename) {
				@unlink($tmpFile);
			}
		}
		return( array("success" => true) );
	}

	/**
	 * Zoomify the image
	 */
	public function zoomifyImage($barcode) {
		global $config;
		if($this->load_by_barcode($barcode)) {
			$outputPath = $config['path']['images'] . $this->barcode_path( $barcode ) . 'zoomify/';
			$this->mkdir_recursive( $outputPath );
			$image = $config['path']['images'] . $this->barcode_path( $barcode ) . $this->get('filename');
			$script_path =  $config['path']['base'] . 'api/classes/zoomify/ZoomifyFileProcessor.py ';
			passthru('python ' . $script_path . $image);

// 			passthru('/usr/bin/python ' . $script_path . $image);
// 			$str = exec('python ' . $script_path . $image, $ret);
/*			print '<br>' . $str . '<br>';
			var_dump($ret);*/

// 			$this->set('processed',1);
// 			$this->save();
		}
		return false;
	}

	public function listImages1($queryFlag = true) {
		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " WHERE " . $where;
		}
		if($this->data['code'] != '') {
			$where .= sprintf(" AND `barcode` LIKE '%s%%' ", mysql_escape_string($this->data['code']));
		}

		if($this->data['image_id'] != '') {
			if($where != '') {
				$where .= sprintf(" AND `image_id` = '%s' ", mysql_escape_string($this->data['image_id']));
			} else {
				$where .= sprintf(" WHERE `image_id` = '%s' ", mysql_escape_string($this->data['image_id']));
			}
		}

		if($this->data['field'] != '' && $this->data['value'] != '') {
			if($where != '') {
				$where .= sprintf(" AND `%s` = '%s' ", mysql_escape_string($this->data['field']), mysql_escape_string($this->data['value']));
			} else {
				$where .= sprintf(" WHERE `%s` = '%s' ", mysql_escape_string($this->data['field']), mysql_escape_string($this->data['value']));
			}
		}

		$where .= build_order( $this->data['order']);
		$where .= build_limit($this->data['start'], $this->data['limit']);

		$query = "SELECT SQL_CALC_FOUND_ROWS  image_id,filename,timestamp_modified,barcode,width,height,Family,Genus,SpecificEpithet,flickr_PlantID, flickr_modified,flickr_details,picassa_PlantID,picassa_modified, gTileProcessed,zoomEnabled,processed,ocr_flag, ocr_value,namefinder_flag,namefinder_value,ScientificName, CollectionCode FROM `image` " . $where;

		if($queryFlag) {
			$ret = $this->db->query_all( $query );
			return is_null($ret) ? array() : $ret;
		} else {
			$ret = $this->db->query( $query );
			return $ret;
		}
	}

	public function listImages($queryFlag = true) {

		$characters = $this->data['characters'];
		$browse = $this->data['browse'];

		$this->query = "SELECT SQL_CALC_FOUND_ROWS  I.image_id,I.filename,I.timestamp_modified, I.barcode, I.width,I.height,I.Family,I.Genus,I.SpecificEpithet,I.flickr_PlantID, I.flickr_modified,I.flickr_details,I.picassa_PlantID,I.picassa_modified, I.gTileProcessed,I.zoomEnabled,I.processed,I.box_flag,I.ocr_flag";
		if($this->data['showOCR']) {
			$this->query .= ',I.ocr_value';
		}
		$this->query .= ",I.namefinder_flag,I.namefinder_value,I.ScientificName, I.CollectionCode, I.GlobalUniqueIdentifier FROM `image` I ";

		$this->queryCount = ' SELECT count(*) AS sz FROM `image` I ';
		
		if (($characters != '') && ($characters != '[]')) {
			$this->query .= ", image_attrib ia ";
			$this->queryCount .= ", image_attrib ia ";
		}

		$this->query .= " WHERE 1=1 AND (";
		$this->queryCount .= " WHERE 1=1 AND (";
		
		$this->setBrowseFilter();
		$this->query .= " AND I.image_id != '' ";
		$this->queryCount .= " AND I.image_id != '' ";
		$this->setAdminCharacterFilter();

		if ($this->data['search_value'] != '') {
			$this->query .= sprintf(" AND %s LIKE '%s%%' ", $this->data['search_type'], $this->data['search_value']);
			$this->queryCount .= sprintf(" AND %s LIKE '%s%%' ", $this->data['search_type'], $this->data['search_value']);
		}

		$where = buildWhere($this->data['filter']);
		if ($where != '') {
			$where = " AND " . $where;
		}
		if($this->data['code'] != '') {
			$where .= sprintf(" AND I.`CollectionCode` LIKE '%s%%' ", mysql_escape_string($this->data['code']));
		}

		if($this->data['image_id'] != '') {
			$where .= sprintf(" AND I.`image_id` = '%s' ", mysql_escape_string($this->data['image_id']));
		}

		if($this->data['field'] != '' && $this->data['value'] != '') {
			$where .= sprintf(" AND `%s` = '%s' ", mysql_escape_string($this->data['field']), mysql_escape_string($this->data['value']));
		}

		$this->query .= $where;
		$this->queryCount .= $where;

		$this->setGroupFilter();
		if(($this->data['sort']!='') && ($this->data['dir']!='')) {
			$this->setOrderFilter('manual');
		} else {
			$this->setOrderFilter();
		}
		$this->setLimitFilter();
// echo $this->query;
		// $this->total = $this->db->query_total();

		$countRet = $this->db->query_one( $this->queryCount );
		if ($countRet != NULL) {
			$this->total = $countRet->sz;
		}

		if($queryFlag) {
			$ret = $this->db->query_all($this->query);
			return is_null($ret) ? array() : $ret;
		} else {
			$ret = $this->db->query( $query );
			return $ret;
		}
	}

	public function imageSequenceCache() {

		$query = 'SELECT SQL_CALC_FOUND_ROWS * FROM `image` WHERE 0=0 ';
		if($this->data['code'] != '') {
			$query .= sprintf(" AND `barcode` LIKE '%s%%' ", mysql_escape_string($this->data['code']));
		}

		$query .= ' ORDER BY `barcode` ';

		$Ret = $this->db->query($query);

		$pre_fix = '';
		$counter = '';
		$strips_array = array();
		$start = '';
		$end = '';
		$preCount = 0;

		if(is_object($Ret)) {
			while ($record = $Ret->fetch_object()) {
				$ar = getBarcodePrefix($record->barcode);
				$barpre = $ar['prefix'];
				$barc = (int) $ar['tail'];

				$tmpStrip = str_replace($barc,'',$ar['tail']);

				$preCount++;

				if($counter == '') {
					$counter = $barc;
					$pre_fix = $barpre;
					$start = $end = $record->barcode;
					$pre = $barpre;
				}

				if($pre_fix != $barpre || $counter != $barc) {

					if($pre_fix == $barpre) {
						$qq = getBarcodePrefix($end);
						$qq_tail = (int) $qq['tail'];
						$lt = $barc - $qq_tail - 1;
						$tmp_start = $barpre . $tmpStrip . ($qq_tail + 1);
						$qq = getBarcodePrefix($record->barcode);
						$qq_tail = (int) $qq['tail'];
						$tmp_end = $barpre . $tmpStrip . ($qq_tail - 1);
						$strips_array[$end][] = array('startRange' => $tmp_start, 'endRange' => $tmp_end, 'prefix' => $pre, 'recordCount' => $lt, 'exist' => 0);
					}

					$strips_array[$start][] = array('startRange' => $start, 'endRange' => $end, 'prefix' => $pre, 'recordCount' => $preCount, 'exist' => 1);
					$preCount = 0;
					$start = $end = $record->barcode;
					$pre = $barpre;
					$counter = $barc;
				}
				$pre_fix = $barpre;
				$end = $record->barcode;
				$counter++;
			} # foreach

			# last record bein the exception and increment not done
			if($preCount == 0) $preCount++;

			$strips_array[$start][] = array('startRange' => $start, 'endRange' => $end, 'prefix' => $pre, 'recordCount' => $preCount, 'exist' => 1);
		} # if count array

		ksort($strips_array);
		$output = array();

		if(count($strips_array) && is_array($strips_array)) {
			foreach($strips_array as $strp) {
				if(count($strp) && is_array($strp)) {
					foreach($strp as $stp) {
						$output[] = $stp;
					}
				}
			}
		}

		return $output;
	}

	public function rotateImage($image = array()) {
		global $config;
		$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';
		if($image['image_id'] == '' || !$this->field_exists($image['image_id'])) {
			$ret['success'] = false;
			return $ret;
		}
		$pqueue = new ProcessQueue($this->db);
		$pqueue->db = &$this->db;
		$this->load_by_id($image['image_id']);
		$barcode = $this->get('barcode');

		if($config['mode'] == 's3') {
			$imagePath = $_TMP;

			# getting the image from s3
			$key = $this->barcode_path($barcode) . $this->get('filename');
			$image['obj']->get_object($config['s3']['bucket'], $key, array('fileDownload' => $imagePath . $this->get('filename')));
		} else {
			$imagePath = $config['path']['images'] . $this->barcode_path( $barcode );
		}
		$imageFile = $imagePath . $this->get('filename');
		if(in_array($image['degree'], array(90, 180, 270))){
			#rotating the image
			$cmd = sprintf("convert -limit memory 16MiB -limit map 32MiB %s -rotate %s %s", $imageFile, $image['degree'], $imageFile);
//  echo '<br>' . $cmd;
			system($cmd);

			if($config['mode'] == 's3') {
				# putting the image to s3
				$key = $this->barcode_path($barcode) . $this->get('filename');
				$response = $image['obj']->create_object ( $config['s3']['bucket'], $key, array('fileUpload' => $imageFile,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
				@unlink($imageFile);
			}
		}

		# deleting related images
		if($config['mode'] == 's3') {
			foreach(array('_s','_m','_l') as $postfix) {
				$response = $image['obj']->delete_object($config['s3']['bucket'], $this->barcode_path($barcode) . $barcode . $postfix . '.jpg');
			}
		} else {
			if(is_dir($imagePath)) {
				$handle = opendir($imagePath);
				while (false !== ($file = readdir($handle))) {
					if( $file == '.' || $file == '..' || $file == $this->get('filename') ) continue;
					if (is_dir($imagePath.$file)) {
						$this->rrmdir($imagePath.$file);
					} else if(is_file($imagePath.$file)) {
						@unlink($imagePath.$file);
					}
				}
			}
		}

		$this->set('flickr_PlantID', 0);
		$this->set('picassa_PlantID', 0);
		$this->set('gTileProcessed', 0);
		$this->set('zoomEnabled', 0);
		$this->set('processed', 0);
		$this->save();

		// $pqueue->set('image_id', $barcode);
		$pqueue->set('image_id', $image['image_id']);
		$pqueue->set('process_type', 'all');
		$pqueue->save();

		$ret['success'] = true;
		return $ret;
	}

	public function deleteImage() {
		global $config;
		$imageId = $this->data['image_id'];
		$storage = new Storage($this->db);
		if($imageId != '' && $this->field_exists($imageId)) {
			$this->load_by_id($imageId);
			$barcode = $this->get('barcode');
			$device = $storage->get($this->get('storage_id'));
			$filenameParts = explode('.', $this->get('filename'));
			switch(strtolower($device['type'])) {
				case 's3':
					$tmp = $this->get('path');
					$tmp = (substr($tmp, 0, 1)=='/') ? (substr($tmp, 1, strlen($tmp)-1)) : ($tmp);
					$tmp = (substr($tmp, strlen($tmp)-1, 1)=='/') ? (substr($tmp, 0, strlen($tmp)-1)) : ($tmp);
					foreach(array('_s','_m','_l','') as $postfix) {
						$response = $this->data['obj']->delete_object($device['basePath'], $tmp .'/'. $filenameParts[0] . $postfix .'.'. $filenameParts[1]);
					}
					break;
				case 'local':
					$imagePath = $device['basePath'] . $this->get('path') . '/';
					# deleting related images
					foreach(array('_s','_m','_l','') as $postfix) {
						if(file_exists($imagePath.$filenameParts[0].$postfix.'.'.$filenameParts[1])) {
							@unlink($imagePath.$filenameParts[0].$postfix.'.'.$filenameParts[1]);
						}
					}
					# Remove empty directories
					$path = $this->get('path');
					$parts = explode('/', $path);
					while(count($parts)>0) {
						if(!rmdir($device['basePath'] . $path . '/')) break;
						$parts = explode('/', $path);
						unset($parts[count($parts)-1]);
						$path = implode('/', $parts);
					}
					break;
			}
			
			$query = sprintf("DELETE FROM `image_attrib` WHERE `imageID` = '%s' ", mysql_escape_string($imageId));
			$this->db->query($query);
			
			$query = sprintf("DELETE FROM `event_images` WHERE `imageId` = '%s' ", mysql_escape_string($imageId));
			$this->db->query($query);
			
			$query = sprintf("DELETE FROM `process_queue` WHERE `image_id` = '%s' ", mysql_escape_string($imageId));
			$this->db->query($query);
			
			$query = sprintf("DELETE FROM `specimen2label` WHERE `barcode` = '%s' ", mysql_escape_string($barcode));
			$this->db->query($query);
			
			$delquery = sprintf("DELETE FROM `image` WHERE `image_id` = '%s' ", mysql_escape_string($imageId));
			if($this->db->query($delquery)) {
				return  array('success' => true);
			}
			return array('success' => false, 'code' => 117);
		}
		return array('success' => false, 'code' => 116);
	}


	public function getGeneraList($filter=array()) {
		$query = "SELECT DISTINCT `Genus` FROM `image` WHERE `Family` = '' AND `Genus` != '' ";
		if($filter['limit'] != '') {
			$query .= sprintf(" LIMIT %s ", $filter['limit']);
		}
		$ret = $this->db->query($query);
		return($ret);
	}

	public function getScientificName($genus) {
		$query = sprintf("SELECT DISTINCT `ScientificName` FROM `image` WHERE `ScientificName` != '' AND `Genus` = '%s' ", mysql_escape_string($genus));
		$ret = $this->db->query_one($query);
		return($ret);
	}

	public function updateFamilyList($genus,$family ) {
		$query = sprintf(" UPDATE `image` SET  `Family` = '%s' WHERE `Genus` = '%s' AND `Family` = '' ", mysql_escape_string($family), mysql_escape_string($genus));
		if($this->db->query($query)) {
			return array('success' => true, 'records' => $this->db->affected_rows);
		} else {
			return array('success' => false);
		}
	}

	public function rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != '.' && $object != '..') {
					if (filetype($dir.'/'.$object) == 'dir') $this->rrmdir($dir.'/'.$object); else unlink($dir.'/'.$object);
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}

	public function loadBrowse() {
		$ar = array();
		$nodes = array();
		$childFlag = true;
		$mapping = array('Family' => 'Genus', 'Genus' => 'SpecificEpithet');
		if($this->data['browse'] != '') $this->data['browse'] = json_decode($this->data['browse'],true);

		switch( $this->data['nodeApi'] ) {
			case 'alpha':
				for ($i=65;$i<91;$i++) {
					$tmp = chr($i);
					$nodes[] = array('text' => $tmp, 'nodeApi' => $this->data['nodeValue'], 'nodeValue' => $tmp . '%', 'path' => $this->data['path'], 'filter' => json_decode($this->data['filter'],true) );
				}
				$ret = true;
				break;
			case "Family":
			case "Genus":
			case "SpecificEpithet":
				$parent = $this->data['nodeApi'];
				if ($this->data['nodeValue'] != 'null' && $this->data['nodeValue'] != '') {
					if (strpos($this->data['nodeValue'], "%") !== false) {
						$childFlag = false;
					}
				}
				$child = ($childFlag && isset($mapping[$this->data['nodeApi']])) ? $mapping[$this->data['nodeApi']] : $this->data['nodeApi'] ;

				if(in_array($child,array('Family','SpecificEpithet'))) {
					$query = sprintf("SELECT %s as text, count(*) as cnt FROM `image` WHERE 1=1 ", $child);
				} else {
					$query = sprintf("SELECT %s as text, count(*) as cnt FROM `image` WHERE 1=1 AND  %s != '' ", $child, $child);
				}

				$this->data['filter'] = json_decode($this->data['filter'], true);

				if ($this->data['nodeValue'] != 'null' && $this->data['nodeValue'] != '') {
					$condition = '=';
					if (strpos($this->data['nodeValue'], "%") !== false) {
						$condition = 'LIKE';
					}
					$query .= sprintf(" AND %s %s '%s' ", $parent, $condition, mysql_escape_string($this->data['nodeValue']) );
				}

				if (is_array($this->data['filter']) && count($this->data['filter'])) {
					foreach( $this->data['filter'] as $key => $value ) {
						switch( $key ) {
							default:
								$condition = '=';
								if (strpos($value, "%") !== false) {
									$condition = 'LIKE';
								}				
								$query .= sprintf(" AND %s %s '%s' ", mysql_escape_string($key), $condition, mysql_escape_string($value) );
								break;
						}
					} # foreach
				}
		
				$query .= sprintf(" GROUP BY %s ORDER BY %s ", $child, $child);
				$nextChild = $mapping[$this->data['nodeApi']];
				$leaf = true;
				if (isset($nextChild)) {
					$cls = 'file';
					$leaf = false;
				}
				$results = $this->db->query_all( $query );
				if($parent != '') {
					$filter = $this->data['filter'];
					$filter[$parent] = $this->data['nodeValue'];
					$this->data['filter'] = $filter;
				}

				if(count($results)){
					foreach( $results as $record ) {

						if($record->text == '') {
							$text = $nv = "<b>BLANK</b>";
						} else {
							$text = $record->text . " (" . number_format($record->cnt) . ")";
							$nv = $record->text;
						}

						$nodes[] = array('text'=>$record->text . " (" . number_format($record->cnt) . ")", 'specimenCount' => $record->cnt, 'checked' => false, 'leaf' => $leaf, 'nodeApi' => $child, 'nodeValue' => $record->text, 'filter' => $this->data['filter']);
					}
				} else {
					$nodes = array();
				}
				break;
			default:
				break;
		} # switch

		$this->data['time_end'] = microtime(true);
		$time = $this->data['time_end'] - $this->data['time_start'];
		$time = number_format($time,4);
		
		$ar = array();
		$ar['success'] = true;
		$ar['total_execute_time'] = $time;
		$ar['totalCount'] = count($nodes);
		$ar['results'] = $nodes;

		return($ar);
	}

	function getCollectionSpecimenCount() {
		if($this->data['nodeApi'] == '' || $this->data['nodeValue'] == '') return false;
		$condition = '=';
		if (strpos($this->data['nodeValue'], "%") !== false) {
			$condition = 'LIKE';
		}

		$query = sprintf(" SELECT count(*) ct, `CollectionCode` FROM `image` WHERE %s %s '%s' GROUP BY `CollectionCode` ", $this->data['nodeApi'], $condition, mysql_escape_string($this->data['nodeValue'] ));
		$ret = $this->db->query_all($query);
		return $ret;
	}

	function populateS3Data($response) {
		$recordCount = 0;
		$srchArray = array('_s','_m','_l','_thumb','google_tiles','tile_');

		if(count($response) && is_array($response)) {
			foreach($response as $filePath) {
				$fileDetails = @pathinfo($filePath);
				$count = 0;
				$tmpStr = $fileDetails['basename'];
				str_replace($srchArray,'',$tmpStr,$count);
				if($count == 0) {
					$this->set('filename',$fileDetails['basename']);
					$this->set('barcode',$fileDetails['filename']);
					$this->set('timestamp_modified',@date('d-m-Y H:i:s',@strtotime($ky->LastModified)));

					if($this->save()) {
						if($this->db->affected_rows == 1) {
							$recordCount++;
						}
					}
				}
			}
		}
		if($recordCount) {
			$ret = array('success' => true, 'recordCount' => $recordCount);
		} else {
			$ret = array('success' => false);
		}
		return $ret;
	}

	private function setFilters() {
		$this->setCharacterFilter();
		$this->setSearchFilter();
		$this->setFilter();
	}

	private function setCharacterFilter() {
	
		if (($this->data['characters'] != '') && ($this->data['characters'] != '[]')) {
			$this->query .= ", count(*) as sz";
		}

		$tstr = ' FROM image I ';
		
		if (($this->data['characters'] != '') && ($this->data['characters'] != '[]')) {
			$tstr .= ', image_attrib ia ';
		}

		$tstr .= ' WHERE 1=1 AND (';

		$this->query = $this->query . $tstr;
		$this->queryCount .= $tstr;

		$this->setBrowseFilter();

		switch($this->data['imagesType']) {
			case 1:
				$tstr = " AND I.barcode != '' ";
				$this->query .= $tstr;
				$this->queryCount .= $tstr;
				break;
			case 2:
			default:
				$this->query .= '';
				break;
			case 3:
				$tstr = " AND I.barcode = '' ";
				$this->query .= $tstr;
				$this->queryCount .= $tstr;
				break;
		}
		if (($this->data['characters'] != '') && ($this->data['characters'] != '[]')) {
			$this->char_list = '';
			$this->char_count = 0;
			foreach(json_decode($this->data['characters']) as $character) {
				$this->char_list .= $character->node_value . ",";
				$this->char_count++;
			}
			$this->char_list = substr($this->char_list, 0, -1);
			$tstr = " AND ia.imageID = I.image_id AND ia.valueID IN ( " . $this->char_list . " ) ";
			$this->query .= $tstr;
			$this->queryCount .= $tstr;
		}
	}

	private function setSearchFilter() {
		if($this->data['search_value'] != '') {
			$tstr = " AND ". $this->data['search_type'] ." LIKE '" .$this->data['search_value'] ."%' ";
			$this->query .= $tstr;
			$this->queryCount .= $tstr;
		}
	}

	private function setFilter() {
		if($this->data['filter'] != '') {
			$filter = json_decode($this->data['filter'],true);
			if(is_array($filter) && count($filter)) {
				$tstr = '';
				foreach($filter as $field => $value) {
					$tstr .= sprintf(" AND %s = '%s' ", $field, mysql_escape_string($value));
				}
				$this->query .= $tstr;
				$this->queryCount .= $tstr;
			}
		}
	}

	private function setBrowseFilter() {

		if ($this->data['browse'] != '' && $this->data['browse'] != '[]') {
			$tstr = '';
			foreach(json_decode($this->data['browse']) as $character) {
				$this->char_list .= $character->node_value . ",";
				if($character->node_type == 'species') $character->node_type = 'SpecificEpithet';
				if ($character->node_type == 'species') {
					$tstr .= " (I." . $character->node_type . " = '" . $character->node_value . "' AND I.genus='" . $character->genus . "') OR";
				} else {
					$tstr .= " (I." . $character->node_type . " = '" . $character->node_value . "') OR";
				}
			}
			$this->query .= $tstr;
			$this->queryCount .= $tstr;
			$this->query = substr($this->query, 0, -2) . ")";
			$this->queryCount = substr($this->queryCount, 0, -2) . ")";
		} else {
			$this->query = substr($this->query, 0, -6);
			$this->queryCount = substr($this->queryCount, 0, -6);
		}

	}

	private function setAdminCharacterFilter() {
		$characters = $this->data['characters'];
		if (($characters != '') && ($characters != '[]')) {
			$this->char_list = '';
			// foreach($json->decode($characters) as $character) {
			foreach(json_decode($characters) as $character) {
				$this->char_list .= $character->node_value . ",";
			}
			$this->char_list = substr($this->char_list, 0, -1);

			$this->query .= " AND ia.imageID = I.image_id AND ia.valueID IN (".$this->char_list.") ";
		}
	}

	private function setGroupFilter() {
		$characters = $this->data['characters'];
		if (($characters != '') && ($characters != '[]')) {
	 		$this->query .= " GROUP BY I.`image_id` ";
		}
	}

	// private function setOrderFilter($mode = 'view_images') {
		// if($mode == 'view_images') {
			// $this->query .= " ORDER BY I.`Family`, I.`Genus`, I.`SpecificEpithet` ";
		// } else {
			// $this->query .= sprintf(" ORDER BY I.%s %s", mysql_escape_string($this->data['sort']),  $this->data['dir']);
		// }
	// }

	private function setOrderFilter($mode = 'view_images') {
		$orderBy = '';
		switch($mode) {
			case 'view_images':
				$orderBy .= ' ORDER BY I.`Family`, I.`Genus`, I.`SpecificEpithet` ';
				break;
			case 'manual':
				$orderBy .= sprintf(" ORDER BY I.%s %s ", mysql_escape_string($this->data['sort']),  $this->data['dir']);
				break;
			default:
				if(($this->data['sort']!='') && ($this->data['dir']!='')) {
					$orderBy .= sprintf(" ORDER BY I.%s %s ", mysql_escape_string($this->data['sort']),  $this->data['dir']);
				} else if($this->data['order'] != '' && is_array($this->data['order'])) {
					$orderBy .= ' ORDER BY ';
					if(count($this->data['order'])) {
						$ordArray = array();
						foreach($this->data['order'] as $order) {
							$ordArray[] = " I.{$order['field']} {$order['dir']} ";
						}
						$orderBy .= implode(',',$ordArray);
					}
				}
				break;
		}
		if($this->data['useRating']) {
			$orderBy .= ($orderBy == '') ? ' ORDER BY I.`rating` DESC ' : ', I.`rating` DESC ';
		}
		if($this->data['useStatus']) {
			$orderBy .= ($orderBy == '') ? ' ORDER BY I.`statusType` DESC ' : ', I.`statusType` DESC ';
		}
		$this->query .= $orderBy;
	}

	private function setLimitFilter() {
		$this->query .= sprintf("  LIMIT %s, %s", mysql_escape_string($this->data['start']),  $this->data['limit']);
	}

	# Image Functions

	public function loadImageCharacters() {

		$nodes = array();
		if ($this->data['attributes'] == "") {
			return array('success' => false, 'recordCount' => 0);
		} else {

			$query = sprintf("SELECT SQL_CALC_FOUND_ROWS ia.imageID, iat.typeID, iav.valueID, iat.title, iav.name FROM image_attrib_type iat, image_attrib_value iav, image_attrib ia WHERE ia.typeID=iat.typeID AND ia.valueID=iav.valueID AND ia.imageID IN (%s) ORDER BY iat.title, name", mysql_escape_string($this->data['attributes']));
			$records = $this->db->query_all($query);
			$this->total = $this->db->query_total();

			if(!is_null($records)) {
				if(count($records)) {
					foreach($records as $record) {
						$collected = @mktime(0,0,0,$record->tmonth,$record->tday,$record->tyear);
						$nodes[] = array('imageID'=>$record->imageID, 'typeID'=>$record->typeID, 'valueID'=>$record->valueID, 'title'=>$record->title, 'name'=>$record->name);
					}
				}
			}
			return array('success' => true, 'recordCount' => $this->total, 'data' => $nodes);
		}
	}

	
# Attribute Functions

		public function getAttributeBy($attribute, $attribType) {
			if(!@in_array($attribType,array('typeID','title','term'))) return false;
			if($attribType == 'typeID') {
				return $this->category_exist($attribute) ? $attribute : false; 
			}
			$ret = $this->db->query_one( sprintf(" SELECT `typeID` FROM `image_attrib_type` WHERE `%s` = '%s' ", mysql_escape_string($attribType), mysql_escape_string($attribute)) );
			return ($ret == NULL) ? false : $ret->typeID;
		}

		public function getValueBy($value, $valueType) {
			if(!@in_array($valueType,array('valueID','name'))) return false;
			if($valueType == 'valueID') {
				return $this->attribute_exist($value) ? $value : false; 
			}

			$ret = $this->db->query_one( sprintf(" SELECT `valueID` FROM `image_attrib_value` WHERE `name` = '%s' ", mysql_escape_string($value)) );
			return ($ret == NULL) ? false : $ret->valueID;
		}
	
	public function addImageAttribute() {
		$imageIDs = @explode(',', $this->data['imageID']);
		$categoryID = $this->data['categoryID'];
		$valueID = $this->data['valueID'];
		if(count($imageIDs)) {
			foreach($imageIDs as $id) {
				if($this->load_by_id($id)) {
					$query = sprintf("INSERT IGNORE INTO image_attrib(imageID, typeID, valueID) VALUES(%s, %s, %s);"
						, mysql_escape_string($id)
						, mysql_escape_string($categoryID)
						, mysql_escape_string($valueID)
					);

					$this->db->query($query);

					$query = sprintf("INSERT INTO `image_log` (action, image_id, after_desc, query, date_created) VALUES (10, '%s', 'Cat ID: %s, Attrib ID: %s', '%s', NOW());"
						, mysql_escape_string($id)
						, mysql_escape_string($categoryID)
						, mysql_escape_string($valueID)
						, mysql_escape_string($query)
					);
					$this->db->query($query);
				}
			}
			return true;
		} else {
			return false;
		}
	}

	public function deleteImageAttribute() {
		$imageIDs = @explode(',', $this->data['imageID']);
		$valueID = $this->data['valueID'];
		if(count($imageIDs)) {
			foreach($imageIDs as $id) {
				$query = sprintf("DELETE FROM `image_attrib` WHERE imageID = %s AND valueID IN (%s)"
				, mysql_escape_string($id)
				, mysql_escape_string($valueID)
				);
				$this->db->query($query);

				$query = sprintf("INSERT INTO `image_log` (action, image_id, after_desc, query, date_created) VALUES (11, '%s', 'Attrib ID: %s', '%s', NOW())"
				, mysql_escape_string($id)
				, mysql_escape_string($valueID)
				, mysql_escape_string($query)
				);
				$this->db->query($query);
			}
			return true;
		} else {
			return false;
		}
	}

	public function addCategory() {
		$id = 0;
		$value = $this->data['value'];
		$query = sprintf("INSERT INTO image_attrib_type(title) VALUES('%s')", mysql_escape_string($value));
		$this->db->query($query);
		$id = $this->db->insert_id;

		$query = sprintf("INSERT INTO `image_log` (action, after_desc, query, date_created) VALUES (4, 'ID: %s, Value: %s', '%s', NOW())"
		, mysql_escape_string($id)
		, mysql_escape_string($value)
		, mysql_escape_string($query)
		);
		$this->db->query($query);
		return( $id );
	}

	public function renameCategory() {
		$value = $this->data['value'];
		$valueID = $this->data['valueID'];
		$query = sprintf("UPDATE image_attrib_type set title = '%s' WHERE typeID = %s "
			, mysql_escape_string($value)
			, mysql_escape_string($valueID)
			);
		$this->db->query($query);

		$query = sprintf("INSERT INTO `image_log` (action, after_desc, query, date_created) VALUES (5, 'ID: %s, Value: %s', '%s', NOW())"
		, mysql_escape_string($valueID)
		, mysql_escape_string($value)
		, mysql_escape_string($query)
		);
		$this->db->query($query);
		return true;
	}

	public function deleteCategory() {
		$categoryID = $this->data['categoryID'];
		$query = sprintf("DELETE FROM `image_attrib` WHERE typeID = %s", mysql_escape_string($categoryID));
		$this->db->query($query);
		$query = sprintf("DELETE FROM `image_attrib_value` WHERE typeID = %s", mysql_escape_string($categoryID));
		$this->db->query($query);
		$query = sprintf("DELETE FROM `image_attrib_type` WHERE typeID = %s", mysql_escape_string($categoryID));
		$this->db->query($query);
		
		$query = sprintf("INSERT INTO `image_log` (action, after_desc, query, date_created) VALUES (6, 'Category ID: %s', '%s', NOW())", mysql_escape_string($categoryID), mysql_escape_string($query));
		$this->db->query($query);
		return true;
	}
	
	public function list_categories() {
		$query = "SELECT * FROM `image_attrib_type`";
		$records = $this->db->query_all($query);
		if(count($records)) {
			foreach($records as $record) {
				$tmpArray['typeID'] = $record->typeID;
				$tmpArray['title'] = $record->title;
				$data[] = $tmpArray;
			}
			return $data;
		} else {
			return false;
		}
	}
	
	public function category_exist($typeID) {
		$query = sprintf("SELECT * FROM `image_attrib_type` WHERE `typeID` = '%s'", mysql_escape_string($typeID));
		$records = $this->db->query_all($query);
		if(count($records)) {
			return true;
		} else {
			return false;
		}
	}

	public function addAttribute() {
		$id = 0;
		$query = sprintf("INSERT INTO image_attrib_value(name, typeID) VALUES('%s',%s);"
			, mysql_escape_string($this->data['value'])
			, mysql_escape_string($this->data['categoryID'])
		);
		
		$this->db->query($query);
		$id = $this->db->insert_id;
		$query = sprintf("INSERT INTO `image_log` (action, after_desc, query, date_created) VALUES (7, 'ID: %s, Value: %s, Category ID: %s', '%s', NOW())"
		, mysql_escape_string($id)
		, mysql_escape_string($this->data['value'])
		, mysql_escape_string($this->data['categoryID'])
		, mysql_escape_string($query)
		);
		$this->db->query($query);
		return($id);
	}

	public function renameAttribute() {
		$value = $this->data['value'];
		$valueID = $this->data['valueID'];
		$query = sprintf("UPDATE image_attrib_value set name = '%s' WHERE valueID = %s"
			, mysql_escape_string($value)
			, mysql_escape_string($valueID)
			);
		$this->db->query($query);

		$query = sprintf("INSERT INTO `image_log` (action, after_desc, query, date_created) VALUES (8, 'ID: %s, Value: %s', '%s', NOW())"
		, mysql_escape_string($valueID)
		, mysql_escape_string($value)
		, mysql_escape_string($query)
		);
		$this->db->query($query);
		return(true);
	}

	public function deleteAttribute() {
		$valueID = $this->data['valueID'];
		$query = sprintf("DELETE FROM `image_attrib` WHERE valueID = %s", mysql_escape_string($valueID));
		$this->db->query($query);
		$query = sprintf("DELETE FROM `image_attrib_value` WHERE valueID = %s", mysql_escape_string($valueID));
		$this->db->query($query);		
		$query = sprintf("INSERT INTO `image_log` (action, after_desc, query, date_created) VALUES (9, 'Attrib ID: %s', '%s', NOW())", mysql_escape_string($valueID), mysql_escape_string($query));
		$this->db->query($query);
		return true;
	}
	
	public function list_attributes ($typeID) {
		$query = sprintf("SELECT * FROM `image_attrib_value` WHERE `typeID` = '%s'", mysql_escape_string($typeID));
		$records = $this->db->query_all($query);
		if(count($records)) {
			foreach($records as $record) {
				$tmpArray['valueID'] = $record->valueID;
				$tmpArray['name'] = $record->name;
				$data[] = $tmpArray;
			}
			return $data;
		} else {
			return false;
		}
	}
	
	public function get_attributes ($typeID,$type = 'ID') {
		$type = (in_array(strtoupper($type), array('ID','TITLE'))) ? strtoupper($type) : 'ID';
		switch($type) {
			case 'TITLE':
				$query = sprintf("SELECT ia.* FROM `image_attrib_value` ia, `image_attrib_type` it  WHERE ia.`typeID` = it.`typeID` AND LOWER(it.`title`) = '%s'", mysql_escape_string(strtolower($typeID)));
				break;
			case 'ID':
			default:
				$query = sprintf("SELECT * FROM `image_attrib_value` WHERE `typeID` = '%s'", mysql_escape_string($typeID));
				break;
		}
		
		$records = $this->db->query_all($query);
		if(count($records)) {
			foreach($records as $record) {
				$tmpArray['valueID'] = $record->valueID;
				$tmpArray['name'] = $record->name;
				$data[] = $tmpArray;
			}
			return $data;
		} else {
			return false;
		}
	}
	
	public function attribute_exist($valueID) {
		$query = sprintf("SELECT * FROM `image_attrib_value` WHERE `valueID` = '%s'", mysql_escape_string($valueID));
		$records = $this->db->query_all($query);
		if(count($records)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function get_all_attributes($image_id) {
		$query = sprintf("SELECT ia.typeID iaTID, ia.valueID iaVID, iat.title iatTitle, iav.name iavValue FROM image_attrib ia LEFT OUTER JOIN image_attrib_type iat ON ( ia.typeID = iat.typeID ) JOIN image_attrib_value iav ON (iav.valueID = ia.valueID AND ia.imageID = '%s' ) ORDER BY ia.typeID", mysql_escape_string($image_id));
		$records = $this->db->query_all($query);
		if(count($records)) {
			$prevID = 0;
			foreach($records as $record) {
				if($prevID != $record->iaTID) {
					$prevID = $record->iaTID;
					if(isset($tmpArray3)) {
						$tmpArray1['values'] = $tmpArray3;
						$data[] = $tmpArray1;
						unset($tmpArray3);
					}
					$tmpArray1['id'] = $record->iaTID;
					$tmpArray1['key'] = $record->iatTitle;
				}
				$tmpArray2['id'] = $record->iaVID;
				$tmpArray2['value'] = $record->iavValue;
				$tmpArray3[] = $tmpArray2;
			}
			$tmpArray1['values'] = $tmpArray3;
			$data[] = $tmpArray1;
			return $data;
		} else {
			return false;
		}
	}
	
	public function exists_attrb_value_by_id($valueID) {
		if($valueID == '') return false;
		$query = sprintf("SELECT * FROM `image_attrib_value` WHERE `valueID` = '%s'", mysql_escape_string($valueID));
		$result = $this->db->query_all($query);
		if(count($result)) {
			return true;
		} else {
			return false;
		}
	}

	public function loadImageNodesCharacters() {
		unset($this->records);
		$this->nodes = array();
		$this->query = '';
		$this->cache = false;
	
		if(isset($this->data['nodeApi'])) {
			switch(@strtolower($this->data['nodeApi'])) {
			case "root":
				$parent = '';
	
				$this->query = "SELECT DISTINCT  it.typeID, it.title, iv.valueID, iv.name FROM image_attrib_type it, image_attrib_value iv, image_attrib ia WHERE it.typeID = iv.typeID AND ia.valueID = iv.valueID ORDER BY it.title, iv.name;";
				$records = $this->db->query_all($this->query);
				if(count($records)) {
					foreach($records as $record) {
						if ($parent != $record->title && $parent != '') {
						$this->nodes[] = array('text'=>$old_title, 'nodeApi'=>'cateogry', 'iconCls'=>'icon_folder_picture', 'cls'=>'tree_panel', 'nodeValue'=>$record->typeID, 'children'=>$children);
						$children = '';
						}
						$children[] = array('id'=>'char_' . $record->valueID, 'id'=>'char_' . $record->valueID, 'text'=>$record->name, 'nodeApi'=>'character', 'checked'=>false, 'leaf'=>true, 'nodeValue'=>$record->valueID);
						
						if ($parent != $record->title) {
							$parent = $record->title;
						}
						
						$old_title = $record->title;
					}
				}
				$this->nodes[] = array('text'=>$old_title, 'nodeApi'=>'cateogry', 'iconCls'=>'icon_folder_picture', 'cls'=>'tree_panel', 'nodeValue'=>$record->typeID, 'children'=>$children);
		
				break;
			}
			return $this->nodes;
		} else {
			return false;
		}
	}

	public function loadImageNodesImages() {
	
		unset($this->records);
		$this->nodes = array();
		$this->query = '';
		if(isset($this->data['nodeApi'])) {
		switch(@strtolower($this->data['nodeApi'])) {
	
		case "root":
			$children='';
			for ($i=65;$i<91;$i++) {
				$tmp = chr($i);
				$children[] = array('text'=>$tmp, 'iconCls'=>'icon_folder_picture', 'nodeApi'=>'families', 'nodeValue'=>$tmp);
			}
			
			$this->nodes[] = array('text'=>"by Family", 'iconCls'=>'icon_folder_picture', 'expanded'=>false, 'nodeApi'=>'alpha', 'nodeValue'=>'alpha', 'children'=> $children);
	
			$children='';
			for ($i=65;$i<91;$i++) {
				$tmp = chr($i);
				$children[] = array('text'=>$tmp, 'iconCls'=>'icon_folder_picture', 'nodeApi'=>'genera', 'nodeValue'=>$tmp);
			}
	
			$this->nodes[] = array('text'=>"by Genus", 'iconCls'=>'icon_folder_picture', 'expanded'=>false, 'nodeApi'=>'alpha', 'nodeValue'=>'alpha', 'children'=> $children);
	
			break;
	
		case "alpha":
			for ($i=65;$i<91;$i++) {
				$tmp = chr($i);
			}
			$this->nodes[] = array('text'=>$tmp, 'iconCls'=>'icon_folder_picture', 'draggable'=>false, 'nodeApi'=>'families', 'nodeValue'=>$tmp);
			break;
	
		case "families":
	
			$this->query = sprintf( "SELECT Family, count(Family) as family_size FROM image WHERE Family like '%s%%' GROUP by Family ORDER by Family", mysql_escape_string($this->data['nodeValue']) );
	
			try {
				$records = $this->db->query_all($this->query);
			} catch (Exception $e) {
				trigger_error($e->getMessage(),E_USER_ERROR);
			}
	
			if(count($records)) {
				foreach($records as $record) {
					$this->nodes[] = array('text'=>$record->Family . " (" . number_format($record->family_size) . ")", 'imageCount' => $record->family_size, 'family'=>$record->Family, 'iconCls'=>'icon_picture', 'checked'=>false, 'nodeApi'=>'family', 'nodeValue'=>$record->Family);
	
				}
			}
			break;
	
		case "family":
				
			if( trim($this->data['nodeValue']) == '' ) {
				$this->query = "SELECT Genus, count(Genus) as genus_size FROM image GROUP by Genus ORDER by Genus";
			} else {
				$this->query = sprintf( "SELECT Genus, count(Genus) as genus_size FROM image WHERE Family = '%s' GROUP by Genus ORDER by Genus ", mysql_escape_string($this->data['nodeValue']) );
			}
	
			try {
				$records = $this->db->query_all($this->query);
			} catch (Exception $e) {
				trigger_error($e->getMessage(),E_USER_ERROR);
			}
	
			if(count($records)) {
				foreach($records as $record) {
					$this->nodes[] = array('text'=>$record->Genus . " (" . $record->genus_size . ")", 'imageCount' => $record->genus_size, 'id'=>$record->Genus, 'family'=>$this->data['family'], 'genus'=>$record->Genus, 'iconCls'=>'icon_picture', 'checked'=>false, 'draggable'=>false, 'isTarget'=>false, 'nodeApi'=>'genus', 'nodeValue'=>$record->Genus);
	
				}
			}
			break;
	
		case "genera":
			$this->query = sprintf( "SELECT Genus, count(Genus) as genus_size FROM image WHERE Genus like '%s%%' GROUP by Genus ORDER by Genus", mysql_escape_string($this->data['nodeValue']) );
			try {
				$records = $this->db->query_all($this->query);
			} catch (Exception $e) {
				trigger_error($e->getMessage(),E_USER_ERROR);
			}
	
			if(count($records)) {
				foreach($records as $record) {
					$this->nodes[] = array('text'=>$record->Genus . " (" . number_format($record->genus_size) . ")", 'imageCount' => $record->genus_size, 'genus'=>$record->Genus, 'iconCls'=>'icon_picture', 'checked'=>false, 'nodeApi'=>'genus', 'nodeValue'=>$record->Genus);
	
				}
			}
			break;
			
		case "genus":
				
			if( trim($this->data['nodeValue']) == '' ) {
				$this->query = "SELECT SpecificEpithet, count(SpecificEpithet) as species_size FROM image GROUP by SpecificEpithet ORDER by SpecificEpithet";
			} else {
				$this->query = sprintf( "SELECT SpecificEpithet, count(SpecificEpithet) as species_size FROM image WHERE Genus = '%s' GROUP by SpecificEpithet ORDER by SpecificEpithet", mysql_escape_string($this->data['nodeValue']) );
			}
	
			try {
				$records = $this->db->query_all($this->query);
			} catch (Exception $e) {
				trigger_error($e->getMessage(),E_USER_ERROR);
			}
	
			if(count($records)) {
				foreach($records as $record) {
					$this->nodes[] = array('text'=>$record->SpecificEpithet . " (" . $record->species_size . ")", 'imageCount' => $record->species_size, 'id'=>$record->SpecificEpithet, 'family'=>$this->data['family'], 'genus'=>$this->data['genus'], 'species'=>$record->SpecificEpithet, 'iconCls'=>'icon_picture', 'checked'=>false, 'leaf'=>true, 'draggable'=>false, 'isTarget'=>false, 'nodeApi'=>'species', 'nodeValue'=>$record->SpecificEpithet, 'genus'=>$this->data['nodeValue']);
	
				}
			}
			break;
	
		case 'scientificname':
				
			if( trim($this->data['nodeValue']) == '' ) {
				$this->query = "SELECT concat(Genus, ' ', SpecificEpithet) as name, count(SpecificEpithet) as sz, Family, Genus, SpecificEpithet  FROM image  GROUP by Genus, SpecificEpithet ORDER by Genus, SpecificEpithet";
			} else {
				$this->query = sprintf( "SELECT concat(Genus, ' ', SpecificEpithet) as name, count(SpecificEpithet) as sz, Family, Genus, SpecificEpithet  FROM image WHERE Family = '%s'  GROUP by Genus, SpecificEpithet ORDER by Genus, SpecificEpithet", mysql_escape_string($this->data['nodeValue']) );
			}
	
			try {
				$records = $this->db->query_all($this->query);
			} catch (Exception $e) {
				trigger_error($e->getMessage(),E_USER_ERROR);
			}
	
			if(count($records)) {
				foreach($records as $record) {
					$this->nodes[] = array('text'=>$record->name . " (" . $record->sz . ")", 'imageCount' => $record->sz, 'id'=>$record->name, 'family'=>$record->Family, 'genus'=>$record->Genus, 'species'=>$record->SpecificEpithet, 'iconCls'=>'icon_picture', 'checked'=>false, 'leaf'=>true, 'draggable'=>false, 'isTarget'=>false, 'nodeApi'=>'ScientificName', 'nodeValue'=>$record->name);
				}
			}
	
			break;
	
		}
			return $this->nodes;
		} else {
			return false;
		}
	
	}

	public function loadCharacterList() {
		unset($this->records);
		$this->query = "SELECT I.image_id";
		$this->setFilters();

		if (($this->data['characters'] != '') && ($this->data['characters'] != '[]')) {
			$this->query .= " GROUP BY I.image_id HAVING sz >= " . ( $this->char_count - 1 );
		}

		$this->query = "SELECT valueID as id FROM image_attrib t1 INNER JOIN  (" . $this->query . ") AS t2 ON t1.imageID = t2.image_id GROUP BY t1.valueID ORDER BY t1.valueID;";
		try {
			$this->records = $this->db->query_all($this->query);
		} catch (Exception $e) {
			trigger_error($e->getMessage(),E_USER_ERROR);
		}

		if(!count($this->records)) {
			$this->records = array();
		}
		return $this->records;
	}

	public function loadImageList() {
		unset($this->records);
		$this->records = array();
		$this->query = '';

		$this->query = "SELECT I.`image_id` AS imageID, I.`filename` AS filename, I.`Family`, I.`Genus`, I.`SpecificEpithet`, I.`zoomEnabled`, I.`gTileProcessed`, I.`timestamp_modified`, I.`characters`, I.`barcode`, I.`GlobalUniqueIdentifier` ";
		$this->queryCount = ' SELECT count(*) AS sz ';
		$this->setFilters();

		if (($this->data['characters'] != '') && ($this->data['characters'] != '[]')) {
			$tstr = " GROUP BY I.image_id HAVING sz >= " . $this->char_count;
			$this->query .= $tstr;
			$this->queryCount .= $tstr;
		}

		if (($this->data['sort'] != '')) {
			$sort = $this->data['sort'];
			if ($sort == "GUID") $sort = "GlobalUniqueIdentifier";
			$this->query .= sprintf(" ORDER BY I.%s %s", $sort, $this->data['dir']);
		} else {
			$this->query .= " ORDER BY I.Family, I.Genus, I.SpecificEpithet, I.rank ";
		}
		
		if ($this->data['start'] && $this->data['limit']) {
			$this->query .= " LIMIT " . stripslashes($this->data['start']) . ", " . stripslashes($this->data['limit']);	
		} elseif ($this->data['limit']) {
			$this->query .= " LIMIT " . stripslashes($this->data['limit']);
		}
		try {
			$records = $this->db->query_all($this->query);
		} catch (Exception $e) {
			trigger_error($e->getMessage(),E_USER_ERROR);
		}

		if (($this->data['characters'] != '') && ($this->data['characters'] != '[]')) {
			$this->queryCount = "SELECT count(sz) as sz FROM (" . $this->queryCount . ") as x1";
		}

		$resCount = $this->db->query_one($this->queryCount);
		$this->total = $resCount->sz;
		
		if(count($records)){
			foreach($records as $record) {
				$this->records[] = $record;
			}
		}
		return $this->records;
	}

	public function loadImageDetails() {
		global $config;
		$ret = array();
		unset($this->records);
		$this->nodes = array();
		$this->query = '';
		if($this->field_exists($this->data['image_id'])) {
			$query = sprintf("SELECT IAT.title as attrib, IAV.name as value FROM image_attrib IA, image_attrib_type IAT, image_attrib_value IAV WHERE IA.typeID = IAT.typeID AND IA.valueID = IAV.valueID AND IA.imageID = %s ORDER BY IAT.title, IAV.name", mysql_escape_string($this->data['image_id']));
	
			try {
				$records = $this->db->query_all($query);
			} catch (Exception $e) {
				trigger_error($e->getMessage(),E_USER_ERROR);
			}
	
			$attrib = '';
			if(count($records)) {
				foreach ($records as $record) {
					if ($attrib != $record->attrib) {
						if ($attrib != '') {
							$attributes[] = array('attrib'=>$attrib, 'value'=> substr($values, 0,-2));
							$values = '';
						}
						$attrib = $record->attrib;
					}
					$values .= $record->value . ", ";
				}
			}
			if ($attrib != '') {
				$attributes[] = array('attrib'=>$attrib, 'value'=> substr($values, 0,-2));
			} else {
				$attributes[] = array('attrib'=>'Note', 'value'=>'This image has not been tagged.');
			}
			unset($record);
	
			$this->load_by_id($this->data['image_id']);
			$barcode = $this->getName();
			$path = $config['path']['images'] . $this->barcode_path( $barcode ) . $this->get('filename');
			$record = $this->record;
			$record['attributes'] = $attributes;
			$record['exif'] = @exif_read_data( $path );
			$ret['status'] = true;
			$ret['record'] = $record;
		} else {
			$ret['status'] = false;
			$ret['error'] = 116;
		}
		return $ret;
	}
	
	public function getUrl($image_id) {
		$this->load_by_id($image_id);
		$storage = new Storage($this->db);
		$device = $storage->get($this->get('storage_id'));
		$url['url'] = $device['baseUrl'];
		switch(strtolower($device['type'])) {
			case 's3':
				$tmp = $this->get('path');
				$tmp = substr($tmp, 0, 1)=='/' ? substr($tmp, 1, strlen($tmp)-1) : $tmp;
				$url['baseUrl'] = $url['url'] . $tmp . '/';
				$url['url'].= $tmp . '/' . $this->get('filename');
				break;
			case 'local':
				if(substr($url['url'], strlen($url['url'])-1, 1) == '/') {
					$url['url'] = substr($url['url'],0,strlen($url['url'])-1);
				}
				$url['baseUrl'] = $url['url'] . $this->get('path') . '/';
				$url['url'].= $this->get('path'). '/' .$this->get('filename');
				break;
		}
		$url['filename'] = $this->get('filename');
		return $url;
	}
	
	public function image_exists($storage_id, $imagePath, $filename) {
		$storage = new Storage($this->db);
		$device = $storage->get($storage_id);
		$path = $device['baseUrl'];
		switch(strtolower($device['type'])) {
			case 's3':
				$tmp = $imagePath;
				$tmp = substr($tmp, 0, 1)=='/' ? substr($tmp, 1, strlen($tmp)-1) : $tmp;
				$path.= $tmp . '/' . $filename;
				break;
			case 'local':
				if(substr($path, strlen($path)-1, 1) == '/' ) {
					$path = substr($path, 0, strlen($path)-1);
				}
				$path.= $imagePath . '/' . $filename;
				break;
		}
		$path = str_replace(' ', '+', $path);
		$f = @fopen($path, "r");
		if($f) {
			fclose($f);
			return true;
		} else {
			return false;
		}
	}
	
	public function importMetaDataPackage($data) {
		// if((!is_array($data)) || (count($data)!=4)) return false;
		if(!is_array($data)) return false;
		$query = sprintf("INSERT IGNORE INTO `image_attrib_type` SET `title` = '%s', `description` = '%s', `elementSet` = '%s', `term` = '%s'"
				, mysql_escape_string($data[2])
				, mysql_escape_string($data[3])
				, mysql_escape_string($data[0])
				, mysql_escape_string($data[1])
				);
		if($this->db->query($query)) {
			return true;
		} else {
			return false;
		}
	}

	public function getNonENProcessedRecords($filter='') {
		if($filter['collection']=='')
			$query = " SELECT * FROM `image` WHERE `barcode` NOT IN (SELECT `barcode` from `specimen2label`) ";
		else
			$query = sprintf(" SELECT * FROM `image` WHERE `barcode` NOT IN (SELECT `barcode` from `specimen2label`) AND `CollectionCode` = '%s' ", mysql_escape_string($filter['collection']) );
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		return ($this->db->query($query));
	}
	
	public function updateImageRating($image_id = '', $rating = '') {
		if($image_id == '' ||  $rating == '') return false;
		$query = sprintf(" UPDATE `image` SET `rating` = '%s' WHERE `image_id` = '%s'; ", mysql_escape_string($rating), mysql_escape_string($image_id));
		return ($this->db->query($query)) ? true : false;
	}
}
?>