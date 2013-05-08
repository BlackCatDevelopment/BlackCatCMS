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
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (defined('CAT_PATH')) {
    if (defined('CAT_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
    include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
    $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));    $dir = $_SERVER['DOCUMENT_ROOT'];
    $inc = false;
    foreach ($subs as $sub) {
        if (empty($sub)) continue; $dir .= '/'.$sub;
        if (file_exists($dir.'/framework/class.secure.php')) {
            include($dir.'/framework/class.secure.php'); $inc = true;    break;
        }
    }
    if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

$val     = CAT_Helper_Validate::getInstance();

// ========================== 
// ! Get system permissions   
// ========================== 

// ============================= 
// ! Get permissions for pages   
// ============================= 
$system_permissions['pages_view']			= $val->sanitizePost('pages_view')   == 1 ? true : false;
$system_permissions['pages_add']			= $val->sanitizePost('pages_add')    == 1 ? true : false;
$system_permissions['pages_add_l0']			= $val->sanitizePost('pages_add_l0') == 1 ? true : false;
if( empty($system_permissions['pages_add']) && !empty($system_permissions['pages_add_l0']) )
	$system_permissions['pages_add']		= true;
$system_permissions['pages_settings']		= $val->sanitizePost('pages_settings') == 1	? true : false;
$system_permissions['pages_modify']			= $val->sanitizePost('pages_modify')   == 1	? true : false;
$system_permissions['pages_intro']			= $val->sanitizePost('pages_intro')    == 1 ? true : false;
$system_permissions['pages_delete']			= $val->sanitizePost('pages_delete')   == 1 ? true : false;

$system_permissions['pages']				= (		!empty($system_permissions['pages_view']) ||
													!empty($system_permissions['pages_add']) ||
													!empty($system_permissions['pages_settings']) ||
													!empty($system_permissions['pages_modify']) ||
													!empty($system_permissions['pages_intro']) ||
													!empty($system_permissions['pages_delete']) )
												? true : false;


// ============================= 
// ! Get permissions for media   
// ============================= 
$system_permissions['media_view']			= ( $val->sanitizePost('media_view')   == 1 ) ? true : false;
$system_permissions['media_upload']			= ( $val->sanitizePost('media_upload') == 1 ) ? true : false;
$system_permissions['media_rename']			= ( $val->sanitizePost('media_rename') == 1 ) ? true : false;
$system_permissions['media_delete']			= ( $val->sanitizePost('media_delete') == 1 ) ? true : false;
$system_permissions['media_create']			= ( $val->sanitizePost('media_create') == 1 ) ? true : false;

$system_permissions['media']				= (		!empty($system_permissions['media_view']) ||
													!empty($system_permissions['media_upload']) ||
													!empty($system_permissions['media_rename']) ||
													!empty($system_permissions['media_delete']) ||
													!empty($system_permissions['media_create']) )
												? true : false;


// ============================= 
// ! Get permission for addons   
// ============================= 

// =============================== 
// ! get permissions for modules   
// =============================== 
$system_permissions['modules_view']			= ( $val->sanitizePost('modules_view') == 1 ) ? true : false;
$system_permissions['modules_install']		= ( $val->sanitizePost('modules_install') == 1 ) ? true : false;
$system_permissions['modules_uninstall']	= ( $val->sanitizePost('modules_uninstall') == 1 ) ? true : false;

$system_permissions['modules']				= (		!empty($system_permissions['modules_view']) ||
													!empty($system_permissions['modules_install']) ||
													!empty($system_permissions['modules_uninstall']) )
												? true : false;


// =============================== 
// ! get permissions for templates   
// =============================== 
$system_permissions['templates_view']		= ( $val->sanitizePost('templates_view') == 1 ) ? true : false;
$system_permissions['templates_install']	= ( $val->sanitizePost('templates_install') == 1 ) ? true : false;
$system_permissions['templates_uninstall']	= ( $val->sanitizePost('templates_uninstall') == 1 ) ? true : false;

$system_permissions['templates']			= (		!empty($system_permissions['templates_view']) ||
													!empty($system_permissions['templates_install']) ||
													!empty($system_permissions['templates_uninstall']) )
												? true : false;


// =============================== 
// ! get permissions for languages   
// =============================== 
$system_permissions['languages_view']		= ( $val->sanitizePost('languages_view') == 1 ) ? true : false;
$system_permissions['languages_install']	= ( $val->sanitizePost('languages_install') == 1 ) ? true : false;
$system_permissions['languages_uninstall']	= ( $val->sanitizePost('languages_uninstall') == 1 ) ? true : false;

$system_permissions['languages']			= (		!empty($system_permissions['languages_view']) ||
													!empty($system_permissions['languages_install']) ||
													!empty($system_permissions['languages_uninstall']) )
												? true : false;
// Do we need permissions for languages if you're allowed to view languages?


// ============================== 
// ! Set permissions for addons   
// ============================== 
$system_permissions['addons']				= (		isset($system_permissions['modules']) ||
													isset($system_permissions['templates']) ||
													isset($system_permissions['languages']) )
												? true : false;


// ================================ 
// ! Get permissions for settings   
// ================================ 
$system_permissions['settings_basic']		= ( $val->sanitizePost('settings_basic') == 1 ) ? true : false;
$system_permissions['settings_advanced']	= ( $val->sanitizePost('settings_advanced') == 1 ) ? true : false;
$system_permissions['settings']				= (		!empty($system_permissions['settings_basic']) ||
													!empty($system_permissions['settings_advanced']) )
												? true : false;


// ============================= 
// ! Get permissions for users   
// ============================= 
$system_permissions['users_view']			= ( $val->sanitizePost('users_view') == 1 ) ? true : false;
$system_permissions['users_add']			= ( $val->sanitizePost('users_add') == 1 ) ? true : false;
$system_permissions['users_modify']			= ( $val->sanitizePost('users_modify') == 1 ) ? true : false;
$system_permissions['users_delete']			= ( $val->sanitizePost('users_delete') == 1 ) ? true : false;

$system_permissions['users']				= (		!empty($system_permissions['users_view']) ||
													!empty($system_permissions['users_add']) ||
													!empty($system_permissions['users_modify']) ||
													!empty($system_permissions['users_delete']) )
												? true : false;


// ============================= 
// ! Get permissions for groups   
// ============================= 
$system_permissions['groups_view']			= ( $val->sanitizePost('groups_view') == 1 ) ? true : false;
$system_permissions['groups_add']			= ( $val->sanitizePost('groups_add') == 1 ) ? true : false;
$system_permissions['groups_modify']		= ( $val->sanitizePost('groups_modify') == 1 ) ? true : false;
$system_permissions['groups_delete']		= ( $val->sanitizePost('groups_delete') == 1 ) ? true : false;

$system_permissions['groups']				= (		!empty($system_permissions['groups_view']) ||
													!empty($system_permissions['groups_add']) ||
													!empty($system_permissions['groups_modify']) ||
													!empty($system_permissions['groups_delete']) )
												? true : false;

// ============================= 
// ! Set permissions for access   
// ============================= 
$system_permissions['access']				= ( !empty($system_permissions['users']) || !empty($system_permissions['groups']) ) ? true : false;

// ================================== 
// ! Get permissions for admintools   
// ================================== 
// Has to be checked whether we need both values?
$system_permissions['admintools']			= ( $val->sanitizePost('admintools') == 1 ) ? true : false;
$system_permissions['admintools_settings']	= $system_permissions['admintools'];

// ============================== 
// ! Implode system permissions   
// ============================== 
$imploded_system_permissions				 = '';
foreach($system_permissions AS $name => $value)
{
	if($value == true)
	{
		if($imploded_system_permissions == '')
		{
			$imploded_system_permissions	 = $name;
		}
		else
		{
			$imploded_system_permissions	.= ','.$name;
		}
	}
}
$system_permissions							= $imploded_system_permissions;

// ========================== 
// ! Get module permissions   
// ========================== 
$modules			= array();
$module_permissions	= '';
$dirs				= scan_current_dir( CAT_PATH . '/modules' );
if ( is_array( $val->sanitizePost('module_permissions') ) )
{
	foreach($val->sanitizePost('module_permissions') AS $selected_name)
	{
		// Check, whether the activated module is also 1
		if( in_array ($selected_name, $dirs['path']) )
		{
			$modules[]						= $selected_name;
		}
	}
}
$modules									= ( sizeof($modules) > 0 ) ? array_diff($dirs['path'], $modules) : $dirs['path'];
$module_permissions							= implode(',', $modules);

// ============================ 
// ! Get template permissions   
// ============================ 
$templates				= array();
$template_permissions	= '';
$dirs					= scan_current_dir(CAT_PATH.'/templates');
if ( is_array( $val->sanitizePost('template_permissions') ) )
{
	foreach($val->sanitizePost('template_permissions') AS $selected_name)
	{
		if ( in_array ($selected_name, $dirs['path']) )
		{
			$templates[]					= $selected_name;
		}
	}
}
$templates									= (sizeof($templates) > 0) ? array_diff($dirs['path'], $templates) : $dirs['path'];
$template_permissions						= implode(',', $templates);

?>