<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2016, Black Cat Development
 *   @link            https://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         menu_link
 *
 */

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

$table = CAT_TABLE_PREFIX ."mod_menu_link";
// $database->query("DROP TABLE IF EXISTS `$table`");

$database->query("
	CREATE TABLE IF NOT EXISTS `$table` (
		`section_id` INT(11) NOT NULL DEFAULT '0',
		`page_id` INT(11) NOT NULL DEFAULT '0',
		`target_page_id` INT(11) NOT NULL DEFAULT '0',
		`redirect_type` INT NOT NULL DEFAULT '302',
		`anchor` VARCHAR(255) NOT NULL DEFAULT '0' ,
		`extern` VARCHAR(255) NOT NULL DEFAULT '' ,
		PRIMARY KEY (`section_id`)
	)
");

// add files to class_secure
$addons_helper = new CAT_Helper_Addons();
foreach(
    array( 'save.php' )
    as $file
) {
    if ( false === $addons_helper->sec_register_file( 'menu_link', $file ) )
    {
         error_log( "Unable to register file -$file-!" );
    }
}


?>
