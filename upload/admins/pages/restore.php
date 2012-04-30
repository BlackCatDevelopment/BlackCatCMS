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



// Get page id
if(!isset($_GET['page_id']) OR !is_numeric($_GET['page_id'])) {
	header("Location: index.php");
	exit(0);
} else {
	$page_id = $_GET['page_id'];
}

require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Pages', 'pages_delete');

// Include the WB functions file
require_once(WB_PATH.'/framework/functions.php');

// Get perms
$results = $database->query("SELECT admin_groups,admin_users FROM ".TABLE_PREFIX."pages WHERE page_id = '$page_id'");
$results_array = $results->fetchRow();

// Find out more about the page
$query = "SELECT * FROM ".TABLE_PREFIX."pages WHERE page_id = '$page_id'";
$results = $database->query($query);
if($database->is_error()) {
	$admin->print_error($database->get_error());
}
if($results->numRows() == 0) {
	$admin->print_error($MESSAGE['PAGES_NOT_FOUND']);
}
$results_array = $results->fetchRow();
$old_admin_groups = explode(',', str_replace('_', '', $results_array['admin_groups']));
$old_admin_users = explode(',', str_replace('_', '', $results_array['admin_users']));

$in_old_group = FALSE;
foreach($admin->get_groups_id() as $cur_gid){
    if (in_array($cur_gid, $old_admin_groups)) {
	$in_old_group = TRUE;
    }
}
if((!$in_old_group) AND !is_numeric(array_search($admin->get_user_id(), $old_admin_users))) {
	$admin->print_error($MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
}

$visibility = $results_array['visibility'];

if(PAGE_TRASH) {
	if($visibility == 'deleted') {	
		// Function to change all child pages visibility to deleted
		function restore_subs($parent = 0) {
			global $database;
			// Query pages
			$query_menu = $database->query("SELECT page_id FROM ".TABLE_PREFIX."pages WHERE parent = '$parent' ORDER BY position ASC");
			// Check if there are any pages to show
			if($query_menu->numRows() > 0) {
				// Loop through pages
				while($page = $query_menu->fetchRow()) {
					// Update the page visibility to 'deleted'
					$database->query("UPDATE ".TABLE_PREFIX."pages SET visibility = 'public' WHERE page_id = '".$page['page_id']."' LIMIT 1");
					// Run this function again for all sub-pages
					restore_subs($page['page_id']);
				}
			}
		}
		
		// Update the page visibility to 'deleted'
		$database->query("UPDATE ".TABLE_PREFIX."pages SET visibility = 'public' WHERE page_id = '$page_id.' LIMIT 1");
		
		// Run trash subs for this page
		restore_subs($page_id);
		
	}
}

// Check if there is a db error, otherwise say successful
if($database->is_error()) {
	$admin->print_error($database->get_error());
} else {
	$admin->print_success($MESSAGE['PAGES_RESTORED']);
}

// Print admin footer
$admin->print_footer();

?>