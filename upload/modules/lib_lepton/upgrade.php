<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          lib_lepton
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
 * @link            http://www.lepton-cms.org
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

global $database;
global $admin;

$error = '';

$SQL = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."pages_load` (".
    "`id` SERIAL, ".
    "`register_name` VARCHAR(255) NOT NULL DEFAULT '', ".
    "`register_type` VARCHAR(64) NOT NULL DEFAULT 'droplep', ".
    "`page_id` INT(11) NOT NULL DEFAULT '0', ".
    "`module_directory` VARCHAR(255) NOT NULL DEFAULT '', ".
    "`file_type` VARCHAR(128) NOT NULL DEFAULT '', ".
    "`file_name` VARCHAR(255) NOT NULL DEFAULT '', ".
    "`file_path` TEXT NOT NULL DEFAULT '', ".
    "`options` TEXT NOT NULL DEFAULT '', ".
    "`timestamp` TIMESTAMP".
    ")";
if (!$database->query($SQL)) {
    $error .= $database->get_error();
}

if (!empty($error)) $admin->print_error($error);

?>