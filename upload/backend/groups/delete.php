<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @version         $Id$
 *
 */
 
// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
	include(WB_PATH.'/framework/class.secure.php');
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

require_once(WB_PATH.'/framework/class.admin.php');
$admin		= new admin('Access', 'groups_delete');

// Check if user id is a valid number and doesnt equal 1
if( !is_numeric($admin->get_get('group_id')) )
{
	$admin->print_error( $database->get_error($MESSAGE['GENERIC_SECURITY_ACCESS']) );
}
else
{
	$group_id	= $admin->get_get('group_id');
	// Delete the group
	$database->query("DELETE FROM `".TABLE_PREFIX."groups` WHERE `group_id` = '".$group_id."' LIMIT 1");
	if ( $database->is_error() )
	{
		$admin->print_error($database->get_error());
	}
	else
	{
		// Delete users in the group
		$database->query("DELETE FROM ".TABLE_PREFIX."users WHERE `group_id` = '".$group_id."'");
		if ( $database->is_error() )
		{
			$admin->print_error($database->get_error());
		}
		else
		{
			$admin->print_success($MESSAGE['GROUPS_DELETED']);
		}
	}
}
// Print admin footer
$admin->print_footer();

?>