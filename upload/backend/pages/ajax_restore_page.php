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

require_once( CAT_PATH . '/framework/class.admin.php' );
$admin		= new admin('Pages', 'pages_delete', false);

// Set header for json
header('Content-type: application/json');

$page_id	= $admin->get_post('page_id');

// Get page id
if ( $page_id == '' || !is_numeric( $page_id ) )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('You send invalid data'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// Include the WB functions file
require_once( CAT_PATH . '/framework/functions.php' );

// Find out more about the page
$results = $database->query("SELECT * FROM " . CAT_TABLE_PREFIX . "pages WHERE page_id = '$page_id'");
if ( $database->is_error() )
{
	$ajax	= array(
		'message'	=> $database->get_error(),
		'success'	=> false
	);

	print json_encode( $ajax );
	exit();
}
if ( $results->numRows() == 0 )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('Page not found'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
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
	$ajax	= array(
		'message'	=> $admin->lang->translate('You do not have the permission to modify this page'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
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
			$query_menu = $database->query("SELECT page_id FROM " . CAT_TABLE_PREFIX . "pages WHERE parent = '$parent' ORDER BY position ASC");
			// Check if there are any pages to show
			if($query_menu->numRows() > 0)
			{
				// Loop through pages
				while ( $page = $query_menu->fetchRow() )
				{
					// Update the page visibility to 'deleted'
					$database->query("UPDATE " . CAT_TABLE_PREFIX . "pages SET visibility = 'public' WHERE page_id = '" . $page['page_id'] . "' LIMIT 1");
					// Run this function again for all sub-pages
					restore_subs( $page['page_id'] );
				}
			}
		}
		// Update the page visibility to 'deleted'
		$database->query("UPDATE " . CAT_TABLE_PREFIX . "pages SET visibility = 'public' WHERE page_id = '$page_id.' LIMIT 1");
		// Run trash subs for this page
		restore_subs( $page_id );
	}
}

// Check if there is a db error, otherwise say successful
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
		'message'	=> $admin->lang->translate('Page restored successfully'),
		'success'	=> true
	);
	print json_encode( $ajax );
	exit();
}
exit();
?>