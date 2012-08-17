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
 *
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

require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Addons', 'languages_uninstall');

$file	= trim( $admin->get_post('file') );

// Check if user selected language
if ( $file == '' )
{
	header("Location: index.php");
	exit(0);
}

// Include the WB functions file
require_once(WB_PATH.'/framework/functions.php');

// Check if the language exists
if ( !file_exists(WB_PATH.'/languages/' . $file . '.php') )
{
	$admin->print_error( 'Not installed' );
}

// Check if the language is in use
if ( $file == DEFAULT_LANGUAGE || $file == LANGUAGE )
{
	$admin->print_error( 'Cannot Uninstall: the selected file is in use' );
}
else
{
	$query_users	= $database->query("SELECT user_id FROM " . TABLE_PREFIX . "users WHERE language = '" . $admin->add_slashes($file) . "' LIMIT 1");
	if ( $query_users->numRows() > 0 )
	{
		$admin->print_error( 'Cannot Uninstall: the selected file is in use' );
	}
}

// Try to delete the language file
if ( !unlink(WB_PATH.'/languages/' . $file . '.php') )
{
	$admin->print_error( 'Cannot uninstall' );
}
else
{
	// Remove entry from DB
	$database->query("DELETE FROM " . TABLE_PREFIX . "addons WHERE directory = '" . $file . "' AND type = 'language'");
}

// Print success message
$admin->print_success( 'Uninstalled successfully' );

// Print admin footer
$admin->print_footer();

?>