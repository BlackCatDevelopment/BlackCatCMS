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
if (defined('CAT_PATH')) {
	include(CAT_PATH.'/framework/class.secure.php');
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

require_once( CAT_PATH . '/framework/class.admin.php' );

$admin	= new admin('Access', 'users_delete', false);
header('Content-type: application/json');

if ( !$admin->get_permission('users_delete') )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('You do not have the permission to delete a user' ),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$user_id		= trim( $admin->get_post('id') );

// Check if user id is a valid number and doesnt equal 1
if( !is_numeric( $user_id ) || $user_id == '' )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('You sent an invalid value'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
else
{
	// Delete the group
	$database->query( "DELETE FROM `" . CAT_TABLE_PREFIX . "users` WHERE `user_id` = '" . $user_id . "' LIMIT 1" );
	if ( $database->is_error() )
	{
		$ajax	= array(
			'message'	=> $database->get_error(),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}
	else
	{
		// Delete users in the group
		$database->query( "DELETE FROM " . CAT_TABLE_PREFIX . "users WHERE `user_id` = '" . $user_id . "'" );
		if ( $database->is_error() )
		{
			$ajax	= array(
				'message'	=> $database->get_error(),
				'success'	=> false
			);
			print json_encode( $ajax );
			exit();
		}
		else
		{
			$ajax	= array(
				'message'	=> $admin->lang->translate('User deleted successfully'),
				'success'	=> true
			);
			print json_encode( $ajax );
			exit();
		}
	}
}
exit();
?>