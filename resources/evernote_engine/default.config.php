<?php

/**
 * Evernote details
 */

	define("BASE_PATH", "/path/to/folder/evernote_engine/");
	define("BASE_URL", "http://webpath/to/folder/evernote_engine/");

/*	$EverNoteValues['username'] = '';
	$EverNoteValues['password'] = '';
	$EverNoteValues['consumerKey'] = '';
	$EverNoteValues['consumerSecret'] = '';*/

	$EverNoteValues['evernoteHost'] = 'www.evernote.com';
	$EverNoteValues['evernotePort'] = '443';
	$EverNoteValues['evernoteScheme'] = 'https';

	$EverNoteValues['spHostname'] = 'http://www.evernote.com';
	$EverNoteValues['requestTokenUrl'] = $EverNoteValues['spHostname'] . '/oauth';
	$EverNoteValues['accessTokenUrl'] = $EverNoteValues['spHostname'] . '/oauth';
	$EverNoteValues['authorizationUrlBase'] = $EverNoteValues['spHostname'] . '/OAuth.action';
	$EverNoteValues['noteStoreHost'] = 'www.evernote.com';
	$EverNoteValues['noteStorePort'] = '80';
	$EverNoteValues['noteStoreProto'] = 'https';
	$EverNoteValues['noteStoreUrl'] = 'edam/note/';
// 	$EverNoteValues['notebookGuid'] = '';

?>