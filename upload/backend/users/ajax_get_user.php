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
$admin = new admin('Access', 'users', false);

header('Content-type: application/json');


if ( !$admin->get_permission('users') )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('You do not have the permission to view users'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$user_id		= $admin->get_post('id');
if ( !is_numeric($user_id ) || $user_id == 1 )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('You sent an invalid value'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
$get_user		= $database->query("SELECT * FROM " . TABLE_PREFIX . "users WHERE user_id = '$user_id'");

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

	$ajax	= array(
		'user_id'				=> $user['user_id'],
		'username'				=> $user['username'],
		'display_name'			=> $user['display_name'],
		'groups'				=> explode( ',', $user['groups_id'] ),
		'email'					=> $user['email'],
		'active'				=> $user['active'] == 1 ? true : false,
		'home_folder'			=> $user['home_folder'],
		'username_fieldname'	=> $username_fieldname,
		'message'				=> $admin->lang->translate( 'User loaded successfully' ),
		'success'				=> true
	);
	print json_encode( $ajax );
	exit();
}
else {
	$ajax	= array(
		'message'	=> $admin->lang->translate('User could not be found in database'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

exit();
?>