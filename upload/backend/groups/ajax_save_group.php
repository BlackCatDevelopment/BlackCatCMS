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

$backend = CAT_Backend::getInstance('Access', 'groups', false);
$users   = CAT_Users::getInstance();
$val     = CAT_Helper_Validate::getInstance();

header('Content-type: application/json');

$addGroup  = trim( $val->sanitizePost('addGroup',NULL,true) );
$saveGroup = trim( $val->sanitizePost('saveGroup',NULL,true) );

if (
       ( $addGroup  && ! $users->checkPermission('Access','groups_add')    )
    || ( $saveGroup && ! $users->checkPermission('Access','groups_modify') )
) {
	$action	= $addGroup != '' ? 'add' : 'modify';
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You do not have the permission to {{action}} a group.', array( 'action' => $action ) ),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// Gather details entered
$group_name = trim( $val->sanitizePost('name',NULL,true) );
$group_id   = $val->sanitizePost('group_id','numeric',true);

if(
       ( $saveGroup && ( !$group_id || $group_id == 1 || $group_id == '' ) )
    || ( $addGroup == '' && $saveGroup == '' )
    || ( $addGroup != '' && $saveGroup != '' )
) {
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You sent an invalid value'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// Check group_name
if( $group_name == '' )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('Group name is blank'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$sql	 = "SELECT * FROM `%sgroups` WHERE name = '%s'";
$sql	.= $saveGroup != '' ? "AND group_id != $group_id" : "";

$results = $backend->db()->query(sprintf(
    $sql,
    CAT_TABLE_PREFIX, $group_name
));

if(	( $results->numRows() > 0 && $addGroup  != '' ) ||
	( $results->numRows() > 0 && $saveGroup != ''  ) )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('Group name already exists'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// Get system and module permissions
require( CAT_ADMIN_PATH . '/groups/get_permissions.php' );

$sql = ( $addGroup != '' )
     ? "INSERT INTO `" . CAT_TABLE_PREFIX . "groups` (name,system_permissions,module_permissions,template_permissions) VALUES ('$group_name','$system_permissions','$module_permissions','$template_permissions')" :
			"UPDATE `" . CAT_TABLE_PREFIX . "groups` SET name = '$group_name', system_permissions = '$system_permissions', module_permissions = '$module_permissions', template_permissions = '$template_permissions' WHERE group_id = '$group_id'";

// Update the database
$backend->db()->query($sql);

if( $backend->db()->is_error() )
{
	$ajax	= array(
		'message'	=> $backend->db()->get_error(),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
else
{
	$action	= $addGroup != '' ? 'added' : 'saved';
	$ajax	= array(
		'message'	=> $backend->lang()->translate('Group {{action}} successfully', array( 'action' => $action ) ),
		'action'	=> $action,
		'name'		=> $group_name,
		'id'		=> $action == 'added' ? $backend->db()->get_one("SELECT LAST_INSERT_ID()") : $group_id,
		'success'	=> true
	);
	print json_encode( $ajax );
	exit();
}

exit();
?>