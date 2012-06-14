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
$module_description = 'Questa pagina è fatta per creare pagine di news.';

$MOD_NEWS = array (
	//	Variables for the backend
	'SETTINGS' => 'Configurazione News',
	'CONFIRM_DELETE'	=> 'se sicuro di voler cancellare la news &laquo;%s&raquo;?',

	//	Variables for the frontend
	'TEXT_READ_MORE' => 'Leggi Tutto',
	'TEXT_POSTED_BY' => 'Postato da',
	'TEXT_ON' => 'il',
	'TEXT_LAST_CHANGED' => 'Ultima modifica',
	'TEXT_AT' => 'in',
	'TEXT_BACK' => 'Torna Indietro',
	'TEXT_COMMENTS' => 'Commenti',
	'TEXT_COMMENT' => 'Commento',
	'TEXT_ADD_COMMENT' => 'Aggiungi Commento',
	'TEXT_BY' => 'Di',
	'TEXT_PAGE_NOT_FOUND' => 'Pagina non trovata',
	'TEXT_UNKNOWN' => 'Ospite',
	'TEXT_NO_COMMENT' => 'non disponibile'
);
?>