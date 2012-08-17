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

  

function get_setting($name, $default = '') {
	global $database;
	$rs = $database->query("SELECT value FROM ".TABLE_PREFIX."mod_jsadmin WHERE name = '".$name."'");
	if($row = $rs->fetchRow())
		return $row['value'];
	return
		$default;
}

function save_setting($name, $value) {
	global $database;

	$prev_value = get_setting($name, false);

	if($prev_value === false) {
		$database->query("INSERT INTO ".TABLE_PREFIX."mod_jsadmin (name,value) VALUES ('$name','$value')");
	} else {
		$database->query("UPDATE ".TABLE_PREFIX."mod_jsadmin SET value = '$value' WHERE name = '$name'");
	}
}

// the follwing variables to use and check existing the YUI
$WB_MAIN_RELATIVE_PATH="../..";
$YUI_PATH = '/include/yui';
$js_yui_min = "-min";  // option for smaller code so faster
$js_yui_scripts = Array();
$js_yui_scripts[] = $YUI_PATH.'/yahoo/yahoo'.$js_yui_min.'.js';
$js_yui_scripts[] = $YUI_PATH.'/event/event'.$js_yui_min.'.js';
$js_yui_scripts[] = $YUI_PATH.'/dom/dom'.$js_yui_min.'.js';
$js_yui_scripts[] = $YUI_PATH.'/connection/connection'.$js_yui_min.'.js';
$js_yui_scripts[] = $YUI_PATH.'/dragdrop/dragdrop'.$js_yui_min.'.js';

?>