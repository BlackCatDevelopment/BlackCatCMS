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

$is_upgrade = false;

global $database;

// check if we already have a droplets table; leave it if yes
$result = $database->query("SHOW TABLES LIKE '".CAT_TABLE_PREFIX ."mod_droplets';");
if ( $result->numRows() == 0 ) {
	if ( file_exists( dirname(__FILE__).'/../droplets/info.php' ) ) {
    	$is_upgrade = true;
	}
	$table = CAT_TABLE_PREFIX .'mod_droplets';
	$database->query("CREATE TABLE `$table` (
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
	if( $database->is_error() ) {
	    $admin->print_error( $admin->lang->translate( 'Database Error: {{error}}', array( 'error' => $database->get_error() ) ) );
	}
}

// create the new permissions table
// check if we already have a permissions table; leave it if yes
$result = $database->query("SHOW TABLES LIKE '".CAT_TABLE_PREFIX ."mod_droplets_permissions';");
if ( $result->numRows() == 0 ) {
    $table = CAT_TABLE_PREFIX .'mod_droplets_permissions';
    $database->query("DROP TABLE IF EXISTS `$table`");
    $database->query("CREATE TABLE `$table` (
	`id` INT(10) UNSIGNED NOT NULL,
	`edit_groups` VARCHAR(50) NOT NULL,
	`view_groups` VARCHAR(50) NOT NULL,
	PRIMARY KEY ( `id` )
	) COLLATE='utf8_general_ci' ENGINE=InnoDB;"
    );
    // check for errors
    if( $database->is_error() ) {
    $admin->print_error( $admin->lang->translate( 'Database Error: {{error}}', array( 'error' => $database->get_error() ) ) );
    }
}

// create the settings table
// check if we already have a settings table; leave it if yes
$result = $database->query("SHOW TABLES LIKE '".CAT_TABLE_PREFIX ."mod_droplets_settings';");
if ( $result->numRows() == 0 ) {
    $table = CAT_TABLE_PREFIX .'mod_droplets_settings';
    $database->query("DROP TABLE IF EXISTS `$table`");
    $database->query("CREATE TABLE `$table` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`attribute` VARCHAR(50) NOT NULL DEFAULT '0',
	`value` VARCHAR(50) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
	) COLLATE='utf8_general_ci' ENGINE=InnoDB;"
    );
    // check for errors
    if( $database->is_error() ) {
    $admin->print_error( $admin->lang->translate( 'Database Error: {{error}}', array( 'error' => $database->get_error() ) ) );
    }
    // insert settings
    $database->query("INSERT INTO `".CAT_TABLE_PREFIX ."mod_droplets_settings` (`id`, `attribute`, `value`) VALUES
        (1, 'manage_backups', '1'),
        (2, 'import_droplets', '1'),
        (3, 'delete_droplets', '1'),
        (4, 'add_droplets', '1'),
        (5, 'export_droplets', '1'),
        (6, 'modify_droplets', '1'),
        (7, 'manage_perms', '1');
    ");
}

// import default droplets
if ( ! class_exists( 'CAT_Helper_Directory' ) ) {
	@include CAT_PATH.'/framework/LEPTON/Helper/Directory.php';
}
if ( ! function_exists( 'droplets_import' ) ) {
	@include dirname(__FILE__).'/include.php';
}
$inst_dir   = sanitize_path( dirname(__FILE__).'/install' );
$temp_unzip = sanitize_path( CAT_PATH.'/temp/unzip/' );
$dirh       = CAT_Helper_Directory::getInstance();
$files      = $dirh->scanDirectory( $inst_dir, true, true, $inst_dir.'/', array('zip') );

if ( is_array($files) && count($files) ) {
	foreach( $files as $file ) {
	    // only files that have 'droplet_' as prefix
	    if( ! preg_match( '~^droplet_~i', $file ) )
	    {
	        continue;
		}
	    // ignore the result here
	    droplets_import( $inst_dir.'/'.$file, $temp_unzip );
	}
}

// if it's an upgrade from the old droplets module...
if ( $is_upgrade ) {

	require sanitize_path( CAT_PATH . '/framework/LEPTON/Helper/Zip.php' );

	// create backup copy
	$temp_file = sanitize_path( CAT_PATH . '/temp/droplets_module_backup.zip' );
	$temp_dir  = sanitize_path( CAT_PATH . '/modules/droplets'                );
	
    $zip1 = new CAT_Helper_Zip($temp_file);
	$zip1->config( 'removePath', $temp_dir );

    $file_list = $zip1->create( $temp_dir );
    if ( $file_list == 0 )
    {
        $admin->print_error( $admin->lang->translate( "Packaging error" ) . ' - ' . $zip1->errorInfo( true ) );
    }

	// remove the folder
	rm_full_dir( CAT_PATH.'/modules/droplets' );
	
	// re-create the folder
	@mkdir( CAT_PATH.'/modules/droplets', 0755 );
	
	// unpack the compatibility files
	$temp_file = sanitize_path( CAT_PATH . '/modules/droplets/install/droplets.zip' );
	$zip2 = new CAT_Helper_Zip($temp_file);
	$zip2->config( 'Path', sanitize_path( CAT_PATH.'/modules/droplets' ) );
	$zip2->extract( $temp_file );
	
}

?>