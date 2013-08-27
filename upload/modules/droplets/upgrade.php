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
 *   @package         droplets
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

$backend   = CAT_Backend::getInstance('Addons', 'modules_install');

// check if we already have a droplets table; leave it if yes
$result = $backend->db()->query(sprintf(
    "SHOW TABLES LIKE '%smod_droplets';",
    CAT_TABLE_PREFIX
));
if ( $result->numRows() == 0 ) {
	$table = CAT_TABLE_PREFIX .'mod_droplets';
	$backend->db()->query("CREATE TABLE `$table` (
		`id` INT NOT NULL auto_increment,
		`name` VARCHAR(32) NOT NULL,
		`code` LONGTEXT NOT NULL ,
		`description` TEXT NOT NULL,
		`modified_when` INT NOT NULL default '0',
		`modified_by` INT NOT NULL default '0',
		`active` INT NOT NULL default '0',
		`admin_edit` INT NOT NULL default '0',
		`admin_view` INT NOT NULL default '0',
		`show_wysiwyg` INT NOT NULL default '0',
		`comments` TEXT NOT NULL,
		PRIMARY KEY ( `id` )
		)"
	);
	// check for errors
	if( $backend->db()->is_error() ) {
	    $backend->print_error( $backend->lang()->translate( 'Database Error: {{error}}', array( 'error' => $backend->db()->get_error() ) ) );
	}
}

// create the new permissions table
$result = $backend->db()->query(sprintf(
    "SHOW TABLES LIKE '%smod_droplets_permissions';",
    CAT_TABLE_PREFIX
));
if ( $result->numRows() == 0 ) {
	$table = CAT_TABLE_PREFIX .'mod_droplets_permissions';
	$backend->db()->query("CREATE TABLE `$table` (
		`id` INT(10) UNSIGNED NOT NULL,
		`edit_groups` VARCHAR(50) NOT NULL,
		`view_groups` VARCHAR(50) NOT NULL,
		PRIMARY KEY ( `id` )
		) COLLATE='utf8_general_ci' ENGINE=InnoDB;"
	);
	// check for errors
	if( $backend->db()->is_error() ) {
	    $backend->print_error( $backend->lang()->translate( 'Database Error: {{error}}', array( 'error' => $backend->db()->get_error() ) ) );
	}
}

// create the settings table
$result = $backend->db()->query(sprintf(
    "SHOW TABLES LIKE '%smod_droplets_settings';",
    CAT_TABLE_PREFIX
));
if ( $result->numRows() == 0 ) {
	$table = CAT_TABLE_PREFIX .'mod_droplets_settings';
	$backend->db()->query("CREATE TABLE `$table` (
		`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`attribute` VARCHAR(50) NOT NULL DEFAULT '0',
		`value` VARCHAR(50) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
		) COLLATE='utf8_general_ci' ENGINE=InnoDB;"
	);
	// check for errors
	if( $backend->db()->is_error() ) {
	    $backend->print_error( $backend->lang()->translate( 'Database Error: {{error}}', array( 'error' => $backend->db()->get_error() ) ) );
	}
    else
    {
	// insert settings
    	$backend->db()->query("INSERT INTO `".CAT_TABLE_PREFIX ."mod_droplets_settings` (`id`, `attribute`, `value`) VALUES
	(1, 'manage_backups', '1'),
	(2, 'import_droplets', '1'),
	(3, 'delete_droplets', '1'),
	(4, 'add_droplets', '1'),
	(5, 'export_droplets', '1'),
	(6, 'modify_droplets', '1'),
	(7, 'manage_perms', '1');
	");
    }
}
