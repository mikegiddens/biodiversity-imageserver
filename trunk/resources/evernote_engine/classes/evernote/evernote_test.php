<?php
ob_start();
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . "bootstrap.php");
require_once("OAuth/SimpleRequest.php");

if ( is_file( dirname(__FILE__) . DIRECTORY_SEPARATOR . "localDefines.php"))
	require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . "localDefines.php");

/**************** CONFIGURATION ****************/
/**
 * This set of information will be provided to you by Evernote when
 * you register for use of the API.
 *
 */
define("consumerKey", "silverbiology");
define("consumerSecret", "30e1bfcea220bcfa");
define("spHostname","http://sandbox.evernote.com");
define("requestTokenUrl", spHostname . "/oauth");
define("accessTokenUrl", spHostname . "/oauth");
define("authorizationUrlBase", spHostname . "/OAuth.action");
// define("noteStoreHost", "stage.evernote.com");
define("noteStoreHost", "sandbox.evernote.com");
define("noteStorePort", "80");
define("noteStoreProto", "https");
define("noteStoreUrl", "edam/note/");

/**
 * Handlers for redirect schema.
 * Evernote provides two redirect schemas - FULL page redirects and 
 * EMBEDed page redirects - as far as the user sees - are not redirects at all,
 * because they are to be embeded on the Consumer's page.
 *
 * Redirects occur when Consumer needs to redirect the User to Evernote in order to
 * authorize Request Token recieved from Evernote by the Consumer.
 *
 */
define("REDIR_FULL", "FULL");
define("REDIR_EMBED", "EMBED");
define("REDIR_DYNAMIC_EMBED", "DYNAMIC_EMBED");

/**
 * Callback configuration:
 *  $callbackUrl is the URL to which Evernote should redirect user after that user authorized Request Token
 *  $callbackEmebedUrl is the same as $callbackUrl but for EMEBEDed redirect schema
 *
 */
define("callbackUrl", "evernote_test.php");
define("callbackEmbedUrl", "callback.php");

$thisUri = (empty($_SERVER['HTTPS'])) ? "http://" : "https://";
$thisUri .= $_SERVER['SERVER_NAME'];
$thisUri .= ($_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443) ? "" : (":".$_SERVER['SERVER_PORT']);
$thisUri .= $_SERVER['REQUEST_URI'];

/**************** INIT ****************/
// start session if none exists - we use it to track tokens and what not
session_start();

// intercept action
$action = isset($_GET['action']) ? $_GET['action'] : null;
if ($action == 'reset') {
	resetState(true);
}

// update ourselves with redirect schema preference
$redirSchema = (isset($_GET['redirSchema'])) ? $_GET['redirSchema'] : null;
$redirSchema = ($redirSchema == null && isset($_SESSION['redirSchema'])) ? $_SESSION['redirSchema'] : $redirSchema;
$redirSchema = ($redirSchema == null) ? REDIR_FULL : $redirSchema;
$_SESSION['redirSchema'] = $redirSchema;

if (isset($_GET['oauth_token']))
	$_SESSION['requestToken'] = $_GET['oauth_token'];

// callback URL - this is passed to Evernote so that Evernote can return to this URL
// after the user authorizes the request token
$authorizationUrl = null;

/**************** ACTIONS ****************/
/**
 * Reset state
 *
 */
function resetState($quiet = false) {
	resetSession();
	$redirSchema = REDIR_FULL;
	$_SESSION['redirSchema'] = $redirSchema;
	if (!$quiet)
		print "Removed all attributes from user session\n";
}
/**
 * Retrieves request token from Evernote
 *
 */
function getRequestToken() {
	// Send an OAuth message to the Provider asking for a new Request
	// Token because we don't have access to the current user's account.
	$oauthRequestor = new OAuth_SimpleRequest(requestTokenUrl, consumerKey, consumerSecret);
	print "Request: " . $oauthRequestor->encode() . "\n";
	try {
		$oauthRequestor->sendRequest();
		print "Reply: " . $oauthRequestor->getResponseBody() . "\n";
		$r = $oauthRequestor->getResponseStruct();
		$reply = $oauthRequestor->getResponseStruct();
		if (isset($reply['oauth_token'])) {
			$_SESSION['requestToken'] = $reply['oauth_token'];
		}
	} catch (HTTP_Exception $e) {
		print $e->getMessage() . ": " . $e->getCode() . " " . $e->getCauseMessage() . "\n";
	}
}
/**
 * Retrieves access token from Evernote
 *
 */
function getAccessToken() {
	// Send an OAuth message to the Provider asking to exchange the
	// existing Request Token for an Access Token
	$oauthRequestor = new OAuth_SimpleRequest(requestTokenUrl, consumerKey, consumerSecret);
	$oauthRequestor->setParameter("oauth_token", $_SESSION['requestToken']);
	print "Request: " . $oauthRequestor->encode() . "\n";
	
	try {
		$oauthRequestor->sendRequest();
		print "Reply: " . $oauthRequestor->getResponseBody() . "\n";
		$reply = $oauthRequestor->getResponseStruct();
		if (isset($reply['oauth_token']))
			$_SESSION['accessToken'] = $reply['oauth_token'];
		if (isset($reply['edam_shard']))
			$_SESSION['shardId'] = $reply['edam_shard'];
		if (isset($_SESSION['requestToken']))
			unset($_SESSION['requestToken']);

	} catch (HTTP_Exception $e) {
		print $e->getMessage() . ": " . $e->getCode() . " " . $e->getCauseMessage() . "\n";
	}
}
/**
 * Handles callback after user has authorized request token
 *
 */
function callbackReturn() {
	if (isset($_GET['requestToken']))
		$_SESSION['requestToken'] = $_GET['requestToken'];
}
/**
 * Lists user's notebooks. This is a sample interaction with Evernote service
 *
 */
function listNotebooks() {
	if (!isset($_SESSION['shardId'])) print "No shardId. ";
	if (!isset($_SESSION['accessToken'])) print "No accessToken. ";
	if (!isset($_SESSION['accessToken']) || !isset($_SESSION['shardId'])) 
		return;
	
	try {
		$noteStoreTrans = new THttpClient(noteStoreHost, noteStorePort, noteStoreUrl . $_SESSION['shardId'], noteStoreProto);
		$noteStoreProt = new TBinaryProtocol($noteStoreTrans);
		$noteStore = new NoteStoreClient($noteStoreProt, $noteStoreProt);
		$notebooks = $noteStore->listNotebooks($_SESSION['accessToken']);
		if (!empty($notebooks)) {
			foreach ($notebooks as $notebook) {
				print "Notebook: ".$notebook->name."\n";
			}
		}
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

function addNote($newnote) {
   global $noteRet;
//    if (empty($noteRet) && isAuthed()) {
   if (empty($noteRet) && isset($_SESSION['accessToken'])) {
//       try {
         $noteStoreTrans = new THttpClient(noteStoreHost, noteStorePort, noteStoreUrl . $_SESSION['shardId'], noteStoreProto);
         $noteStoreProt = new TBinaryProtocol($noteStoreTrans);
         $noteStore = new NoteStoreClient($noteStoreProt, $noteStoreProt);
         $noteRet = $noteStore->createNote($_SESSION['accessToken'], $newnote);
var_dump($noteRet);
//          echo $noteRet."hello";
 /*     } catch (edam_error_EDAMSystemException $e) {
         if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
            raiseAppError(edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter);
         } else {
            raiseAppError($e->getCode() . ": " . $e->getMessage());
         }
      } catch (edam_error_EDAMUserException $e) {
         if ($e->errorCode == $GLOBALS['edam_error_E_EDAMErrorCode']['AUTH_EXPIRED']) {
            resetSession();
            getRequestToken();
            raiseAppError("Authorization expired. You must re-authorize access with Evernote.");
         } else {
            if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
               raiseAppError(edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter);
            } else {
               raiseAppError($e->getCode() . ": " . $e->getMessage());
            }
         }
      } catch (edam_error_EDAMNotFoundException $e) {
         if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
            raiseAppError(edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter);
         } else {
            raiseAppError($e->getCode() . ": " . $e->getMessage());
         }
      } catch (Exception $e) {
         raiseAppError("Unexpected error: " . $e->getMessage());
      }*/
   }
   return $noteRet;
}

function test_list () {
      try {
	$noteStoreTrans = new THttpClient(noteStoreHost, noteStorePort, noteStoreUrl . $_SESSION['shardId'], noteStoreProto);
	$noteStoreProt = new TBinaryProtocol($noteStoreTrans);
	$noteStore = new NoteStoreClient($noteStoreProt, $noteStoreProt);
	$filter = new edam_notestore_NoteFilter();
	$filter->notebookGuid = '8d8cadae-3641-4890-8514-2f258641d661';
	print "<pre>";
	$noteList = $noteStore->findNotes($_SESSION['accessToken'], $filter, 0, 100);
	var_dump($noteList);
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

function getNote() {
	$noteStore = getStoreObj();
	$guid = 'e341f51f-ba94-4d99-afec-dd8895c46fe5';

	$note_detail = $noteStore->getNote($_SESSION['accessToken'],$guid,true,true,true,true);
	var_dump($note_detail);
}

function getRecognition() {
	$noteStore = getStoreObj();
	$guid = '1314ecb2-37d8-4f17-a7d3-3e09da4046dc';
try{
	$reco = $noteStore->getResourceRecognition($_SESSION['accessToken'],$guid);
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
	print '<br> Recognition : ' . $reco;
}

function testFunction() {
	$noteStoreTrans = new THttpClient(noteStoreHost, noteStorePort, noteStoreUrl . $_SESSION['shardId'], noteStoreProto);
	$noteStoreProt = new TBinaryProtocol($noteStoreTrans);
	$noteStore = new NoteStoreClient($noteStoreProt, $noteStoreProt);
	$notebooks = $noteStore->listNotebooks($_SESSION['accessToken']);

	$filter = new edam_notestore_NoteFilter();
	$filter->notebookGuid = "8d8cadae-3641-4890-8514-2f258641d661";

	$noteList = $noteStore->findNotes($_SESSION['accessToken'], $filter, 0, 100);

foreach ($noteList->notes as $note) {
	print '<br> Title : ' . $note->title . ' GUID : ' . $note->guid;
}

}

function getStoreObj() {
	$noteStoreTrans = new THttpClient(noteStoreHost, noteStorePort, noteStoreUrl . $_SESSION['shardId'], noteStoreProto);
	$noteStoreProt = new TBinaryProtocol($noteStoreTrans);
	$noteStore = new NoteStoreClient($noteStoreProt, $noteStoreProt);
	return $noteStore;
}

function hasErrors() {
	if (isset($_SESSION['app_errors']) && is_array($_SESSION['app_errors']) && count($_SESSION['app_errors']) > 0)
		return true;
	return false;
}

function raiseAppError($message, $code = 0) {
	if (!hasErrors())
		$_SESSION['app_errors']= array();
	$_SESSION['app_errors'][] = PEAR::raiseError($message, intval($code));
}

/**************** OTHER FUNCTIONS ****************/
/**
 * Constructs and returns callback url relevant for current redirect schema
 */
function getCallbackUrl() {
	global $redirSchema;
	global $authorizationUrl;
	
	if (empty($authorizationUrl)) {
		$cbUrl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
		$cbUrl = preg_replace("/[^\/]+$/", "", $cbUrl);
		if (!empty($redirSchema) && $redirSchema == REDIR_EMBED)
			$cbUrl .= callbackEmbedUrl;
		else
			$cbUrl .= callbackUrl;
			
		$authorizationUrl = authorizationUrlBase . "?oauth_callback=" .
			rawurlencode($cbUrl) . "&oauth_token=" .
			$_SESSION['requestToken'];
		if (!empty($redirSchema) && ($redirSchema == REDIR_EMBED || $redirSchema == DYNAMIC_EMBED))
			$authorizationUrl .= "&format=microclip";
	}
	return $authorizationUrl;
}

function resetSession() {
	$_SESSION = array();
	$_SESSION['requestToken'] = null;
	$_SESSION['accessToken'] = null;
}


/**************** MAIN ****************/
?>
<html>
	<head><title>Evernote OAuth test</title></head>
	<body style="background-color: white;">
<?
if (isset($action) && !empty($action)) { ?>
    <hr/>
    <h3>Action results:</h3>
    <pre>
<?php 
	switch($action) {
		case "reset":
			resetState();
			break;
		case "getRequestToken":
			getRequestToken();
			break;
		case "getAccessToken":
			getAccessToken();
			break;
		case "callbackReturn":
			callbackReturn();
			break;
		case "listNotebooks":
			listNotebooks();
			break;
		case "addNote":

// image = open('enlogo.png', 'r').read()
// hashHex = md5.new(image).hexdigest()
// 
// data = Types.Data()
// data.size = len(image)
// data.bodyHash = hashHex
// data.body = image
// 
// resource = Types.Resource()
// resource.mime = 'image/png'
// resource.data = data

// 			$handle = fopen('6.jpg','r');
// 			$image = fread($handle,filesize('6.jpg'));
// 			fclose($handle);

$image = file_get_contents('6.jpg');

// var_dump($image);

// 			$hash = md5(bin2hex($image));
			$hash = md5($image);

$data = new edam_type_Data();
$data->size = strlen($image);
$data->bodyHash = $hash;
$data->body = $image;

$resource = new edam_type_Resource();
$resource->mime = 'image/jpeg';
$resource->data = $data;

			$note = new edam_type_Note();
// 			$note = new Note();
			$note->guid = null;
			$note->title = "Image test Title";
			$note->content = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE en-note SYSTEM "http://xml.evernote.com/pub/enml.dtd"><en-note><en-media width="671" height="503" type="image/jpeg" hash="' . $hash . '"/></en-note>';
			$note->contentHash = null;
			$note->contentLength = null;
			$note->created = time()*1000;
			$note->updated = time()*1000;
			$note->deleted = null;
			$note->active = null;
			$note->updateSequenceNum = null;
			$note->notebookGuid = '8d8cadae-3641-4890-8514-2f258641d661';
			$note->tagGuids = null;
			$note->resources = array($resource);
			$note->attributes = null;
			$note->tagNames = null;
			addNote($note);
			break;
		case 'getNote':
			getNote();
			break;
		case 'getRecognition':
			getRecognition();
			break;
		case 'testList':
			test_list();
			break;
		case 'test':
			testFunction();
			break;
	}
var_dump($_SESSION);

?>
	</pre>
    <hr/>
<?php
}

?>

<!-- Redirection method selection -->
<!-- <h5><?= $thisUri ?></h5> -->
<form>
    Redirect method: <select action="/EDAMWebTest" 
        method="GET" 
        name="redirSchema"
        onChange="document.forms[0].submit();">
        <option value="<?= REDIR_FULL ?>" <?php if (isset($redirSchema) && 
        	$redirSchema == REDIR_FULL) echo "selected" ?> >Full page</option>
        <option value="<?= REDIR_EMBED ?>" <?php if (isset($redirSchema) &&
        	$redirSchema == REDIR_EMBED) echo "selected" ?> >Embeded authorization</option>
        <option value="<?= REDIR_DYNAMIC_EMBED ?>" <?php if (isset($redirSchema) &&
        	$redirSchema == REDIR_DYNAMIC_EMBED) echo "selected" ?> >Dynamically embeded authorization</option>
    </select>
</form>

<!-- Information used by consumer -->
<h3>Evernote EDAM API Web Test State</h3>
Consumer key: <?= consumerKey ?><br/>
Request token URL: <?= requestTokenUrl ?><br/>
Access token URL: <?= accessTokenUrl ?><br/>
Authorization URL Base: <?= authorizationUrlBase ?><br/>
<br/>
User request token: <?= $_SESSION['requestToken'] ?><br/>
User access token: <?= $_SESSION['accessToken'] ?><br/>
User shardId: <?= $_SESSION['shardId'] ?>

<!-- Manual operation controls -->
<hr/>
<h3>Actions</h3>

<!-- Reset state -->
<a href="?action=reset">Reset user session</a><br/>


<?php if (empty($_SESSION['accessToken']) && empty($_SESSION['requestToken'])): ?>
	<!-- Request Request Token -->
	<a href='?action=getRequestToken'>Get OAuth Request Token from Provider</a><br/>
<?php endif; ?>

<?php 
// AUTH_LINKS
if (empty($_SESSION['accessToken']) && !empty($_SESSION['requestToken'])) { ?>
	<!-- Link to request access token from service provider -->
	<a href="?action=getAccessToken">Get OAuth Access Token from Provider</a>
	<br/>

	<!-- Link to obtain user authorization -->
<?php if (!empty($redirSchema) && $redirSchema == DYNAMIC_EMBED) { ?>
	<script type="text/javascript">
		var target = '<?= getCallbackUrl() ?>';
		var authFrame = document.createElement("iframe");
		authFrame.width = 400;
		authFrame.height = 200;
		authFrame.name="iframe1"
		authFrame.setAttribute("frameborder", 1);
		authFrame.setAttribute("scrolling", "no");
		authFrame.src=target
		var urlText = document.createTextNode('<?= getCallbackUrl() ?>');
		//document.body.appendChild(document.createElement("br"));
		//document.body.appendChild(urlText);
		document.body.appendChild(document.createElement("br"));
		document.body.appendChild(authFrame);
	</script>
<?php } else if (!empty($redirSchema) && $redirSchema == REDIR_EMBED) { ?>
    <a href='<?= getCallbackUrl() ?>' target='iframe1'>Send user to get authorization</a><br/>
    <iframe name="iframe1" src="about:blank" frameborder="1" scrolling='no'
            width="400" height="200"></iframe>
<?php } else { ?>
    <a href='<?= getCallbackUrl() ?>'>Send user to get authorization</a>
<?php } ?>
    <br/>

<?php
// END OF AUTH_LINKS
} ?>

<?php if (!empty($_SESSION['accessToken'])): ?>
  <!-- Sample usage -->
  <a href="?action=listNotebooks">List notebooks in account</a><br/>
<?php endif; ?>
<?php if (!empty($_SESSION['accessToken'])): ?>
  <!-- Sample usage -->
  <a href="?action=testList">List Notes in Notebook</a><br/>
<?php endif; ?>
<?php if (!empty($_SESSION['accessToken'])): ?>
  <!-- Sample usage -->
  <a href="?action=addNote">Add a Note in the Notebook</a><br/>
<?php endif; ?>
<?php if (!empty($_SESSION['accessToken'])): ?>
  <!-- Sample usage -->
  <a href="?action=test">Test function</a><br/>
<?php endif; ?>
<?php if (!empty($_SESSION['accessToken'])): ?>
  <!-- Sample usage -->
  <a href="?action=getNote">Get Note Detail</a><br/>
<?php endif; ?>
<?php if (!empty($_SESSION['accessToken'])): ?>
  <!-- Sample usage -->
  <a href="?action=getRecognition">Get Recognition xml</a><br/>
<?php endif; ?>

</body>
</html>
<?php
ob_end_flush();
?>