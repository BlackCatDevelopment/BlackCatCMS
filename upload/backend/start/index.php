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
 * @license			http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
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

// exec initial_page
if(file_exists(CAT_PATH .'/modules/initial_page/classes/c_init_page.php') && isset($_SESSION['USER_ID'])) {
	require_once (CAT_PATH .'/modules/initial_page/classes/c_init_page.php');
	$ins = new c_init_page($database, $_SESSION['USER_ID'], $_SERVER['SCRIPT_NAME']);
}

require_once(CAT_PATH.'/framework/class.admin.php');

$admin = new admin('Start','start');
$lang  = CAT_Helper_I18n::getInstance();

// ================================================ 
// ! Check if installation directory still exists   
// ================================================ 
if( file_exists(CAT_PATH.'/install/') ) {
	// Check if user is part of Adminstrators group
	if( in_array (1, $admin->get_groups_id() ) )
	{
		/** 
		 *	Try to delete it - it's still not needed anymore.
		 */
		if (function_exists('rm_full_dir') ) {
			rm_full_dir(CAT_PATH.'/install/');
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

$tpl_data = array();

// ===================================================== 
// ! Insert permission values into the template object   
// ===================================================== 
$tpl_data['sections']['media']['permission']			= ($admin->get_permission('media')) ? true : false;
$tpl_data['sections']['media']['name']					= 'media';
$tpl_data['sections']['media']['title']				    = $lang->translate('Media');
$tpl_data['sections']['media']['description']			= $OVERVIEW['MEDIA'];

$tpl_data['sections']['addons']['permission']			= ($admin->get_permission('addons')) ? true : false;
$tpl_data['sections']['addons']['name']				    = 'addons';
$tpl_data['sections']['addons']['title']				=  $lang->translate('Addons');

$tpl_data['sections']['addons']['subpages']['modules']['permission']    = ($admin->get_permission('modules')) ? true : false;
$tpl_data['sections']['addons']['subpages']['modules']['name']          = 'addons';
$tpl_data['sections']['addons']['subpages']['modules']['title']         = $lang->translate('Modules');
$tpl_data['sections']['addons']['subpages']['modules']['description']   = $OVERVIEW['MODULES'];

$tpl_data['sections']['addons']['subpages']['templates']['permission']  = ($admin->get_permission('templates')) ? true : false;
$tpl_data['sections']['addons']['subpages']['templates']['name']        = 'templates';
$tpl_data['sections']['addons']['subpages']['templates']['title']       = $lang->translate('Templates');
$tpl_data['sections']['addons']['subpages']['templates']['description'] = $OVERVIEW['TEMPLATES'];

$tpl_data['sections']['addons']['subpages']['languages']['permission']  = ($admin->get_permission('languages')) ? true : false;
$tpl_data['sections']['addons']['subpages']['languages']['name']        = 'languages';
$tpl_data['sections']['addons']['subpages']['languages']['title']       = $lang->translate('Languages');
$tpl_data['sections']['addons']['subpages']['languages']['description'] = $OVERVIEW['LANGUAGES'];

$tpl_data['sections']['access']['permission']                           = ($admin->get_permission('access')) ? true : false;
$tpl_data['sections']['access']['name']                                 = 'access';
$tpl_data['sections']['access']['title']                                = $lang->translate('Access');

$tpl_data['sections']['access']['subpages']['users']['permission']      = ($admin->get_permission('modules')) ? true : false;
$tpl_data['sections']['access']['subpages']['users']['name']            = 'users';
$tpl_data['sections']['access']['subpages']['users']['title']           = $lang->translate('Users');
$tpl_data['sections']['access']['subpages']['users']['description']     = $OVERVIEW['USERS'];

$tpl_data['sections']['access']['subpages']['groups']['permission']     = ($admin->get_permission('templates')) ? true : false;
$tpl_data['sections']['access']['subpages']['groups']['name']           = 'groups';
$tpl_data['sections']['access']['subpages']['groups']['title']          = $lang->translate('Groups');
$tpl_data['sections']['access']['subpages']['groups']['description']    = $OVERVIEW['GROUPS'];

$tpl_data['sections']['settings']['permission']                         = ($admin->get_permission('settings')) ? true : false;
$tpl_data['sections']['settings']['name']                               = 'settings';
$tpl_data['sections']['settings']['title']                              = $lang->translate('Settings');
$tpl_data['sections']['settings']['description']                        = $OVERVIEW['SETTINGS'];

$tpl_data['sections']['admintools']['permission']                       = ($admin->get_permission('admintools')) ? true : false;
$tpl_data['sections']['admintools']['name']                             = 'admintools';
$tpl_data['sections']['admintools']['title']                            =  $lang->translate('Admintools');
$tpl_data['sections']['admintools']['description']                      = $OVERVIEW['ADMINTOOLS'];

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_start_index.lte', $tpl_data);

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();


?>