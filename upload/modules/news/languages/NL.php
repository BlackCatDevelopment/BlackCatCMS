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
} else {
	$oneback = "../";
	$root = $oneback;
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= $oneback;
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php




//Modul Description
$module_description = 'Met deze module maak je een nieuwspagina.';

//Variables for the backend
$MOD_NEWS['SETTINGS'] = 'Instellingen van de Nieuwsmodule';

//Variables for the frontend
$MOD_NEWS['TEXT_READ_MORE'] = 'Lees verder';
$MOD_NEWS['TEXT_POSTED_BY'] = 'Geplaatst door';
$MOD_NEWS['TEXT_ON'] = 'op';
$MOD_NEWS['TEXT_LAST_CHANGED'] = 'Laatst vernieuwd';
$MOD_NEWS['TEXT_AT'] = 'om';
$MOD_NEWS['TEXT_BACK'] = 'Terug';
$MOD_NEWS['TEXT_COMMENTS'] = 'Reacties';
$MOD_NEWS['TEXT_COMMENT'] = 'Reactie';
$MOD_NEWS['TEXT_ADD_COMMENT'] = 'Toevoegen reactie';
$MOD_NEWS['TEXT_BY'] = 'door';
$MOD_NEWS['TEXT_PAGE_NOT_FOUND'] = 'Pagina niet gevonden';
$MOD_NEWS['TEXT_UNKNOWN'] = 'Gast';
$MOD_NEWS['TEXT_NO_COMMENT'] = 'niet aanwezig';
?>