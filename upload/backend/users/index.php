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

$backend = CAT_Backend::getInstance('Access', 'users');
$users   = CAT_Users::getInstance();

// this will redirect to the login page if the permission is not set
$users->checkPermission('Access','users');

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
		$tpl_data['users'][$counter]['VALUE']			= $user['user_id'];
		$tpl_data['users'][$counter]['DISPLAY_NAME']	= $user['display_name'];
		$tpl_data['users'][$counter]['USER_NAME']		= $user['username'];
		$tpl_data['users'][$counter]['GROUPS']			= array();

		$users_groups									= preg_split('/,/',$user['groups_id']);
		foreach ( $users_groups as $group_id )
		{
			$tpl_data['users'][$counter]['GROUPS'][$group_id]		= true;
		}
		$tpl_data['users'][$counter]['EMAIL']			= $user['email'];
		$tpl_data['users'][$counter]['ACTIVE']			= $user['active'] == 1 ? true : false;
		$tpl_data['users'][$counter]['HOMEFOLDER']		= $user['home_folder'];

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
		$tpl_data['users'][$counter]['USERNAME_FIELDNAME']		= $username_fieldname;
		$counter++;
	}
}

// =========================== 
// ! Add permissions to Dwoo   
// =========================== 
$tpl_data['permissions']['USERS_ADD']		= ($users->checkPermission('access','users_add'))    ? true : false;
$tpl_data['permissions']['USERS_MODIFY']	= ($users->checkPermission('access','users_modify')) ? true : false;
$tpl_data['permissions']['USERS_DELETE']	= ($users->checkPermission('access','users_delete')) ? true : false;
$tpl_data['permissions']['GROUPS']			= ($users->checkPermission('access','groups'))       ? true : false;

if ( $tpl_data['permissions']['USERS_ADD'] == true )
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
	$tpl_data['USERNAME_FIELDNAME']		= $username_fieldname;
}

$tpl_data['USERNAME']						= isset($_SESSION['au']['username']) ? $_SESSION['au']['username'] : false;
$tpl_data['PASSWORD']						= isset($_SESSION['au']['password']) ? $_SESSION['au']['password'] : false;
$tpl_data['DISPLAY_NAME']					= isset($_SESSION['au']['display_name']) ? $_SESSION['au']['display_name'] : false;
$tpl_data['EMAIL']							= isset($_SESSION['au']['email']) ? $_SESSION['au']['email'] : false;
$tpl_data['HOME_FOLDERS']					= HOME_FOLDERS;
$tpl_data['INITIAL_PAGE']                 			= INITIAL_PAGE;
$tpl_data['NEWUSERHINT']
    = preg_split('/, /',
          $backend->lang()->translate(
              'Minimum length for user name: {{ name }} chars, Minimum length for Password: {{ password }} chars!',
              array('name'=>CAT_Registry::get('AUTH_MIN_LOGIN_LENGTH'),'password'=>CAT_Registry::get('AUTH_MIN_PASS_LENGTH'))
          )
      );

// ============================ 
// ! Add groups to $tpl_data   
// ============================ 
$tpl_data['groups']						= $users->get_groups();

// ====================================================================================== 
// ! Only allow the user to add a user to the Administrators group if he belongs to it   
// ====================================================================================== 
$tpl_data['is_admin']					= in_array(1, $users->get_groups_id()) ? true : false;

// Add media folders to home folder list
foreach ( directory_list(CAT_PATH.MEDIA_DIRECTORY) as $index => $name )
{
	$tpl_data['home_folders'][$index]['NAME']	= str_replace(CAT_PATH, '', $name);
	$tpl_data['home_folders'][$index]['FOLDER']	= str_replace(CAT_PATH.MEDIA_DIRECTORY, '', $name);
}

// initial page selection
$pages = CAT_Helper_Page::getPages();
$frontend_pages = array();
foreach($pages as $page)
    $frontend_pages[$page['menu_title']] = 'pages/modify.php?page_id='.$page['page_id'];

$tools = CAT_Helper_Addons::get_addons(NULL,'module','tool');
$admin_tools = array();
foreach($tools as $tool)
    $admin_tools[$tool['name']] = 'admintools/tool.php?tool='.$tool['directory'];

$tpl_data['backend_pages']                 = $backend->getPages();
$tpl_data['frontend_pages']                = $frontend_pages;
$tpl_data['admin_tools']                   = $admin_tools;
$tpl_data['init_page']                     = 'start/index.php';
$tpl_data['init_page_param']               = '';

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_users_index', $tpl_data);

// ====================== 
// ! Print admin footer   
// ====================== 
$backend->print_footer();

?>
