<?php
require_once( BASE_PATH . 'classes/evernote' . DIRECTORY_SEPARATOR . "bootstrap.php");
if ( is_file( BASE_PATH . 'classes/evernote' . DIRECTORY_SEPARATOR . "localDefines.php"))
	require_once( BASE_PATH . 'classes/evernote' . DIRECTORY_SEPARATOR . "localDefines.php");

Class EverNote {

	private $user, $authToken;
	public $evernote_data,$evernote_account;

	public function __construct ($evernote_array) {
		$this->evernote_data = $evernote_array;
	}

	public function authenticate($evernote_array) {
		$this->evernote_account['evernote_data'] = $evernote_array;
		try{
			$userStoreHttpClient = new THttpClient($this->evernote_data['evernoteHost'], $this->evernote_data['evernotePort'], "/edam/user", $this->evernote_data['evernoteScheme']);
			$userStoreProtocol = new TBinaryProtocol($userStoreHttpClient);
			$userStore = new UserStoreClient($userStoreProtocol, $userStoreProtocol);
	
			$authResult = $userStore->authenticate($evernote_array['username'], $evernote_array['password'], $evernote_array['consumerKey'], $evernote_array['consumerSecret']);

			$this->evernote_account['user'] = $authResult->user;
			$this->evernote_account['authToken'] = $authResult->authenticationToken;

			$noteStoreHttpClient = new THttpClient($this->evernote_data['evernoteHost'], $this->evernote_data['evernotePort'], '/edam/note/' . $this->evernote_account['user']->shardId, $this->evernote_data['evernoteScheme']);
			$noteStoreProtocol = new TBinaryProtocol($noteStoreHttpClient);
			$this->evernote_account['noteStore'] = new NoteStoreClient($noteStoreProtocol, $noteStoreProtocol);
			$this->evernote_account['notebookGuid'] = $this->getDefaultNoteBookId();
		
		} catch (edam_error_EDAMSystemException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
			} else {
				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (edam_error_EDAMUserException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
			} else {
				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (edam_error_EDAMNotFoundException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
			} else {
				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (Exception $e) {
			print $e->getMessage();
		}

	}

	public function getName( $name, $part = 'name' ) {
		if ($part == 'name' || $part == 'ext') {
			$ext = split('\.', $name);
			return ($part == 'name') ? $ext[0] : $ext[1];
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
	public function createNote($label,$title) {

		$image = file_get_contents($label);
		$hash = md5($image);
		
		$data = new edam_type_Data();
		$data->size = strlen($image);
		$data->bodyHash = $hash;
		$data->body = $image;
		
		$resource = new edam_type_Resource();
		$ext = $this->getName(@basename($label),'ext');
		$resource->mime = 'image/'. ($ext == 'jpg' ? 'jpeg' : $ext);
		$resource->data = $data;

		$note = new edam_type_Note();
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
		$note->notebookGuid = $this->evernote_account['notebookGuid'];
		$note->tagGuids = null;
		$note->resources = array($resource);
		$note->attributes = null;
		$note->tagNames = null;

		return $note;
	}


/**
 * Adds the given note to evernote
 * @param mixed $note
 */
	public function addNote($newnote) {
		if (isset($this->evernote_account['authToken'])) {
		try{
			$noteRet = $this->evernote_account['noteStore']->createNote($this->evernote_account['authToken'], $newnote);
			return array('success' => true, 'noteRet' => $noteRet);
			
		} catch (edam_error_EDAMSystemException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
// 				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
				return array('success' => false, 'error_code' => $e->errorCode);
			} else {
// 				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (edam_error_EDAMUserException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
// 				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
				return array('success' => false, 'error_code' => $e->errorCode);
			} else {
// 				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (edam_error_EDAMNotFoundException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
// 				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
				return array('success' => false, 'error_code' => $e->errorCode);
			} else {
// 				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (Exception $e) {
			print $e->getMessage();
		}

		}
// 		return $noteRet;
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

			$notebooks = $this->evernote_account['noteStore']->listNotebooks($this->evernote_account['authToken']);
		} catch (edam_error_EDAMSystemException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
// 				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
				return array('success' => false, 'error_code' => $e->errorCode);
			} else {
// 				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (edam_error_EDAMUserException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
// 				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
				return array('success' => false, 'error_code' => $e->errorCode);
			} else {
// 				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (edam_error_EDAMNotFoundException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
// 				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
				return array('success' => false, 'error_code' => $e->errorCode);
			} else {
// 				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (Exception $e) {
// 			print $e->getMessage();
			return array('success' => false, 'error_code' => $e->errorCode);
		}
		return $notebooks;
	}

	public function listNotes($noteGuid) {
		$note_detail = $this->evernote_account['noteStore']->getNote($this->evernote_account['authToken'],$noteGuid,true,true,true,true);
		return $note_detail;
	}

	public function getRecognition($noteGuid) {
		try {
			$reco = $this->evernote_account['noteStore']->getResourceRecognition($this->evernote_account['authToken'],$noteGuid);
		} catch (edam_error_EDAMSystemException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
// 				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
				return array('success' => false, 'error_code' => $e->errorCode);
			} else {
// 				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (edam_error_EDAMUserException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
// 				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
				return array('success' => false, 'error_code' => $e->errorCode);
			} else {
// 				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (edam_error_EDAMNotFoundException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
// 				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
				return array('success' => false, 'error_code' => $e->errorCode);
			} else {
// 				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (Exception $e) {
// 			print $e->getMessage();
		}
		return $reco;
	}

	public function getusage() {
		try{
			$syncState = $this->evernote_account['noteStore']->getSyncState($this->evernote_account['authToken']);
			$uploadLimitEnd = $this->evernote_account['user']->accounting->uploadLimitEnd;
			$syncState->uploadLimitEnd = $uploadLimitEnd;
		} catch (edam_error_EDAMSystemException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => $e->errorCode);
			}
		} catch (edam_error_EDAMUserException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => $e->errorCode);
			}
		} catch (edam_error_EDAMNotFoundException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
				return array('success' => false, 'error_code' => $e->errorCode);
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
		$filterObject = new edam_notestore_NoteFilter($filterArray);
		return $filterObject;
	}


	public function findEverNotes($filter,$start = 0,$limit = 25) {
		if (isset($this->evernote_account['authToken'])) {
			try{
				$noteRet = $this->evernote_account['noteStore']->findNotes($this->evernote_account['authToken'], $filter, $start,$limit);
				return array('success' => true, 'noteRet' => $noteRet);
			} catch (edam_error_EDAMSystemException $e) {
				if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
					return array('success' => false, 'error_code' => $e->errorCode);
				}
			} catch (edam_error_EDAMUserException $e) {
				if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
					return array('success' => false, 'error_code' => $e->errorCode);
				}
			} catch (edam_error_EDAMNotFoundException $e) {
				if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
					return array('success' => false, 'error_code' => $e->errorCode);
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