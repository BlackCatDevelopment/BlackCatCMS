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

 

// add new rows to table "settings"

$table = TABLE_PREFIX ."mod_jsadmin";
$database->query("DROP TABLE IF EXISTS `$table`");

$database->query("
	CREATE TABLE `$table` (
    `id` INT(11) NOT NULL DEFAULT '0',
		`name` VARCHAR(255) NOT NULL DEFAULT '0',
		`value` INT(11) NOT NULL DEFAULT '0',
   	PRIMARY KEY (`id`)
	)
");

global $database;
$database->query("INSERT INTO ".$table." (id,name,value) VALUES ('1','mod_jsadmin_persist_order','1')");
$database->query("INSERT INTO ".$table." (id,name,value) VALUES ('2','mod_jsadmin_ajax_order_pages','1')");
$database->query("INSERT INTO ".$table." (id,name,value) VALUES ('3','mod_jsadmin_ajax_order_sections','1')");

?>