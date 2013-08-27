<?php

/**
 * This file is part of an ADDON for use with Black Cat CMS Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 *
 * @author          Thomas Hornik (thorn),LEPTON Project
 * @copyright       2008-2011, Thomas Hornik (thorn),LEPTON Project
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



$table = CAT_TABLE_PREFIX.'mod_captcha_control';
$database->query("DROP TABLE IF EXISTS `$table`");

$database->query("CREATE TABLE `$table` (
	`enabled_captcha` VARCHAR(1) NOT NULL DEFAULT '1',
	`enabled_asp` VARCHAR(1) NOT NULL DEFAULT '0',
	`captcha_type` VARCHAR(255) NOT NULL DEFAULT 'calc_text',
	`asp_session_min_age` INT(11) NOT NULL DEFAULT '20',
	`asp_view_min_age` INT(11) NOT NULL DEFAULT '10',
	`asp_input_min_age` INT(11) NOT NULL DEFAULT '5',
	`ct_text` LONGTEXT NOT NULL
	)"
);

// add new row using the table default values defined above
$database->query("
	INSERT INTO `$table`
		(`enabled_captcha`, `enabled_asp`, `captcha_type`, `ct_text`)
	VALUES
		('1', '1', 'calc_text', '')
");

?>