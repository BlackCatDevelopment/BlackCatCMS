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

$backend = CAT_Backend::getInstance('Access','users',false,false);
$users   = CAT_Users::getInstance();
$val     = CAT_Helper_Validate::getInstance();

header('Content-type: application/json');

if ( !$users->checkPermission('access','users') )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You do not have the permission to view users'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$user_id		= $val->sanitizePost('id','numeric');
if ( !$user_id || $user_id == 1 )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You sent an invalid value'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$get_user		= $backend->db()->query("SELECT * FROM " . CAT_TABLE_PREFIX . "users WHERE user_id = '$user_id'");

// ==============================================
// ! Insert admin group and current group first
// ==============================================
if ( $user = $get_user->fetchRow( MYSQL_ASSOC ) )
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
    $page   = $users->get_initial_page($user_id,true);
	$ajax	= array(
		'user_id'				=> $user['user_id'],
		'username'				=> $user['username'],
		'display_name'			=> $user['display_name'],
		'groups'				=> explode( ',', $user['groups_id'] ),
		'email'					=> $user['email'],
		'active'				=> $user['active'] == 1 ? true : false,
		'home_folder'			=> $user['home_folder'],
		'username_fieldname'	=> $username_fieldname,
		'message'				=> $backend->lang()->translate( 'User loaded successfully' ),
		'success'				=> true,
        'initial_page'          => $page['init_page'],
        'initial_page_param'    => $page['init_page_param'],
	);
	print json_encode( $ajax );
	exit();
}
else {
	$ajax	= array(
		'message'	=> $backend->lang()->translate('User could not be found in database'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

exit();
?>