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
// end include class.secure.php

// exec initial_page
if(file_exists(WB_PATH .'/modules/initial_page/classes/c_init_page.php') && isset($_SESSION['USER_ID'])) {
	require_once (WB_PATH .'/modules/initial_page/classes/c_init_page.php');
	$ins = new c_init_page($database, $_SESSION['USER_ID'], $_SERVER['SCRIPT_NAME']);
}
require_once(WB_PATH.'/framework/class.admin.php');$admin = new admin('Start','start');


// ================================================ 
// ! Check if installation directory still exists   
// ================================================ 
if( file_exists(WB_PATH.'/install/') ) {
	// Check if user is part of Adminstrators group
	if( in_array (1, $admin->get_groups_id() ) )
	{
		/** 
		 *	Try to delete it - it's still not needed anymore.
		 */
		if (function_exists('rm_full_dir') ) {
			rm_full_dir(WB_PATH.'/install/');
		}
	}
}

// =========================================================================== 
// ! Create the controller, it is reusable and can render multiple templates 	
// =========================================================================== 
global $parser;

if (!is_object($parser))
{
	$admin->print_error('Global parser error couldn\'t be loaded!', false);
}

$data_dwoo = array();

//$data_dwoo['TEXT']['DISPLAY_NAME'] = $admin->get_display_name();


// ===================================================== 
// ! Insert permission values into the template object   
// ===================================================== 
$data_dwoo['sections']['media']['permission']			= ($admin->get_permission('media')) ? true : false;
$data_dwoo['sections']['media']['name']					= 'media';
$data_dwoo['sections']['media']['title']				= $MENU['MEDIA'];
$data_dwoo['sections']['media']['description']			= $OVERVIEW['MEDIA'];


$data_dwoo['sections']['addons']['permission']			= ($admin->get_permission('addons')) ? true : false;
$data_dwoo['sections']['addons']['name']				= 'addons';
$data_dwoo['sections']['addons']['title']				=  $MENU['ADDONS'];

$data_dwoo['sections']['addons']['subpages']['modules']['permission'] = ($admin->get_permission('modules')) ? true : false;
$data_dwoo['sections']['addons']['subpages']['modules']['name'] = 'addons';
$data_dwoo['sections']['addons']['subpages']['modules']['title'] =  $MENU['MODULES'];
$data_dwoo['sections']['addons']['subpages']['modules']['description'] = $OVERVIEW['MODULES'];

$data_dwoo['sections']['addons']['subpages']['templates']['permission'] = ($admin->get_permission('templates')) ? true : false;
$data_dwoo['sections']['addons']['subpages']['templates']['name'] = 'templates';
$data_dwoo['sections']['addons']['subpages']['templates']['title'] =  $MENU['TEMPLATES'];
$data_dwoo['sections']['addons']['subpages']['templates']['description'] = $OVERVIEW['TEMPLATES'];

$data_dwoo['sections']['addons']['subpages']['languages']['permission'] = ($admin->get_permission('languages')) ? true : false;
$data_dwoo['sections']['addons']['subpages']['languages']['name'] = 'languages';
$data_dwoo['sections']['addons']['subpages']['languages']['title'] =  $MENU['LANGUAGES'];
$data_dwoo['sections']['addons']['subpages']['languages']['description'] = $OVERVIEW['LANGUAGES'];


$data_dwoo['sections']['access']['permission'] = ($admin->get_permission('access')) ? true : false;
$data_dwoo['sections']['access']['name'] = 'access';
$data_dwoo['sections']['access']['title'] =  $MENU['ACCESS'];

$data_dwoo['sections']['access']['subpages']['users']['permission'] = ($admin->get_permission('modules')) ? true : false;
$data_dwoo['sections']['access']['subpages']['users']['name'] = 'users';
$data_dwoo['sections']['access']['subpages']['users']['title'] =  $MENU['USERS'];
$data_dwoo['sections']['access']['subpages']['users']['description'] = $OVERVIEW['USERS'];

$data_dwoo['sections']['access']['subpages']['groups']['permission'] = ($admin->get_permission('templates')) ? true : false;
$data_dwoo['sections']['access']['subpages']['groups']['name'] = 'groups';
$data_dwoo['sections']['access']['subpages']['groups']['title'] =  $MENU['GROUPS'];
$data_dwoo['sections']['access']['subpages']['groups']['description'] = $OVERVIEW['GROUPS'];


$data_dwoo['sections']['settings']['permission'] = ($admin->get_permission('settings')) ? true : false;
$data_dwoo['sections']['settings']['name'] = 'settings';
$data_dwoo['sections']['settings']['title'] =  $MENU['SETTINGS'];
$data_dwoo['sections']['settings']['description'] = $OVERVIEW['SETTINGS'];

$data_dwoo['sections']['admintools']['permission'] = ($admin->get_permission('admintools')) ? true : false;
$data_dwoo['sections']['admintools']['name'] = 'admintools';
$data_dwoo['sections']['admintools']['title'] =  $MENU['ADMINTOOLS'];
$data_dwoo['sections']['admintools']['description'] = $OVERVIEW['ADMINTOOLS'];

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_start_index.lte', $data_dwoo);

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();


?>