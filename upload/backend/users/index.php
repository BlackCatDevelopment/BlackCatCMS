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

require_once(LEPTON_PATH.'/framework/class.admin.php');
$admin = new admin('Access', 'users');

require_once(LEPTON_PATH.'/framework/functions.php');

// =========================================================================== 
// ! Create the controller, it is reusable and can render multiple templates 	
// =========================================================================== 
global $parser;

if (!is_object($parser))
{
	$admin->print_error('Global parser error couldn\'t be loaded!', false);
}

// ============================================= 
// ! Insert values into the modify/remove menu   
// ============================================= 
$results = $database->query("SELECT * FROM `".TABLE_PREFIX."users` WHERE `user_id` != '1' ORDER BY `display_name`,`username`");
if ( $database->is_error())
{
	$admin->print_error($database->get_error(), 'index.php');
}
if ( $results->numRows() > 0 )
{
	// ====================== 
	// ! Loop through users   
	// ====================== 
	$counter = 0;
	while (false != ($user = $results->fetchRow( MYSQL_ASSOC ) ) )
	{
		$data_dwoo['users'][$counter]['VALUE']			= $user['user_id'];
		$data_dwoo['users'][$counter]['DISPLAY_NAME']	= $user['display_name'];
		$data_dwoo['users'][$counter]['USER_NAME']		= $user['username'];
		$data_dwoo['users'][$counter]['GROUPS']			= array();

		$users_groups									= preg_split('/,/',$user['groups_id']);
		foreach ( $users_groups as $group_id )
		{
			$data_dwoo['users'][$counter]['GROUPS'][$group_id]		= true;
		}
		$data_dwoo['users'][$counter]['EMAIL']			= $user['email'];
		$data_dwoo['users'][$counter]['ACTIVE']			= $user['active'] == 1 ? true : false;
		$data_dwoo['users'][$counter]['HOMEFOLDER']		= $user['home_folder'];

		// ================================ 
		// ! Generate username field name   
		// ================================ 
		$username_fieldname			= 'username_';
		$salt						= "abcdefghijklmnopqrstuvwxyz0123456789ABCDEZ_+-";
		$salt_len					= strlen($salt) -1;
		$i = 0;
		while (++$i <= 7)
		{
			$num					 = mt_rand(0, $salt_len);
			$username_fieldname		.= $salt[ $num ];
		}
		$data_dwoo['users'][$counter]['USERNAME_FIELDNAME']		= $username_fieldname;
		$counter++;
	}
}

// =========================== 
// ! Add permissions to Dwoo   
// =========================== 
$data_dwoo['permissions']['USERS_ADD']		= ($admin->get_permission('users_add')) ? true : false;
$data_dwoo['permissions']['USERS_MODIFY']	= ($admin->get_permission('users_modify')) ? true : false;
$data_dwoo['permissions']['USERS_DELETE']	= ($admin->get_permission('users_delete')) ? true : false;
$data_dwoo['permissions']['GROUPS']			= ($admin->get_permission('groups')) ? true : false;

if ( $data_dwoo['permissions']['USERS_ADD'] == true )
{
	// ================================ 
	// ! Generate username field name   
	// ================================ 
	$username_fieldname			= 'username_';
	$salt						= "abcdefghijklmnopqrstuvwxyz0123456789ABCDEZ_+-";
	$salt_len					= strlen($salt) -1;
	$i = 0;
	while (++$i <= 7)
	{
		$num					 = mt_rand(0, $salt_len);
		$username_fieldname		.= $salt[ $num ];
	}
	$data_dwoo['USERNAME_FIELDNAME']		= $username_fieldname;
}

$data_dwoo['USERNAME']						= isset($_SESSION['au']['username']) ? $_SESSION['au']['username'] : false;
$data_dwoo['PASSWORD']						= isset($_SESSION['au']['password']) ? $_SESSION['au']['password'] : false;
$data_dwoo['DISPLAY_NAME']					= isset($_SESSION['au']['display_name']) ? $_SESSION['au']['display_name'] : false;
$data_dwoo['EMAIL']							= isset($_SESSION['au']['email']) ? $_SESSION['au']['email'] : false;
$data_dwoo['HOME_FOLDERS']					= HOME_FOLDERS;
$data_dwoo['NEWUSERHINT']					= preg_split('/, /',sprintf($TEXT['NEW_USER_HINT'], 3, AUTH_MIN_PASS_LENGTH));

// ============================ 
// ! Add groups to $data_dwoo   
// ============================ 
require_once(LEPTON_PATH . '/framework/class.pages.php');
$pages = new pages();
$data_dwoo['groups']						= $pages->get_groups();

// ====================================================================================== 
// ! Only allow the user to add a user to the Administrators group if he belongs to it   
// ====================================================================================== 
$data_dwoo['is_admin']						= in_array(1, $admin->get_groups_id()) ? true : false;

// Add media folders to home folder list
foreach ( directory_list(LEPTON_PATH.MEDIA_DIRECTORY) as $index => $name )
{
	$data_dwoo['home_folders'][$index]['NAME']		= str_replace(LEPTON_PATH, '', $name);
	$data_dwoo['home_folders'][$index]['FOLDER']	= str_replace(LEPTON_PATH.MEDIA_DIRECTORY, '', $name);
}

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_users_index.lte', $data_dwoo);

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>