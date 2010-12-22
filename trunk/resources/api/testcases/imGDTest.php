<?php

define('WORKING_PATH', '/var/www/gui/api/testcases/images/');
define('DEST_IM', WORKING_PATH . 'im/');
define('DEST_GD', WORKING_PATH . 'gd/');
define('URL', 'http://images.cyberfloralouisiana.com/gui/api/testcases/images/');

$gdArray = array();
$imArray = array();

$imageArray = array('NLU0062121.jpg','NLU0062119.jpg'/*,'NLU0062118.jpg'*/);
$sizeArray = array('_s' => 100, '_m' => 250, '_l' => 800);

function createThumbnail( $source, $new_width, $new_height, $postfix = '') {
	$extension = '.' . getName($source,'ext');
	$func = 'imagecreatefrom' . (@strtolower(getName($source,'ext')) == 'jpg' ? 'jpeg' : @strtolower(getName($source,'ext')));
	$im = @$func($source);
	if($im !== false) {
		$image_file = DEST_GD . getName($source,'name') . $postfix . $extension;
		$width = imageSX($im);
		$height = imageSY($im);
		resizeImage($new_width, $new_height, $im, $image_file, $width, $height);
		ImageDestroy($im); // Remove tmp Image Object
	}
}

function createThumbnailIMagik( $source, $new_width, $new_height, $postfix = '' ) {
	$extension = '.' . getName($source,'ext');
	$destination = DEST_IM .  getName($source,'name') . $postfix . $extension;
	$tmp = sprintf("convert %s -resize %sx%s %s", $source,$new_width,$new_height,$destination);
	$res = system($tmp);
}

function getName( $filename, $field = 'name' ) {

	if ($field == 'name' || $field == 'ext') {
		$ext = @pathinfo($filename);
		return ($field == 'name') ? $ext['filename'] : $ext['extension'];
	} else {
		return ($filename);
	}
	
}

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

# GD Processing

$gdStart = time();

if(is_array($imageArray) && count($imageArray)) {
	foreach($imageArray as $image) {
		$source = WORKING_PATH . $image;
		foreach($sizeArray as $k => $size) {
			createThumbnail($source, $size, $size, $k);
			$gdArray[$image][] = URL . 'gd/' . getName($source,'name') . $k . '.' . getName($source,'ext');
		}
	}
}
$gdTime = time() - $gdStart;

# IM Processing

$imStart = time();

if(is_array($imageArray) && count($imageArray)) {
	foreach($imageArray as $image) {
		$source = WORKING_PATH . $image;
		foreach($sizeArray as $k => $size) {
			createThumbnailIMagik($source, $size, $size, $k);
			$imArray[$image][] = URL . 'im/' . getName($source,'name') . $k . '.' . getName($source,'ext');
		}
	}
}
$imTime = time() - $imStart;

print '<br> Time taken for GD : ' . $gdTime;
print '<br> Time taken for IM : ' . $imTime;
if(is_array($imageArray) && count($imageArray)) {
	foreach($imageArray as $image) {
print '<hr>';
print '<br> Image : ' . $image;
print '<br> <b>GD</b> ';
		print '<div>';
		if(is_array($gdArray[$image]) && count($gdArray[$image])) {
			foreach($gdArray[$image] as $pth) {
				print '<input type="image" src="' . $pth . '">';
			}
		}
		print '</div>';
print '<br> <b>IM</b> ';
		print '<div>';
		if(is_array($imArray[$image]) && count($imArray[$image])) {
			foreach($imArray[$image] as $pth) {
				print '<input type="image" src="' . $pth . '">';
			}
		}
		print '</div>';
	} # for each image
} # images

?>