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
 *
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

 

// Headings and text outputs
$MOD_JSADMIN['TXT_HEADING_B'] 				= 'Kies de Javascript functies die je wenst te gebruiken';
$MOD_JSADMIN['TXT_PERSIST_ORDER_B'] 		= "Onthouden van uitgeklapte pagina's";
$MOD_JSADMIN['TXT_AJAX_ORDER_PAGES_B'] 	= "Klikken-en-slepen om pagina's te herordenen";
$MOD_JSADMIN['TXT_AJAX_ORDER_SECTIONS_B'] = 'Klikken-en-slepen om secties te herordenen';
$MOD_JSADMIN['TXT_ERROR_INSTALLINFO_B'] 	= '<h1>Fout</h1><p>JavaScript Admin heeft de YUI (Yahoo User Interface) code nodig.<br />De volgende bestanden zijn nodig om Javascript Admin te laten werken:<br /><br />';

?>