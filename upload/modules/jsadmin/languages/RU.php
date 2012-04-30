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

  

// Headings and text outputs
$MOD_JSADMIN['TXT_HEADING_B'] 				= '&#1055;&#1086;&#1078;&#1072;&#1083;&#1091;&#1081;&#1089;&#1090;&#1072; &#1074;&#1099;&#1073;&#1077;&#1088;&#1080;&#1090;&#1077; Javascript &#1092;&#1091;&#1085;&#1082;&#1094;&#1080;&#1080;, &#1082;&#1086;&#1090;&#1086;&#1088;&#1099;&#1077; &#1074;&#1099; &#1093;&#1086;&#1090;&#1080;&#1090;&#1077; &#1074;&#1082;&#1083;&#1102;&#1095;&#1080;&#1090;&#1100;';
$MOD_JSADMIN['TXT_PERSIST_ORDER_B'] 		= '&#1047;&#1072;&#1087;&#1086;&#1084;&#1080;&#1085;&#1072;&#1090;&#1100; &#1087;&#1086;&#1088;&#1103;&#1076;&#1086;&#1082; &#1080; &#1074;&#1080;&#1076; &#1089;&#1090;&#1088;&#1072;&#1085;&#1080;&#1094; &#1074; &#1084;&#1077;&#1085;&#1102;';
$MOD_JSADMIN['TXT_AJAX_ORDER_PAGES_B'] 	= '&#1048;&#1079;&#1084;&#1077;&#1085;&#1103;&#1090;&#1100; &#1087;&#1086;&#1088;&#1103;&#1076;&#1086;&#1082; &#1089;&#1090;&#1088;&#1072;&#1085;&#1080;&#1094;, &#1080;&#1089;&#1087;&#1086;&#1083;&#1100;&#1079;&#1091;&#1103; drag-and-drop';
$MOD_JSADMIN['TXT_AJAX_ORDER_SECTIONS_B'] = '&#1048;&#1079;&#1084;&#1077;&#1085;&#1103;&#1090;&#1100; &#1087;&#1086;&#1088;&#1103;&#1076;&#1086;&#1082; &#1089;&#1077;&#1082;&#1094;&#1080;&#1081;, &#1080;&#1089;&#1087;&#1086;&#1083;&#1100;&#1079;&#1091;&#1103; drag-and-drop';
$MOD_JSADMIN['TXT_ERROR_INSTALLINFO_B'] 	= '<h1>&#1054;&#1096;&#1080;&#1073;&#1082;&#1072;</h1><p>JavaScript Admin &#1090;&#1088;&#1077;&#1073;&#1091;&#1077;&#1090; YUI (Yahoo User Interface) framework.<br />&#1057;&#1083;&#1077;&#1076;&#1091;&#1102;&#1097;&#1080;&#1077; &#1092;&#1072;&#1081;&#1083;&#1099; &#1085;&#1077;&#1086;&#1073;&#1093;&#1086;&#1076;&#1080;&#1084;&#1099; &#1076;&#1083;&#1103; &#1082;&#1086;&#1088;&#1088;&#1077;&#1082;&#1090;&#1085;&#1086;&#1081; &#1088;&#1072;&#1073;&#1086;&#1090;&#1099; Javascript Admin:<br /><br />';

?>
