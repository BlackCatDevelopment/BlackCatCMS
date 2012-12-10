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
if (defined('LEPTON_PATH'))
{
	include(LEPTON_PATH.'/framework/class.secure.php'); 
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

require_once( LEPTON_PATH . '/framework/class.admin.php' );

if ( isset( $_POST['addGroup'] ) ) $admin	= new admin('Access', 'groups_add', false);
else $admin	= new admin('Access', 'groups_modify', false);
header('Content-type: application/json');

$addGroup		= trim( $admin->get_post_escaped('addGroup') );
$saveGroup		= trim( $admin->get_post_escaped('saveGroup') );

if(	(!$admin->get_permission('groups_add') && $addGroup != '' ) ||
	(!$admin->get_permission('groups_modify') && $saveGroup != '' ) )
{
	$action	= $addGroup != '' ? 'add' : 'modify';
	$ajax	= array(
		'message'	=> $admin->lang->translate('You do not have the permission to {{action}} a group.', array( 'action' => $action ) ),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
include_once( LEPTON_PATH . '/framework/functions.php' );

// Gather details entered
$group_name		= trim( $admin->get_post_escaped('name') );
$group_id		= $admin->get_post_escaped('group_id');

if( ( $saveGroup &&
		( !is_numeric($group_id) ||
		$group_id == 1 ||
		$group_id == '' ) ) ||
	( $addGroup == '' && $saveGroup == '' ) || 
	( $addGroup != '' && $saveGroup != '' ) )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('You sent an invalid value'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// Check group_name
if( $group_name == '' )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('Group name is blank'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$sql	 = "SELECT * FROM " . TABLE_PREFIX . "groups WHERE name = '$group_name'";
$sql	.= $saveGroup != '' ? "AND group_id != '$group_id'" : "";

$results	= $database->query($sql);

if(	( $results->numRows() > 0 && $addGroup != '' ) || 
	( $results->numRows() > 0 && $saveGroup != ''  ) )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('Group name already exists'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// Get system and module permissions
require( ADMIN_PATH . '/groups/get_permissions.php' );

$sql	= $addGroup != '' ? 
			"INSERT INTO " . TABLE_PREFIX . "groups (name,system_permissions,module_permissions,template_permissions) VALUES ('$group_name','$system_permissions','$module_permissions','$template_permissions')" :
			"UPDATE " . TABLE_PREFIX . "groups SET name = '$group_name', system_permissions = '$system_permissions', module_permissions = '$module_permissions', template_permissions = '$template_permissions' WHERE group_id = '$group_id'";

// Update the database
$database->query($sql);

if( $database->is_error() )
{
	$ajax	= array(
		'message'	=> $database->get_error(),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
} else {
	$action	= $addGroup != '' ? 'added' : 'saved';
	$ajax	= array(
		'message'	=> $admin->lang->translate('Group {{action}} successfully', array( 'action' => $action ) ),
		'action'	=> $action,
		'name'		=> $group_name,
		'id'		=> $action == 'added' ? $database->get_one("SELECT LAST_INSERT_ID()") : $group_id,
		'success'	=> true
	);
	print json_encode( $ajax );
	exit();
}

exit();
?>