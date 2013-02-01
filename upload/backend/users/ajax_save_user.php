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
if (defined('CAT_PATH'))
{
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
// end include class.secure.php

require_once( CAT_PATH . '/framework/class.admin.php' );

if ( isset( $_POST['addUser'] ) ) $admin	= new admin('Access', 'users_add', false);
else $admin	= new admin('Access', 'users_modify', false);

header('Content-type: application/json');

$addUser		= trim( $admin->get_post_escaped('addUser') );
$saveUser		= trim( $admin->get_post_escaped('saveUser') );

if(	(!$admin->get_permission('users_add') && $addUser != '' ) ||
	(!$admin->get_permission('users_modify') && $saveUser != '' ) )
{
	$action	= $addUser != '' ? 'add' : 'modify';
	$ajax	= array(
		'message'	=> $admin->lang->translate('You do not have the permission to {{action}} a user.', array( 'action' => $action ) ),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
include_once( CAT_PATH . '/framework/functions.php' );

// Gather details entered
$username_fieldname	= str_replace(array("[[", "]]"), '', htmlspecialchars($admin->get_post('username_fieldname'), ENT_QUOTES));
$username			= trim( $admin->get_post_escaped( $username_fieldname ) );
$display_name		= trim( str_replace(array( '[[', ']]'), '', htmlspecialchars( $admin->get_post('display_name'), ENT_QUOTES ) ) );

$user_id			= $admin->get_post_escaped('user_id');
$password			= $admin->get_post('password');
$password2			= $admin->get_post('password2');

$email				= $admin->get_post_escaped('email');
$home_folder		= $admin->get_post_escaped('home_folder');

$active				= $admin->get_post('active') != '' ? true : false;

$groups				= implode(",", $admin->get_post_escaped( 'groups' ) );

/**
 *	Check user_id
 *
 */
if ( ( $saveUser &&
		( !is_numeric($user_id) ||
		$user_id == 1 ||
		$user_id == '' ) ) ||
	( $addUser == '' && $saveUser == '' ) || 
	( $addUser != '' && $saveUser != '' ) ||
	$user_id == 'admin' )
		/**
		*	Is "admin" still the superuser? I guess, no!
		*
		*/
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('You sent an invalid value'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
if ( $groups == '' )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('No group was selected'),
		'check'		=> $groups_id,
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
if ( strlen( $username ) < 3 )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('The username you entered was too short'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
if ( !preg_match('/^[a-z]{1}[a-z0-9@\._-]{2,}$/i', $username) )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('Invalid chars for username found'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
if (	( $password != '' && strlen($password) < AUTH_MIN_PASS_LENGTH ) ||
		( $addUser != '' && strlen($password) < AUTH_MIN_PASS_LENGTH) )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('The password you entered was too short (Please use at least {{AUTH_MIN_PASS_LENGTH}} chars)' , array( 'AUTH_MIN_PASS_LENGTH' => AUTH_MIN_PASS_LENGTH )),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
if( $password != $password2 )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('The passwords you entered do not match'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
if( $email != '' )
{
	if($admin->validate_email($email) == false)
	{
		$ajax	= array(
			'message'	=> $admin->lang->translate('The email address you entered is invalid'),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}
} else {
		$ajax	= array(
			'message'	=> $admin->lang->translate('You must enter an email address'),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
}

$sql	 = "SELECT * FROM " . CAT_TABLE_PREFIX . "users WHERE username = '$username'";
$sql	.= $saveUser != '' ? "AND user_id != '$user_id'" : "";

$results	= $database->query($sql);

if(	( $results->numRows() > 0 && $addUser != '' ) || 
	( $results->numRows() > 0 && $saveUser != ''  ) )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('The username you entered is already in use'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$sql	 = "SELECT * FROM " . CAT_TABLE_PREFIX . "users WHERE email = '$username'";
$sql	.= $saveUser != '' ? "AND user_id != '$user_id'" : "";

$results	= $database->query($sql);

if(	( $results->numRows() > 0 && $addUser != '' ) || 
	( $results->numRows() > 0 && $saveUser != ''  ) )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('The email you entered is already in use'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$group_id	= $admin->get_post_escaped( 'groups' );
$group_id	= in_array('1', $group_id) && $addUser != '' ? $group_id = '1' : $group_id[0];

/**
 *	Inser the user-data into the database
 *
 */

$fields = array(
	'groups_id'			=> $groups,
	'active'			=> $active,
	'username'			=> $username,
	'display_name'		=> $display_name,
	'email'				=> $email,
	'home_folder'		=> $home_folder,

	'password'			=> $password != '' ? md5( $password ) : false,

	'group_id'			=> $addUser != '' ? $group_id : false,
	'timezone_string'	=> $addUser != '' ? DEFAULT_TIMEZONE_STRING : false,
	'language'			=> $addUser != '' ? DEFAULT_LANGUAGE : false
);

$fields		= array_map( 'mysql_real_escape_string', $fields );

$sql		= $addUser != '' ?
				'INSERT INTO ' . CAT_TABLE_PREFIX . 'users ' :
				'UPDATE ' .CAT_TABLE_PREFIX . 'users SET ';

$sql1		= '';
$sql2		= '';

foreach ( $fields as $index => $value )
{
	if ( $value != false )
	{
		if ( $addUser != '' )
		{
			$sql1 .= $index . ', ';
			$sql2 .= "'" . $value . "', ";
		}
		else {
			$sql1 .= "`" . $index . "` = '" . $value ."', ";
		}
	}
}

$sql1	= substr( $sql1, 0, -2 );
$sql2	= $sql2 == '' ? "WHERE `user_id` = '" . $user_id . "'" : substr( $sql2, 0, -2 );

$sql	.= $addUser != '' ?
			'( ' . $sql1 . ') VALUES (' . $sql2 . ')' :
			$sql1 . $sql2;

	
// Update the database
$database->query( $sql );

if( $database->is_error() )
{
	$ajax	= array(
		'message'	=> $database->get_error(),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
} else {
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

	$action	= $addUser != '' ? 'added' : 'saved';
	$ajax	= array(
		'message'				=> $admin->lang->translate('User {{action}} successfully', array( 'action' => $action ) ),
		'action'				=> $action,
		'username'				=> $username,
		'display_name'			=> $display_name,
		'username_fieldname'	=> $username_fieldname,
		'id'					=> $action == 'added' ? $database->get_one("SELECT LAST_INSERT_ID()") : $user_id,
		'success'				=> true
	);
	print json_encode( $ajax );
	exit();
}

exit();
?>