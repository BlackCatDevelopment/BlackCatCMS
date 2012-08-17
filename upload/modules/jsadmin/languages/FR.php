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

 

//Module Description
$module_description = 'Ce module am&eacute;liore l&apos;interactivit&eacute; de l&apos;interface d&apos;administration gr&acirc;ce &agrave; l&apos;ajout de fonctionnalit&eacute;s javascript am&eacute;lior&eacute;es. Utilise la librairie YahooUI.';

// Headings and text outputs
$MOD_JSADMIN['TXT_HEADING_B'] 				= 'Veuillez s&eacute;lectionner la fonctionnalit&eacute; Javascript &agrave; activer';
$MOD_JSADMIN['TXT_PERSIST_ORDER_B'] 		= 'Garder en m&eacute;moire l&apos;arborescence des pages';
$MOD_JSADMIN['TXT_AJAX_ORDER_PAGES_B'] 	= 'R&eacute;organisation des pages gr&acirc;ce au glisser-d&eacute;poser';
$MOD_JSADMIN['TXT_AJAX_ORDER_SECTIONS_B'] = 'R&eacute;organisation des sections gr&acirc;ce au glisser-d&eacute;poser';
$MOD_JSADMIN['TXT_ERROR_INSTALLINFO_B'] 	= '<h1>Erreur</h1><p>JavaScript Admin a besoin du framework YUI (Yahoo User Interface).<br />Les fichiers suivants sont requis pour que Javascript Admin fonctionne correctement:<br /><br />';

?>