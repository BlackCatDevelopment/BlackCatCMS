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

// make sure the temp directory exists, is writable and is empty
rm_full_dir( $temp_unzip );
make_dir( $temp_unzip );

// Try to upload the file to the temp dir
if ( !move_uploaded_file($_FILES['userfile']['tmp_name'], $temp_file) )
{
	CLEANUP();
	$admin->print_error( 'Cannot upload file' );
}

// Check if uploaded file is a valid language file (no binary file etc.)
$fileinfo	= pathinfo( $temp_file );
if ( $fileinfo['extension'] == "php" )
{
	$content	= file_get_contents( $temp_file );
	if (strpos($content, '<?php') === false) $admin->print_error( 'Invalid LEPTON language file. Please check the text file.' );
	else $function	= 'language';
	$temp_subdir	= $temp_unzip;
}
else {
	// to avoid problems with two admins installing modules at the same time, we
	// create a unique subdir
	$temp_subdir	= $temp_unzip . 'unzip/';
	make_dir( $temp_subdir );
	
	$admin->get_helper('LEPTON_Helper_Zip');
	
	// Setup the PclZip object and unzip the files to the temp unzip folder
	$archive	= new PclZip( $temp_file );
	$list		= $archive->extract( PCLZIP_OPT_PATH, $temp_subdir );
	
	// Check if uploaded file is a valid Add-On zip file
	if ( !( $list && file_exists( $temp_subdir . 'index.php' ) ) )
	{
		CLEANUP();
		$admin->print_error( 'Invalid LEPTON installation file. Please check the *.zip format.' );
	}
}
// As we are going to check for a valid info.php, let's unset all vars expected
// there to see if they're set correctly
$varnames = array(
	'module'	=> array (
		'module_license',
		'module_author',
		'module_name',
		'module_directory',
		'module_version',
		'module_function',
		'module_description',
		'module_platform',
		'module_guid'
		),
	'template'	=> array (
		'template_license',
		'template_author',
		'template_name',
		'template_directory',
		'template_version',
		'template_function',
		'template_description',
		'template_platform',
		'template_guid'
		//'theme_directory'
	),
	'language'	=> array (
		'language_license',
		'language_code',
		'language_name',
		'language_version',
		'language_platform',
		'language_author',
		'language_guid'
	)
);

// Perform Add-on requirement checks before proceeding
require( LEPTON_PATH . '/framework/addon.precheck.inc.php' );

// Include the modules info file
if ( !isset($function) )
{
	foreach ( $varnames['module'] as $varname )
	{
		unset(${$varname});
	}
	foreach ( $varnames['template'] as $varname )
	{
		unset(${$varname});
	}

	$module_functions	= array( 'page', 'library', 'tool', 'snippet' );
	$template_functions	= array( 'template', 'theme' );

	require( $temp_subdir . 'info.php' );

	preCheckAddon( $temp_file, $temp_subdir );

	if ( isset( $module_function ) && in_array( $module_function , $module_functions ) )
	{
		$function			= 'module';
	}
	else if ( isset( $template_function ) && in_array( $template_function , $template_functions ) )
	{
		$function			= 'template';
	}
	else {
		CLEANUP();
		$admin->print_error( 'Invalid LEPTON installation file. Please check the *.zip format.' );
	}
}
else if ( $function == 'language' )
{
	foreach ( $varnames['language'] as $varname )
	{
		unset(${$varname});
	}
	require( $temp_file );
}
else {
	CLEANUP();
	$admin->print_error( 'Invalid LEPTON installation file. Please check the *.zip format.' );
}

// Check if the file is valid
foreach ( $varnames[$function] as $varname )
{
	if ( !isset(${$varname}) )
	{
		CLEANUP();
		// Restore to correct language
		if ( $function == 'language') require( LEPTON_PATH . '/' . $function . 's/' . LANGUAGE . '.php' );
		$admin->print_error( $admin->lang->translate( 'The installation of add-on failed, because the following variable is missing {{varname}}', array( 'varname' => $varname ) ) );
	}
	else {
		${'new_' . $varname} = ${$varname};
		unset(${$varname});
	}
}

// So, now we have done all preinstall checks, lets see what to do next
$addon_directory	= $function == 'language' ? ${'new_' . $function . '_code'} . '.php' : ${'new_' . $function . '_directory'};

// Set module directory
$addon_dir			= LEPTON_PATH .  '/' . $function . 's/' . $addon_directory;

$action				= 'install';

if ( file_exists( $addon_dir ) )
{
	$action = 'upgrade';
	// look for old info.php
	if ( file_exists( $addon_dir . '/info.php' ) || $function == 'language' )
	{
		if ( $function == 'language') require( $addon_dir );
		else require( $addon_dir . '/info.php');
		/**
		*	Version to be installed is older than currently installed version
		*/
		if ( versionCompare ( ${$function . '_version'}, ${'new_' . $function . '_version'}, '>=' ) )
		{
			CLEANUP();
			// Restore to correct language
			if ( $function == 'language') require(LEPTON_PATH . '/languages/' . LANGUAGE . '.php');
			$admin->print_error( 'Already installed' );
		}
	}
}

//rename($temp_file, $language_file);


// Make sure the module dir exists, and chmod if needed
if ( $function != 'language')
{
	make_dir( $addon_dir );

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
	require($addon_dir . '/info.php');
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
	if ( $function == 'module' ) load_module( $addon_dir , false);
	else if ( $function == 'template' ) load_template( $addon_dir );
	else {
		rename($temp_file, $addon_dir);
		// Chmod the file
		change_mode( $addon_dir , 'file');

		// Load language info into DB
		load_language($addon_dir);

		// Restore to correct language
		require( LEPTON_PATH . '/' . $function . 's/' . LANGUAGE . '.php');
	}

	// let admin set access permissions for modules of type 'page' and 'tool'
	if ( ( $function == 'module' && ( $module_function == 'page' || $module_function == 'tool' ) )
		|| $function == 'template' )
	{
		$check_permission	= $function . '_permissions';

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
elseif ( $function == 'module' && $action == 'upgrade' )
{
	upgrade_module( $addon_directory, false );
	$admin->print_success( 'Upgraded successfully' );
}
elseif ( $function == 'template' && $action == 'upgrade' )
{
	load_template( $addon_dir );
	$admin->print_success( 'Upgraded successfully' );
}
elseif ( $function == 'language' && $action == 'upgrade' )
{
	rename( $temp_file, $addon_dir );
	// Chmod the file
	change_mode( $addon_dir , 'file');
	load_language( $addon_dir );
	$admin->print_success( 'Upgraded successfully' );
}

// Print admin footer
$admin->print_error( 'Install/Upgrade of add-on failed' );

// remove temp dirs/files
function CLEANUP()
{
	global $temp_unzip, $temp_file;
	@rm_full_dir($temp_unzip);
	if (file_exists($temp_file))
	{
		unlink($temp_file); // Remove temp file
	}
}

// recursive function to copy
// all subdirectories and contents:
function COPY_RECURSIVE_DIRS($dirsource, $dirdest)
{
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
				make_dir($dirdest . "/" . $file);
				COPY_RECURSIVE_DIRS($dirsource . "/" . $file, $dirdest . '/' . $file);
			}
		}
	}
	closedir($dir_handle);
	return true;
}

?>