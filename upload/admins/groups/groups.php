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

// Create new database object
// $database = new database();

if(!isset($_POST['action']) || ($_POST['action'] != "modify" && $_POST['action'] != "delete")) {
	header("Location: index.php");
	exit(0);
}

// Set parameter 'action' as alternative to javascript mechanism
if(isset($_POST['modify']))
	$_POST['action'] = "modify";
if(isset($_POST['delete']))
	$_POST['action'] = "delete";

// Check if group group_id is a valid number and doesnt equal 1
if(!isset($_POST['group_id']) || !is_numeric($_POST['group_id']) || $_POST['group_id'] == 1) {
	header("Location: index.php");
	exit(0);
}

if($_POST['action'] == 'modify')
{
	// Create new admin object
	$admin = new admin('Access', 'groups_modify', false);
	// Print header
	$admin->print_header();
	// Get existing values
	$results = $database->query("SELECT * FROM ".TABLE_PREFIX."groups WHERE group_id = '".$_POST['group_id']."'");
	$group = $results->fetchRow();
	// Setup template object
	$tpl = new Template(THEME_PATH.'/templates');
	$tpl->set_file('page', 'groups_form.htt');
	$tpl->set_block('page', 'main_block', 'main');
	$tpl->set_var(	array(
		'ACTION_URL' => ADMIN_URL.'/groups/save.php',
		'SUBMIT_TITLE' => $TEXT['SAVE'],
		'GROUP_ID' => $group['group_id'],
		'GROUP_NAME' => $group['name'],
		'ADVANCED_ACTION' => 'groups.php'
		)
	);
	
	$tpl->set_var('USERNAME_INPUT_DISABLED', 'input_text_disabled');
	// Tell the browser whether or not to show advanced options
	if( true == (isset( $_POST['advanced']) && ( strpos( $_POST['advanced'], ">>") > 0 ) ) )
	{
		$tpl->set_var('DISPLAY_ADVANCED', '');
		$tpl->set_var('DISPLAY_BASIC', 'display:none;');
		$tpl->set_var('ADVANCED', 'yes');
		$tpl->set_var('ADVANCED_BUTTON', '&lt;&lt; '.$TEXT['HIDE_ADVANCED']);
	} else {
		$tpl->set_var('DISPLAY_ADVANCED', 'display:none;');
		$tpl->set_var('DISPLAY_BASIC', '');
		$tpl->set_var('ADVANCED', 'no');
		$tpl->set_var('ADVANCED_BUTTON', $TEXT['SHOW_ADVANCED'].'  &gt;&gt;');
	}

	// Explode system permissions
	$system_permissions = explode(',', $group['system_permissions']);
	// Check system permissions boxes
	foreach($system_permissions AS $name)
	{
			$tpl->set_var($name.'_checked', ' checked="checked"');
	}
	// Explode module permissions
	$module_permissions = explode(',', $group['module_permissions']);
	// Explode template permissions
	$template_permissions = explode(',', $group['template_permissions']);
	
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
				$tpl->set_var('JS_ADDITIOM', '');
				if(!is_numeric(array_search($addon['directory'], $module_permissions)))
				{
					$tpl->set_var('CHECKED', ' checked="checked"');
				} else {
 					$tpl->set_var('CHECKED', '');
				}
				$tpl->parse('module_list', 'module_list_block', true);
			}
		}
	}
	/**
	 *
	 *
	 *
	 */
	$tpl->set_var('VALUE', "");
	$tpl->set_var('NAME', "<p style='display:block; margin-top:10px;'>".$MENU['ADMINTOOLS'].":</p>" );
	$tpl->set_var('CHECKED', "style='display: none;'");
	$tpl->parse('module_list', 'module_list_block', true);
	
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
				$tpl->set_var('JS_ADDITIOM', 'onchange="check(this);"');
				if(!is_numeric(array_search($addon['directory'], $module_permissions)))
				{
					$tpl->set_var('CHECKED', ' checked="checked"');
				} else {
 					$tpl->set_var('CHECKED', '');
				}
				$tpl->parse('admintools_list', 'admintools_list_block', true);
				
				$js_admin_tools_array[] = $addon['directory'];
			}
		}
		$tpl->set_var("JS_ADMIN_TOOLS_ARRAY", "'m_".implode("','m_", $js_admin_tools_array)."'");
	}
	
	// Insert values into template list
	$tpl->set_block('main_block', 'template_list_block', 'template_list');
	$result = $database->query('SELECT * FROM `'.TABLE_PREFIX.'addons` WHERE `type` = "template" ORDER BY `name`');
	if($result->numRows() > 0) {
		while($addon = $result->fetchRow( MYSQL_ASSOC ))
		{
			if(file_exists(WB_PATH.'/templates/'.$addon['directory'].'/info.php'))
			{
				$tpl->set_var('TEMPLATE_VALUE', $addon['directory']);
				$tpl->set_var('TEMPLATE_NAME', $addon['name']);
				if(!is_numeric(array_search($addon['directory'], $template_permissions)))
				{
					$tpl->set_var('CHECKED', ' checked="checked"');
				} else {
 					$tpl->set_var('CHECKED', '');
				}
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
				'SECTION_LANGUAGES' => $MENU['LANGUAGES'],
				'SECTION_SETTINGS' => $MENU['SETTINGS'],
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
				'HEADING_MODIFY_GROUP' => $HEADING['MODIFY_GROUP']
				));

	// Parse template object
	$tpl->parse('main', 'main_block', false);
	$tpl->pparse('output', 'page');
} elseif($_POST['action'] == 'delete') {
	// Create new admin object
	$admin = new admin('Access', 'groups_delete', false);
	// Print header
	$admin->print_header();
	// Delete the group
	$database->query("DELETE FROM ".TABLE_PREFIX."groups WHERE group_id = '".$_POST['group_id']."' LIMIT 1");
	if($database->is_error())
	{
		$admin->print_error($database->get_error());
	} else {
		// Delete users in the group
		$database->query("DELETE FROM ".TABLE_PREFIX."users WHERE group_id = '".$_POST['group_id']."'");
		if($database->is_error()) {
			$admin->print_error($database->get_error());
		} else {
			$admin->print_success($MESSAGE['GROUPS_DELETED']);
		}
	}
}

// Print admin footer
$admin->print_footer();

?>