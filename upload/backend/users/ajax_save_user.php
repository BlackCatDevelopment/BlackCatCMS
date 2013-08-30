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

$val  = CAT_Helper_Validate::getInstance();
$perm = 'users_modify';

if ( $val->sanitizePost('addUser') )
    $perm = 'users_add';

$backend = CAT_Backend::getInstance('access',$perm,false);
$users   = CAT_Users::getInstance();

header('Content-type: application/json');

if ( ! $users->checkPermission('access',$perm) )
{
    $ajax = array(
		'message'	=> $backend->lang()->translate('You do not have the permission to {{action}} a user.', array( 'action' => str_replace('users','',$perm) ) ),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$addUser  = trim( $val->sanitizePost('addUser',NULL,true) );
$saveUser = trim( $val->sanitizePost('saveUser',NULL,true) );

include_once( CAT_PATH . '/framework/functions.php' );

// Gather details entered
$username_fieldname	= str_replace(array("[[", "]]"), '', htmlspecialchars($val->sanitizePost('username_fieldname'), ENT_QUOTES));
$username			= trim( $val->sanitizePost($username_fieldname,NULL,true) );
$display_name		= trim( str_replace(array( '[[', ']]'), '', htmlspecialchars( $val->sanitizePost('display_name'), ENT_QUOTES ) ) );

$user_id			= $val->sanitizePost('user_id',NULL,true);
$password			= $val->sanitizePost('password');
$password2			= $val->sanitizePost('password2');

$email				= $val->sanitizePost('email',NULL,true);
$home_folder		= $val->sanitizePost('home_folder',NULL,true);

$active				= $val->sanitizePost('active') != '' ? true : false;
$groups             = NULL;

if($val->sanitizePost('groups',NULL,true))
    $groups	= implode(",", $val->sanitizePost('groups',NULL,true));

/**
 *	Check user_id
 *
 */
if (
    (
           $saveUser
        && ( !is_numeric($user_id) || $user_id == 1 || $user_id == '' )
    )
    || ( $addUser == '' && $saveUser == '' )
    || ( $addUser != '' && $saveUser != '' )
    || $user_id == 'admin'
) {
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You sent an invalid value'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
if ( $groups == '' )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('No group was selected'),
		'check'		=> '',
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
if ( strlen( $username ) < 3 )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('The username you entered was too short'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
if ( ! $users->validateUsername($username) )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('Invalid chars for username found'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
if (	( $password != '' && strlen($password) < AUTH_MIN_PASS_LENGTH ) ||
		( $addUser  != '' && strlen($password) < AUTH_MIN_PASS_LENGTH) )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('The password you entered was too short (Please use at least {{AUTH_MIN_PASS_LENGTH}} chars)' , array( 'AUTH_MIN_PASS_LENGTH' => AUTH_MIN_PASS_LENGTH )),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
if( $password != $password2 )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('The passwords you entered do not match'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
if( $email != '' )
{
	if($val->validate_email($email) == false)
	{
		$ajax	= array(
			'message'	=> $backend->lang()->translate('The email address you entered is invalid'),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}
} else {
		$ajax	= array(
			'message'	=> $backend->lang()->translate('You must enter an email address'),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
}

if ( $addUser && $users->checkUsernameExists($username) )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('The username you entered is already in use'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

if ( $addUser && $users->checkEmailExists($email) )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('The email you entered is already in use'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$group_id = $val->sanitizePost('groups',NULL,true);
$group_id = ( is_array($group_id) && in_array('1', $group_id) && $addUser != '' )
          ? $group_id = '1'
          : $group_id[0];

// create new user
if ( $addUser )
{
    $users->createUser($group_id, $active, $username, md5( $password ), $display_name, $email );
}
else
{
    $options = array(
        'group_id'     => $group_id,
        'groups_id'    => $group_id,
        'active'       => $active,
        'username'     => $username,
        'display_name' => $display_name,
        'email'        => $email,
    );
    if($password)
        $options['password'] = md5($password);
    // extended
    $available = $users->getExtendedOptions();
    foreach( $available as $key => $method )
    {
        $value = $val->sanitizePost($key);
        if ( $value )
        {
            if ( $method )
            {
            }
        }
        $options[$key] = $value;
    }

    $errors = $users->setUserOptions($user_id,$options);
    if(count($errors))
    {
        $ajax	= array(
        	'message'	=> 'Errors:<br />'.implode('<br />',$errors),
        	'success'	=> false
        );
        print json_encode( $ajax );
        exit();
    }
}


if( $backend->db()->is_error() )
{
	$ajax	= array(
		'message'	=> $backend->db()->get_error(),
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
		'message'				=> $backend->lang()->translate('User {{action}} successfully', array( 'action' => $backend->lang()->translate($action) ) ),
		'action'				=> $action,
		'username'				=> $username,
		'display_name'			=> $display_name,
		'username_fieldname'	=> $username_fieldname,
		'id'					=> $action == 'added' ? $backend->db()->get_one("SELECT LAST_INSERT_ID()") : $user_id,
		'success'				=> true
	);
	print json_encode( $ajax );
	exit();
}

exit();
?>