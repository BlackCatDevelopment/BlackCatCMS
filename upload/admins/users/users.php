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

if(!isset($_POST['action']) OR ($_POST['action'] != "modify" AND $_POST['action'] != "delete")) {
	header("Location: index.php");
	exit(0);
}

// Set parameter 'action' as alternative to javascript mechanism
if(isset($_POST['modify']))
	$_POST['action'] = "modify";
if(isset($_POST['delete']))
	$_POST['action'] = "delete";

// Check if user id is a valid number and doesnt equal 1
if(!isset($_POST['user_id']) OR !is_numeric($_POST['user_id']) OR $_POST['user_id'] == 1) {
	header("Location: index.php");
	exit(0);
}

if($_POST['action'] == 'modify')
{
	// Print header
	$admin = new admin('Access', 'users_modify');
	// Get existing values
	$results = $database->query("SELECT * FROM `".TABLE_PREFIX."users` WHERE `user_id`= '".$_POST['user_id']."'");
	$user = $results->fetchRow( MYSQL_ASSOC );
	
	// Setup template object
	$template = new Template(THEME_PATH.'/templates');
	$template->set_file('page', 'users_form.htt');
	$template->set_block('page', 'main_block', 'main');
	$template->set_var(	array(
			'ACTION_URL' => ADMIN_URL.'/users/save.php',
			'SUBMIT_TITLE' => $TEXT['SAVE'],
			'USER_ID' => $user['user_id'],
			'USERNAME' => $user['username'],
			'DISPLAY_NAME' => $user['display_name'],
			'EMAIL' => $user['email'],
			'ADMIN_URL' => ADMIN_URL,
			'WB_URL' => WB_URL,
			'WB_PATH' => WB_PATH,
			'THEME_URL' => THEME_URL
			)
	);
	
	if($user['active'] == 1) {
		$template->set_var('ACTIVE_CHECKED', ' checked="checked"');
	} else {
		$template->set_var('DISABLED_CHECKED', ' checked="checked"');
	}
	
	// Add groups (except Administrators) to list
	$template->set_block('main_block', 'group_list_block', 'group_list');
	$results = $database->query("SELECT `group_id`,`name` FROM `".TABLE_PREFIX."groups` WHERE `group_id` != '1' ORDER BY `name`");
	if($results->numRows() > 0) {
		$template->set_var('ID', '');
		$template->set_var('NAME', $TEXT['PLEASE_SELECT'].'...');
		$template->set_var('SELECTED', '');
		$template->parse('group_list', 'group_list_block', true);
		while(false != ($group = $results->fetchRow( MYSQL_ASSOC ) ) ) {
			$template->set_var('ID', $group['group_id']);
			$template->set_var('NAME', $group['name']);
			
			$template->set_var(
				'SELECTED', 
				(in_array( $group['group_id'], explode(",",$user['groups_id'])))
				? ' selected="selected"'
				: ''
			);
			
			$template->parse('group_list', 'group_list_block', true);
		}
	}

	// Only allow the user to add a user to the Administrators group if they belong to it
	if(in_array(1, $admin->get_groups_id()))
    {
		// Add Administrators group
		$qr2 = $database->query("SELECT `group_id`,`name` FROM `".TABLE_PREFIX."groups` WHERE `group_id` = '1'");
		if ($qr2->numRows() > 0) {
			$group = $qr2->fetchRow(MYSQL_ASSOC);
			$template->set_var('ID', $group['group_id']);
			$template->set_var('NAME', $group['name']);
			$template->set_var(
				'SELECTED', 
				(in_array( $group['group_id'], explode(",",$user['groups_id'])))
				? ' selected="selected"'
				: ''
			);
			$template->parse('group_list', 'group_list_block', true);
		}
	} else {
		// just in case there is no (visible) membership at all
		if($results->numRows() == 0) {
			$template->set_var('ID', '');
			$template->set_var('NAME', $TEXT['NONE_FOUND']);
			$template->set_var('SELECTED', ' selected="selected"');
			$template->parse('group_list', 'group_list_block', true);
		}
	}

	/**
	 *	Generate username field name
	 *
	 */
	$username_fieldname = 'username_';
	$salt = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEZ_+-";
	$salt_len = strlen($salt) -1;
	$i = 0;
	while (++$i <= 7) {
		$num = mt_rand(0, $salt_len);
		$username_fieldname .= $salt[ $num ];
	}
	
	// Work-out if home folder should be shown
	if(!HOME_FOLDERS) {
		$template->set_var('DISPLAY_HOME_FOLDERS', 'display:none;');
	}
	
	// Include the WB functions file
	require_once(WB_PATH.'/framework/functions.php');
	
	// Add media folders to home folder list
	$template->set_block('main_block', 'folder_list_block', 'folder_list');
	foreach(directory_list(WB_PATH.MEDIA_DIRECTORY) AS $name)
    {
		$template->set_var('NAME', str_replace(WB_PATH, '', $name));
		
		$temp_name = str_replace(WB_PATH.MEDIA_DIRECTORY, '', $name);
		$template->set_var('FOLDER', $temp_name);
		
		$template->set_var(
			'SELECTED', 
			($user['home_folder'] == $temp_name)
			? ' selected="selected"'
			: ''
		);
		
		$template->parse('folder_list', 'folder_list_block', true);
	}
	
	// Insert text and messages
	$template->set_var(array(
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
			'TEXT_NONE' => $TEXT['NONE'],
			'TEXT_HOME_FOLDER' => $TEXT['HOME_FOLDER'],
			'USERNAME_FIELDNAME' => $username_fieldname,
			'CHANGING_PASSWORD' => $MESSAGE['USERS_CHANGING_PASSWORD'],
			'HEADING_MODIFY_USER' => $HEADING['MODIFY_USER']
			)
	);
	
	// Parse template object
	$template->parse('main', 'main_block', false);
	$template->pparse('output', 'page');
} elseif($_POST['action'] == 'delete') {
	// Print header
	$admin = new admin('Access', 'users_delete');
	// Delete the user
	$database->query("DELETE FROM `".TABLE_PREFIX."users` WHERE `user_id`= '".$_POST['user_id']."' LIMIT 1");
	if($database->is_error()) {
		$admin->print_error($database->get_error());
	} else {
		$admin->print_success($MESSAGE['USERS_DELETED']);
	}
}

// Print admin footer
$admin->print_footer();

?>