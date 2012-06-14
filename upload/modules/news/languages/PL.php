<?php

/**
 *  @module         news
 *  @version        see info.php of this module
 *  @author         Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan (Stefek), Jurgen Nijhuis (Argos)
 *  @copyright      2004-2011, Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan (Stefek), Jurgen Nijhuis (Argos) 
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php'); 
} else {
	$subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));	$dir = $_SERVER['DOCUMENT_ROOT'];
	$inc = false;
	foreach ($subs as $sub) {
		if (empty($sub)) continue; $dir .= '/'.$sub;
		if (file_exists($dir.'/framework/class.secure.php')) { 
			include($dir.'/framework/class.secure.php'); $inc = true;	break; 
		} 
	}
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}
// end include class.secure.php

//	Modul Description
$module_description = 'Ten moduł wyświetla stronę wiadomości (News).';

$MOD_NEWS = array (
	//	Variables for the backend
	'SETTINGS' => 'News Ustawienia',
	'CONFIRM_DELETE'	=> 'Jestes pewien usuniecia &laquo;%s&raquo;?',
	
	//	Variables for the frontend
	'TEXT_READ_MORE' => 'Czytaj więcej',
	'TEXT_POSTED_BY' => 'Napisał(a)',
	'TEXT_ON' => 'dnia',
	'TEXT_LAST_CHANGED' => 'Edytowano',
	'TEXT_AT' => 'o',
	'TEXT_BACK' => 'Wstecz',
	'TEXT_COMMENTS' => 'Komentarze',
	'TEXT_COMMENT' => 'Komentarz',
	'TEXT_ADD_COMMENT' => 'Dodaj komentarz',
	'TEXT_BY' => 'Dodano: ',
	'TEXT_PAGE_NOT_FOUND' => 'Strona nie istnieje',
	'TEXT_UNKNOWN' => 'Gość',
	'TEXT_NO_COMMENT' => 'Brak komentarzy'
);
?>