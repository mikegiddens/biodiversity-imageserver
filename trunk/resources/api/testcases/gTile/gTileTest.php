<?php


$barcode = 'NLU0062116';

function mkdir_recursive( $pathname )	{
		
	is_dir(dirname($pathname)) || mkdir_recursive(dirname($pathname));
	return is_dir($pathname) || @mkdir($pathname, 0775);
}

ini_set('memory_limit', '400M');

$image = 'NLU0062116.jpg';


$gdTime = time();

# GD
$gdPath = 'gdTiles/';

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
			
			mkdir_recursive($imPath . $k . '/');
			imagecopy($dest, $sample, 0, 0, ($i * 256), ($j * 256), 256, 256);
			imagejpeg($dest, sprintf( $imPath . '%s/tile_%s_%s_%s.jpg', $k, $z, $x, $y) );
	
		}
	}
	
}

imagedestroy($dest);
imagedestroy($src);
imagedestroy($sample);

$gdTime = time() - $gdTime;

# IM

$imTime = time();
$cmd = sprintf("./googletilecutter-0.11.sh -r imTiles/tile_ -z 1 -o 1 -t 0,0 %s",$image);
print '<br> Cmd : ' . $cmd;
$res = shell_exec($cmd);
$imTime = time() - $imTime;

print '<br><br><br> GD Time Taken : ' . $gdTime;
print '<br> Im Time Taken : ' . $imTime;

var_dump($res);

/*
# IM
$imPath = 'imTiles/';
$sample = 'sample.jpg';
$dimensions = exec('identify -format "%w,%h" ' . $image);
list($width,$height) = explode(',',$dimensions);


for ($k = 0; $k <= 5; $k++) {

	list($width,$height) = explode(',',$dimensions);
	if ($k == 0) {
		$sample = $image;
	} else {
		$sample = 'sample.jpg';
		$percent = 1 / pow(2, $k);
		$newwidth = $width * $percent;
		$newheight = $height * $percent;
	
		$tmp = sprintf("convert %s -resize %sx%s %s", $image,$newwidth,$newheight,$sample);
		exec($tmp,$op);
		$width = $newwidth;
		$height = $newheight;
	}
	for ($i = 0; $i <= (int) ( $width / 256 ); $i++) {
		for ($j = 0; $j <= (int) ( $height / 256 ); $j++) {
		
			$x = $i;
			$y = $j;
			$z = 1;
			
			mkdir_recursive($imPath . $k . '/');
	
			$tmp = sprintf("convert %s -crop %sx%s+%s+%s $imPath%s/tile_%s_%s_%s.jpg", $sample,($i * 256),($j * 256),256,256,$k, $z, $x, $y);
			$res = system($tmp);
		}
	}
	unlink($sample);
}
*/
