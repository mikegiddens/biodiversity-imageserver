<?php

/**
 * 
 */

/**
 * Setting library include path
 */
$oldPath = set_include_path(get_include_path() . PATH_SEPARATOR . $config['picassa']['lib_path']);

/**
 * Including Loader
 */

require_once 'Zend/Loader.php';

/**
 * Wrapper class for picassa web albums
 */
class PicassaWeb
{

	private $client;

	public function __construct() {
		Zend_Loader::loadClass('Zend_Gdata_Photos');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		Zend_Loader::loadClass('Zend_Gdata_AuthSub');
		Zend_Loader::loadClass('Zend_Gdata_Photos_UserQuery');
		Zend_Loader::loadClass('Zend_Gdata_Photos_AlbumQuery');
		Zend_Loader::loadClass('Zend_Gdata_Photos_PhotoQuery');
		Zend_Loader::loadClass('Zend_Gdata_App_Extension_Category');

		$this->serviceName = Zend_Gdata_Photos::AUTH_SERVICE_NAME;
	}

	
/**
 * Returns a since field value
 * @param string $field database field
 * @return boolean|integer|string
 */
	public function get( $field ) {
		if (isset($this->$field)) {
			return( $this->$field );
		} else {
			return( false );
		}
	}

/**
 * Set the value to a field
 * @param string $field : database field
 * @param mixed  $value : value
 * @return boolean
 */
	public function set( $field, $value ) {
		$this->$field = $value;
		return( true );
		
	}

	public function clientLogin() {
		$this->client = Zend_Gdata_ClientLogin::getHttpClient($this->get('picassa_user'),$this->get('picassa_pass'),$this->serviceName);
		$this->gp = new Zend_Gdata_Photos($this->client);

		return true;
	}

	public function getAlbumID () {
		$userFeed = $this->gp->getUserFeed($this->get('picassa_user'));
		if(count($userFeed)) {
			foreach ($userFeed as $userEntry) {
				if(@trim($userEntry->title->text) == $this->get('picassa_album')){
					$this->album_id = $userEntry->gphotoId->text;
					return $this->album_id;
				}
			}
		}
		return false;
	}

	public function listPhotos() {
		$query = new Zend_Gdata_Photos_AlbumQuery();
		var_dump($query);
		$query->setUser($this->get('picassa_user'));
		$query->setAlbumName($this->get('picassa_album'));
		
		$albumFeed = $this->gp->getAlbumFeed($query);
		if(is_array($albumFeed) && count($albumFeed)) {
			foreach ($albumFeed as $albumEntry) {
// 			echo $albumEntry->title->text . "<br />\n";
				var_dump($albumEntry);
			}
		}
	}

	public function addPhoto($photo){
		
		$fd = $this->gp->newMediaFileSource($photo['tmp_name']);
		$fd->setContentType($photo['type']);
		
		// Create a PhotoEntry
		$entry = new Zend_Gdata_Photos_PhotoEntry();
		$entry->setMediaSource($fd);
		$entry->setTitle($this->gp->newTitle($photo['name']));
		$entry->setSummary($this->gp->newSummary($photo['name']));
		// add some tags
		$keywords = new Zend_Gdata_Media_Extension_MediaKeywords();
		$keywords->setText($photo['tags']);
		$entry->mediaGroup = new Zend_Gdata_Media_Extension_MediaGroup();
		$entry->mediaGroup->keywords = $keywords;

		$albumQuery = new Zend_Gdata_Photos_AlbumQuery;
		$albumQuery->setUser($this->get('picassa_user'));
		$albumQuery->setAlbumId($this->get('album_id'));
		
		$albumEntry = $this->gp->getAlbumEntry($albumQuery);
		
		$result = $this->gp->insertPhotoEntry($entry, $albumEntry);

		if ($result) {
			return $result->gphotoId->text;
		} else {
			return false;
		}
	}

/**
 * Deletes a given photo
 * @param int $photoId
 * @return boolean
 */
	public function deletePhoto($photoId) {
		$albumId = $this->getAlbumID();

		$photoQuery = new Zend_Gdata_Photos_PhotoQuery;
		$photoQuery->setUser($this->get('picassa_user'));
		$photoQuery->setAlbumId($albumId);
		$photoQuery->setPhotoId($photoId);
		$photoQuery->setType('entry');
		
		$entry = $this->gp->getPhotoEntry($photoQuery);
		
		$this->gp->deletePhotoEntry($entry, true);
		return true;
	}

/**
 * Updates the properties of a specified photo
 */

	public function updatePhoto($photoId) {
		$albumId = $this->getAlbumID();
		$photoQuery = new Zend_Gdata_Photos_PhotoQuery;
		$photoQuery->setUser($this->get('picassa_user'));
		$photoQuery->setAlbumId($albumId);
		$photoQuery->setPhotoId($photoId);
		$photoQuery->setType('entry');

		$entry = $this->gp->getPhotoEntry($photoQuery);
		$entry->title->text = ($this->get('photo_title') != '') ? $this->get('photo_title') : $entry->title->text;
		$entry->summary->text = ($this->get('photo_summary') != '') ? $this->get('photo_summary') : $entry->summary->text;

		if($this->get('photo_tags') != '') {
			$keywords = new Zend_Gdata_Media_Extension_MediaKeywords();
			$keywords->setText($this->get('photo_tags'));
			$entry->mediaGroup->keywords = $keywords;
		}
		$updatedEntry = $entry->save();
		return true;
	}

/**
 * Get the details of a particular photo
 */
	public function getPhotodetails($photoId) {
		$albumId = $this->getAlbumID();
		$photoQuery = new Zend_Gdata_Photos_PhotoQuery;
		$photoQuery->setUser($this->get('picassa_user'));
		$photoQuery->setAlbumId($albumId);
		$photoQuery->setPhotoId($photoId);
		$photoQuery->setType('entry');

		$entry = $this->gp->getPhotoEntry($photoQuery);
		$photo_details = array('title' => $entry->title->text, 'summary' => $entry->summary->text, 'keywords' => $entry->mediaGroup->keywords->text);
		return $photo_details;
	}

/**
 * Adds a new tag to the specified photo
 *
 * @param  integer          $photoId  The photo's id
 * @param  string           $tag    The tag to add to the photo
 * @return boolean
 */
	public function addTag($photoId, $tag) {
		$albumId = $this->getAlbumID();
	
		$entry = new Zend_Gdata_Photos_TagEntry();
		$entry->setTitle($this->gp->newTitle($tag));
		
		$photoQuery = new Zend_Gdata_Photos_PhotoQuery;
		$photoQuery->setUser($this->get('picassa_user'));
		$photoQuery->setAlbumId($albumId);
		$photoQuery->setPhotoId($photoId);
		$photoQuery->setType('entry');
		
		$photoEntry = $this->gp->getPhotoEntry($photoQuery);
		
		$result = $this->gp->insertTagEntry($entry, $photoEntry);
		if ($result) {
			return $result;
		}
		return false;
	}

/**
 * Deletes the specified tag
 *
 * @param  integer          $photoId    The photo's id
 * @param  string           $tagContent The name of the tag to be deleted
 * @return boolean
 */
	public function deleteTag($photoId, $tagContent) {
		$albumId = $this->getAlbumID();
		$photoQuery = new Zend_Gdata_Photos_PhotoQuery;
		$photoQuery->setUser($this->get('picassa_user'));
		$photoQuery->setAlbumId($albumId);
		$photoQuery->setPhotoId($photoId);
		$query = $photoQuery->getQueryUrl() . "?kind=tag";
		
		$photoFeed = $this->gp->getPhotoFeed($query);
		
		foreach ($photoFeed as $entry) {
			if ($entry instanceof Zend_Gdata_Photos_TagEntry) {
// 				if ($entry->getContent() == $tagContent) {
// 					$tagEntry = $entry;
// 				}
				if ($entry->title->text == $tagContent) {
					$tagEntry = $entry;
				}
			}
		}
		if(!is_null($tagEntry)) {
			$this->gp->deleteTagEntry($tagEntry, true);
			return true;
		}
		return false;
	}

/**
 * Lists the tags for a specified photo
 */
	public function listTags($photoId) {
		$albumId = $this->getAlbumID();
		$photoQuery = new Zend_Gdata_Photos_PhotoQuery;
		$photoQuery->setUser($this->get('picassa_user'));
		$photoQuery->setAlbumId($albumId);
		$photoQuery->setPhotoId($photoId);
		$query = $photoQuery->getQueryUrl() . "?kind=tag";
		$photoFeed = $this->gp->getPhotoFeed($query);
		if(count($photoFeed)) {
			$tag_array = array();
			foreach ($photoFeed as $entry) {
				if ($entry instanceof Zend_Gdata_Photos_TagEntry) {
					$tag_array[] = $entry->title->text;
				}
			}
			return $tag_array;
		}
		return false;
	}

}

?>
