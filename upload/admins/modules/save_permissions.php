<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
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
$admin = new admin('Addons', 'modules_install');

// Include the WB functions file
require_once(WB_PATH.'/framework/functions.php');

// get marked groups
if ( isset( $_POST['group_id'] ) ) {
    foreach ( $_POST['group_id'] as $gid ) {
        $allowed_groups[$gid] = $gid;
    }
}
else {
// no groups marked, so don't allow any group
    $allowed_groups = array();
}

// get all known groups
$groups = array();
$stmt = $database->query( 'SELECT * FROM '.TABLE_PREFIX.'groups WHERE group_id <> 1' );
if ( $stmt->numRows() > 0 ) {
    while( $row = $stmt->fetchRow(MYSQL_ASSOC) ) {
        $groups[ $row['group_id'] ] = $row;
        $gid = $row['group_id'];
        // add newly installed module to any group that's NOT in the $allowed_groups array
        if ( ! array_key_exists( $gid, $allowed_groups ) ) {
            // get current value
            $modules = explode(',', $groups[$gid]['module_permissions'] );
            // add newly installed module
            $modules[] = $_POST['module'];
            $modules = array_unique($modules);
            asort($modules);
            // Update the database
            $module_permissions = implode(',', $modules);
            $query = "UPDATE ".TABLE_PREFIX."groups SET module_permissions='$module_permissions' WHERE group_id='$gid';";
            $database->query($query);
            if($database->is_error()) {
              	$admin->print_error($database->get_error());
            }
        }
    }
}


$admin->print_success($MESSAGE['GENERIC_INSTALLED']);

// Print admin footer
$admin->print_footer();


?>