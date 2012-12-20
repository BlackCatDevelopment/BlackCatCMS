<?php

/**
 * This file is part of LEPTON2 Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author			LEPTON2 Project
 * @copyright		2012, LEPTON2 Project
 * @link			http://lepton2.org
 * @license			http://www.gnu.org/licenses/gpl.html
 * @license_terms	please see LICENSE and COPYING files in your package
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {
	include(LEPTON_PATH.'/framework/class.secure.php');
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

// Check if user uploaded a file
if (!isset($_FILES['userfile']) || $_FILES['userfile']['size'] == 0)
{
	header("Location: index.php");
	exit(0);
}

require_once( LEPTON_PATH . '/framework/class.admin.php' );
$admin = new admin('Addons', 'addons');

// Include the WB functions file
require_once( LEPTON_PATH . '/framework/functions.php' );

// Check if module dir is writable (doesn't make sense to go on if not)
if ( !(is_writable( LEPTON_PATH .  '/modules/') && is_writable( LEPTON_PATH . '/templates/') && is_writable( LEPTON_PATH . '/languages/') ) )
{
	$admin->print_error( 'Unable to write to the target directory' );
}

// Set temp vars
$temp_dir		= LEPTON_PATH . '/temp/';
$temp_file		= $temp_dir . $_FILES['userfile']['name'];
$temp_unzip		= LEPTON_PATH . '/temp/unzip ' . basename($_FILES['userfile']['tmp_name']) . '/';

$addon_helper		= $admin->get_helper('Addons');

// make sure the temp directory exists, is writable and is empty
$admin->get_helper('Directory')->removeDirectory( $temp_unzip );
$admin->get_helper('Directory')->createDirectory( $temp_unzip );

// Try to upload the file to the temp dir
if ( !move_uploaded_file( $_FILES['userfile']['tmp_name'], $temp_file ) )
{
	CLEANUP();
	$admin->print_error( 'Cannot upload file' );
}

// Check for language or template/module
$extension	= pathinfo( $temp_file, PATHINFO_EXTENSION );

if ( $extension == 'php' )
{
	$temp_subdir	= $temp_file;
}
else if ( $extension == 'zip' ) {
	$temp_subdir	= $temp_unzip . 'unzip/';
	$admin->get_helper('Directory')->createDirectory( $temp_subdir );

	// Setup the PclZip object and unzip the files to the temp unzip folder
	$list	= $admin->get_helper( 'Zip', $temp_file )->config( 'Path', sanitize_path( $temp_subdir ) )->extract( PCLZIP_OPT_PATH, $temp_file );

	if ( !( $list && file_exists( $temp_subdir . 'index.php' ) ) )
	{
		CLEANUP();
		$admin->print_error( 'Invalid LEPTON installation file. Please check the *.zip format.' );
	}
}
else {
	CLEANUP();
	$admin->print_error( 'Invalid LEPTON installation file. Please check the *.zip format.' );
}

// Check the info.php file / language file
if ( $addon_info = $addon_helper->checkInfo( $temp_subdir ) )
{
	$addon_helper->preCheckAddon( $temp_file, $temp_subdir );
}
else {
	CLEANUP();
	$admin->print_error( '2Invalid LEPTON installation file. Please check the *.zip format.' );
}

// So, now we have done all preinstall checks, lets see what to do next
$addon_directory	= $addon_info['addon_function'] == 'language' ?
						$addon_info[$addon_info['addon_function'] . '_code'] . '.php' :
						$addon_info[$addon_info['addon_function'] . '_directory'];

// Set module directory
$addon_dir			= LEPTON_PATH .  '/' . $addon_info['addon_function'] . 's/' . $addon_directory;

$action				= 'install';

if ( file_exists( $addon_dir ) )
{
	$action			= 'upgrade';
	// look for old info.php
	$previous_info	= $addon_helper->checkInfo( $addon_dir );
	if ( $previous_info
		|| $addon_info['addon_function'] == 'language' )
	{
		/**
		*	Version to be installed is older than currently installed version
		*/
		if ( $addon_helper->versionCompare ( $previous_info[$addon_info['addon_function'] . '_version'], $addon_info[$addon_info['addon_function'] . '_version'], '>=' ) )
		{
			CLEANUP();
			$admin->print_error( 'Already installed' );
		}
	}
}

// Make sure the module dir exists, and chmod if needed
if ( $addon_info['addon_function'] != 'language')
{
	$admin->get_helper('Directory')->createDirectory( $addon_dir );

	// copy files from temp folder
	if ( COPY_RECURSIVE_DIRS( $temp_subdir, $addon_dir ) !== true )
	{
		CLEANUP();
		$admin->print_error( 'Actualization not possibly' );
	}
	// remove temp
	CLEANUP();
}

// load info.php again to have current values
if ( file_exists($addon_dir . '/info.php') )
{
	require( $addon_dir . '/info.php' );
}
// Run the modules install // upgrade script if there is one
if ( file_exists( $addon_dir . '/' . $action . '.php') )
{
	require( $addon_dir . '/' . $action . '.php' );
}

// Print success message
if ( $action == 'install' )
{
	// Load module info into DB
	if ( $addon_info['addon_function'] == 'module' ) $addon_helper->installModule( $addon_dir , false );
	else if ( $addon_info['addon_function'] == 'template' ) $addon_helper->installTemplate( $addon_dir );
	else {
		rename($temp_file, $addon_dir);
		// Chmod the file
		change_mode( $addon_dir , 'file');

		// Load language info into DB
		$addon_helper->installLanguage( $addon_dir );
	}

	// let admin set access permissions for modules of type 'page' and 'tool'
	if ( ( $addon_info['addon_function'] == 'module' && ( $module_function == 'page' || $module_function == 'tool' ) )
		|| $addon_info['addon_function'] == 'template' )
	{
		$check_permission	= $addon_info['addon_function'] . '_permissions';

		// get groups
		$stmt = $database->query('SELECT * FROM ' . TABLE_PREFIX . 'groups WHERE group_id <> 1');
		if ( $stmt->numRows() > 0 )
		{
			$group_ids	= $admin->get_post('group_id');
			// get marked groups
			if ( is_array($group_ids) )
			{
				foreach ( $group_ids as $gid )
				{
					$allowed_groups[$gid]	= $gid;
				}
			}
			else
			{
			// no groups marked, so don't allow any group
				$allowed_groups	= array();
			}
			// get all known groups
			$groups	= array();
			while( $row = $stmt->fetchRow(MYSQL_ASSOC) )
			{
				$groups[ $row['group_id'] ]		= $row;
				$gid							= $row['group_id'];
				// add newly installed module to any group that's NOT in the $allowed_groups array
				if ( ! array_key_exists( $gid, $allowed_groups ) )
				{
					// get current value
					$addons		= explode(',', $groups[$gid][$check_permission] );
					// add newly installed module
					$addons[]	= $addon_directory;
					$addons		= array_unique($addons);
					asort( $addons );
					// Update the database
					$addon_permissions = implode(',', $addons);
					$database->query('UPDATE '.TABLE_PREFIX.'groups SET `' . $check_permission . '` = "' . $addon_permissions . '" WHERE `group_id` = '.$gid);
					if ( $database->is_error() )
					{
						$admin->print_error($database->get_error());
					}
				}
			}
			$admin->print_success( 'Installed successfully' );
		}
		else
		{
			$admin->print_success( 'Installed successfully' );
		}
	}
	else
	{
		$admin->print_success( 'Installed successfully' );
	}
}
elseif ( $addon_info['addon_function'] == 'module' && $action == 'upgrade' )
{
	$addon_helper->upgradeModule( $addon_directory, false );
	$admin->print_success( 'Upgraded successfully' );
}
elseif ( $addon_info['addon_function'] == 'template' && $action == 'upgrade' )
{
	$addon_helper->installTemplate( $addon_dir );
	$admin->print_success( 'Upgraded successfully' );
}
elseif ( $addon_info['addon_function'] == 'language' && $action == 'upgrade' )
{
	rename( $temp_file, $addon_dir );
	// Chmod the file
	change_mode( $addon_dir , 'file');
	$addon_helper->installLanguage( $addon_dir );
	$admin->print_success( 'Upgraded successfully' );
}

// Print admin footer
$admin->print_error( 'Install/Upgrade of add-on failed' );

// remove temp dirs/files
function CLEANUP()
{
	global $admin, $temp_unzip, $temp_file;
	$admin->get_helper('Directory')->removeDirectory($temp_unzip);
	$admin->get_helper('Directory')->removeDirectory($temp_file);
}

// recursive function to copy
// all subdirectories and contents:
function COPY_RECURSIVE_DIRS( $dirsource, $dirdest )
{
	global $admin;
	if (is_dir($dirsource))
	{
		$dir_handle = opendir($dirsource);
	}
	while ($file = readdir($dir_handle))
	{
		if ($file != "." && $file != "..")
		{
			if (!is_dir($dirsource . "/" . $file))
			{
				copy($dirsource . "/" . $file, $dirdest . '/' . $file);
				if ($file != '.svn')
				{
					change_mode($dirdest . "/" . $file, 'file');
				}
			}
			else
			{
				$admin->get_helper('Directory')->createDirectory( $dirdest . '/' . $file );
				COPY_RECURSIVE_DIRS($dirsource . "/" . $file, $dirdest . '/' . $file);
			}
		}
	}
	closedir($dir_handle);
	return true;
}

?>