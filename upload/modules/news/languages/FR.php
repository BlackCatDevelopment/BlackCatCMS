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



//Module Description
$module_description = 'Ce type de page est conçu &agrave faire une page de nouvelles.';

//Variables for the  backend
$MOD_NEWS['SETTINGS'] = 'Configurations Nouvelles';

//Variables for the frontend
$MOD_NEWS['TEXT_READ_MORE'] = 'En savoir plus';
$MOD_NEWS['TEXT_POSTED_BY'] = 'Post&eacute; par';
$MOD_NEWS['TEXT_ON'] = '&agrave;';
$MOD_NEWS['TEXT_LAST_CHANGED'] = 'Derni&egrave;re modification';
$MOD_NEWS['TEXT_AT'] = '&agrave;';
$MOD_NEWS['TEXT_BACK'] = 'Retour';
$MOD_NEWS['TEXT_COMMENTS'] = 'Commentaires';
$MOD_NEWS['TEXT_COMMENT'] = 'Commentaire';
$MOD_NEWS['TEXT_ADD_COMMENT'] = 'Ajouter un commentaire';
$MOD_NEWS['TEXT_BY'] = 'Par';
$MOD_NEWS['TEXT_PAGE_NOT_FOUND'] = 'Page non trouv&eacute;e';
$MOD_NEWS['TEXT_UNKNOWN'] = 'Invit&eacute;';
$MOD_NEWS['TEXT_NO_COMMENT'] = 'none available';
?>