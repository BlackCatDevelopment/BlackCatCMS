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



// Get system permissions
if($admin->get_post('advanced') != 'yes')
{
	$system_permissions['pages'] = $admin->get_post('pages');
		$system_permissions['pages_view'] = $system_permissions['pages'];
		$system_permissions['pages_add'] = $system_permissions['pages'];
		$system_permissions['pages_add_l0'] = $system_permissions['pages'];
		$system_permissions['pages_settings'] = $system_permissions['pages'];
		$system_permissions['pages_modify'] = $system_permissions['pages'];
		$system_permissions['pages_intro'] = $system_permissions['pages'];
		$system_permissions['pages_delete'] = $system_permissions['pages'];
	$system_permissions['media'] = $admin->get_post('media');
		$system_permissions['media_view'] = $system_permissions['media'];
		$system_permissions['media_upload'] = $system_permissions['media'];
		$system_permissions['media_rename'] = $system_permissions['media'];
		$system_permissions['media_delete'] = $system_permissions['media'];
		$system_permissions['media_create'] = $system_permissions['media'];
	if($admin->get_post('modules') != '' || $admin->get_post('templates') != '' || $admin->get_post('languages') != '')
	{
		$system_permissions['addons'] = 1;
	} else {
		$system_permissions['addons'] = 0;
	}
		$system_permissions['modules'] = $admin->get_post('modules');
			$system_permissions['modules_view'] = $system_permissions['modules'];
			$system_permissions['modules_install'] = $system_permissions['modules'];
			$system_permissions['modules_uninstall'] = $system_permissions['modules'];
		$system_permissions['templates'] = $admin->get_post('templates');
			$system_permissions['templates_view'] = $system_permissions['templates'];
			$system_permissions['templates_install'] = $system_permissions['templates'];
			$system_permissions['templates_uninstall'] = $system_permissions['templates'];
		$system_permissions['languages'] = $admin->get_post('languages');
			$system_permissions['languages_view'] = $system_permissions['languages'];
			$system_permissions['languages_install'] = $system_permissions['languages'];
			$system_permissions['languages_uninstall'] = $system_permissions['languages'];
	$system_permissions['settings'] = $admin->get_post('settings');
		$system_permissions['settings_basic'] = $system_permissions['settings'];
		$system_permissions['settings_advanced'] = $system_permissions['settings'];
	if($admin->get_post('users') != '' || $admin->get_post('groups') != '')
	{
		$system_permissions['access'] = 1;
	} else {
		$system_permissions['access'] = 0;
	}
	$system_permissions['users'] = $admin->get_post('users');
	$system_permissions['users_view'] = $system_permissions['users'];
	$system_permissions['users_add'] = $system_permissions['users'];
	$system_permissions['users_modify'] = $system_permissions['users'];
	$system_permissions['users_delete'] = $system_permissions['users'];
	$system_permissions['groups'] = $admin->get_post('groups');
	$system_permissions['groups_view'] = $system_permissions['groups'];
	$system_permissions['groups_add'] = $system_permissions['groups'];
	$system_permissions['groups_modify'] = $system_permissions['groups'];
	$system_permissions['groups_delete'] = $system_permissions['groups'];
	$system_permissions['admintools'] = $admin->get_post('admintools');
	$system_permissions['admintools_settings'] = $system_permissions['admintools'];
} else {
	// Pages
	$system_permissions['pages_view'] = $admin->get_post('pages_view');
		$system_permissions['pages_add'] = $admin->get_post('pages_add');
	if($admin->get_post('pages_add') != 1 && $admin->get_post('pages_add_l0') == 1) {
		$system_permissions['pages_add'] = $admin->get_post('pages_add_l0');
	}
	$system_permissions['pages_add_l0'] = $admin->get_post('pages_add_l0');
	$system_permissions['pages_settings'] = $admin->get_post('pages_settings');
	$system_permissions['pages_modify'] = $admin->get_post('pages_modify');
	$system_permissions['pages_intro'] = $admin->get_post('pages_intro');
	$system_permissions['pages_delete'] = $admin->get_post('pages_delete');
	if($system_permissions['pages_view'] == 1 || $system_permissions['pages_add'] == 1 || $system_permissions['pages_settings'] == 1 || $system_permissions['pages_modify'] == 1 || $system_permissions['pages_intro'] == 1 || $system_permissions['pages_delete'] == 1)
	{
		$system_permissions['pages'] = 1;
	} else {
		$system_permissions['pages'] = '';
	}
	// Media
	$system_permissions['media_view'] = $admin->get_post('media_view');
	$system_permissions['media_upload'] = $admin->get_post('media_upload');
	$system_permissions['media_rename'] = $admin->get_post('media_rename');
	$system_permissions['media_delete'] = $admin->get_post('media_delete');
	$system_permissions['media_create'] = $admin->get_post('media_create');
	if($system_permissions['media_view'] == 1 || $system_permissions['media_upload'] == 1 || $system_permissions['media_rename'] == 1 || $system_permissions['media_delete'] == 1 || $system_permissions['media_create'] == 1)
	{
		$system_permissions['media'] = 1;
	} else {
		$system_permissions['media'] = '';
	}
	// Add-ons
		// Modules
		$system_permissions['modules_view'] = $admin->get_post('modules_view');
		$system_permissions['modules_install'] = $admin->get_post('modules_install');
		$system_permissions['modules_uninstall'] = $admin->get_post('modules_uninstall');
		if($system_permissions['modules_view'] == 1 || $system_permissions['modules_install'] == 1 || $system_permissions['modules_uninstall'] == 1)
		{
			$system_permissions['modules'] = 1;
		} else {
			$system_permissions['modules'] = '';
		}
		// Templates
		$system_permissions['templates_view'] = $admin->get_post('templates_view');
		$system_permissions['templates_install'] = $admin->get_post('templates_install');
		$system_permissions['templates_uninstall'] = $admin->get_post('templates_uninstall');
		if($system_permissions['templates_view'] == 1 || $system_permissions['templates_install'] == 1 || $system_permissions['templates_uninstall'] == 1)
		{
			$system_permissions['templates'] = 1;
		} else {
			$system_permissions['templates'] = '';
		}
		// Languages
		$system_permissions['languages_view'] = $admin->get_post('languages_view');
		$system_permissions['languages_install'] = $admin->get_post('languages_install');
		$system_permissions['languages_uninstall'] = $admin->get_post('languages_uninstall');
		if($system_permissions['languages_install'] == 1 || $system_permissions['languages_uninstall'] == 1)
		{
			$system_permissions['languages'] = 1;
		} else {
			$system_permissions['languages'] = '';
		}
		// Admintools
		$system_permissions['admintools_settings'] = $admin->get_post('admintools_settings');
		if($system_permissions['admintools_settings'] == 1) {
			$system_permissions['admintools'] = 1;
		} else {
			$system_permissions['admintools'] = '';
		}
	if($system_permissions['modules'] == 1 || $system_permissions['templates'] == 1 || $system_permissions['languages'] == 1)
	{
		$system_permissions['addons'] = 1;
	} else {
		$system_permissions['addons'] = '';
	}
	// Settings
	$system_permissions['settings_basic'] = $admin->get_post('settings_basic');
	$system_permissions['settings_advanced'] = $admin->get_post('settings_advanced');
	if($system_permissions['settings_basic'] == 1 || $system_permissions['settings_advanced'] == 1)
	{
		$system_permissions['settings'] = 1;
	} else {
		$system_permissions['settings'] = '';
	}
	// Access
		// Users
		$system_permissions['users_view'] = $admin->get_post('users_view');
		$system_permissions['users_add'] = $admin->get_post('users_add');
		$system_permissions['users_modify'] = $admin->get_post('users_modify');
		$system_permissions['users_delete'] = $admin->get_post('users_delete');
		if($system_permissions['users_view'] == 1 || $system_permissions['users_add'] == 1 || $system_permissions['users_modify'] == 1 || $system_permissions['users_delete'] == 1)
		{
			$system_permissions['users'] = 1;
		} else {
			$system_permissions['users'] = '';
		}
		// Groups
		$system_permissions['groups_view'] = $admin->get_post('groups_view');
		$system_permissions['groups_add'] = $admin->get_post('groups_add');
		$system_permissions['groups_modify'] = $admin->get_post('groups_modify');
		$system_permissions['groups_delete'] = $admin->get_post('groups_delete');
		if($system_permissions['groups_view'] == 1 || $system_permissions['groups_add'] == 1 || $system_permissions['groups_modify'] == 1 || $system_permissions['groups_delete'] == 1)
		{
			$system_permissions['groups'] = 1;
		} else {
			$system_permissions['groups'] = '';
		}
	if($system_permissions['users'] == 1 || $system_permissions['groups'] == 1)
	{
		$system_permissions['access'] = 1;
	} else {
		$system_permissions['access'] = '';
	}
}

// Implode system permissions
$imploded_system_permissions = '';
foreach($system_permissions AS $name => $value)
{
	if($value == true)
	{
		if($imploded_system_permissions == '')
		{
			$imploded_system_permissions = $name;
		} else {
			$imploded_system_permissions .= ','.$name;
		}
	}
}

$system_permissions = $imploded_system_permissions;

// Get module permissions
$modules = array();
$module_permissions = '';
$dirs = scan_current_dir(WB_PATH.'/modules');
if(is_array($admin->get_post('module_permissions')))
{
	foreach($admin->get_post('module_permissions') AS $selected_name)
	{
		if(in_array ($selected_name, $dirs['path'])  )
		{
			$modules[] = $selected_name;
		}
	}
}
$modules = (sizeof($modules) > 0) ? array_diff($dirs['path'], $modules) : $dirs['path'];
$module_permissions = implode(',', $modules);

// Get template permissions
$templates = array();
$template_permissions = '';
$dirs = scan_current_dir(WB_PATH.'/templates');
if(is_array($admin->get_post('template_permissions')))
{
	foreach($admin->get_post('template_permissions') AS $selected_name)
	{
		if(in_array ($selected_name, $dirs['path']) )
		{
			$templates[] = $selected_name;
		}
	}
}
$templates = (sizeof($templates) > 0) ? array_diff($dirs['path'], $templates) : $dirs['path'];
$template_permissions = implode(',', $templates);

?>