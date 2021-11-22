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
	while (($level < 10) && (!file_exists($root.'framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'framework/class.secure.php')) { 
		include($root.'framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php



// Module desription
$module_description 	= 'This module allows you to insert a link into the menu.';

// Headings and text outputs
$MOD_MENU_LINK['TEXT']				= '&#1053;&#1072;&#1078;&#1084;&#1080;&#1090;&#1077; &#1079;&#1076;&#1077;&#1089;&#1100; &#1076;&#1083;&#1103; &#1087;&#1077;&#1088;&#1077;&#1093;&#1086;&#1076;&#1072; &#1085;&#1072; &#1075;&#1083;&#1072;&#1074;&#1085;&#1091;&#1102;';
$MOD_MENU_LINK['EXTERNAL_LINK'] = '&#1042;&#1085;&#1077;&#1096;&#1085;&#1103;&#1103; &#1089;&#1089;&#1099;&#1083;&#1082;&#1072;';
$MOD_MENU_LINK['R_TYPE'] = 'Redirect-Type';
$MOD_MENU_LINK['XHTML_EXPLANATION'] = 'Info: If you use the SM2_XHTML_STRICT option with show_menu2(), this setting has no effect!';
$MOD_MENU_LINK['REDIRECT_EXPLANATION'] = 'Info: 301: The requested resource has been assigned a new permanent URI and any future references to this resource SHOULD use one of the returned URIs.<br />302: The requested resource resides temporarily under a different URI. Since the redirection might be altered on occasion, the client SHOULD continue to use the Request-URI for future requests <br />(See <a href="http://www.w3.org/Protocols/rfc2616/rfc2616.html" target="_blank">RFC2616</a>)';

?>
