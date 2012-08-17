<?php

/**
 *
 * @module          initial_page
 * @author          Ralf Hertsch, Dietrich Roland Pehlke 
 * @copyright       2010-2011, Ralf Hertsch, Dietrich Roland Pehlke
 * @link            http://www.LEPTON-cms.org
 * @license         copyright, all rights reserved
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



$table = TABLE_PREFIX ."mod_initial_page";

$jobs = array("DROP TABLE IF EXISTS `".$table."`");

$jobs[] = "CREATE TABLE `".$table."` (
	`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`user_id` INT(11) NOT NULL DEFAULT '1',
	`init_page` TEXT NOT NULL,
	`page_param` VARCHAR(255) NOT NULL DEFAULT '')";

$jobs[] = "INSERT into `".$table."` (`user_id`, `init_page`, `page_param`) VALUES ('1', 'start/index.php', '')";

$errors = array();

foreach($jobs as $query) {
	$database->query( $query );
	if ($database->is_error()) $errors[] = $database->get_error();
}

// try to patch /admin/start/index.php
require_once(WB_PATH.'/modules/initial_page/classes/c_patch.php');
$patch = new patchStartPage();
if (!$patch->isPatched()) {
	if (!$patch->doPatch()) {
		// can't patch /admin/start/index.php - prompt message
		echo '<script language="javascript">alert("PROBLEM - the installer was not able to patch \\admin\\start\\index.php automatically - please consult documentation for instructions who to patch this file by yourself.");</script>';
	}
}

// prompt database errors
if (count($errors) > 0) {
	if (count($errors) > 0) $admin->print_error( implode("<br />\n", $errors) );
}

?>