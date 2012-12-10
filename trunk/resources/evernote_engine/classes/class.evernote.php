<?php
define("EVERNOTE_LIBS", dirname(__FILE__) . DIRECTORY_SEPARATOR . "evernote-sdk/lib");
ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . EVERNOTE_LIBS);

require_once("Thrift.php");
require_once("transport/TTransport.php");
require_once("transport/THttpClient.php");
require_once("protocol/TProtocol.php");
require_once("protocol/TBinaryProtocol.php");
require_once("packages/Types/Types_types.php");
require_once("packages/UserStore/UserStore.php");
require_once("packages/NoteStore/NoteStore.php");

// Import the classes that we're going to be using
use EDAM\NoteStore\NoteStoreClient;
use EDAM\UserStore\UserStoreClient;
use EDAM\NoteStore\NoteFilter;
use EDAM\Types\Data, EDAM\Types\Note, EDAM\Types\Resource, EDAM\Types\ResourceAttributes;
use EDAM\Error\EDAMSystemException, EDAM\Error\EDAMUserException, EDAM\Error\EDAMErrorCode;

// Verify that you successfully installed the PHP OAuth Extension
if (!class_exists('OAuth')) {
die("<span style=\"color:red\">The PHP OAuth Extension is not installed</span>");
}

Class EverNote {

	private $user;
	private $authToken;
	# private $authToken = 'S=s1:U=1c25:E=142b6ab5bb8:C=13b5efa2fb8:P=185:A=silverbiology-5164:H=f544ce4e4cd28e4dad308c6ed12ee804';
	public $evernoteData,$evernoteAccount;

	public function __construct ($evernoteArray) {
		$this->evernoteData = $evernoteArray;
	}

	public function authenticate($evernoteArray) {
		if(isset($evernoteArray['authToken']) && $evernoteArray['authToken'] != '') {
			$this->authToken = $evernoteArray['authToken'];
		}
		$this->evernoteAccount['evernoteData'] = $evernoteArray;
		try{
			$userStoreHttpClient = new THttpClient($this->evernoteData['evernoteHost'], $this->evernoteData['evernotePort'], "/edam/user", $this->evernoteData['evernoteScheme']);
			$userStoreProtocol = new TBinaryProtocol($userStoreHttpClient);
			$userStore = new UserStoreClient($userStoreProtocol, $userStoreProtocol);

			$this->evernoteAccount['user'] = $userStore->getUser($this->authToken);
			
			$noteStoreUrl = $userStore->getNoteStoreUrl($this->authToken);

			// $noteStoreUrl = str_replace('https:','http:',$noteStoreUrl);# temp code to mitigate the https issue

			$parts = parse_url($noteStoreUrl);
			if (!isset($parts['port'])) {
			  if ($parts['scheme'] === 'https') {
				$parts['port'] = 443;
			  } else {
				$parts['port'] = 80;
			  }
			}
			$noteStoreHttpClient = new THttpClient($parts['host'], $parts['port'], $parts['path'], $parts['scheme']);
			$noteStoreProtocol = new TBinaryProtocol($noteStoreHttpClient);
			$this->evernoteAccount['noteStore'] = new NoteStoreClient($noteStoreProtocol, $noteStoreProtocol);
			$this->evernoteAccount['notebookGuid'] = $this->getDefaultNoteBookId();
		
		} catch (EDAMSystemException $e) {
			if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode]);
			} else {
				return array('success' => false, 'error_code' => $e->getCode(), 'error_message' => $e->getMessage());
			}
		} catch (EDAMUserException $e) {
			if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode]);
			} else {
				return array('success' => false, 'error_code' => $e->getCode(), 'error_message' => $e->getMessage());
			}
		} catch (EDAMNotFoundException $e) {
			if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode]);
			} else {
				return array('success' => false, 'error_code' => $e->getCode(), 'error_message' => $e->getMessage());
			}
		} catch (Exception $e) {
			return array('success' => false, 'error_message' => $e->getMessage());
		}

	}

	public function getName( $name, $part = 'name' ) {
		if ($part == 'name' || $part == 'ext') {
			$extAr = explode('.', $name);
			$ext = array_pop($extAr);
			$name = implode('.',$extAr);
			return ($part == 'name') ? $name : $ext;
		} else {
			return ($name);
		}
	}

/**
 * Creates a note from the given image
 * @param string $label : path of the image
 * @param string $title : title of the note
 * @return mixed $note
 */
	public function createNote($label,$title,$tag) {

		$image = file_get_contents($label);
		$hash = md5($image);
		
		$data = new Data();
		$data->size = strlen($image);
		$data->bodyHash = $hash;
		$data->body = $image;

		$resource = new Resource();
		$ext = $this->getName(@basename($label),'ext');
		$resource->mime = 'image/'. ($ext == 'jpg' ? 'jpeg' : $ext);
		$resource->data = $data;

		$note = new Note();
		$note->guid = null;
		$note->title = $title;
		$note->content = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE en-note SYSTEM "http://xml.evernote.com/pub/enml.dtd"><en-note><en-media width="671" height="503" type="' . $resource->mime . '" hash="' . $hash . '"/></en-note>';
		$note->contentHash = null;
		$note->contentLength = null;
		$note->created = time()*1000;
		$note->updated = time()*1000;
		$note->deleted = null;
		$note->active = null;
		$note->updateSequenceNum = null;
		$note->notebookGuid = $this->evernoteAccount['notebookGuid'];
		$note->tagGuids = null;
		$note->resources = array($resource);
		$note->attributes = null;
		$note->tagNames = $tag;

		return $note;
	}


/**
 * Adds the given note to evernote
 * @param mixed $note
 */
	public function addNote($newnote) {
		if (isset($this->authToken)) {
		try{
			$noteRet = $this->evernoteAccount['noteStore']->createNote($this->authToken, $newnote);
			
		} catch (EDAMSystemException $e) {
			if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode], 'error_message' => $e->parameter);
			} else {
				return array('success' => false, 'error_code' => $e->getCode(), 'error_message' => $e->getMessage());
			}
		} catch (EDAMUserException $e) {
			if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode], 'error_message' => $e->parameter);
			} else {
				return array('success' => false, 'error_code' => $e->getCode(), 'error_message' => $e->getMessage());
			}
		} catch (EDAMNotFoundException $e) {
			if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode], 'error_message' => $e->parameter);
			} else {
				return array('success' => false, 'error_code' => $e->getCode(), 'error_message' => $e->getMessage());
			}
		} catch (Exception $e) {
			return array('success' => false, 'error_code' => $e->getCode(), 'error_message' => $e->getMessage());
		}

		}
		return array('success' => true, 'noteRet' => $noteRet);
	}

	public function getDefaultNoteBookId() {
		$notebooks = $this->listNotebooks();
		if( is_array($notebooks) && count($notebooks) ) {
			return $notebooks[0]->guid;
		}
		return false;
	}

	public function listNotebooks() {
		try{

			$notebooks = $this->evernoteAccount['noteStore']->listNotebooks($this->authToken);
		} catch (EDAMSystemException $e) {
			if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode], 'error_message' => $e->parameter);
			} else {
				return array('success' => false, 'error_code' => $e->getCode(), 'error_message' => $e->getMessage());
			}
		} catch (EDAMUserException $e) {
			if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode], 'error_message' => $e->parameter);
			} else {
				return array('success' => false, 'error_code' => $e->getCode(), 'error_message' => $e->getMessage());
			}
		} catch (EDAMNotFoundException $e) {
			if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode], 'error_message' => $e->parameter);
			} else {
				return array('success' => false, 'error_code' => $e->getCode(), 'error_message' => $e->getMessage());
			}
		} catch (Exception $e) {
			return array('success' => false, 'error_code' => $e->errorCode);
		}
		return $notebooks;
	}

	public function listNotes($noteGuid) {
		$note_detail = $this->evernoteAccount['noteStore']->getNote($this->authToken,$noteGuid,true,true,true,true);
		return $note_detail;
	}

	public function getRecognition($noteGuid) {
		try {
			$reco = $this->evernoteAccount['noteStore']->getResourceRecognition($this->authToken,$noteGuid);
		} catch (EDAMSystemException $e) {
			if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode], 'error_message' => $e->parameter);
			} else {
				return array('success' => false, 'error_code' => $e->getCode(), 'error_message' => $e->getMessage());
			}
		} catch (EDAMUserException $e) {
			if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode], 'error_message' => $e->parameter);
			} else {
				return array('success' => false, 'error_code' => $e->getCode(), 'error_message' => $e->getMessage());
			}
		} catch (EDAMNotFoundException $e) {
			if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode], 'error_message' => $e->parameter);
			} else {
				return array('success' => false, 'error_code' => $e->getCode(), 'error_message' => $e->getMessage());
			}
		} catch (Exception $e) {
			return array('success' => false, 'error_code' => $e->getCode(), 'error_message' => $e->getMessage());
		}
		return $reco;
	}

	public function getusage() {
		try{
			$syncState = $this->evernoteAccount['noteStore']->getSyncState($this->authToken);
			$uploadLimitEnd = $this->evernoteAccount['user']->accounting->uploadLimitEnd;
			$syncState->uploadLimitEnd = $uploadLimitEnd;
		} catch (EDAMSystemException $e) {
			if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode], 'error_message' => $e->parameter);
			}
		} catch (EDAMUserException $e) {
			if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode], 'error_message' => $e->parameter);
			}
		} catch (EDAMNotFoundException $e) {
			if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode], 'error_message' => $e->parameter);
			}
		} catch (Exception $e) {
			return array('success' => false, 'error_code' => $e->errorCode);
		}
		return $syncState;
	}


	public function filterOcrData($ocrdata = '') {
		if(!is_array($ocrdata)) return false;

		$testAr = $ocrdata['recoIndex']['item'];

		$resArray = array();

		$ruleArray = array(10 => array('check' => 5, 'low' => 3, 'high' => 5), 15 => array('check' => 5, 'low' => 5, 'high' => 8), 20 => array('check' => 5, 'low' => 8, 'high' => 10));

		foreach($testAr as $r) {
			$ct = count($r['t']);
			$case = ($ct>20)?20:(($ct>15)?15:(($ct>10)?10:1));
			if($case>1) {
				$k=(strlen($r['t'][0]) > $ruleArray[$case]['check'])?'high':'low';
				$resArray[] = array('@attributes' => $r['@attributes'],'t' => array_slice($r['t'],0,$ruleArray[$case][$k]));
			} else {
				$resArray[] = array('@attributes' => $r['@attributes'],'t' => $r['t']);
			}
		}
		$ocrdata['recoIndex']['item'] = $resArray;
		return $ocrdata;
	}

/**
 * Creates a filter object from the given array of params
 * @param mixed $filter : array of params
 * @return mixed filter Object
 */
	public function createFilter($filter) {
		$filterArray = array('order' => null
					, 'ascending' => null
					, 'words' => null
					, 'notebookGuid' => null
					, 'tagGuids' => null
					, 'timeZone' => null
					, 'inactive' => null);
		foreach($filterArray as $index => &$value) {
			if(isset($filter[$index]) && $filter[$index] != '') {
				$value = $filter[$index];
			}
		}
		$filterObject = new NoteFilter($filterArray);
		return $filterObject;
	}


	public function findEverNotes($filter,$start = 0,$limit = 25) {
		if (isset($this->authToken)) {
			try{
				$noteRet = $this->evernoteAccount['noteStore']->findNotes($this->authToken, $filter, $start,$limit);
				return array('success' => true, 'noteRet' => $noteRet);
			} catch (EDAMSystemException $e) {
				if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
					return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode]);
				}
			} catch (EDAMUserException $e) {
				if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
					return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode]);
				}
			} catch (EDAMNotFoundException $e) {
				if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
					return array('success' => false, 'error_code' => EDAMErrorCode::$__names[$e->errorCode]);
				}
			} catch (EDAMUserException $e) {
				return array('success' => false, 'userException' => 1, 'error_code' => $e->errorCode);
			} catch (EDAMNotFoundException $e) {
				return array('success' => false, 'notFoundException' => 1, 'error_code' => $e->errorCode);
			} catch (Exception $e) {
				return array('success' => false, 'error_code' => $e->errorCode);
			}
		}

	}
}

?>