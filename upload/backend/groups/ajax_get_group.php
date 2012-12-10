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
$admin = new admin('Access', 'groups', false);

header('Content-type: application/json');


if ( !$admin->get_permission('groups') )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('You do not have the permission to view groups'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$group_id		= $admin->get_post('id');
if ( !is_numeric($group_id ) )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('You sent an invalid value'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
$get_group		= $database->query("SELECT * FROM " . TABLE_PREFIX . "groups WHERE group_id = '$group_id'");

// ==============================================
// ! Insert admin group and current group first
// ==============================================
if ( $group = $get_group->fetchRow( MYSQL_ASSOC ) )
{
	$system_permissions			= explode( ',', $group['system_permissions']);
	$module_permissions			= explode( ',', $group['module_permissions']);
	$template_permissions		= explode( ',', $group['template_permissions']);

	$ajax	= array(
		'group_id'				=> $group['group_id'],
		'name'					=> $group['name'],
		'system_permissions'	=> $system_permissions,
		'module_permissions'	=> $module_permissions,
		'template_permissions'	=> $template_permissions,
		'message'				=> $admin->lang->translate( 'Group loaded successfully' ),
		'success'				=> true
	);
	print json_encode( $ajax );
	exit();
}
else {
	$ajax	= array(
		'message'	=> $admin->lang->translate('Group could not be found in database'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

exit();
?>