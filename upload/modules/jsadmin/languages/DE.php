<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the BSD License.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          jsadmin 
 * @author          WebsiteBaker Project
 * @author          LEPTON Project
 * @copyright       2004-2010, Ryan Djurovich,WebsiteBaker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         BSD License
 * @license_terms   please see info.php of this module
 * @version         $Id$
 *
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

 

// Deutsche Modulbeschreibung
$module_description 	= 'Dieses Modul stellt Javascript Funktionen f&uuml;r die Website Baker Benutzeroberfl&auml;che zur Verf&uuml;gung. Verwendet das YahooUI Javascript Framework.';

// Ueberschriften und Textausgaben
$MOD_JSADMIN['TXT_HEADING_B'] 				= 'Bitte w&auml;hlen Sie die gew&uuml;nschten Javascript Funktionen aus';
$MOD_JSADMIN['TXT_PERSIST_ORDER_B'] 		= 'Ge&ouml;ffneten Seitenbaum merken';
$MOD_JSADMIN['TXT_AJAX_ORDER_PAGES_B'] 	= 'Sortierung von Seiten per "drag-and-drop" erlauben';
$MOD_JSADMIN['TXT_AJAX_ORDER_SECTIONS_B'] = 'Sortierung von Abschnitten per "drag-and-drop" erlauben';
$MOD_JSADMIN['TXT_ERROR_INSTALLINFO_B'] 	= '<h1>Fehler</h1><p>JavaScript Admin ben&ouml;tigt das YUI (Yahoo User Interface) Framework.<br />Folgende Dateien werden f&uuml;r das Modul ben&ouml;tigt:<br /><br />';

?>