<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         menu_link
 *
 */

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

// Deutsche Modulbeschreibung
$module_description 	= 'Dieses Modul erm&ouml;glicht die Anzeige eines Links im Seitenmen&uuml;.';

// Ueberschriften und Textausgaben
$MOD_MENU_LINK['TEXT'] = 'Klicken Sie HIER um zur Startseite zu gelangen';
$MOD_MENU_LINK['EXTERNAL_LINK'] = 'Entfernte Adresse';
$MOD_MENU_LINK['R_TYPE'] = 'Redirect-Typ';
$MOD_MENU_LINK['XHTML_EXPLANATION'] = 'Info: diese Einstellung hat keine Auswirkungen, wenn bei show_menu2() der Schalter SM2_XHTML_STRICT verwendet wird!';
$MOD_MENU_LINK['REDIRECT_EXPLANATION'] = '301: Die angeforderte Ressource steht ab sofort unter der im „Location“-Header-Feld angegebenen Adresse bereit. Die alte Adresse ist nicht länger gültig.<br />302: Die angeforderte Ressource steht vorübergehend unter der im „Location“-Header-Feld angegebenen Adresse bereit. Die alte Adresse bleibt gültig.<br />(Siehe <a href="http://www.w3.org/Protocols/rfc2616/rfc2616.html" target="_blank">RFC2616</a>)';

?>