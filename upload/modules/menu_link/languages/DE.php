<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          menu-link
 * @author          WebsiteBaker Project, LEPTON Project
 * @copyright       2004-2010, WebsiteBaker Project
 * @copyright       2010-2011, LEPTON Project 
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
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



// Deutsche Modulbeschreibung
$module_description 	= 'Dieses Modul erm&ouml;glicht die Anzeige eines Links im Seitenmen&uuml;.';

// Ueberschriften und Textausgaben
$MOD_MENU_LINK['TEXT']				= 'Klicken Sie HIER um zur Startseite zu gelangen';
$MOD_MENU_LINK['EXTERNAL_LINK'] = 'Entfernte Adresse';
$MOD_MENU_LINK['R_TYPE'] = 'Redirect-Typ';
$MOD_MENU_LINK['XHTML_EXPLANATION'] = 'Info: diese Einstellung hat keine Auswirkungen, wenn bei show_menu2() der Schalter SM2_XHTML_STRICT verwendet wird!';
$MOD_MENU_LINK['REDIRECT_EXPLANATION'] = 'Info: 301: Die angeforderte Ressource steht ab sofort unter der im „Location“-Header-Feld angegebenen Adresse bereit. Die alte Adresse ist nicht länger gültig.<br />302: Die angeforderte Ressource steht vorübergehend unter der im „Location“-Header-Feld angegebenen Adresse bereit. Die alte Adresse bleibt gültig.<br />(Siehe <a href="http://www.w3.org/Protocols/rfc2616/rfc2616.html" target="_blank">RFC2616</a>)';

?>