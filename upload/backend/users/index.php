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

$backend = CAT_Backend::getInstance('Access', 'users');
$users   = CAT_Users::getInstance();

// this will redirect to the login page if the permission is not set
$user->checkPermission('Access','users',false);

require_once(CAT_PATH.'/framework/functions.php');

global $parser;

// ============================================= 
// ! Insert values into the modify/remove menu   
// ============================================= 
$results = $backend->db()->query("SELECT * FROM `".CAT_TABLE_PREFIX."users` WHERE `user_id` != '1' ORDER BY `display_name`,`username`");
if ( $backend->db()->is_error())
{
	$backend->print_error($backend->db()->get_error(), 'index.php');
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
$data_dwoo['permissions']['USERS_ADD']		= ($users->checkPermission('access','users_add'))    ? true : false;
$data_dwoo['permissions']['USERS_MODIFY']	= ($users->checkPermission('access','users_modify')) ? true : false;
$data_dwoo['permissions']['USERS_DELETE']	= ($users->checkPermission('access','users_delete')) ? true : false;
$data_dwoo['permissions']['GROUPS']			= ($users->checkPermission('access','groups'))       ? true : false;

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
$data_dwoo['groups']						= $users->get_groups();

// ====================================================================================== 
// ! Only allow the user to add a user to the Administrators group if he belongs to it   
// ====================================================================================== 
$data_dwoo['is_admin']						= in_array(1, $users->get_groups_id()) ? true : false;

// Add media folders to home folder list
foreach ( directory_list(CAT_PATH.MEDIA_DIRECTORY) as $index => $name )
{
	$data_dwoo['home_folders'][$index]['NAME']		= str_replace(CAT_PATH, '', $name);
	$data_dwoo['home_folders'][$index]['FOLDER']	= str_replace(CAT_PATH.MEDIA_DIRECTORY, '', $name);
}

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_users_index', $data_dwoo);

// ====================== 
// ! Print admin footer   
// ====================== 
$backend->print_footer();

?>