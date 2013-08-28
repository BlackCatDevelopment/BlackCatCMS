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
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         blackcatFilter
 *
 */

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

$addons_helper = CAT_Helper_Addons::getInstance();

// Create table
$addons_helper->db()->query("DROP TABLE IF EXISTS `" . CAT_TABLE_PREFIX . "mod_filters`");
$addons_helper->db()->query(sprintf(
    "CREATE TABLE `%smod_filter` (
    `filter_name` VARCHAR(50) NOT NULL,
    `module_name` VARCHAR(50) NULL DEFAULT NULL,
    `filter_description` TEXT NULL,
    `filter_code` TEXT NULL,
    `filter_active` ENUM('Y','N') NOT NULL DEFAULT 'Y',
    UNIQUE INDEX `filter_name_module_name` (`filter_name`, `module_name`)
    )
    COMMENT='Successor of Output Filters'
    COLLATE='utf8_general_ci'
    ENGINE=InnoDB;",
    CAT_TABLE_PREFIX
));

// insert default filters
$addons_helper->db()->query(sprintf(
    "REPLACE INTO `%smod_filter` (`filter_name`, `module_name`, `filter_description`, `filter_code`, `filter_active`)
    VALUES ('obfuscateEmail', 'blackcatFilter', 'Obfuscates eMail addresses', '', 'N'),
           ('cmsplink', 'blackcatFilter', 'Processes internal page links', '', 'Y'),
           ('fixDate', 'blackcatFilter', 'fixes dates emitted from old modules', '', 'N'),
           ('searchHighlight', 'blackcatFilter', 'Highlights search terms forwarded from search engines', '', 'N');",
    CAT_TABLE_PREFIX
));

// add files to class_secure
if ( false === $addons_helper->sec_register_file( 'blackcatFilter', 'ajax_set.php' ) )
{
     error_log( "Unable to register file -ajax_set.php-!" );
}