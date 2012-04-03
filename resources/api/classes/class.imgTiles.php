<?php

class imgTiles extends SQLite3
{
	public $newFlag = true;

	public function __construct($path) {

		$filename = @basename($path,'.sqlite');
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

		$stmt = $this->prepare("INSERT INTO tiles (zoom_level, tile_column, tile_row, tile_data, cell) VALUES (:zoomLevel, :tileColumn, :tileRow, :tileData, :tileCell)");
		$stmt->bindValue(':zoomLevel', $zoom, SQLITE3_INTEGER);
		$stmt->bindValue(':tileColumn', 0, SQLITE3_INTEGER);
		$stmt->bindValue(':tileRow', 0, SQLITE3_INTEGER);
		$stmt->bindValue(':tileData', file_get_contents($path), SQLITE3_BLOB);
		$stmt->bindValue(':tileCell', $index, SQLITE3_INTEGER);
		$result = $stmt->execute();
	}

	public function getTileData($zoom,$index) {
		$stmt = $this->prepare("SELECT tile_data FROM tiles WHERE zoom_level = :zoomLevel AND cell = :tileCell");
		$stmt->bindValue(':zoomLevel', $zoom, SQLITE3_INTEGER);
		$stmt->bindValue(':tileCell', $index, SQLITE3_INTEGER);
		$result = $stmt->execute();
		$ret = $result->fetchArray();
		return $ret[0];
	}

}
?>