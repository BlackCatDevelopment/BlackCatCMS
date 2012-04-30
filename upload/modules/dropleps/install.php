<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          dropleps
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 * @version         $Id$
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if ( defined( 'WB_PATH' ) )
{
    include( WB_PATH . '/framework/class.secure.php' );
}
else
{
    $root  = "../";
    $level = 1;
    while ( ( $level < 10 ) && ( !file_exists( $root . '/framework/class.secure.php' ) ) )
    {
        $root .= "../";
        $level += 1;
    }
    if ( file_exists( $root . '/framework/class.secure.php' ) )
    {
        include( $root . '/framework/class.secure.php' );
    }
    else
    {
        trigger_error( sprintf( "[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER[ 'SCRIPT_NAME' ] ), E_USER_ERROR );
    }
}
// end include class.secure.php

$is_upgrade = false;

// check if we already have a droplets table; leave it if yes
$result = $database->query("SHOW TABLES LIKE '".TABLE_PREFIX ."mod_droplets';");
if ( $result->numRows() == 0 ) {
	if ( file_exists( dirname(__FILE__).'/../droplets/info.php' ) ) {
    	$is_upgrade = true;
	}
	$table = TABLE_PREFIX .'mod_droplets';
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
$table = TABLE_PREFIX .'mod_dropleps_permissions';
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

// create the settings table
$table = TABLE_PREFIX .'mod_dropleps_settings';
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
$database->query("INSERT INTO `".TABLE_PREFIX ."mod_dropleps_settings` (`id`, `attribute`, `value`) VALUES
(1, 'manage_backups', '1'),
(2, 'import_dropleps', '1'),
(3, 'delete_dropleps', '1'),
(4, 'add_dropleps', '1'),
(5, 'export_dropleps', '1'),
(6, 'modify_dropleps', '1'),
(7, 'manage_perms', '1');
");

// import default dropleps
if ( ! class_exists( 'LEPTON_Helper_Directory' ) ) {
	@include WB_PATH.'/framework/LEPTON/Helper/Directory.php';
}
if ( ! function_exists( 'dropleps_import' ) ) {
	@include dirname(__FILE__).'/include.php';
}
$inst_dir   = sanitize_path( dirname(__FILE__).'/install' );
$temp_unzip = sanitize_path( WB_PATH.'/temp/unzip/' );
$dirh       = new LEPTON_Helper_Directory();
$files      = $dirh->getFiles( $inst_dir, $inst_dir.'/' );

if ( is_array($files) && count($files) ) {
	foreach( $files as $file ) {
	    // only files that have 'droplep_' as prefix
	    if( ! preg_match( '~^droplep_~i', $file ) )
	    {
	        continue;
		}
	    // ignore the result here
	    dropleps_import( $inst_dir.'/'.$file, $temp_unzip );
	}
}

// if it's an upgrade from the old droplets module...
if ( $is_upgrade ) {

	require sanitize_path( LEPTON_PATH . '/framework/LEPTON/Helper/Zip.php' );

	// create backup copy
	$temp_file = sanitize_path( LEPTON_PATH . '/temp/droplets_module_backup.zip' );
	$temp_dir  = sanitize_path( LEPTON_PATH . '/modules/droplets'                );
	
    $zip1 = new LEPTON_Helper_Zip($temp_file);
	$zip1->config( 'removePath', $temp_dir );

    $file_list = $zip1->create( $temp_dir );
    if ( $file_list == 0 )
    {
        $admin->print_error( $admin->lang->translate( "Packaging error" ) . ' - ' . $zip1->errorInfo( true ) );
    }

	// remove the folder
	rm_full_dir( LEPTON_PATH.'/modules/droplets' );
	
	// re-create the folder
	@mkdir( LEPTON_PATH.'/modules/droplets', 0755 );
	
	// unpack the compatibility files
	$temp_file = sanitize_path( LEPTON_PATH . '/modules/dropleps/install/droplets.zip' );
	$zip2 = new LEPTON_Helper_Zip($temp_file);
	$zip2->config( 'Path', sanitize_path( LEPTON_PATH.'/modules/droplets' ) );
	$zip2->extract( $temp_file );
	
}

?>