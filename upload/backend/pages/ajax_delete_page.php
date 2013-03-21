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
 * @license			http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */
 
if (defined('CAT_PATH')) {
	if (defined('CAT_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
	$subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));	$dir = $_SERVER['DOCUMENT_ROOT'];
	$inc = false;
	foreach ($subs as $sub) {
		if (empty($sub)) continue; $dir .= '/'.$sub;
		if (file_exists($dir.'/framework/class.secure.php')) {
			include($dir.'/framework/class.secure.php'); $inc = true;	break;
	}
	}
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

require_once(CAT_PATH . '/framework/class.admin.php');
$admin = new admin('Pages', 'pages_delete', false);

$user  = CAT_Users::getInstance();
$val   = CAT_Helper_Validate::getInstance();

header('Content-type: application/json');

// Get perms
if ( ! $user->checkPermission('pages','pages_delete',false) )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('You do not have the permission to delete a page.'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// Include the LEPTON functions file
require_once(CAT_PATH . '/framework/functions.php');

$page_id        = $val->sanitizePost('page_id','numeric');

// Get page id
if ( $page_id == '' || !is_numeric($page_id) )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('You sent an invalid value'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

if ( !$admin->get_page_permission( $page_id, 'admin' ) )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('You do not have permissions to modify this page'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// Find out more about the page
$results	= $database->query("SELECT * FROM " . CAT_TABLE_PREFIX . "pages WHERE page_id = '$page_id'");
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

$results_array	= $results->fetchRow();
$visibility		= $results_array['visibility'];

// Check if we should delete it or just set the visibility to 'deleted'
if ( PAGE_TRASH != 'disabled' && $visibility != 'deleted' )
{
	$ajax_status	= 1;
	// Page trash is enabled and page has not yet been deleted
	// Function to change all child pages visibility to deleted
	function trash_subs($parent = 0)
	{
		global $database;
		// Query pages
		$query_menu	= $database->query("SELECT page_id FROM " . CAT_TABLE_PREFIX . "pages WHERE parent = '$parent' ORDER BY position ASC");
		// Check if there are any pages to show
		if($query_menu->numRows() > 0)
		{
			// Loop through pages
			while($page = $query_menu->fetchRow())
			{
				// Update the page visibility to 'deleted'
				$database->query("UPDATE " . CAT_TABLE_PREFIX . "pages SET visibility = 'deleted' WHERE page_id = '".$page['page_id']."' LIMIT 1");
				// Run this function again for all sub-pages
				trash_subs( $page['page_id'] );
			}
		}
	}
	
	// Update the page visibility to 'deleted'
	$database->query("UPDATE " . CAT_TABLE_PREFIX . "pages SET visibility = 'deleted' WHERE page_id = '$page_id.' LIMIT 1");
	
	// Run trash subs for this page
	trash_subs( $page_id );
} else {
	$ajax_status	= 0;
	// Really dump the page
	// Delete page subs
	$sub_pages = get_subs($page_id, array());
	foreach($sub_pages AS $sub_page_id)
	{
		delete_page( $sub_page_id );
	}
	// Delete page
	delete_page($page_id);
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
		'message'	=> $admin->lang->translate('Page deleted successfully'),
		'status'	=> $ajax_status,
		'success'	=> true
	);
	print json_encode( $ajax );
	exit();
}
exit();
?>