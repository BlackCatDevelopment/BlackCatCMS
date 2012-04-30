<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @version         $Id$
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

// Check if user uploaded a file
if (!isset($_FILES['userfile']) || $_FILES['userfile']['size'] == 0)
{
    header("Location: index.php");
    exit(0);
}

require_once( WB_PATH . '/framework/class.admin.php' );
$admin = new admin('Addons', 'modules_install');

// Include the WB functions file
require_once( WB_PATH . '/framework/functions.php' );

// Check if module dir is writable (doesn't make sense to go on if not)
if ( !is_writable(WB_PATH . '/modules/') )
{
	$admin->print_error( 'Unable to write to the target directory' );
}

// Set temp vars
$temp_dir		= WB_PATH . '/temp/';
$temp_file		= $temp_dir . $_FILES['userfile']['name'];
$temp_unzip		= WB_PATH . '/temp/unzip/';

// make sure the temp directory exists, is writable and is empty
rm_full_dir( $temp_unzip );
make_dir( $temp_unzip );

// Try to upload the file to the temp dir
if (!move_uploaded_file($_FILES['userfile']['tmp_name'], $temp_file))
{
	CLEANUP();
	$admin->print_error( 'Cannot upload file' );
}

// to avoid problems with two admins installing modules at the same time, we
// create a unique subdir
$temp_subdir	= $temp_unzip . basename($_FILES['userfile']['tmp_name']) . '/';
make_dir( $temp_subdir );

// Include the PclZip class file
require_once( WB_PATH . '/modules/lib_lepton/pclzip/pclzip.lib.php' );

// Setup the PclZip object and unzip the files to the temp unzip folder
$archive	= new PclZip($temp_file);
$list		= $archive->extract( PCLZIP_OPT_PATH, $temp_subdir );

// Check if uploaded file is a valid Add-On zip file
if (!($list && file_exists($temp_subdir . 'index.php')))
{
	CLEANUP();
	$admin->print_error( 'Invalid LEPTON installation file. Please check the *.zip format.' );
}

// As we are going to check for a valid info.php, let's unset all vars expected
// there to see if they're set correctly
$varnames = array(
	'module_license',
	'module_author',
	'module_name',
	'module_directory',
	'module_version',
	'module_function',
	'module_description',
	'module_platform'
);

foreach ( $varnames as $varname )
{
	unset(${$varname});
}

// Include the modules info file
require( $temp_subdir . 'info.php' );

// Perform Add-on requirement checks before proceeding
require( WB_PATH . '/framework/addon.precheck.inc.php' );
preCheckAddon( $temp_file, $temp_subdir );

// Delete the temp unzip directory
// ----- MOVED! Why should we unzip more than once? ------
// rm_full_dir($temp_unzip);

// Check if the file is valid
if (
	(!isset($module_license)) ||
	(!isset($module_author)) ||
	(!isset($module_directory)) ||
	(!isset($module_name)) ||
	(!isset($module_version)) ||
	(!isset($module_function)) //||
//    (!isset($module_guid))
    )
{
	CLEANUP();
	$admin->print_error( $admin->lang->translate( 'The installation of module "{{module}}" failed, one (or more) of the following variables is missing:<ul><li>module_directory</li><li>module_name</li><li>module_version</li><li>module_author</li><li>module_license</li><li>module_guid</li><li>module_function</li></ul>', array( 'module' => $module_name ) ) );
}

foreach ( $varnames as $varname )
{
	${'new_' . $varname} = ${$varname};
	unset(${$varname});
}

// So, now we have done all preinstall checks, lets see what to do next
$module_directory	= $new_module_directory;
$action				= 'install';

if ( is_dir( WB_PATH . '/modules/' . $module_directory ) )
{
	$action = 'upgrade';
	// look for old info.php
	if ( file_exists( WB_PATH . '/modules/' . $module_directory . '/info.php' ) )
	{
		require(WB_PATH . '/modules/' . $module_directory . '/info.php');
		/**
		*	Version to be installed is older than currently installed version
		*/
		if ( versionCompare ( $module_version, $new_module_version, '>=' ) )
		{
			CLEANUP();
			$admin->print_error( 'Already installed' );
		}
	}
}

// Set module directory
$module_dir = WB_PATH . '/modules/' . $module_directory;

// Make sure the module dir exists, and chmod if needed
make_dir($module_dir);

// copy files from temp folder
if (COPY_RECURSIVE_DIRS($temp_subdir, $module_dir) !== true)
{
	CLEANUP();
	$admin->print_error( 'Actualization not possibly' );
}

// remove temp
CLEANUP();

// load info.php again to have current values
if (file_exists(WB_PATH . '/modules/' . $module_directory . '/info.php'))
{
	require(WB_PATH . '/modules/' . $module_directory . '/info.php');
}
// Run the modules install // upgrade script if there is one
if (file_exists($module_dir . '/' . $action . '.php'))
{
	require($module_dir . '/' . $action . '.php');
}

// Print success message
if ( $action == 'install' )
{
	// Load module info into DB
	load_module(WB_PATH . '/modules/' . $module_directory, false);
	// let admin set access permissions for modules of type 'page' and 'tool'
	if ( $module_function == 'page' || $module_function == 'tool' )
	{
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
					$modules = explode(',', $groups[$gid]['module_permissions'] );
					// add newly installed module
					$modules[] = $module_directory;
					$modules = array_unique($modules);
					asort($modules);
					// Update the database
					$module_permissions = implode(',', $modules);
					$database->query('UPDATE '.TABLE_PREFIX.'groups SET `module_permissions` = "'.$module_permissions.'" WHERE `group_id` = '.$gid);
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
elseif ( $action == 'upgrade' )
{
	upgrade_module( $module_directory, false );
	$admin->print_success( 'Upgraded successfully' );
}

// Print admin footer
$admin->print_error( 'Install/Upgrade of module failed' );

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