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



require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Access', 'groups');

// Create new template object for the modify/remove menu
$tpl = new Template(THEME_PATH.'/templates');
$tpl->set_file('page', 'groups.htt');
$tpl->set_block('page', 'main_block', 'main');
$tpl->set_block('main_block', 'manage_users_block', 'users');
// insert urls
$tpl->set_var(array(
	'ADMIN_URL' => ADMIN_URL,
	'WB_URL' => WB_URL,
	'WB_PATH' => WB_PATH,
	'THEME_URL' => THEME_URL
	)
);

// Get existing value from database
// $database = new database();
$query = "SELECT group_id,name FROM ".TABLE_PREFIX."groups WHERE group_id != '1' ORDER BY name";
$results = $database->query($query);
if($database->is_error()) {
	$admin->print_error($database->get_error(), 'index.php');
}

// Insert values into the modify/remove menu
$tpl->set_block('main_block', 'list_block', 'list');
if($results->numRows() > 0) {
	// Insert first value to say please select
	$tpl->set_var('VALUE', '');
	$tpl->set_var('NAME', $TEXT['PLEASE_SELECT'].'...');
	$tpl->parse('list', 'list_block', true);
	// Loop through groups
	while($group = $results->fetchRow()) {
		$tpl->set_var('VALUE', $group['group_id']);
		$tpl->set_var('NAME', $group['name']);
		$tpl->parse('list', 'list_block', true);
	}
} else {
	// Insert single value to say no groups were found
	$tpl->set_var('NAME', $TEXT['NONE_FOUND']);
	$tpl->parse('list', 'list_block', true);
}

// Insert permissions values
if($admin->get_permission('groups_add') != true) {
	$tpl->set_var('DISPLAY_ADD', 'hide');
}
if($admin->get_permission('groups_modify') != true) {
	$tpl->set_var('DISPLAY_MODIFY', 'hide');
}
if($admin->get_permission('groups_delete') != true) {
	$tpl->set_var('DISPLAY_DELETE', 'hide');
}

// Insert language headings
$tpl->set_var(array(
	'HEADING_MODIFY_DELETE_GROUP' => $HEADING['MODIFY_DELETE_GROUP'],
	'HEADING_ADD_GROUP' => $HEADING['ADD_GROUP']
	)
);
// Insert language text and messages
$tpl->set_var(array(
	'TEXT_MODIFY' => $TEXT['MODIFY'],
	'TEXT_DELETE' => $TEXT['DELETE'],
	'TEXT_MANAGE_USERS' => ( $admin->get_permission('users') == true ) ? $TEXT['MANAGE_USERS']: "",
	'CONFIRM_DELETE' => $MESSAGE['GROUPS_CONFIRM_DELETE']
	)
);
if ( $admin->get_permission('users') == true ) $tpl->parse("users", "manage_users_block", true);
// Parse template object
$tpl->parse('main', 'main_block', false);
$tpl->pparse('output', 'page');

// Setup template for add group form
$tpl = new Template(THEME_PATH.'/templates');
$tpl->set_file('page', 'groups_form.htt');
$tpl->set_block('page', 'main_block', 'main');
$tpl->set_var('DISPLAY_EXTRA', 'display:none;');
$tpl->set_var('ACTION_URL', ADMIN_URL.'/groups/add.php');
$tpl->set_var('SUBMIT_TITLE', $TEXT['ADD']);
$tpl->set_var('ADVANCED_ACTION', 'index.php');

// Tell the browser whether or not to show advanced options
if ( true == (isset( $_POST['advanced']) && ( strpos( $_POST['advanced'], ">>") > 0 ) ) )
{
	$tpl->set_var('DISPLAY_ADVANCED', '');
	$tpl->set_var('DISPLAY_BASIC', 'display:none;');
	$tpl->set_var('ADVANCED', 'yes');
	$tpl->set_var('ADVANCED_BUTTON', '<< '.$TEXT['HIDE_ADVANCED']);
} else {
	$tpl->set_var('DISPLAY_ADVANCED', 'display:none;');
	$tpl->set_var('DISPLAY_BASIC', '');
	$tpl->set_var('ADVANCED', 'no');
	$tpl->set_var('ADVANCED_BUTTON', $TEXT['SHOW_ADVANCED'].' >>');
}

// Insert permissions values
if($admin->get_permission('groups_add') != true)
{
	$tpl->set_var('DISPLAY_ADD', 'hide');
}

$tpl->set_var('ADMINTOOLS', $MENU['ADMINTOOLS']);

// Insert values into module list
$tpl->set_block('main_block', 'module_list_block', 'module_list');

$result = $database->query('SELECT * FROM `'.TABLE_PREFIX.'addons` WHERE `type` = "module" AND `function` = "page" ORDER BY `name`');
if($result->numRows() > 0)
{
	while($addon = $result->fetchRow( MYSQL_ASSOC ))
	{
		if(file_exists(WB_PATH.'/modules/'.$addon['directory'].'/info.php'))
		{
			$tpl->set_var('VALUE', $addon['directory']);
			$tpl->set_var('NAME', $addon['name']);
			$tpl->set_var('CHECKED', "");
			$tpl->set_var('JS_ADDITIOM', '');
			$tpl->parse('module_list', 'module_list_block', true);
        }
	}
}
/**
 *
 *
 */
$tpl->set_block('main_block', 'admintools_list_block', 'admintools_list');
$js_admin_tools_array = array();

$result = $database->query('SELECT * FROM `'.TABLE_PREFIX.'addons` WHERE `type` = "module" AND `function` = "tool" ORDER BY `name`');
if($result->numRows() > 0)
{
	while($addon = $result->fetchRow( MYSQL_ASSOC ))
	{
		if(file_exists(WB_PATH.'/modules/'.$addon['directory'].'/info.php'))
		{
			$tpl->set_var('VALUE', $addon['directory']);
			$tpl->set_var('NAME', "<span class='admin_tool'>".$addon['name']."</span>" );
			$tpl->set_var('CHECKED', "");
			$tpl->set_var('JS_ADDITIOM', 'onchange="check(this);"');
			$tpl->parse('admintools_list', 'admintools_list_block', true);
			
			$js_admin_tools_array[] = $addon['directory'];
        }
	}
}
$tpl->set_var("JS_ADMIN_TOOLS_ARRAY", "'m_".implode("','m_", $js_admin_tools_array)."'");

// Insert values into template list
$tpl->set_block('main_block', 'template_list_block', 'template_list');
$result = $database->query('SELECT * FROM `'.TABLE_PREFIX.'addons` WHERE `type` = "template" ORDER BY `name`');
if($result->numRows() > 0)
{
	while($addon = $result->fetchRow())
	{
		if(file_exists(WB_PATH.'/templates/'.$addon['directory'].'/info.php'))
		{
			$tpl->set_var('TEMPLATE_VALUE', $addon['directory']);
			$tpl->set_var('TEMPLATE_NAME', $addon['name']);
			$tpl->parse('template_list', 'template_list_block', true);
        }
	}
}

// Insert language text and messages
$tpl->set_var(array(
			'TEXT_RESET' => $TEXT['RESET'],
			'TEXT_ACTIVE' => $TEXT['ACTIVE'],
			'TEXT_DISABLED' => $TEXT['DISABLED'],
			'TEXT_PLEASE_SELECT' => $TEXT['PLEASE_SELECT'],
			'TEXT_USERNAME' => $TEXT['USERNAME'],
			'TEXT_PASSWORD' => $TEXT['PASSWORD'],
			'TEXT_RETYPE_PASSWORD' => $TEXT['RETYPE_PASSWORD'],
			'TEXT_DISPLAY_NAME' => $TEXT['DISPLAY_NAME'],
			'TEXT_EMAIL' => $TEXT['EMAIL'],
			'TEXT_GROUP' => $TEXT['GROUP'],
			'TEXT_SYSTEM_PERMISSIONS' => $TEXT['SYSTEM_PERMISSIONS'],
			'TEXT_MODULE_PERMISSIONS' => $TEXT['MODULE_PERMISSIONS'],
			'TEXT_TEMPLATE_PERMISSIONS' => $TEXT['TEMPLATE_PERMISSIONS'],
			'TEXT_NAME' => $TEXT['NAME'],
			'SECTION_PAGES' => $MENU['PAGES'],
			'SECTION_MEDIA' => $MENU['MEDIA'],
			'SECTION_MODULES' => $MENU['MODULES'],
			'SECTION_TEMPLATES' => $MENU['TEMPLATES'],
			'SECTION_SETTINGS' => $MENU['SETTINGS'],
			'SECTION_LANGUAGES' => $MENU['LANGUAGES'],
			'SECTION_USERS' => $MENU['USERS'],
			'SECTION_GROUPS' => $MENU['GROUPS'],
			'SECTION_ADMINTOOLS' => $MENU['ADMINTOOLS'],
			'TEXT_VIEW' => $TEXT['VIEW'],
			'TEXT_ADD' => $TEXT['ADD'],
			'TEXT_LEVEL' => $TEXT['LEVEL'],
			'TEXT_MODIFY' => $TEXT['MODIFY'],
			'TEXT_DELETE' => $TEXT['DELETE'],
			'TEXT_MODIFY_CONTENT' => $TEXT['MODIFY_CONTENT'],
			'TEXT_MODIFY_SETTINGS' => $TEXT['MODIFY_SETTINGS'],
			'HEADING_MODIFY_INTRO_PAGE' => $HEADING['MODIFY_INTRO_PAGE'],
			'TEXT_CREATE_FOLDER' => $TEXT['CREATE_FOLDER'],
			'TEXT_RENAME' => $TEXT['RENAME'],
			'TEXT_UPLOAD_FILES' => $TEXT['UPLOAD_FILES'],
			'TEXT_BASIC' => $TEXT['BASIC'],
			'TEXT_ADVANCED' => $TEXT['ADVANCED'],
			'CHANGING_PASSWORD' => $MESSAGE['USERS_CHANGING_PASSWORD'],
			'ADMIN_URL' => ADMIN_URL,
			'WB_URL' => WB_URL,
			'WB_PATH' => WB_PATH,
			'THEME_URL' => THEME_URL
			));

// Parse template for add group form
$tpl->parse('main', 'main_block', false);
$tpl->pparse('output', 'page');

// Print the admin footer
$admin->print_footer();

?>