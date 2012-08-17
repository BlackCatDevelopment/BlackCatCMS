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
 *
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

require_once(LEPTON_PATH . '/framework/class.admin.php');
$admin = new admin('Pages', 'pages_delete');

$page_id		= $admin->get_get('page_id');

// Get page id
if ( $page_id == '' || !is_numeric($page_id) )
{
	header("Location: index.php");
	exit(0);
}

// Include the WB functions file
require_once(LEPTON_PATH . '/framework/functions.php');

// Get perms
if ( !$admin->get_page_permission($page_id,'admin') )
{
	$admin->print_error('You do not have permissions to modify this page');
}

// Find out more about the page
$results = $database->query("SELECT * FROM " . TABLE_PREFIX . "pages WHERE page_id = '$page_id'");
if ( $database->is_error() )
{
	$admin->print_error($database->get_error());
}
if ( $results->numRows() == 0 )
{
	$admin->print_error('Page not found');
}

$results_array = $results->fetchRow();

$visibility = $results_array['visibility'];

// Check if we should delete it or just set the visibility to 'deleted'
if ( PAGE_TRASH != 'disabled' AND $visibility != 'deleted')
{
	// Page trash is enabled and page has not yet been deleted
	// Function to change all child pages visibility to deleted
	function trash_subs($parent = 0) {
		global $database;
		// Query pages
		$query_menu = $database->query("SELECT page_id FROM " . TABLE_PREFIX . "pages WHERE parent = '$parent' ORDER BY position ASC");
		// Check if there are any pages to show
		if($query_menu->numRows() > 0) {
			// Loop through pages
			while($page = $query_menu->fetchRow()) {
				// Update the page visibility to 'deleted'
				$database->query("UPDATE " . TABLE_PREFIX . "pages SET visibility = 'deleted' WHERE page_id = '".$page['page_id']."' LIMIT 1");
				// Run this function again for all sub-pages
				trash_subs($page['page_id']);
			}
		}
	}
	
	// Update the page visibility to 'deleted'
	$database->query("UPDATE " . TABLE_PREFIX . "pages SET visibility = 'deleted' WHERE page_id = '$page_id.' LIMIT 1");
	
	// Run trash subs for this page
	trash_subs($page_id);
} else {
	// Really dump the page
	// Delete page subs
	$sub_pages = get_subs($page_id, array());
	foreach($sub_pages AS $sub_page_id) {
		delete_page($sub_page_id);
	}
	// Delete page
	delete_page($page_id);
}	

// Check if there is a db error, otherwise say successful
if ( $database->is_error() )
{
	$admin->print_error($database->get_error());
}
else
{
	$admin->print_success('Page deleted successfully');
}

?>