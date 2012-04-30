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

require_once( LEPTON_PATH . '/framework/class.admin.php' );
$admin		= new admin('Pages', 'pages_delete');

$page_id	= $admin->get_post('page_id');

// Get page id
if ( $page_id == '' || !is_numeric( $page_id ) )
{
	header('Location: index.php');
	exit(0);
}

// Include the WB functions file
require_once( LEPTON_PATH . '/framework/functions.php' );

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
$results_array			= $results->fetchRow( MYSQL_ASSOC );
$old_admin_groups		= explode( ',', str_replace( '_', '', $results_array['admin_groups'] ) );
$old_admin_users		= explode( ',', str_replace( '_', '', $results_array['admin_users'] ) );

$in_old_group			= false;
foreach ( $admin->get_groups_id() as $cur_gid )
{
	if ( in_array( $cur_gid, $old_admin_groups ) )
	{
		$in_old_group	= true;
	}
}
if ( (!$in_old_group) && !is_numeric( array_search( $admin->get_user_id(), $old_admin_users ) ) )
{
	$admin->print_error('You do not have permissions to modify this page');
}

$visibility		= $results_array['visibility'];

if ( PAGE_TRASH )
{
	if ( $visibility == 'deleted' )
	{
		// Function to change all child pages visibility to deleted
		function restore_subs($parent = 0) {
			global $database;
			// Query pages
			$query_menu = $database->query("SELECT page_id FROM " . TABLE_PREFIX . "pages WHERE parent = '$parent' ORDER BY position ASC");
			// Check if there are any pages to show
			if($query_menu->numRows() > 0)
			{
				// Loop through pages
				while ( $page = $query_menu->fetchRow() )
				{
					// Update the page visibility to 'deleted'
					$database->query("UPDATE " . TABLE_PREFIX . "pages SET visibility = 'public' WHERE page_id = '" . $page['page_id'] . "' LIMIT 1");
					// Run this function again for all sub-pages
					restore_subs( $page['page_id'] );
				}
			}
		}
		// Update the page visibility to 'deleted'
		$database->query("UPDATE " . TABLE_PREFIX . "pages SET visibility = 'public' WHERE page_id = '$page_id.' LIMIT 1");
		// Run trash subs for this page
		restore_subs( $page_id );
	}
}

// Check if there is a db error, otherwise say successful
if ( $database->is_error() )
{
	$admin->print_error($database->get_error());
}
else
{
	$admin->print_success('Page restored successfully');
}

// Print admin footer
$admin->print_footer();

?>