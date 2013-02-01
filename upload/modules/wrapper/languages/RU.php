<?php

/**
 * This file is part of an ADDON for use with Black Cat CMS Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          wrapper
 * @author          WebsiteBaker Project
 * @author          LEPTON Project
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
$MOD_WRAPPER['NOTICE'] 		=	'&#1042;&#1072;&#1096; browser &#1085;&#1077; &#1087;&#1086;&#1076;&#1076;&#1077;&#1088;&#1078;&#1080;&#1074;&#1072;&#1077;&#1090; inline-&#1092;&#1088;&#1077;&#1081;&#1084;&#1099;.<br />&#1053;&#1072;&#1078;&#1084;&#1080;&#1090;&#1077; &#1085;&#1072; &#1089;&#1089;&#1099;&#1083;&#1082;&#1091; &#1085;&#1080;&#1078;&#1077;, &#1095;&#1090;&#1086;&#1073;&#1099; &#1087;&#1077;&#1088;&#1077;&#1081;&#1090;&#1080; &#1082; &#1080;&#1084;&#1087;&#1086;&#1088;&#1090;&#1080;&#1088;&#1086;&#1074;&#1072;&#1085;&#1085;&#1086;&#1084;&#1091; &#1089;&#1102;&#1076;&#1072; &#1089;&#1072;&#1081;&#1090;&#1091;...<br />';

?>
