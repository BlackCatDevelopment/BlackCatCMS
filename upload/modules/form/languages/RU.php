<?php

/**
 *  @module         form
 *  @version        see info.php of this module
 *  @authors        Ryan Djurovich, Rudolph Lartey, John Maats, Dietrich Roland Pehlke 
 *  @copyright      2004-2011 Ryan Djurovich, Rudolph Lartey, John Maats, Dietrich Roland Pehlke 
 *  @license        see info.php of this module
 *  @license terms  see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
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

//Modul Description
$module_description = '&#1052;&#1086;&#1076;&#1091;&#1083;&#1100; &#1087;&#1086;&#1079;&#1074;&#1086;&#1083;&#1103;&#1077;&#1090; &#1089;&#1086;&#1079;&#1076;&#1072;&#1074;&#1072;&#1090;&#1100; &#1088;&#1072;&#1079;&#1083;&#1080;&#1095;&#1085;&#1099;&#1077; &#1085;&#1072;&#1089;&#1090;&#1088;&#1072;&#1080;&#1074;&#1072;&#1077;&#1084;&#1099;&#1077; &#1092;&#1086;&#1088;&#1084;&#1099;, &#1085;&#1072;&#1087;&#1088;&#1080;&#1084;&#1077;&#1088; &#1092;&#1086;&#1088;&#1084;&#1099; &#1086;&#1073;&#1088;&#1072;&#1090;&#1085;&#1086;&#1081; &#1089;&#1074;&#1103;&#1079;&#1080;. Rudolph Lartey &#1087;&#1086;&#1084;&#1086;&#1075; &#1091;&#1083;&#1091;&#1095;&#1096;&#1080;&#1090;&#1100; &#1076;&#1072;&#1085;&#1085;&#1099;&#1081; &#1084;&#1086;&#1076;&#1091;&#1083;&#1100;.';

//Variables for the  backend
$MOD_FORM['SETTINGS'] = '&#1053;&#1072;&#1089;&#1090;&#1088;&#1086;&#1081;&#1082;&#1080; &#1092;&#1086;&#1088;&#1084;&#1099;';

?>
