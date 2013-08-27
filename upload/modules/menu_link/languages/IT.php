<?php

/**
 * This file is part of an ADDON for use with Black Cat CMS Core.
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
if (defined('CAT_PATH')) {	
	include(CAT_PATH.'/framework/class.secure.php'); 
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
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
$MOD_MENU_LINK['TEXT'] = 'Clicca QUI per andare alla Home';
$MOD_MENU_LINK['EXTERNAL_LINK'] = 'Link Esterno';
$MOD_MENU_LINK['R_TYPE'] = 'Tipo Redirect';
$MOD_MENU_LINK['XHTML_EXPLANATION'] = 'Info: Se usi l\'opzione SM2_XHTML_STRICT con show_menu2(), questa configurazione non ha effetto!';
$MOD_MENU_LINK['REDIRECT_EXPLANATION'] = 'Info: 301: la risorsa richiesta ha un nuovo indirizzo permanente e in futuro i riferimenti a questa risorsa sar&agrave;nno soltanto una.<br />302: la risorsa richiesta &egrave; spostata temporaneamente sotto un altro indirizzo. Quando la rendeirizzazione sarà alterata in una occasione, il client continuerà ad usare la Request-URI per richieste future <br />(Vedi <a href="http://www.w3.org/Protocols/rfc2616/rfc2616.html" target="_blank">RFC2616</a>)';

?>