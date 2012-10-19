<?php

/**
 * @copyright SilverBiology, LLC
 * @author Michael Giddens
 * @website http://www.silverbiology.com
 */

Class Images {

    private $filesArray;
    public $db, $record;

	public function __construct($db = null) {
		$this->db = $db;
	}

    public function imagesLoadFromFolder( $folderPath ) {
        if(is_dir($folderPath)) {
            $handle = opendir($folderPath);

            while (false !== ($filename = readdir($handle))) {
                    if( $filename == '.' || $filename == '..') continue;
                    $tmpFile = new Image($this->db);
                    $tmpFile->imageSetFullPath($folderPath . $filename);
                    $this->imagesAddFile( $tmpFile );
            }
            return true;
        } else {
                return false;
        }
    }

    public function imagesGetFiles() {
        return $this->filesArray;
    }

    public function imagesAddFile( $fileObj ) {
        $this->filesArray[] = $fileObj;
    }

    public function imagesClearFiles() {
        unset($this->filesArray);
    }

}

?>