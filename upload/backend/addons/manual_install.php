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

// ==================================== 
// ! check if there is anything to do   
// ==================================== 
require_once('../../framework/class.admin.php');

// check user permissions for admintools (redirect users with wrong permissions)
$admin		= new admin('Admintools', 'admintools', false, false);
if ($admin->get_permission('admintools') == false)
{
	die(header('Location: ../../index.php'));
}

$action		= $admin->get_post('action');
$file		= $admin->get_post('file');

if ( !in_array( $action, array('install', 'upgrade') ) )
{
	die(header('Location: ' . ADMIN_URL));
}
if ( $file == '' || !( strpos($file, '..') === false ) )
{
	die(header('Location: ' . ADMIN_URL));
}

// include WB functions file
require_once(WB_PATH . '/framework/functions.php');

// create Admin object with admin header
$admin		= new admin('Addons', '', true, false);
$js_back	= ADMIN_URL . '/modules/index.php';

/**
 * Manually execute the specified module file (install.php, upgrade.php or uninstall.php)
 */
 
// check if specified module folder exists
$mod_path		= WB_PATH . '/modules/' . basename(WB_PATH . '/' . $file);

// let the old variablename if module use it
$module_dir		= $mod_path;
if ( !file_exists( $mod_path . '/' . $action . '.php') )
{
	$admin->print_error($admin->lang->translate( 'Not found' ) . ': <tt>"' . htmlentities(basename($mod_path)) . '/' . $action . '.php"</tt> ', $js_back);
}

// Perform Add-on requirement checks before proceeding
require ( WB_PATH . '/framework/addon.precheck.inc.php' );
preCheckAddon( NULL, $mod_path, false );

// include modules install.php script
require ( $mod_path . '/' . $action . '.php' );

// load module info into database and output status message
require( $mod_path . '/info.php' );
load_module($mod_path, false);
$msg		= $admin->lang->translate( 'Execute' ) . ': <tt>"' . htmlentities(basename($mod_path)) . '/' . $action . '.php"</tt>';

switch ($action)
{
	case 'install':
	case 'upgrade':
		$admin->print_success($msg, $js_back);
		break;
	default:
		$admin->print_error( 'Action not supported', $js_back );
}

?>