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
$module_description = 'Mit diesem Modul k&ouml;nnen sie eine News Seite ihrer Seite hinzuf&uuml;gen.';

$MOD_NEWS = array (
	//	Variables for the backend
	'SETTINGS'			=> 'News Einstellungen',
	'CONFIRM_DELETE'	=> 'Sind Sie sicher, das Sie die News\nmit dem Titel &raquo;%s&laquo; l&ouml;schen m&ouml;chten?\nDas kann nicht widerufen werden!',
	
	//	Variables for the frontend
	'TEXT_READ_MORE'	=>'Weiterlesen',
	'TEXT_POSTED_BY'	=> 'Ver&ouml;ffentlicht von',
	'TEXT_ON'			=> 'am',
	'TEXT_LAST_CHANGED'	=> 'Zuletzt ge&auml;ndert am',
	'TEXT_AT'			=> 'um',
	'TEXT_BACK'			=> 'Zur&uuml;ck',
	'TEXT_COMMENTS'		=> 'Kommentare',
	'TEXT_COMMENT'		=> 'Kommentar',
	'TEXT_ADD_COMMENT'	=> 'Kommentar hinzuf&uuml;gen',
	'TEXT_BY'			=> 'von',
	'TEXT_PAGE_NOT_FOUND' => 'Seite nicht gefunden',
	'TEXT_UNKNOWN'		=> 'Gast',
	'TEXT_NO_COMMENT'	=> 'keine vorhanden'
);

?>