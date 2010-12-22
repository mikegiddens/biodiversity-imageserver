<?php

	function mkdir_recursive( $pathname )	{
			
		is_dir(dirname($pathname)) || mkdir_recursive(dirname($pathname));
		return is_dir($pathname) || @mkdir($pathname, 0775);
	}

ini_set('memory_limit', '400M');

$image = 'LSU00022020.jpg';
$outputPath = 'tiles/';

$tmp = split('\.', $image );
$outputPath = $tmp[0] . '/' . $outputPath;

$src = imagecreatefromjpeg( $image );
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
			
			mkdir_recursive($outputPath . $k . '/');
			imagecopy($dest, $sample, 0, 0, ($i * 256), ($j * 256), 256, 256);
			imagejpeg($dest, sprintf( $outputPath . '%s/tile_%s_%s_%s.jpg', $k, $z, $x, $y) );
	
		}
	}
	
}

imagedestroy($dest);
imagedestroy($src);
imagedestroy($sample);

?>