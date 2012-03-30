<?php

class MBTiles extends SQLite3
{
	public $newFlag = true;

	public function __construct($path) {

		$filename = @basename($path,'.db');
		if(file_exists($path)) $this->newFlag = false;
		$this->open($path);
		if($this->newFlag) {
			$this->createTables($filename);
		}
	}

	public function createTables($filename) {
		$this->exec("CREATE TABLE metadata (name text, value text)");
		$this->exec("CREATE TABLE tiles (zoom_level integer, tile_column integer, tile_row integer, tile_data blob, cell integer)");
		$this->exec("INSERT INTO metadata (name,value) VALUES ('name','$filename');");
		$this->exec("INSERT INTO metadata (name,value) VALUES ('type', 'baselayer');");
		$this->exec("INSERT INTO metadata (name,value) VALUES ('version','1');");
		$this->exec("INSERT INTO metadata (name,value) VALUES ('description','$filename');");
		$this->exec("INSERT INTO metadata (name,value) VALUES ('format','jpg');");
	}

	public function recordTile($zoom,$path) {
		$tile = basename($path,'.jpg');
		$index = array_pop(explode('_',$tile));
		$sql = sprintf("INSERT INTO tiles (zoom_level, tile_column, tile_row, tile_data, cell) VALUES (%d,0,0,%s,%d);"
				, $zoom
				, "'" . $this->escapeString(@file_get_contents($path)) . "'"
				,$index);
		$this->exec($sql);
	}

}
?>