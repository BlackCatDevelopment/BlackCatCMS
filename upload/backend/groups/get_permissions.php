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

// ========================== 
// ! Get system permissions   
// ========================== 

// ============================= 
// ! Get permissions for pages   
// ============================= 
$system_permissions['pages_view']			= $admin->get_post('pages_view') == 1		? true : false;
$system_permissions['pages_add']			= $admin->get_post('pages_add') == 1		? true : false;
$system_permissions['pages_add_l0']			= $admin->get_post('pages_add_l0') == 1		? true : false;
if( empty($system_permissions['pages_add']) && !empty($system_permissions['pages_add_l0']) )
{
	$system_permissions['pages_add']		= true;
}

$system_permissions['pages_settings']		= $admin->get_post('pages_settings') == 1	? true : false;
$system_permissions['pages_modify']			= $admin->get_post('pages_modify') == 1		? true : false;
$system_permissions['pages_intro']			= $admin->get_post('pages_intro') == 1		? true : false;
$system_permissions['pages_delete']			= $admin->get_post('pages_delete') == 1		? true : false;

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
$system_permissions['media_view']			= ( $admin->get_post('media_view') == 1 ) ? true : false;
$system_permissions['media_upload']			= ( $admin->get_post('media_upload') == 1 ) ? true : false;
$system_permissions['media_rename']			= ( $admin->get_post('media_rename') == 1 ) ? true : false;
$system_permissions['media_delete']			= ( $admin->get_post('media_delete') == 1 ) ? true : false;
$system_permissions['media_create']			= ( $admin->get_post('media_create') == 1 ) ? true : false;

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
$system_permissions['modules_view']			= ( $admin->get_post('modules_view') == 1 ) ? true : false;
$system_permissions['modules_install']		= ( $admin->get_post('modules_install') == 1 ) ? true : false;
$system_permissions['modules_uninstall']	= ( $admin->get_post('modules_uninstall') == 1 ) ? true : false;

$system_permissions['modules']				= (		!empty($system_permissions['modules_view']) ||
													!empty($system_permissions['modules_install']) ||
													!empty($system_permissions['modules_uninstall']) )
												? true : false;


// =============================== 
// ! get permissions for templates   
// =============================== 
$system_permissions['templates_view']		= ( $admin->get_post('templates_view') == 1 ) ? true : false;
$system_permissions['templates_install']	= ( $admin->get_post('templates_install') == 1 ) ? true : false;
$system_permissions['templates_uninstall']	= ( $admin->get_post('templates_uninstall') == 1 ) ? true : false;

$system_permissions['templates']			= (		!empty($system_permissions['templates_view']) ||
													!empty($system_permissions['templates_install']) ||
													!empty($system_permissions['templates_uninstall']) )
												? true : false;


// =============================== 
// ! get permissions for languages   
// =============================== 
$system_permissions['languages_view']		= ( $admin->get_post('languages_view') == 1 ) ? true : false;
$system_permissions['languages_install']	= ( $admin->get_post('languages_install') == 1 ) ? true : false;
$system_permissions['languages_uninstall']	= ( $admin->get_post('languages_uninstall') == 1 ) ? true : false;

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
$system_permissions['settings_basic']		= ( $admin->get_post('settings_basic') == 1 ) ? true : false;
$system_permissions['settings_advanced']	= ( $admin->get_post('settings_advanced') == 1 ) ? true : false;
$system_permissions['settings']				= (		!empty($system_permissions['settings_basic']) ||
													!empty($system_permissions['settings_advanced']) )
												? true : false;


// ============================= 
// ! Get permissions for users   
// ============================= 
$system_permissions['users_view']			= ( $admin->get_post('users_view') == 1 ) ? true : false;
$system_permissions['users_add']			= ( $admin->get_post('users_add') == 1 ) ? true : false;
$system_permissions['users_modify']			= ( $admin->get_post('users_modify') == 1 ) ? true : false;
$system_permissions['users_delete']			= ( $admin->get_post('users_delete') == 1 ) ? true : false;

$system_permissions['users']				= (		!empty($system_permissions['users_view']) ||
													!empty($system_permissions['users_add']) ||
													!empty($system_permissions['users_modify']) ||
													!empty($system_permissions['users_delete']) )
												? true : false;


// ============================= 
// ! Get permissions for groups   
// ============================= 
$system_permissions['groups_view']			= ( $admin->get_post('groups_view') == 1 ) ? true : false;
$system_permissions['groups_add']			= ( $admin->get_post('groups_add') == 1 ) ? true : false;
$system_permissions['groups_modify']		= ( $admin->get_post('groups_modify') == 1 ) ? true : false;
$system_permissions['groups_delete']		= ( $admin->get_post('groups_delete') == 1 ) ? true : false;

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
$system_permissions['admintools']			= ( $admin->get_post('admintools') == 1 ) ? true : false;
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
$dirs				= scan_current_dir( LEPTON_PATH . '/modules' );
if ( is_array( $admin->get_post('module_permissions') ) )
{
	foreach($admin->get_post('module_permissions') AS $selected_name)
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
$dirs					= scan_current_dir(LEPTON_PATH.'/templates');
if ( is_array( $admin->get_post('template_permissions') ) )
{
	foreach($admin->get_post('template_permissions') AS $selected_name)
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