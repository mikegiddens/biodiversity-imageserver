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

	public function imageSetFullPath( $file ){
		$parts = explode('/', $file);
		if ( count($parts) == 1 ) {
			$parts = explode('\\', $file);
		}
		$filename = $parts[count($parts) - 1];
		unset($parts[count($parts) - 1]);
		$path = implode('/', $parts) . "/";
		$this->imageSetProperty('fileName', $filename);
		$this->imageSetProperty('path', $path);
	}

	public function imageGetName( $field = 'name' ) {
		if ($field == 'name' || $field == 'ext') {
			$ext = explode('.', $this->imageGetProperty('fileName'));
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
	public function imageSetData($data) {
		$this->data = $data;
		return(true);
	}

	/**
	* Returns all the values in the record
	* @return mixed
	*/
	public function imageGetAllProperties() {
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
	public function imageGetProperty( $field ) {
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
	public function imageSetProperty( $field, $value ) {
		$this->record[$field] = $value;
		return(true);
	}

	public function imageLoadByBarcode( $barcode ) {
		if($barcode == '') return(false);
		$query = sprintf("SELECT * FROM `image` WHERE `barcode` = '%s'", mysql_escape_string($barcode) );
		try {
		$ret = $this->db->query_one( $query );
		} catch (Exception $e) {
			trigger_error($e->getMessage(),E_USER_ERROR);
		}
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->imageSetProperty($field, $value);
			}
			return(true);
		} else {
			return(false);
		}
	}

	public function imageLoadById( $imageId ) {
		if($imageId == '') return false;
		$query = sprintf("SELECT * FROM `image` WHERE `imageId` = %s", mysql_escape_string($imageId) );
		try {
		$ret = $this->db->query_one( $query );
		} catch (Exception $e) {
			trigger_error($e->getMessage(),E_USER_ERROR);
		}
		if ($ret != NULL) {
			foreach( $ret as $field => $value ) {
				$this->imageSetProperty($field, $value);
			}
			return(true);
		} else {
			return(false);
		}
	}

	public function imageMoveToImages() {
		global $config;
		$barcode = $this->imageGetName();
		$tmpPath = $config['path']['images'] . $this->imageBarcodePath( $barcode );
		$this->mkdir_recursive( $tmpPath );
		$flsz = @filesize($this->imageGetProperty('path') . $this->imageGetProperty('fileName'));
		if(!$flsz) {
			if(!@rename( $this->imageGetProperty('path') . $this->imageGetProperty('fileName'), $config['path']['error'] . $this->imageGetProperty('fileName') )) {
				return array('success' => false, 'code' => 140);
			}
			return array('success' => false);
		}
		if(@rename( $this->imageGetProperty('path') . $this->imageGetProperty('fileName'), $tmpPath . $this->imageGetProperty('fileName') )) {
			$this->imageSetProperty('path',$tmpPath);
			return array('success' => true);
		} else {
			return array('success' => false, 'code' => 141);
		}
	}

	public function imageBarcodePath( $barcode ) {
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

	public function imageMkdirRecursive( $pathname ) {
		is_dir(dirname($pathname)) || $this->imageMkdirRecursive(dirname($pathname));
		return is_dir($pathname) || @mkdir($pathname, 0775);
	}

	function imageRmdirRecursive($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") $this->imageRmdirRecursive($dir."/".$object); else unlink($dir."/".$object);
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}

	function imageCreateThumbnail( $tmp_path, $new_width, $new_height, $postfix = '', $display_flag=false ) {
		$extension = '.' . $this->imageGetName('ext');
		$func = 'imagecreatefrom' . (@strtolower($this->imageGetName('ext')) == 'jpg' ? 'jpeg' : @strtolower($this->imageGetName('ext')));
		$im = @$func($tmp_path);
		if($im !== false) {
			$image_file = $this->imageGetProperty("path") . $this->imageGetName() . $postfix . $extension;
			$width = imageSX($im);
			$height = imageSY($im);
			$image_file = ($display_flag)?NULL:$image_file;
			$this->imageResize($new_width, $new_height, $im, $image_file, $width, $height);
			ImageDestroy($im); // Remove tmp Image Object
		}
	}

	function imageCreateThumbnailIMagik( $tmp_path, $new_width, $new_height, $postfix = '' ) {
		$extension = '.' . $this->imageGetName('ext');
		$destination = $this->imageGetProperty("path") . $this->imageGetName() . $postfix . $extension;
		$tmp = sprintf("convert %s -resize %sx%s %s", $tmp_path,$new_width,$new_height,$destination);
		$res = system($tmp);
	}

	function imageCreateThumb( $tmp_path, $new_width, $new_height, $postfix = '', $display_flag=false, $type='jpg') {
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
				$this->imageResize($new_width, $new_height, $im, $image_file, $width, $height);
				ImageDestroy($im); // Remove tmp Image Object
			}
		}
	}

	/**
	 * Creates the Thumbnails for the image using IM/GD for s3 mode
	 * @param string barcode
	 * @param mixed s3 details and object
	 */
	function createThumbS3($imageId,$arr,$deleteFlag = true) {
		global $config;
		$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';

		if($this->imageLoadById($imageId)) {
			$filName = 'Img_' . time();
			$fname = explode(".", $this->imageGetProperty('fileName'));
			$tmpThumbPath = $_TMP . $filName . $arr['postfix'] . '.' . $fname[1];
			$thumbName = $this->imageGetProperty('path') .'/'. $fname[0] . $arr['postfix'] . '.' . $fname[1];
			$thumbName = (substr($thumbName,0,1)=='/')? substr($thumbName,1,strlen($thumbName)-1) : $thumbName;
			$tmpPath = $_TMP . $filName . '.' . $fname[1];

			$fp = fopen($tmpPath, "w+b");

			# getting the image from s3
			$bucket = $arr['s3']['bucket'];
			$key = $this->imageGetProperty('path') .'/'. $this->imageGetProperty('fileName');
			$key = (substr($key,0,1)=='/')? substr($key,1,strlen($key)-1) : $key;
			$rr = $arr['obj']->get_object($bucket, $key, array('fileDownload' => $tmpPath));

			$this->imageCreateThumb($tmpPath, $arr['width'], $arr['height'], $arr['postfix']);
 			
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

	function createFromFileS3($tmpPath,$imageId,$arr,$deleteFlag = false) {
		if(!@file_exists($tmpPath)) return false;
		$dtls = @pathinfo($tmpPath);
		$extension = '.' . $dtls['extension'];
		$tmpThumbPath =  $dtls['dirname'] . '/' . $dtls['filename'] . $arr['postfix'] . $extension;
		$fname = explode(".", $this->imageGetProperty('fileName'));
		$thumbName = $this->imageGetProperty('path') . '/' . $fname[0] . $arr['postfix'] . '.' . $fname[1];
		$thumbName = (substr($thumbName,0,1)=='/')? substr($thumbName,1,strlen($thumbName)-1) : $thumbName;

		# uploading thumb to s3
		$this->imageCreateThumb($tmpPath, $arr['width'], $arr['height'],$arr['postfix']);
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
    function imageResize($x,$y,$im,$path=NULL,$width,$height) {
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
		if($this->data['imageId'] != '') {
			$flag = $this->imageLoadById($this->data['imageId']);
		}
		if(!$flag && $this->data['barcode'] != '') {
			$flag = $this->imageLoadByBarcode($this->data['barcode']);
		}
		if(!$flag) return array('success' => false, 'code' => 135);
		
		$fname = explode(".", $this->imageGetProperty('fileName'));
		$ext = ($this->data['type']==''?@strtolower($this->imageGetName('ext')):$this->data['type']);
		$extension = '.' . $ext;
		$func1 = 'image' . ($ext == 'jpg' ? 'jpeg' : $ext);
		$content_type = 'image/' . ($ext == 'jpg' ? 'jpeg' : $ext);
		$size = @strtolower($this->data['size']);
		//$path = $config['path']['images'] . substr($this->imageGetProperty('path'), 1, strlen($this->imageGetProperty('path'))-1);
		//$image =  $path . $fname[0] . $extension;
		$existsFlag = false;
		//$bucket = $config['s3']['bucket'];
		$tmpPath = $_TMP . $this->imageGetProperty('fileName');
		
		$storage = new Storage($this->db);
		$device = $storage->get($this->imageGetProperty('storage_id'));
		$bucket = $device['basePath'];
		$path = $device['basePath'] . $this->imageGetProperty('path');
		$image =  $path .'/'. $this->imageGetProperty('fileName');
		
		# checking if exists
		if(in_array(strtolower($size),array('s', 'm', 'l'))) {
			if(strtolower($device['type']) == 's3') {
				$key = $this->imageGetProperty('path') .'/'. $fname[0] . '_' . $size . $extension;
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

		$ext = @strtolower($this->imageGetName('ext'));
		$extension = '.' . $ext;
		
		# Image variation does not exist
		if(strtolower($device['type']) == 's3') {
			# downloading original image
			$key = $this->imageGetProperty('path') .'/'. $fname[0] . $extension;
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
			$type = ($this->data['type']==''?@strtolower($this->imageGetName('ext')):$this->data['type']);
			$extension = '.' . ($type == 'jpg' ? 'jpeg' : $type);
			$file_name =  $dtls['dirname'] . '/' . $dtls['filename'] . '_' . $size . $extension;

			switch($size) {
				case 's':
					$this->imageCreateThumb( $tmpPath, 100, 100, '_s', false, $type);
					break;
				case 'm':
					$this->imageCreateThumb( $tmpPath, 275, 275, "_m", false, $type);
					break;
				case 'l':
					$this->imageCreateThumb( $tmpPath, 800, 800, "_l", false, $type);
					break;
				case 'custom':
					$width = ($this->data['width']!='') ? $this->data['width'] : $this->data['height'];
					$height = ($this->data['height']!='') ? $this->data['height'] : $this->data['width'];
					$this->imageCreateThumb( $tmpPath, $width, $height, 'tmp', true, $type);
					break;
			}
			
			if(strtolower($device['type']) == 's3') {
				# putting the image to s3
				$key = $this->imageGetProperty('path') .'/'. $fname[0] . '_' . $size . $extension;
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

	public function imageGetId($filename, $filepath, $storage_id) {
		if($filename == '' || $filepath == '' || $storage_id == '') return false;
		$query = sprintf("SELECT `imageId` FROM `image` WHERE `originalFilename` = '%s' AND `path` = '%s' AND `storageId` = '%s';", $filename, $filepath, $storage_id);
		$ret = $this->db->query_one($query);
		if($ret->imageId == NULL) {
			return false;
		} else {
			return $ret->imageId;
		}
	}

	/**
	 * checks whether field exists in image table
	 */
	public function imageFieldExists ($imageId){
		if($imageId == '' || is_null($imageId)) return(false);

		$query = sprintf("SELECT `imageId` FROM `image` WHERE `imageId` = %s;", $imageId );
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
	public function imageBarcodeExists ($barcode,$returnFlag = false){
		$query = sprintf("SELECT `imageId` FROM `image` WHERE `barcode` = '%s';", $barcode );
		$ret = $this->db->query_one( $query );
		if ($ret == NULL) {
			return false;
		} else {
			if($returnFlag) {
				return $ret->imageId;
			} else {
				return true;
			}
		}
	}

	public function save() {
		if($this->imageFieldExists($this->imageGetProperty('imageId'))) {
			$query = sprintf("UPDATE `image` SET  `fileName` = '%s', `timestampModified` = now(), `barcode` = '%s', `width` = '%s', `height` = '%s', `family` = '%s', `genus` = '%s', `specificEpithet` = '%s', `rank` = '%s', `author` = '%s', `title` = '%s', `description` = '%s', `globalUniqueIdentifier` = '%s', `creativeCommons` = '%s', `characters` = '%s', `flickrPlantId` = '%s', `flickrModified` = '%s', `flickrDetails` = '%s', `picassaPlantId` = '%s', `picassaModified` = '%s', `gTileProcessed` = '%s', `zoomEnabled` = '%s', `processed` = '%s', `boxFlag` = '%s', `ocrFlag` = '%s', `ocrValue` = '%s', `nameFinderFlag` = '%s', `nameFinderValue` = '%s', `scientificName` = '%s', `collectionCode` = '%s', `tmpFamily` = '%s', `tmpFamilyAccepted` = '%s', `tmpGenus` = '%s', `tmpGenusAccepted` = '%s', `guessFlag` = '%s', `storageId` = '%s', `path` = '%s', `originalFilename` = '%s', `remoteAccessKey` = '%s', `statusType` = '%s', `rating` = '%s'  WHERE imageId = '%s' ;"
				, mysql_escape_string($this->imageGetProperty('fileName'))
				, mysql_escape_string($this->imageGetProperty('barcode'))
				, mysql_escape_string($this->imageGetProperty('width'))
				, mysql_escape_string($this->imageGetProperty('height'))
				, mysql_escape_string($this->imageGetProperty('family'))
				, mysql_escape_string($this->imageGetProperty('genus'))
				, mysql_escape_string($this->imageGetProperty('specificEpithet'))
				, mysql_escape_string($this->imageGetProperty('rank'))
				, mysql_escape_string($this->imageGetProperty('author'))
				, mysql_escape_string($this->imageGetProperty('title'))
				, mysql_escape_string($this->imageGetProperty('description'))
				, mysql_escape_string($this->imageGetProperty('globalUniqueIdentifier'))
				, mysql_escape_string($this->imageGetProperty('creativeCommons'))
				, mysql_escape_string($this->imageGetProperty('characters'))
				, mysql_escape_string($this->imageGetProperty('flickrPlantId'))
				, mysql_escape_string($this->imageGetProperty('flickrModified'))
				, mysql_escape_string($this->imageGetProperty('flickrDetails'))
				, mysql_escape_string($this->imageGetProperty('picassaPlantId'))
				, mysql_escape_string($this->imageGetProperty('picassaModified'))
				, mysql_escape_string($this->imageGetProperty('gTileProcessed'))
				, mysql_escape_string($this->imageGetProperty('zoomEnabled'))
				, mysql_escape_string($this->imageGetProperty('processed'))
				, mysql_escape_string($this->imageGetProperty('boxFlag'))
				, mysql_escape_string($this->imageGetProperty('ocrFlag'))
				, mysql_escape_string($this->imageGetProperty('ocrValue'))
				, mysql_escape_string($this->imageGetProperty('nameFinderFlag'))
				, mysql_escape_string($this->imageGetProperty('nameFinderValue'))
				, mysql_escape_string($this->imageGetProperty('scientificName'))
				, mysql_escape_string($this->imageGetProperty('collectionCode'))
				, mysql_escape_string($this->imageGetProperty('tmpFamily'))
				, mysql_escape_string($this->imageGetProperty('tmpFamilyAccepted'))
				, mysql_escape_string($this->imageGetProperty('tmpGenus'))
				, mysql_escape_string($this->imageGetProperty('tmpGenusAccepted'))
				, mysql_escape_string($this->imageGetProperty('guessFlag'))
				, mysql_escape_string($this->imageGetProperty('storageId'))
				, mysql_escape_string($this->imageGetProperty('path'))
				, mysql_escape_string($this->imageGetProperty('originalFilename'))
				, mysql_escape_string($this->imageGetProperty('remoteAccessKey'))
				, mysql_escape_string($this->imageGetProperty('statusType'))
				, mysql_escape_string($this->imageGetProperty('rating'))
				, mysql_escape_string($this->imageGetProperty('imageId'))
			);
		} else {
			$query = sprintf("INSERT IGNORE INTO `image` SET `filename` = '%s', `timestampModified` = now(), `barcode` = '%s', `width` = '%s', `height` = '%s', `family` = '%s', `genus` = '%s', `specificEpithet` = '%s', `rank` = '%s', `author` = '%s', `title` = '%s', `description` = '%s', `globalUniqueIdentifier` = '%s', `creativeCommons` = '%s', `characters` = '%s', `flickrPlantId` = '%s', `flickrModified` = '%s', `flickrDetails` = '%s', `picassaPlantId` = '%s', `picassaModified` = '%s', `gTileProcessed` = '%s', `zoomEnabled` = '%s', `processed` = '%s', `boxFlag` = '%s', `ocrFlag` = '%s', `ocrValue` = '%s', `nameFinderFlag` = '%s', `nameFinderValue` = '%s', `scientificName` = '%s', `collectionCode` = '%s', `tmpFamily` = '%s', `tmpFamilyAccepted` = '%s', `tmpGenus` = '%s', `tmpGenusAccepted` = '%s', `guessFlag` = '%s', `storageId` = '%s', `path` = '%s', `originalFilename` = '%s', `remoteAccessKey` = '%s', `statusType` = '%s', `rating` = '%s' ;"
				, mysql_escape_string($this->imageGetProperty('fileName'))
				, mysql_escape_string($this->imageGetProperty('barcode'))
				, mysql_escape_string($this->imageGetProperty('width'))
				, mysql_escape_string($this->imageGetProperty('height'))
				, mysql_escape_string($this->imageGetProperty('family'))
				, mysql_escape_string($this->imageGetProperty('genus'))
				, mysql_escape_string($this->imageGetProperty('specificEpithet'))
				, mysql_escape_string($this->imageGetProperty('rank'))
				, mysql_escape_string($this->imageGetProperty('author'))
				, mysql_escape_string($this->imageGetProperty('title'))
				, mysql_escape_string($this->imageGetProperty('description'))
				, mysql_escape_string($this->imageGetProperty('globalUniqueIdentifier'))
				, mysql_escape_string($this->imageGetProperty('creativeCommons'))
				, mysql_escape_string($this->imageGetProperty('characters'))
				, mysql_escape_string($this->imageGetProperty('flickrPlantId'))
				, mysql_escape_string($this->imageGetProperty('flickrModified'))
				, mysql_escape_string($this->imageGetProperty('flickrDetails'))
				, mysql_escape_string($this->imageGetProperty('picassaPlantId'))
				, mysql_escape_string($this->imageGetProperty('picassaModified'))
				, mysql_escape_string($this->imageGetProperty('gTileProcessed'))
				, mysql_escape_string($this->imageGetProperty('zoomEnabled'))
				, mysql_escape_string($this->imageGetProperty('processed'))
				, mysql_escape_string($this->imageGetProperty('boxFlag'))
				, mysql_escape_string($this->imageGetProperty('ocrFlag'))
				, mysql_escape_string($this->imageGetProperty('ocrValue'))
				, mysql_escape_string($this->imageGetProperty('nameFinderFlag'))
				, mysql_escape_string($this->imageGetProperty('nameFinderValue'))
				, mysql_escape_string($this->imageGetProperty('scientificName'))
				, mysql_escape_string($this->imageGetProperty('collectionCode'))
				, mysql_escape_string($this->imageGetProperty('tmpFamily'))
				, mysql_escape_string($this->imageGetProperty('tmpFamilyAccepted'))
				, mysql_escape_string($this->imageGetProperty('tmpGenus'))
				, mysql_escape_string($this->imageGetProperty('tmpGenusAccepted'))
				, mysql_escape_string($this->imageGetProperty('guessFlag'))
				, mysql_escape_string($this->imageGetProperty('storageId'))
				, mysql_escape_string($this->imageGetProperty('path'))
				, mysql_escape_string($this->imageGetProperty('originalFilename'))
				, mysql_escape_string($this->imageGetProperty('remoteAccessKey'))
				, mysql_escape_string($this->imageGetProperty('statusType'))
				, mysql_escape_string($this->imageGetProperty('rating'))
			);
		}
// echo '<br> Query : ' . $query;
		if($this->db->query($query)) {
			return(true);
		} else {
			return (false);
		}
	}

	public function imageGetNameFinderRecords($filter = '') {
		$query = " SELECT * FROM `image` WHERE ( `nameFinderFlag` = 0 OR `nameFinderFlag` IS NULL ) AND `ocrFlag` = 1 ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		$ret = $this->db->query($query);
		return($ret);
	}

	public function imageGetOcrRecords($filter = '') {
		$query = " SELECT * FROM `image` WHERE ( `ocrFlag` = 0 OR `ocrFlag` IS NULL ) AND `processed` = 1 ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		$ret = $this->db->query($query);
		return($ret);
	}

	public function imageGetGuessTaxaRecords($filter = '') {
		$query = " SELECT * FROM `image` WHERE ( `guessFlag` = 0 OR `guessFlag` IS NULL ) AND `processed` = 1 ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		$ret = $this->db->query($query);
		return($ret);
	}

	public function imageGetBoxRecords($filter = '') {
		$query = " SELECT * FROM `image` WHERE ( `boxFlag` = 0 OR `boxFlag` IS NULL ) ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		$ret = $this->db->query($query);
		return($ret);
	}

	public function imageGetFlickrRecords($filter = '') {
		$query = " SELECT * FROM `image` WHERE `flickrPlantId` = 0 OR `flickrPlantId` IS NULL ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		$ret = $this->db->query($query);
		return($ret);
	}

	public function imageGetPicassaRecords($filter = '') {
		$query = " SELECT * FROM `image` WHERE `picassaPlantId` = 0 OR `picassaPlantId` IS NULL ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		return ($this->db->query($query));
	}

	/**
	 * Return image records yet to be gTileProcessed
	 * @return mysql resultset
	 */
	public function imageGetGTileRecords($filter='') {
		$query = " SELECT * FROM `image` WHERE `gTileProcessed` = 0 OR `gTileProcessed` IS NULL ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		return ($this->db->query($query));
	}

	public function imageGetZoomifyRecords($filter='') {
		$query = " SELECT * FROM `image` WHERE `zoomEnabled` = 0 OR `zoomEnabled` IS NULL ";
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		return ($this->db->query($query));
	}

	public function imgeGetNonProcessedRecords($filter='') {
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
	public function imageProcessGTile($barcode) {
		global $config;
		if($this->imageLoadByBarcode($barcode)) {

		$ext = @strtolower($this->imageGetName('ext'));
		$func = 'imagecreatefrom' . ($ext == 'jpg' ? 'jpeg' : $ext);
		$func1 = 'image' . ($ext == 'jpg' ? 'jpeg' : $ext);

		$outputPath = $config['path']['images'] . $this->imageBarcodePath( $barcode ) . 'google_tiles/';
		$image = $config['path']['images'] . $this->imageBarcodePath( $barcode ) . $this->imageGetProperty('fileName');

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

			$this->imageSetProperty('gTileProcessed',1);
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
	public function imageProcessGTileIM($barcode) {
		global $config;
		if($this->imageLoadByBarcode($barcode)) {
			$tilepath = $config['path']['images'] . $this->imageBarcodePath( $barcode ) . 'google_tiles/';
			$filename = $config['path']['images'] . $this->imageBarcodePath( $barcode ) . $this->imageGetProperty('fileName');
			$this->mkdir_recursive($tilepath);
			# creating tiles using Image Magik
			$gTileRes = $this->imageCreateGTileIM($filename, $tilepath);
			return true;
		}
		return false;
	}

	/**
	 * Creates the GoogleMap Tiles for the image using IM for s3 mode
	 * @param string barcode
	 * @param mixed s3 details and object
	 */
	public function imageProcessGTileIMS3($barcode, $arr) {
		global $config;
		$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';		if($this->imageLoadByBarcode($barcode)) {

		$tmpPath = $_TMP . 'tiles/';
		if(!@file_exists($tmpPath)) {
			@mkdir($tmpPath,0775);
		}
		$tilepath = $tmpPath;

		# getting the image from s3
		$filename = $_TMP . $this->imageGetProperty('fileName');

		$bucket = $arr['s3']['bucket'];
		$key = $this->imageBarcodePath($barcode) . $this->imageGetProperty('fileName');
		$arr['obj']->get_object($bucket, $key, array('fileDownload' => $filename));

		# creating tiles using Image Magik
		$gTileRes = $this->imageCreateGTileIM($filename,$tilepath);

		# uploading to s3 and deleting the files
		$tiles3path = $this->imageBarcodePath($barcode) . 'google_tiles/';
		
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
	function imageCreateGTileIM($filename, $outputPath) {
	
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
	public function imageZoomify($barcode) {
		global $config;
		if($this->imageLoadByBarcode($barcode)) {
			$outputPath = $config['path']['images'] . $this->imageBarcodePath( $barcode ) . 'zoomify/';
			$this->mkdir_recursive( $outputPath );
			$image = $config['path']['images'] . $this->imageBarcodePath( $barcode ) . $this->imageGetProperty('fileName');
			$script_path =  $config['path']['base'] . 'api/classes/zoomify/ZoomifyFileProcessor.py ';
			passthru('python ' . $script_path . $image);

// 			passthru('/usr/bin/python ' . $script_path . $image);
// 			$str = exec('python ' . $script_path . $image, $ret);
/*			print '<br>' . $str . '<br>';
			var_dump($ret);*/

// 			$this->imageSetProperty('processed',1);
// 			$this->save();
		}
		return false;
	}

	public function imageList($queryFlag = true) {

		$characters = $this->data['characters'];
		$browse = $this->data['browse'];

		$this->query = "SELECT I.imageId,I.fileName,I.timestampModified, I.barcode, I.width,I.height,I.family,I.genus,I.specificEpithet,I.flickrPlantId, I.flickrModified,I.flickrDetails,I.picassaPlantId,I.picassaModified, I.gTileProcessed,I.zoomEnabled,I.processed,I.boxFlag,I.ocrFlag,I.rating";
		if($this->data['showOCR']) {
			$this->query .= ',I.ocrValue';
		}
		
		# fields for url computation
		$this->query .= ',I.storageId,I.path';
		
		$this->query .= ",I.nameFinderFlag,I.nameFinderValue,I.scientificName, I.collectionCode, I.globalUniqueIdentifier FROM `image` I ";

		$this->queryCount = ' SELECT count(*) AS sz FROM `image` I ';
		
		if (($characters != '') && ($characters != '[]')) {
			if($this->data['characterType'] == 'ids') {
				$this->query .= ", imageAttrib ia ";
				$this->queryCount .= ", imageAttrib ia ";
			} 
			// $this->query .= " LEFT OUTER JOIN imageAttrib ia ON ia.`imageId` = I.`imageId` LEFT OUTER JOIN imageAttribValue iav ON  ia.`attributeId` = iav.`attributeId` ";
			// $this->queryCount .= " LEFT OUTER JOIN imageAttrib ia ON ia.`imageId` = I.`imageId` LEFT OUTER JOIN imageAttribValue iav ON  ia.`attributeId` = iav.`attributeId` ";
		}

		$this->query .= " WHERE 1=1 AND (";
		$this->queryCount .= " WHERE 1=1 AND (";
		
		$this->setBrowseFilter();
		$this->query .= " AND I.imageId != '' ";
		$this->queryCount .= " AND I.imageId != '' ";
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
			$where .= sprintf(" AND I.`collectionCode` LIKE '%s%%' ", mysql_escape_string($this->data['code']));
		}

		if($this->data['imageId'] != '') {
			$where .= sprintf(" AND I.`imageId` = '%s' ", mysql_escape_string($this->data['imageId']));
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
// die($this->query);
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

	public function imageModifyRotate($image = array()) {
		global $config;
		$_TMP = ($config['path']['tmp'] != '') ? $config['path']['tmp'] : sys_get_temp_dir() . '/';
		if($image['imageId'] == '' || !$this->imageFieldExists($image['imageId'])) {
			$ret['success'] = false;
			return $ret;
		}
		$pqueue = new ProcessQueue($this->db);
		$pqueue->db = &$this->db;
		$this->imageLoadById($image['imageId']);
		$barcode = $this->imageGetProperty('barcode');

		if($config['mode'] == 's3') {
			$imagePath = $_TMP;

			# getting the image from s3
			$key = $this->imageBarcodePath($barcode) . $this->imageGetProperty('fileName');
			$image['obj']->get_object($config['s3']['bucket'], $key, array('fileDownload' => $imagePath . $this->imageGetProperty('fileName')));
		} else {
			$imagePath = $config['path']['images'] . $this->imageBarcodePath( $barcode );
		}
		$imageFile = $imagePath . $this->imageGetProperty('fileName');
		if(in_array($image['degree'], array(90, 180, 270))){
			#rotating the image
			$cmd = sprintf("convert -limit memory 16MiB -limit map 32MiB %s -rotate %s %s", $imageFile, $image['degree'], $imageFile);
//  echo '<br>' . $cmd;
			system($cmd);

			if($config['mode'] == 's3') {
				# putting the image to s3
				$key = $this->imageBarcodePath($barcode) . $this->imageGetProperty('fileName');
				$response = $image['obj']->create_object ( $config['s3']['bucket'], $key, array('fileUpload' => $imageFile,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );
				@unlink($imageFile);
			}
		}

		# deleting related images
		if($config['mode'] == 's3') {
			foreach(array('_s','_m','_l') as $postfix) {
				$response = $image['obj']->delete_object($config['s3']['bucket'], $this->imageBarcodePath($barcode) . $barcode . $postfix . '.jpg');
			}
		} else {
			if(is_dir($imagePath)) {
				$handle = opendir($imagePath);
				while (false !== ($file = readdir($handle))) {
					if( $file == '.' || $file == '..' || $file == $this->imageGetProperty('fileName') ) continue;
					if (is_dir($imagePath.$file)) {
						$this->imageRrmdir($imagePath.$file);
					} else if(is_file($imagePath.$file)) {
						@unlink($imagePath.$file);
					}
				}
			}
		}

		$this->imageSetProperty('flickrPlantId', 0);
		$this->imageSetProperty('picassaPlantId', 0);
		$this->imageSetProperty('gTileProcessed', 0);
		$this->imageSetProperty('zoomEnabled', 0);
		$this->imageSetProperty('processed', 0);
		$this->save();

		// $pqueue->set('imageId', $barcode);
		$pqueue->set('imageId', $image['imageId']);
		$pqueue->set('processType', 'all');
		$pqueue->save();

		$ret['success'] = true;
		return $ret;
	}

	public function imageDelete() {
		global $config;
		$imageId = $this->data['imageId'];
		$storage = new Storage($this->db);
		if($imageId != '' && $this->imageFieldExists($imageId)) {
			$this->imageLoadById($imageId);
			$barcode = $this->imageGetProperty('barcode');
			$device = $storage->get($this->imageGetProperty('storage_id'));
			$filenameParts = explode('.', $this->imageGetProperty('fileName'));
			switch(strtolower($device['type'])) {
				case 's3':
					$tmp = $this->imageGetProperty('path');
					$tmp = (substr($tmp, 0, 1)=='/') ? (substr($tmp, 1, strlen($tmp)-1)) : ($tmp);
					$tmp = (substr($tmp, strlen($tmp)-1, 1)=='/') ? (substr($tmp, 0, strlen($tmp)-1)) : ($tmp);
					foreach(array('_s','_m','_l','') as $postfix) {
						$response = $this->data['obj']->delete_object($device['basePath'], $tmp .'/'. $filenameParts[0] . $postfix .'.'. $filenameParts[1]);
					}
					break;
				case 'local':
					$imagePath = $device['basePath'] . $this->imageGetProperty('path') . '/';
					# deleting related images
					foreach(array('_s','_m','_l','') as $postfix) {
						if(file_exists($imagePath.$filenameParts[0].$postfix.'.'.$filenameParts[1])) {
							@unlink($imagePath.$filenameParts[0].$postfix.'.'.$filenameParts[1]);
						}
					}
					# Remove empty directories
					$path = $this->imageGetProperty('path');
					$parts = explode('/', $path);
					while(count($parts)>0) {
						if(!rmdir($device['basePath'] . $path . '/')) break;
						$parts = explode('/', $path);
						unset($parts[count($parts)-1]);
						$path = implode('/', $parts);
					}
					break;
			}
			
			$query = sprintf("DELETE FROM `imageAttrib` WHERE `imageId` = '%s' ", mysql_escape_string($imageId));
			$this->db->query($query);
			
			$query = sprintf("DELETE FROM `eventImages` WHERE `imageId` = '%s' ", mysql_escape_string($imageId));
			$this->db->query($query);
			
			$query = sprintf("DELETE FROM `processQueue` WHERE `imageId` = '%s' ", mysql_escape_string($imageId));
			$this->db->query($query);
			
			$query = sprintf("DELETE FROM `specimen2label` WHERE `barcode` = '%s' ", mysql_escape_string($barcode));
			$this->db->query($query);
			
			$delquery = sprintf("DELETE FROM `image` WHERE `imageId` = '%s' ", mysql_escape_string($imageId));
			if($this->db->query($delquery)) {
				return  array('success' => true);
			}
			return array('success' => false, 'code' => 117);
		}
		return array('success' => false, 'code' => 116);
	}


	public function imageGetGeneraList($filter=array()) {
		$query = "SELECT DISTINCT `genus` FROM `image` WHERE `family` = '' AND `genus` != '' ";
		if($filter['limit'] != '') {
			$query .= sprintf(" LIMIT %s ", $filter['limit']);
		}
		$ret = $this->db->query($query);
		return($ret);
	}

	public function imageGetScientificName($genus) {
		$query = sprintf("SELECT DISTINCT `scientificName` FROM `image` WHERE `scientificName` != '' AND `genus` = '%s' ", mysql_escape_string($genus));
		$ret = $this->db->query_one($query);
		return($ret);
	}

	public function imageUpdateFamilyList($genus,$family ) {
		$query = sprintf(" UPDATE `image` SET  `family` = '%s' WHERE `genus` = '%s' AND `family` = '' ", mysql_escape_string($family), mysql_escape_string($genus));
		if($this->db->query($query)) {
			return array('success' => true, 'records' => $this->db->affected_rows);
		} else {
			return array('success' => false);
		}
	}

	public function imageRrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != '.' && $object != '..') {
					if (filetype($dir.'/'.$object) == 'dir') $this->imageRrmdir($dir.'/'.$object); else unlink($dir.'/'.$object);
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

	function imageGetCollectionSpecimenCount() {
		if($this->data['nodeApi'] == '' || $this->data['nodeValue'] == '') return false;
		$condition = '=';
		if (strpos($this->data['nodeValue'], "%") !== false) {
			$condition = 'LIKE';
		}

		$query = sprintf(" SELECT count(*) ct, `collectionCode` FROM `image` WHERE %s %s '%s' GROUP BY `collectionCode` ", $this->data['nodeApi'], $condition, mysql_escape_string($this->data['nodeValue'] ));
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
					$this->imageSetProperty('filename',$fileDetails['basename']);
					$this->imageSetProperty('barcode',$fileDetails['filename']);
					$this->imageSetProperty('timestampModified',@date('d-m-Y H:i:s',@strtotime($ky->LastModified)));

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
			$tstr .= ', imageAttrib ia ';
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
			$tstr = " AND ia.imageId = I.imageId AND ia.attributeId IN ( " . $this->char_list . " ) ";
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

	// private function setAdminCharacterFilter() {
		// $characters = $this->data['characters'];
		// if (($characters != '') && ($characters != '[]')) {
			// $this->char_list = '';
			// // foreach($json->decode($characters) as $character) {
			// foreach(json_decode($characters) as $character) {
				// $this->char_list .= $character->node_value . ",";
			// }
			// $this->char_list = substr($this->char_list, 0, -1);

			// $this->query .= " AND ia.imageId = I.imageId AND ia.attributeId IN (".$this->char_list.") ";
		// }
// //	print $this->query;
	// }

	private function setAdminCharacterFilter() {
		$characters = json_decode($this->data['characters'],true);
		$char_list = '';
		if(is_array($characters) && count($characters)) {
			switch($this->data['characterType']) {
				case 'ids':
					foreach($characters as $character) {
						$char_list .= $character->node_value . ",";
					}
					$char_list = substr($this->char_list, 0, -1);
					$this->query .= " AND ia.imageId = I.imageId AND ia.attributeId IN (".$char_list.") ";
					$this->queryCount .= " AND ia.imageId = I.imageId AND ia.attributeId IN (".$char_list.") ";
					// $this->query .= " AND ia.attributeId IN (".$char_list.") ";
					break;
				case 'string':
				default:
					$this->query .= " AND I.`imageId` IN ( SELECT DISTINCT ia.`imageId` FROM `imageAttrib` ia, `imageAttribValue` iav WHERE ia.`attributeId` = iav.`attributeId` AND iav.`name` IN ('".implode("','",$characters)."') ) ";
					$this->queryCount .= " AND I.`imageId` IN ( SELECT DISTINCT ia.`imageId` FROM `imageAttrib` ia, `imageAttribValue` iav WHERE ia.`attributeId` = iav.`attributeId` AND iav.`name` IN ('".implode("','",$characters)."') ) ";
					// $this->query .= " AND ia.`imageId` = I.`imageId` AND ia.`attributeId` = iav.`attributeId` AND iav.`name` IN ('".implode("','",$characters)."') ";
					// $this->query .= " AND iav.`name` IN ('".implode("','",$characters)."') ";
					break;
			}
		}
	}

	private function setGroupFilter() {
		$characters = $this->data['characters'];
		if (($characters != '') && ($characters != '[]')) {
	 		$this->query .= " GROUP BY I.`imageId` ";
		}
	}

	// private function setOrderFilter($mode = 'view_images') {
		// if($mode == 'view_images') {
			// $this->query .= " ORDER BY I.`family`, I.`genus`, I.`specificEpithet` ";
		// } else {
			// $this->query .= sprintf(" ORDER BY I.%s %s", mysql_escape_string($this->data['sort']),  $this->data['dir']);
		// }
	// }

	private function setOrderFilter($mode = 'view_images') {
		$orderBy = '';
		switch($mode) {
			case 'view_images':
				$orderBy .= ' ORDER BY I.`family`, I.`genus`, I.`specificEpithet` ';
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

			$query = sprintf("SELECT SQL_CALC_FOUND_ROWS ia.imageId, iat.categoryId, iav.attributeId, iat.title, iav.name FROM imageAttribType iat, imageAttribValue iav, imageAttrib ia WHERE ia.categoryId=iat.categoryId AND ia.attributeId=iav.attributeId AND ia.imageId IN (%s) ORDER BY iat.title, name", mysql_escape_string($this->data['attributes']));
			$records = $this->db->query_all($query);
			$this->total = $this->db->query_total();

			if(!is_null($records)) {
				if(count($records)) {
					foreach($records as $record) {
						$collected = @mktime(0,0,0,$record->tmonth,$record->tday,$record->tyear);
						$nodes[] = array('imageId'=>$record->imageId, 'categoryId'=>$record->categoryId, 'attributeId'=>$record->attributeId, 'title'=>$record->title, 'name'=>$record->name);
					}
				}
			}
			return array('success' => true, 'recordCount' => $this->total, 'data' => $nodes);
		}
	}

	
# Attribute Functions

		public function getAttributeBy($attribute, $attribType) {
			if(!@in_array($attribType,array('categoryId','title','term'))) return false;
			if($attribType == 'categoryId') {
				return $this->category_exist($attribute) ? $attribute : false; 
			}
			$ret = $this->db->query_one( sprintf(" SELECT `categoryId` FROM `imageAttribType` WHERE `%s` = '%s' ", mysql_escape_string($attribType), mysql_escape_string($attribute)) );
			return ($ret == NULL) ? false : $ret->categoryId;
		}

		public function getValueBy($value, $valueType) {
			if(!@in_array($valueType,array('attributeId','name'))) return false;
			if($valueType == 'attributeId') {
				return $this->attribute_exist($value) ? $value : false; 
			}

			$ret = $this->db->query_one( sprintf(" SELECT `attributeId` FROM `imageAttribValue` WHERE `name` = '%s' ", mysql_escape_string($value)) );
			return ($ret == NULL) ? false : $ret->attributeId;
		}
	
	public function addImageAttribute() {
		$imageIds = @explode(',', $this->data['imageId']);
		$categoryId = $this->data['categoryId'];
		$attributeId = $this->data['attributeId'];
		if(count($imageIds)) {
			foreach($imageIds as $id) {
				if($this->imageLoadById($id)) {
					$query = sprintf("INSERT IGNORE INTO imageAttrib(imageId, categoryId, attributeId) VALUES(%s, %s, %s);"
						, mysql_escape_string($id)
						, mysql_escape_string($categoryId)
						, mysql_escape_string($attributeId)
					);

					$this->db->query($query);

					$query = sprintf("INSERT INTO `imageLog` (action, imageId, afterDesc, query, dateCreated) VALUES (10, '%s', 'Cat ID: %s, Attrib ID: %s', '%s', NOW());"
						, mysql_escape_string($id)
						, mysql_escape_string($categoryId)
						, mysql_escape_string($attributeId)
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
		$imageIds = @explode(',', $this->data['imageId']);
		$attributeId = $this->data['attributeId'];
		if(count($imageIds)) {
			foreach($imageIds as $id) {
				$query = sprintf("DELETE FROM `imageAttrib` WHERE imageId = %s AND attributeId IN (%s)"
				, mysql_escape_string($id)
				, mysql_escape_string($attributeId)
				);
				$this->db->query($query);

				$query = sprintf("INSERT INTO `imageLog` (action, imageId, afterDesc, query, dateCreated) VALUES (11, '%s', 'Attrib ID: %s', '%s', NOW())"
				, mysql_escape_string($id)
				, mysql_escape_string($attributeId)
				, mysql_escape_string($query)
				);
				$this->db->query($query);
			}
			return true;
		} else {
			return false;
		}
	}

	public function imageAddCategory() {
		$id = 0;
		$value = $this->data['value'];
		$query = sprintf("INSERT INTO imageAttribType (title) VALUES('%s')", mysql_escape_string($value));
		$this->db->query($query);
		$id = $this->db->insert_id;

		$query = sprintf("INSERT INTO `imageLog` (action, afterDesc, query, dateCreated) VALUES (4, 'categoryId: %s, value: %s', '%s', NOW())"
		, mysql_escape_string($id)
		, mysql_escape_string($value)
		, mysql_escape_string($query)
		);
		$this->db->query($query);
		return( $id );
	}

	public function imageUpdateCategory() {
		$value = $this->data['value'];
		$categoryId = $this->data['categoryId'];
		$query = sprintf("UPDATE imageAttribType set title = '%s' WHERE categoryId = %s "
			, mysql_escape_string($value)
			, mysql_escape_string($categoryId)
			);
		$this->db->query($query);

		$query = sprintf("INSERT INTO `imageLog` (action, afterDesc, query, dateCreated) VALUES (5, 'categoryId: %s, value: %s', '%s', NOW())"
		, mysql_escape_string($categoryId)
		, mysql_escape_string($value)
		, mysql_escape_string($query)
		);
		$this->db->query($query);
		return true;
	}

	public function deleteCategory() {
		$categoryId = $this->data['categoryId'];
		$query = sprintf("DELETE FROM `imageAttrib` WHERE categoryId = %s", mysql_escape_string($categoryId));
		$this->db->query($query);
		$query = sprintf("DELETE FROM `imageAttribValue` WHERE categoryId = %s", mysql_escape_string($categoryId));
		$this->db->query($query);
		$query = sprintf("DELETE FROM `imageAttribType` WHERE categoryId = %s", mysql_escape_string($categoryId));
		$this->db->query($query);
		
		$query = sprintf("INSERT INTO `imageLog` (action, afterDesc, query, dateCreated) VALUES (6, 'Category ID: %s', '%s', NOW())", mysql_escape_string($categoryId), mysql_escape_string($query));
		$this->db->query($query);
		return true;
	}
	
	public function imageListCategory() {
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM `imageAttribType` WHERE 1=1 ";
		
		if(is_array($this->data['categoryId']) && count($this->data['categoryId'])) {
			$query .= sprintf(" AND `categoryId` IN (%s) ", implode(',',$this->data['categoryId']));
		} else if($this->data['categoryId'] != '' && is_numeric($this->data['categoryId'])) {
			$query .= sprintf(" AND `categoryId` = %s ", mysql_escape_string($this->data['categoryId']));
		}
		
		if($this->data['value'] != '') {
			switch($this->data['searchFormat']) {
				case 'exact':
					$query .= sprintf(" AND `title` = '%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'left':
					$query .= sprintf(" AND `title` LIKE '%s%%' ", mysql_escape_string($this->data['value']));
					break;
				case 'right':
					$query .= sprintf(" AND `title` LIKE '%%%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'both':
				default:
					$query .= sprintf(" AND `title` LIKE '%%%s%%' ", mysql_escape_string($this->data['value']));
					break;
			}
		}
		$query .= " ORDER BY `categoryId`, `title` ";
		if($this->data['start'] != '' && $this->data['limit'] != '') {
			$query .= sprintf(" LIMIT %s, %s ", $this->data['start'], $this->data['limit']);
		}
		try {
			$records = $this->db->query_all($query);
		} catch (Exception $e) {
			trigger_error($e->getMessage(),E_USER_ERROR);
		}
		return $records;
	}
	
	public function imageCategoryExist($categoryId) {
		$query = sprintf("SELECT * FROM `imageAttribType` WHERE `categoryId` = '%s'", mysql_escape_string($categoryId));
		$records = $this->db->query_all($query);
		if(count($records)) {
			return true;
		} else {
			return false;
		}
	}

	public function imageAddAttribute() {
		$id = 0;
		$query = sprintf("INSERT INTO imageAttribValue(name, categoryId) VALUES('%s',%s);"
			, mysql_escape_string($this->data['value'])
			, mysql_escape_string($this->data['categoryId'])
		);
		
		$this->db->query($query);
		$id = $this->db->insert_id;
		$query = sprintf("INSERT INTO `imageLog` (action, afterDesc, query, dateCreated) VALUES (7, 'attributeId: %s, value: %s, categoryId: %s', '%s', NOW())"
		, mysql_escape_string($id)
		, mysql_escape_string($this->data['value'])
		, mysql_escape_string($this->data['categoryId'])
		, mysql_escape_string($query)
		);
		$this->db->query($query);
		return($id);
	}

	public function imageRenameAttribute() {
		$value = $this->data['value'];
		$attributeId = $this->data['attributeId'];
		$query = sprintf("UPDATE imageAttribValue set name = '%s' WHERE attributeId = %s"
			, mysql_escape_string($value)
			, mysql_escape_string($attributeId)
			);
		$this->db->query($query);

		$query = sprintf("INSERT INTO `imageLog` (action, afterDesc, query, dateCreated) VALUES (8, 'attributeId: %s, value: %s', '%s', NOW())"
		, mysql_escape_string($attributeId)
		, mysql_escape_string($value)
		, mysql_escape_string($query)
		);
		$this->db->query($query);
		return(true);
	}

	public function imageDeleteAttribute() {
		$attributeId = $this->data['attributeId'];
		$query = sprintf("DELETE FROM `imageAttrib` WHERE attributeId = %s", mysql_escape_string($attributeId));
		$this->db->query($query);
		$query = sprintf("DELETE FROM `imageAttribValue` WHERE attributeId = %s", mysql_escape_string($attributeId));
		$this->db->query($query);		
		$query = sprintf("INSERT INTO `imageLog` (action, afterDesc, query, dateCreated) VALUES (9, 'attributeId: %s', '%s', NOW())", mysql_escape_string($attributeId), mysql_escape_string($query));
		$this->db->query($query);
		return true;
	}
	
	public function imageListAttributes($queryFlag = true) {
		if($this->data['code'] != '') {
			$query = sprintf(" SELECT iav.* FROM `imageAttribValue` iav, `image` i, `imageAttrib` ia WHERE 1=1 AND i.`imageId` = ia.`imageId` AND ia.`attributeId` = iav.`attributeId` AND i.`collectionCode` LIKE '%s%%' ", mysql_escape_string($this->data['code']));
		} else {
			$query = ' SELECT iav.* FROM `imageAttribValue` iav WHERE 1=1 ';
		}
		if(is_array($this->data['categoryId']) && count($this->data['categoryId'])) {
			$query .= sprintf(" AND iav.`categoryId` IN (%s) ", implode(',',$this->data['categoryId']));
		} else if($this->data['categoryId'] != '' && is_numeric($this->data['categoryId'])) {
			$query .= sprintf(" AND iav.`categoryId` = %s ", mysql_escape_string($this->data['categoryId']));
		}
		
		if($this->data['value'] != '') {
			switch($this->data['searchFormat']) {
				case 'exact':
					$query .= sprintf(" AND iav.`name` = '%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'left':
					$query .= sprintf(" AND iav.`name` LIKE '%s%%' ", mysql_escape_string($this->data['value']));
					break;
				case 'right':
					$query .= sprintf(" AND iav.`name` LIKE '%%%s' ", mysql_escape_string($this->data['value']));
					break;
				case 'both':
				default:
					$query .= sprintf(" AND iav.`name` LIKE '%%%s%%' ", mysql_escape_string($this->data['value']));
					break;
			}
		}
		$queryCount = str_replace('iav.*', 'count(*) ct', $query);

		$countRet = $this->db->query_one( $queryCount );
		if ($countRet != NULL) {
			$this->total = $countRet->ct;
		}
		
		$query .= " ORDER BY iav.`categoryId`, iav.`name` ";
		if($this->data['start'] != '' && $this->data['limit'] != '') {
			$query .= sprintf(" LIMIT %s, %s ", $this->data['start'], $this->data['limit']);
		}
		// die($query);
		if($queryFlag) {
			return $this->db->query($query);
		} else {
			return $this->db->query_all($query);
		}
	}

/*	
	public function list_attributes ($categoryId) {
		$query = sprintf("SELECT * FROM `imageAttribValue` WHERE `categoryId` = '%s'", mysql_escape_string($categoryId));
		$records = $this->db->query_all($query);
		if(count($records)) {
			foreach($records as $record) {
				$tmpArray['attributeId'] = $record->attributeId;
				$tmpArray['name'] = $record->name;
				$data[] = $tmpArray;
			}
			return $data;
		} else {
			return false;
		}
	}
	
	public function get_attributes ($categoryId,$type = 'ID') {
		$type = (in_array(strtoupper($type), array('ID','TITLE'))) ? strtoupper($type) : 'ID';
		switch($type) {
			case 'TITLE':
				$query = sprintf("SELECT ia.* FROM `imageAttribValue` ia, `imageAttribType` it  WHERE ia.`categoryId` = it.`categoryId` AND LOWER(it.`title`) = '%s'", mysql_escape_string(strtolower($categoryId)));
				break;
			case 'ID':
			default:
				$query = sprintf("SELECT * FROM `imageAttribValue` WHERE `categoryId` = '%s'", mysql_escape_string($categoryId));
				break;
		}
		
		$records = $this->db->query_all($query);
		if(count($records)) {
			foreach($records as $record) {
				$tmpArray['attributeId'] = $record->attributeId;
				$tmpArray['name'] = $record->name;
				$data[] = $tmpArray;
			}
			return $data;
		} else {
			return false;
		}
	}
*/
	
	public function imageAttributeExist($attributeId) {
		$query = sprintf("SELECT count(*) ct FROM `imageAttribValue` WHERE `attributeId` = '%s'", mysql_escape_string($attributeId));
		$ret = $this->db->query_one($query);
		return (is_object($ret) && $ret->ct) ? true : false;
	}
	
	public function get_all_attributes($imageId) {
		$query = sprintf("SELECT ia.categoryId iaTID, ia.attributeId iaVID, iat.title iatTitle, iav.name iavValue FROM imageAttrib ia LEFT OUTER JOIN imageAttribType iat ON ( ia.categoryId = iat.categoryId ) JOIN imageAttribValue iav ON (iav.attributeId = ia.attributeId AND ia.imageId = '%s' ) ORDER BY ia.categoryId", mysql_escape_string($imageId));
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

	public function loadImageNodesCharacters() {
		unset($this->records);
		$this->nodes = array();
		$this->query = '';
		$this->cache = false;
	
		if(isset($this->data['nodeApi'])) {
			switch(@strtolower($this->data['nodeApi'])) {
			case "root":
				$parent = '';
	
				$this->query = "SELECT DISTINCT  it.categoryId, it.title, iv.attributeId, iv.name FROM imageAttribType it, imageAttribValue iv, imageAttrib ia WHERE it.categoryId = iv.categoryId AND ia.attributeId = iv.attributeId ORDER BY it.title, iv.name;";
				$records = $this->db->query_all($this->query);
				if(count($records)) {
					foreach($records as $record) {
						if ($parent != $record->title && $parent != '') {
						$this->nodes[] = array('text'=>$old_title, 'nodeApi'=>'cateogry', 'iconCls'=>'icon_folder_picture', 'cls'=>'tree_panel', 'nodeValue'=>$record->categoryId, 'children'=>$children);
						$children = '';
						}
						$children[] = array('id'=>'char_' . $record->attributeId, 'id'=>'char_' . $record->attributeId, 'text'=>$record->name, 'nodeApi'=>'character', 'checked'=>false, 'leaf'=>true, 'nodeValue'=>$record->attributeId);
						
						if ($parent != $record->title) {
							$parent = $record->title;
						}
						
						$old_title = $record->title;
					}
				}
				$this->nodes[] = array('text'=>$old_title, 'nodeApi'=>'cateogry', 'iconCls'=>'icon_folder_picture', 'cls'=>'tree_panel', 'nodeValue'=>$record->categoryId, 'children'=>$children);
		
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
		$this->query = "SELECT I.imageId";
		$this->setFilters();

		if (($this->data['characters'] != '') && ($this->data['characters'] != '[]')) {
			$this->query .= " GROUP BY I.imageId HAVING sz >= " . ( $this->char_count - 1 );
		}

		$this->query = "SELECT attributeId as id FROM imageAttrib t1 INNER JOIN  (" . $this->query . ") AS t2 ON t1.imageId = t2.imageId GROUP BY t1.attributeId ORDER BY t1.attributeId;";
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

		$this->query = "SELECT I.`imageId` AS imageId, I.`filename` AS filename, I.`family`, I.`genus`, I.`specificEpithet`, I.`zoomEnabled`, I.`gTileProcessed`, I.`timestampModified`, I.`characters`, I.`barcode`, I.`globalUniqueIdentifier` ";
		$this->queryCount = ' SELECT count(*) AS sz ';
		$this->setFilters();

		if (($this->data['characters'] != '') && ($this->data['characters'] != '[]')) {
			$tstr = " GROUP BY I.imageId HAVING sz >= " . $this->char_count;
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
		if($this->imageFieldExists($this->data['imageId'])) {
			$query = sprintf("SELECT IAT.title as attrib, IAV.name as value FROM imageAttrib IA, imageAttribType IAT, imageAttribValue IAV WHERE IA.categoryId = IAT.categoryId AND IA.attributeId = IAV.attributeId AND IA.imageId = %s ORDER BY IAT.title, IAV.name", mysql_escape_string($this->data['imageId']));
	
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
	
			$this->imageLoadById($this->data['imageId']);
			$barcode = $this->imageGetName();
			$path = $config['path']['images'] . $this->imageBarcodePath( $barcode ) . $this->imageGetProperty('fileName');
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
	
	public function getUrl($imageId) {
		$this->imageLoadById($imageId);
		$storage = new Storage($this->db);
		$device = $storage->get($this->imageGetProperty('storage_id'));
		$url['url'] = $device['baseUrl'];
		switch(strtolower($device['type'])) {
			case 's3':
				$tmp = $this->imageGetProperty('path');
				$tmp = substr($tmp, 0, 1)=='/' ? substr($tmp, 1, strlen($tmp)-1) : $tmp;
				$url['baseUrl'] = $url['url'] . $tmp . '/';
				$url['url'].= $tmp . '/' . $this->imageGetProperty('fileName');
				break;
			case 'local':
				if(substr($url['url'], strlen($url['url'])-1, 1) == '/') {
					$url['url'] = substr($url['url'],0,strlen($url['url'])-1);
				}
				$url['baseUrl'] = $url['url'] . $this->imageGetProperty('path') . '/';
				$url['url'].= $this->imageGetProperty('path'). '/' .$this->imageGetProperty('fileName');
				break;
		}
		$url['filename'] = $this->imageGetProperty('fileName');
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
	
	public function imageMetaDataPackageImport($data) {
		// if((!is_array($data)) || (count($data)!=4)) return false;
		if(!is_array($data)) return false;
		$query = sprintf("INSERT IGNORE INTO `imageAttribType` SET `title` = '%s', `description` = '%s', `elementSet` = '%s', `term` = '%s'"
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
			$query = sprintf(" SELECT * FROM `image` WHERE `barcode` NOT IN (SELECT `barcode` from `specimen2label`) AND `collectionCode` = '%s' ", mysql_escape_string($filter['collection']) );
		if(trim($filter['start']) != '' && trim($filter['limit']) != '') {
			$query .= build_limit(trim($filter['start']),trim($filter['limit']));
		}
		return ($this->db->query($query));
	}
	
	public function updateImageRating($imageId = '', $rating = '') {
		if($imageId == '' ||  $rating == '') return false;
		$query = sprintf(" UPDATE `image` SET `rating` = '%s' WHERE `imageId` = '%s'; ", mysql_escape_string($rating), mysql_escape_string($imageId));
		return ($this->db->query($query)) ? true : false;
	}
}
?>