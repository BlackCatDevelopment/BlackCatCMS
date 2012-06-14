<?php
/**
 * outputInterface
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
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

$table = TABLE_PREFIX .'mod_output_interface';
$database->query("DROP TABLE IF EXISTS `$table`");
if ($database->is_error()) $admin->print_error($database->get_error());

$database->query("CREATE TABLE `$table` (
	`module_directory` VARCHAR(64) NOT NULL DEFAULT '',
	`module_name` VARCHAR(64) NOT NULL DEFAULT '',
	`timestamp` TIMESTAMP,
	PRIMARY KEY (module_directory))"
);

if ($database->is_error()) $admin->print_error($database->get_error());

?>