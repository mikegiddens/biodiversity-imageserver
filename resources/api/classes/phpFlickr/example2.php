<?php

require_once("phpFlickr.php");
// Create new phpFlickr object
$f = new phpFlickr("0041b2e829b96d804b0b7194ff026cbe","58fbe380449e822f");
$f->setToken("72157618017454673-370d9940efcc9210");

$token = $f->auth_checkToken();
 
// Find the NSID of the authenticated user
$nsid = $token['user']['nsid'];
 
// Get the friendly URL of the user's photos
$photos_url = $f->urls_getUserPhotos($nsid);
 
// Get the user's first 36 public photos
$photos = $f->photos_search(array("user_id" => $nsid, "per_page" => 36));
 
// Loop through the photos and output the html
foreach ((array)$photos['photo'] as $photo) {
	echo "<a href=$photos_url$photo[id]>";
	echo "<img border='0' alt='$photo[title]' ".
		"src=" . $f->buildPhotoURL($photo, "Square") . ">";
	echo "</a>";
	$i++;
	// If it reaches the sixth photo, insert a line break
	if ($i % 6 == 0) {
		echo "<br>\n";
	}
}
 
?>