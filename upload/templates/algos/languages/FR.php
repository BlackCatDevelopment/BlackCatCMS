<?php

/**
 *  @template       Algos Backend-Theme
 *  @version        see info.php of this template
 *  @author         Jurgen Nijhuis & Ruud Eisinga, Dietrich Roland Pehlke
 *  @copyright      2009-2011 Jurgen Nijhuis & Ruud Eisinga, Dietrich Roland Pehlke
 *  @license        GNU General Public License
 *  @license terms  see info.php of this template
 *  @platform       LEPTON, see info.php of this template
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
$template_description 	= 'Enhanced backend theme for Lepton CMS';

//Texts
$TEXT['ADMIN_ONLY'] = 'Seul l&apos;administrateur peut modifier ces r&eacute;glages';
$TEXT['NO_SHOW_THUMBS'] = 'Cacher les vignettes';
$TEXT['TEXT_HEADER'] = 'D&eacute;finir la taille des images par dossier</b><br><small><i>(Redimensionnement seulement lors d&apos;un nouvel upload)</i></small>';
?>