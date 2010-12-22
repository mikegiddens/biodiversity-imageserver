<?php

function mkdir_recursive( $pathname )	{
	is_dir(dirname($pathname)) || mkdir_recursive(dirname($pathname));
	return is_dir($pathname) || @mkdir($pathname, 0775);
}

ini_set('memory_limit', '400M');

$gdTime = time();
/*
# GD
$gdPath = 'gdTiles/';
if(!file_exists($gdPath)) {
	mkdir($gdPath,0775);
}

$src = imagecreatefromjpeg( $image );
$dest = imagecreatetruecolor(256, 256);

// 2x Zoom
$zoomfactor = 1;
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
			
			mkdir_recursive($gdPath . $k . '/');
			imagecopy($dest, $sample, 0, 0, ($i * 256), ($j * 256), 256, 256);
			imagejpeg($dest, sprintf( $gdPath . '%s/tile_%s_%s_%s.jpg', $k, $z, $x, $y) );
	
		}
	}
	
}

imagedestroy($dest);
imagedestroy($src);
imagedestroy($sample);

$gdTime = time() - $gdTime;
*/

# ----------------------------

function createGTile($filename, $outputPath) {

	if (!file_exists($filename)) {
		return( array("success" => false, "error" => array("code" => 100, "msg" => "File does not exist.") ) );
	}
	
	$dimensions = exec('identify -format "%w,%h" ' . $filename);
	list($owidth,$oheight) = explode(',',$dimensions);
	
	if(!file_exists($outputPath)) {
		mkdir($outputPath, 0777);
	}

	$zoomLevels = round(sqrt($oheight / 256));
echo '<br><br> Zoom Levels : ' . $zoomLevels;
	for ($z = 0; $z < $zoomLevels; $z++) {
		if ($z == 0) {
			$width = $owidth;
			$height = $oheight;
			$tmpFile = $filename;
		} else {
			$tmpFile = $z . "tmp" . $filename;
			$percent = 1 / pow(2, $z);
			$width = $owidth * $percent;
			$height = $oheight * $percent;
			$cmd = sprintf("convert %s -resize %sx%s %s"
				,	$filename
				,	$width
				,	$height
				,	$tmpFile
			);
	 echo '<br><br> Command when k = ' . $z . ' : ' . $cmd;
			$res = system($cmd);
		}

		$iLimit = (int) ( $width / 256 );
		$jLimit = (int) ( $height / 256 );

		for ($i = 0; $i <= $iLimit; $i++) {
			for ($j = 0; $j <= $jLimit; $j++) {
			
				$x = $i;
				$y = $j;
//				$z = 1;
				
				mkdir_recursive($outputPath . $z . '/');
	
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
				echo '<br> Tile : ' . $cmd;
				if($i == $iLimit || $j == $jLimit) {
					$tmpImage = sprintf("%s%s/tile_%s_%s_%s.jpg",$outputPath,$z,$z,$x,$y);
					$cmd = sprintf("convert %s -background white -extent 256x256 +repage %s", $tmpImage, $tmpImage);
				echo '<br><br> In exception loop : ' . $cmd;
					$res = system($cmd);
				}
			}
		}
		if($tmpFile != $filename) {
			@unlink($tmpFile);
		}
	}
	
//	@unlink($sample);
	return( array("success" => true) );
}

$image = 'USMS000018206.jpg';
$image = 'NLU0062116.jpg';
$res = createGTile($image, 'imTiles3/');

if ( $res["success"] ) {
	echo 'Image Tiles Created';
} else {
	echo 'Error - ' . $rec["code"]["msg"];
}
?>