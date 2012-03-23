<?php
require_once('./config.php');
class SilverTile {

	function __construct($path = "", $image = "", $sharpenFlag = false, $tileSize = 256) {
		$record = array();
		if ($path != "") $this->set("sourcePath", $path);
		if ($image != "") $this->set("image", $image);
		is_bool($sharpenFlag) ? $this->set("sharpenFlag", $sharpenFlag) : false;
		is_int($tileSize) ? $this->set("tileSize", $tileSize) : 256;
	}

	function getOriginalDimensions() {
		if($this->sourceExist()) {
			@list($dimensions['width'], $dimensions['height']) = @getimagesize($this->get("sourcePath") . $this->get("image"));
			return $dimensions;
		}
		return false;
	}

	function sourceExist() {
		return(file_exists($this->get("sourcePath") . $this->get("image")));
	}

	function cacheExist() {
		return(file_exists($this->getTileLocation()));
	}

	function touchCache() {
		$this->getTileLevels();
		@touch($this->getTileLocation());
	}
	
	function set($field, $value) {
		if ($field != "") {
			$this->record[$field] = $value;
			return(true);
		} else {
			return(false);
		}
	}

	function get($field) {
		return($this->record[$field]);
	}
	
	function getTileLocation() {
		$file_parts = pathinfo($this->get("image"));
		return(PATH_CACHE . strtolower($file_parts["filename"]) . "/");
	}

	private function createTileLocation() {
		return(@mkdir($this->getTileLocation(), 0775));
	}

	function getZoomLevel() {
		return $this->get('zoomLevel');
	}

	function getTileLevels() {
#TODO Add logic to get the right zoom level

		$dimensions = $this->getOriginalDimensions();
		$i = 1;
		while($dimensions['height'] > (pow($i,4) * $this->get("tileSize"))) {
			$i++;
		}
		if($i<=1) $i=4;
		$i++; # increasing the zoomlevel by 1 (temporary)
		$this->set('zoomLevel',$i);
		return($i);
	}

	function getUrl() {
		$file_parts = pathinfo($this->get("image"));
		return(BASE_URL . 'cache/' . strtolower($file_parts["filename"]) . "/");
	}

	function getTempFileLocation() {
		return($this->getTileLocation() . 'tmpFile.jpg');
	}

	function tempFileExist() {
# checks if the temp file exists for the filename
		return(file_exists($this->getTempFileLocation()));
	}

# Creates the tiles from the re-sized temp image at each level
	function createTiles() {
		$this->createTileLocation();
		$timeStart = time();
		for($i=$this->getTileLevels(),$j=0; $i>=1; $i--) {
		$time = time() - $timeStart;
			$x = pow($i,2);
			$width = $height = $this->get("tileSize") * $x;
			$tmpCachePath = $this->getTileLocation() . $i . '/';
			if(!file_exists($tmpCachePath)) @mkdir($tmpCachePath, 0775);

			if($this->tempFileExist()) {
				$source = $this->getTempFileLocation();
				$tmp = sprintf("convert %s -resize 50%% %s", $source, $source);
				$res = system($tmp);
			} else {
				$source = $this->get("sourcePath") . $this->get("image");
				$tmp = copy($source, $this->getTempFileLocation());
				$source = $this->getTempFileLocation();
			}

			if($i == 1) {
				$tmp = sprintf("convert %s -background transparent -gravity center -resize %sx%s -extent %sx%s %s  %s%s_%s.jpg"
					,	$source
					,	$width
					,	$height
					,	$width
					,	$height
					,	$sharpen
					,	$tmpCachePath
					,	"tile"
					,	0
				);

				$res = system($tmp);
			} else {
				$tmp = sprintf("convert %s -background transparent -gravity center -resize %sx%s -extent %sx%s %s   -crop %sx%s  %s%s_%%d.jpg"
					,	$source
					,	$width
					,	$height
					,	$width
					,	$height
					,	$sharpen
					,	$this->get("tileSize")
					,	$this->get("tileSize")
					,	$tmpCachePath
					,	"tile"
					,	$i
				);
				$res = system($tmp);
			}
			$tmp = sprintf("rm %s", $this->getTempFileLocation());
			$res = system($tmp);
		}
		$time = time() - $timeStart;
	}


	function findOldestFile($directory) {
		$directory = rtrim($directory,'/') . '/';
		if ($handle = opendir($directory)) {
			while (false !== ($file = readdir($handle))) {
				if($file != "." && $file != "..") {
					$file_date[$file] = filemtime($directory . $file);
				}
			}
		}
		closedir($handle);
		if(is_array($file_date) && count($file_date)) {
		asort($file_date, SORT_NUMERIC);
		reset($file_date);
		$oldest = key($file_date);
		return $oldest;
		}
		return false;
	}

}
?>