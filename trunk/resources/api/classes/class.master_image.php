<?php
/**
 * @copyright SilverBiology, LLC
 * @author Michael Giddens
 * @website http://www.silverbiology.com
 */

Class Image {

    public $db, $record;

    public function set_fullpath( $file ){
        $parts = explode('/', $file);
        if ( count($parts) == 1 ) {
        $parts = explode('\\', $file);
        }
        $filename = $parts[count($parts) - 1];
        unset($parts[count($parts) - 1]);
        $path = implode('/', $parts) . "/";
        $this->set('filename',$filename);
        $this->set('path',$path);
    }

    public function getName( $field = 'name' ) {
        if ($field == 'name' || $field == 'ext') {
            $ext = explode('\.', $this->get('filename'));
            return ($field == 'name') ? $ext[0] : $ext[1];
        } else {
            return ($this->$field);
        }
    }

    /**
     * Set the value to Data
     * @param mixed $data : input data
     * @return bool
     */
    public function setData($data) {
        $this->data = $data;
        return( true );
    }

    /**
    * Returns all the values in the record
    * @return mixed
    */
    public function get_all() {
        if (isset($this->record)) {
            return( $this->record );
        } else {
            return( false );
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
            return( false );
        }
    }

    /**
    * Set the value to a field
    * @return bool
    */
    public function set( $field, $value ) {
        $this->record[$field] = $value;
        return( true );
    }

	public function load_by_barcode( $barcode ) {
		if($barcode == '') return false;
		$query = sprintf("SELECT * FROM `image` WHERE `barcode` = '%s'", mysql_escape_string($barcode) );
		$ret = $this->db->query_one( $query );
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
		$ret = $this->db->query_one( $query );
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
	$flsz = filesize($this->get('path') . $this->get('filename'));
	if(!$flsz) {
		@rename( $this->get('path') . $this->get('filename'), $config['path']['error'] . $this->get('filename') );
		return false;
	}
        if(@rename( $this->get('path') . $this->get('filename'), $tmpPath . $this->get('filename') )) {
        	$this->set('path',$tmpPath);
		return true;
	} else {
		return false;
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

	function createThumb( $tmp_path, $new_width, $new_height, $postfix = '', $display_flag=false ) {
		global $config;
		$dtls = @pathinfo($tmp_path);
		$extension = '.' . $dtls['extension'];

		if($config['image_processing'] == 1) {
			$destination =  $dtls['dirname'] . '/' . $dtls['filename'] . $postfix . $extension;
#			$tmp = sprintf("convert %s -thumbnail %sx%s %s", $tmp_path,$new_width,$new_height,$destination);
			$tmp = sprintf("convert -limit memory 16MiB -limit map 32MiB %s -thumbnail %sx%s %s", $tmp_path,$new_width,$new_height,$destination);
// 			$res = system($tmp);
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
	function createThumbS3($barcode,$arr,$deleteFlag = true) {
		if($this->load_by_barcode($barcode)) {
			$filName = 'Img_' . time();
			$tmpThumbPath = sys_get_temp_dir() . '/' . $filName . $arr['postfix'] . '.jpg';
			$thumbName = $this->barcode_path($barcode) . $barcode . $arr['postfix'] . '.jpg';
			$tmpPath = sys_get_temp_dir() . '/' . $filName . '.jpg';

			$fp = fopen($tmpPath, "w+b");

			# getting the image from s3
			$bucket = $arr['s3']['bucket'];
			$key = $this->barcode_path($barcode) . $this->get('filename');
			$arr['obj']->get_object($bucket, $key, array('fileDownload' => $tmpPath));

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

	function createFromFileS3($tmpPath,$barcode,$arr,$deleteFlag = false) {
		if(!@file_exists($tmpPath)) return false;
		$dtls = @pathinfo($tmpPath);
		$extension = '.' . $dtls['extension'];
		$tmpThumbPath =  $dtls['dirname'] . '/' . $dtls['filename'] . $arr['postfix'] . $extension;
		$thumbName = $this->barcode_path($barcode) . $barcode . $arr['postfix'] . '.jpg';

		# uploading thumb to s3
		$this->createThumb($tmpPath, $arr['width'], $arr['height'],$arr['postfix']);
		$response = $arr['obj']->create_object ( $arr['s3']['bucket'], $thumbName, array('fileUpload' => $tmpThumbPath,'acl' => AmazonS3::ACL_PUBLIC,'storage' => AmazonS3::STORAGE_REDUCED) );

		@unlink($tmpPath);
		if($deleteFlag) {
			@unlink($tmpThumbPath);
			return true;
		}
		return $tmpThumbPath;
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

/**
 * Gets the requested dimension image
 */

    public function getImage() {
	global $config;
	$this->load_by_id($this->data['image_id']);
	$ext = @strtolower($this->getName('ext'));
	$extension = '.' . $ext;
	$func1 = 'image' . ($ext == 'jpg' ? 'jpeg' : $ext);
	$content_type = 'image/' . ($ext == 'jpg' ? 'jpeg' : $ext);
        $path = $config['path']['images'] . $this->barcode_path($this->get('barcode'));
        $size = @strtolower($this->data['size']);
        $image =  $path . $this->get('barcode') . $extension;

	if(in_array(strtolower($size),array('s','m','l'))){
		$file_name = $path . $this->get('barcode').'_' . $size . $extension;
		if(!file_exists ($file_name)) {
			switch($size) {
			case 's':
				$this->createThumbnail( $image, 100, 100, "_s");
				break;
			case 'm':
				$this->createThumbnail( $image, 275, 275, "_m");
				break;
			case 'l':
				$this->createThumbnail( $image, 800, 800, "_l");
				break;
			}
		}

		$fp = fopen($file_name, 'rb');
		header("Content-Type: $content_type");
		header("Content-Length: " . filesize($file_name));
		fpassthru($fp);
		exit;
	} else if($this->data['width'] != '' || $this->data['height'] != "") {
#custom dimensions
		$width = ($this->data['width']!='')?$this->data['width']:$this->data['height'];
		$height = ($this->data['height']!='')?$this->data['height']:$this->data['width'];
		$this->createThumb( $image, $width, $height, 'tmp', true);
	} else {
		return false;
	}
    }


    /**
     * checks whether field exists in image table
     */
    public function field_exists ($image_id){
	if($image_id == '' || is_null($image_id)) return false;
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
            $query = sprintf("UPDATE `image` SET  `filename` = '%s', `timestamp_modified` = now(), `barcode` = '%s', `width` = '%s', `height` = '%s', `Family` = '%s', `Genus` = '%s', `SpecificEpithet` = '%s', `flickr_PlantID` = '%s', `flickr_modified` = '%s', `flickr_details` = '%s', `picassa_PlantID` = '%s', `picassa_modified` = '%s', `gTileProcessed` = '%s', `zoomEnabled` = '%s', `processed` = '%s', `ocr_flag` = '%s', `ocr_value` = '%s', `namefinder_flag` = '%s', `namefinder_value` = '%s', `ScientificName` = '%s', `CollectionCode` = '%s'  WHERE image_id = '%s' ;"
            , mysql_escape_string($this->get('filename'))
            , mysql_escape_string($this->get('barcode'))
            , mysql_escape_string($this->get('width'))
            , mysql_escape_string($this->get('height'))
            , mysql_escape_string($this->get('Family'))
            , mysql_escape_string($this->get('Genus'))
            , mysql_escape_string($this->get('SpecificEpithet'))
            , mysql_escape_string($this->get('flickr_PlantID'))
            , mysql_escape_string($this->get('flickr_modified'))
            , mysql_escape_string($this->get('flickr_details'))
            , mysql_escape_string($this->get('picassa_PlantID'))
            , mysql_escape_string($this->get('picassa_modified'))
            , mysql_escape_string($this->get('gTileProcessed'))
            , mysql_escape_string($this->get('zoomEnabled'))
            , mysql_escape_string($this->get('processed'))
            , mysql_escape_string($this->get('ocr_flag'))
            , mysql_escape_string($this->get('ocr_value'))
            , mysql_escape_string($this->get('namefinder_flag'))
            , mysql_escape_string($this->get('namefinder_value'))
            , mysql_escape_string($this->get('ScientificName'))
            , mysql_escape_string($this->get('CollectionCode'))
            , mysql_escape_string($this->get('image_id'))
            );
		} else {
            $query = sprintf("INSERT IGNORE INTO `image` SET `filename` = '%s', `timestamp_modified` = now(), `barcode` = '%s', `width` = '%s', `height` = '%s', `Family` = '%s', `Genus` = '%s', `SpecificEpithet` = '%s', `flickr_PlantID` = '%s', `flickr_modified` = '%s', `flickr_details` = '%s', `picassa_PlantID` = '%s', `picassa_modified` = '%s', `gTileProcessed` = '%s', `zoomEnabled` = '%s', `processed` = '%s', `ocr_flag` = '%s', `ocr_value` = '%s', `namefinder_flag` = '%s', `namefinder_value` = '%s', `ScientificName` = '%s', `CollectionCode` = '%s' ;"
            , mysql_escape_string($this->get('filename'))
            , mysql_escape_string($this->get('barcode'))
            , mysql_escape_string($this->get('width'))
            , mysql_escape_string($this->get('height'))
            , mysql_escape_string($this->get('Family'))
            , mysql_escape_string($this->get('Genus'))
            , mysql_escape_string($this->get('SpecificEpithet'))
            , mysql_escape_string($this->get('flickr_PlantID'))
            , mysql_escape_string($this->get('flickr_modified'))
            , mysql_escape_string($this->get('flickr_details'))
            , mysql_escape_string($this->get('picassa_PlantID'))
            , mysql_escape_string($this->get('picassa_modified'))
            , mysql_escape_string($this->get('gTileProcessed'))
            , mysql_escape_string($this->get('zoomEnabled'))
            , mysql_escape_string($this->get('processed'))
            , mysql_escape_string($this->get('ocr_flag'))
            , mysql_escape_string($this->get('ocr_value'))
            , mysql_escape_string($this->get('namefinder_flag'))
            , mysql_escape_string($this->get('namefinder_value'))
            , mysql_escape_string($this->get('ScientificName'))
            , mysql_escape_string($this->get('CollectionCode'))
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
	 * Creates the GoogleMap Tiles for the image using IM for s3 mode
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
		if($this->load_by_barcode($barcode)) {

			$tmpPath = sys_get_temp_dir() . '/tiles/';
			if(!@file_exists($tmpPath)) {
				@mkdir($tmpPath,0775);
			}
			$tilepath = $tmpPath;

			# getting the image from s3
			$filename = sys_get_temp_dir() . '/' . $this->get('filename');

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

	public function listImages($queryFlag = true) {
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

		$query = "SELECT SQL_CALC_FOUND_ROWS  image_id,filename,timestamp_modified,barcode,width,height,Family,Genus,SpecificEpithet,flickr_PlantID, flickr_modified,flickr_details,picassa_PlantID,picassa_modified, gTileProcessed,zoomEnabled,processed,ocr_flag,namefinder_flag,namefinder_value,ScientificName, CollectionCode FROM `image` " . $where;

// print $query;
		if($queryFlag) {
			$ret = $this->db->query_all( $query );
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

// 		$records = $this->db->query_all( $query );
		$Ret = $this->db->query($query);

		$pre_fix = '';
		$counter = '';
		$strips_array = array();
		$start = '';
		$end = '';
		$preCount = 0;

// 		if(count($records) && is_array($records)) {
		if(is_object($Ret)) {

// 			foreach($records as $record) {
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
		if($image['image_id'] == '' || !$this->field_exists($image['image_id'])) {
			$ret['success'] = false;
			return $ret;
		}
		$pqueue = new ProcessQueue();
		$pqueue->db = &$this->db;

		$this->load_by_id($image['image_id']);

		$barcode = $this->get('barcode');

		$imagePath = $config['path']['images'] . $this->barcode_path( $barcode );
		$imageFile = $imagePath . $this->get('filename');
		if(in_array($image['degree'],array(90,180,270))){
			#rotating the image
			$cmd = sprintf("convert %s -rotate %s %s", $imageFile, $image['degree'], $imageFile);
			exec($cmd);
		}
		# deleting related images
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

		$this->set('flickr_PlantID',0);
		$this->set('picassa_PlantID',0);
		$this->set('gTileProcessed',0);
		$this->set('zoomEnabled',0);
		$this->set('processed',0);
		$this->save();

		$pqueue->set('image_id',$barcode);
		$pqueue->set('process_type','all');
		$pqueue->save();

		$ret['success'] = true;
		return $ret;
	}

/*
	public function deleteImage() {
		$imageId = $this->get('image_id');
		if($image_id != '' && $this->field_exists($imageId)) {
			$this->load_by_id($imageId);
			$barcode = $this->get('barcode');
			$imagePath = PATH_IMAGES . $this->barcode_path( $barcode );
			# deleting related images
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
			$delquery = sprintf("DELETE FROM `image` WHERE `image_id` = '%s' ", mysql_escape_string($imageId));
			if($this->db->query($delquery)) {
				return true;
			}
		}
		return false;
	}
*/

	public function deleteImage() {
// 		$imageId = $this->get('image_id');
		$imageId = $this->data['image_id'];
		if($imageId != '' && $this->field_exists($imageId)) {
			$this->load_by_id($imageId);
			$barcode = $this->get('barcode');
			$imagePath = $config['path']['images'] . $this->barcode_path( $barcode );
			# deleting related images
			if(is_dir($imagePath)) {
				$handle = opendir($imagePath);
				while (false !== ($file = readdir($handle))) {
					if( $file == '.' || $file == '..' /* || $file == $this->get('filename') */ ) continue;
					if (is_dir($imagePath.$file)) {
						$this->rrmdir($imagePath.$file);
					} else if(is_file($imagePath.$file)) {
						@unlink($imagePath.$file);
					}
				}
			}
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

// print $query;

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

		return $ar;

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


}
?>