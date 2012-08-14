<?php

/**
 * @copyright SilverBiology, LLC
 * @author Michael Giddens
 * @website http://www.silverbiology.com
 */

Class Images {

    private $files_array;
    public $db, $record;

	public function __construct($db = null) {
		$this->db = $db;
	}

    public function load_from_folder( $folder_path ) {
        if(is_dir($folder_path)) {
            $handle = opendir($folder_path);

            while (false !== ($file_name = readdir($handle))) {
                    if( $file_name == '.' || $file_name == '..') continue;
                    $tmpFile = new Image();
                    $tmpFile->set_fullpath($folder_path . $file_name);
                    $this->add_file( $tmpFile );
            }
            return true;
        } else {
                return false;
        }
    }

    public function get_files() {
        return $this->files_array;
    }

    public function add_file( $file_obj ) {
        $this->files_array[] = $file_obj;
    }

    public function clear_files() {
        unset($this->files_array);
    }

}

?>