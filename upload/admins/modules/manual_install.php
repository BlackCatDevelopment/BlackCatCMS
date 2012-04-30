<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
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



/**
 * check if there is anything to do
 */

if (!(isset($_POST['action']) && in_array($_POST['action'], array('install', 'upgrade', 'uninstall')))) { die(header('Location: index.php?advanced')); }
if (!(isset($_POST['file']) && $_POST['file'] != '' && (strpos($_POST['file'], '..') === false))){  die(header('Location: index.php?advanced'));  }

/**
 * check if user has permissions to access this file
 */
require_once('../../framework/class.admin.php');

// check user permissions for admintools (redirect users with wrong permissions)
$admin = new admin('Admintools', 'admintools', false, false);
if ($admin->get_permission('admintools') == false) { die(header('Location: ../../index.php')); }

// check if the referer URL if available
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 
	(isset($HTTP_SERVER_VARS['HTTP_REFERER']) ? $HTTP_SERVER_VARS['HTTP_REFERER'] : '');

// if referer is set, check if script was invoked from "admin/modules/index.php"
$required_url = ADMIN_URL . '/modules/index.php';
if ($referer != '' && (!(strpos($referer, $required_url) !== false || strpos($referer, $required_url) !== false))) 
{ die(header('Location: ../../index.php')); }

// include WB functions file
require_once(WB_PATH . '/framework/functions.php');

// load WB language file
require_once(WB_PATH . '/languages/' . LANGUAGE .'.php');

// create Admin object with admin header
$admin = new admin('Addons', '', true, false);
$js_back = ADMIN_URL . '/modules/index.php?advanced';

/**
 * Manually execute the specified module file (install.php, upgrade.php or uninstall.php)
 */
// check if specified module folder exists
$mod_path = WB_PATH . '/modules/' . basename(WB_PATH . '/' . $_POST['file']);

// let the old variablename if module use it
$module_dir = $mod_path;
if (!file_exists($mod_path . '/' . $_POST['action'] . '.php'))
{
    $admin->print_error($TEXT['NOT_FOUND'].': <tt>"'.htmlentities(basename($mod_path)).'/'.$_POST['action'].'.php"</tt> ', $js_back);
}

// include modules install.php script
require($mod_path . '/' . $_POST['action'] . '.php');

// load module info into database and output status message
require( $mod_path."/info.php");
load_module($mod_path, false);
$msg = $TEXT['EXECUTE'] . ': <tt>"' . htmlentities(basename($mod_path)) . '/' . $_POST['action'] . '.php"</tt>';

switch ($_POST['action'])
{
	case 'install':
	case 'upgrade':
	case 'uninstall':
		$admin->print_success($msg, $js_back);
		break;
		
	default:
		$admin->print_error( $TEXT["ACTION_NOT_SUPPORTED"], $js_back);
}

?>