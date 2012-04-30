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
$admin = new admin('Access', 'users');

// Create new template object for the modify/remove menu
$template = new Template(THEME_PATH.'/templates');
$template->set_file('page', 'users.htt');
$template->set_block('page', 'main_block', 'main');
$template->set_block("main_block", "manage_groups_block", "groups");
$template->set_var('ADMIN_URL', ADMIN_URL);

$query = "SELECT `user_id`,`username`,`display_name` FROM `".TABLE_PREFIX."users` WHERE `user_id` != '1' ORDER BY `display_name`,`username`";
$results = $database->query($query);
if($database->is_error()) {
	$admin->print_error($database->get_error(), 'index.php');
}

// Insert values into the modify/remove menu
$template->set_block('main_block', 'list_block', 'list');
if($results->numRows() > 0) {
	// Insert first value to say please select
	$template->set_var('VALUE', '');
	$template->set_var('NAME', $TEXT['PLEASE_SELECT'].'...');
	$template->parse('list', 'list_block', true);
	// Loop through users
	while(false != ($user = $results->fetchRow( MYSQL_ASSOC ) ) ) {
		$template->set_var('VALUE', $user['user_id']);
		$template->set_var('NAME', $user['display_name'].' ('.$user['username'].')');
		$template->parse('list', 'list_block', true);
	}
} else {
	// Insert single value to say no users were found
	$template->set_var('NAME', $TEXT['NONE_FOUND']);
	$template->parse('list', 'list_block', true);
}

// Insert permissions values
if($admin->get_permission('users_add') != true) {
	$template->set_var('DISPLAY_ADD', 'hide');
}
if($admin->get_permission('users_modify') != true) {
	$template->set_var('DISPLAY_MODIFY', 'hide');
}
if($admin->get_permission('users_delete') != true) {
	$template->set_var('DISPLAY_DELETE', 'hide');
}

// Insert language headings
$template->set_var(array(
	'HEADING_MODIFY_DELETE_USER' => $HEADING['MODIFY_DELETE_USER'],
	'HEADING_ADD_USER' => $HEADING['ADD_USER']
	)
);
// insert urls
$template->set_var(array(
	'ADMIN_URL' => ADMIN_URL,
	'WB_URL' => WB_URL,
	'WB_PATH' => WB_PATH,
	'THEME_URL' => THEME_URL
	)
);
// Insert language text and messages
$template->set_var(array(
	'TEXT_MODIFY' => $TEXT['MODIFY'],
	'TEXT_DELETE' => $TEXT['DELETE'],
	'TEXT_MANAGE_GROUPS' => ( $admin->get_permission('groups') == true ) ? $TEXT['MANAGE_GROUPS'] : "**",
	'CONFIRM_DELETE' => $MESSAGE['USERS_CONFIRM_DELETE']
	)
);
if ( $admin->get_permission('groups') == true ) $template->parse("groups", "manage_groups_block", true);
// Parse template object
$template->parse('main', 'main_block', false);
$template->pparse('output', 'page');

// Setup template for add user form
$template = new Template(THEME_PATH.'/templates');
//$template->set_unknowns('keep');   // DEBUG only
$template->set_file('page', 'users_form.htt');
$template->set_block('page', 'main_block', 'main');
$template->set_var('DISPLAY_EXTRA', 'display:none;');
$template->set_var('ACTIVE_CHECKED', ' checked="checked"');
$template->set_var('ACTION_URL', ADMIN_URL.'/users/add.php');
$template->set_var('SUBMIT_TITLE', $TEXT['ADD']);

$username = (isset($_SESSION['au']['username'])) ? $_SESSION['au']['username'] : '';
$password = (isset($_SESSION['au']['password'])) ? $_SESSION['au']['password'] : '';
$display_name = (isset($_SESSION['au']['display_name'])) ? $_SESSION['au']['display_name'] : '';
$email = (isset($_SESSION['au']['email'])) ? $_SESSION['au']['email'] : '';

$template->set_var('USERNAME', $username);
$template->set_var('PASSWORD', $password);
$template->set_var('DISPLAY_NAME', $display_name);
$template->set_var('EMAIL', $email);
$template->set_var('NEWUSERHINT', sprintf($TEXT['NEW_USER_HINT'], AUTH_MIN_LOGIN_LENGTH, AUTH_MIN_PASS_LENGTH));


// insert urls
$template->set_var(array(
	'ADMIN_URL' => ADMIN_URL,
	'WB_URL' => WB_URL,
	'WB_PATH' => WB_PATH,
	'THEME_URL' => THEME_URL
	)
);

// Add groups to list
$template->set_block('main_block', 'group_list_block', 'group_list');
$results = $database->query("SELECT group_id, name FROM ".TABLE_PREFIX."groups WHERE group_id != '1'");
if($results->numRows() > 0)
{
	$users_groups = array();
	$got_users = true;
	$template->set_var('ID', '');
	$template->set_var('NAME', $TEXT['PLEASE_SELECT'].'...');
	$template->set_var('SELECTED', ' selected="selected"');
	$template->parse('group_list', 'group_list_block', true);
	while(false != ($group = $results->fetchRow( MYSQL_ASSOC ) ) )
	{
		$template->set_var('ID', $group['group_id']);
		$template->set_var('NAME', $group['name']);
		$template->set_var('SELECTED', '');
		$template->parse('group_list', 'group_list_block', true);
	}
} else {
	$got_users = false;
}

// Only allow the user to add a user to the Administrators group if they belong to it
if(in_array(1, $admin->get_groups_id())) {

	$users_groups = $admin->get_groups_name();
	$template->set_var('ID', '1');

	if(is_array($users_groups)) {
		$template->set_var('NAME', $TEXT[ strtoupper($users_groups[1]) ] );
	} else {
		$template->set_var('NAME', $users_groups);
	}

	$template->set_var('SELECTED', (true === $got_users ? '' : ' selected="selected"') );
	$template->parse('group_list', 'group_list_block', true);
} else {
	if($results->numRows() == 0) {
		$template->set_var('ID', '');
		$template->set_var('NAME', $TEXT['NONE_FOUND']);
		$template->parse('group_list', 'group_list_block', true);
	}
}

// Insert permissions values
if($admin->get_permission('users_add') != true)
{
	$template->set_var('DISPLAY_ADD', 'hide');
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
if(!HOME_FOLDERS)
{
	$template->set_var('DISPLAY_HOME_FOLDERS', 'display:none;');
}

// Include the WB functions file
require_once(WB_PATH.'/framework/functions.php');

// Add media folders to home folder list
$template->set_block('main_block', 'folder_list_block', 'folder_list');
foreach(directory_list(WB_PATH.MEDIA_DIRECTORY) AS $name)
{
	$template->set_var('NAME', str_replace(WB_PATH, '', $name));
	$template->set_var('FOLDER', str_replace(WB_PATH.MEDIA_DIRECTORY, '', $name));
	$template->set_var('SELECTED', ' ');
	$template->parse('folder_list', 'folder_list_block', true);
}

// Insert language text and messages
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
		'CHANGING_PASSWORD' => $MESSAGE['USERS_CHANGING_PASSWORD']
		)
);

// Parse template for add user form
$template->parse('main', 'main_block', false);
$template->pparse('output', 'page');

$admin->print_footer();

?>