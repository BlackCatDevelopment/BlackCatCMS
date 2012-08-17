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

require_once( LEPTON_PATH . '/framework/class.admin.php' );
$admin = new admin('Pages', 'pages_settings');

// Get page id
$page_id		= $admin->get_post('page_id');

// Check page_id
if ( $page_id == '' || !is_numeric($page_id) )
{
	header("Location: index.php");
	exit(0);
}

if ( !$admin->get_permission('pages_settings') )
{
	$admin->print_error('You do not have permissions to modify this page');
}

// Include the WB functions file
require_once( LEPTON_PATH . '/framework/functions.php' );

// Get values
$page_title			= htmlspecialchars($admin->get_post_escaped('page_title') );
$menu_title			= htmlspecialchars($admin->get_post_escaped('menu_title') );
$page_link			= htmlspecialchars($admin->get_post_escaped('page_link') );
$description		= htmlspecialchars($admin->get_post_escaped('description') );
$keywords			= htmlspecialchars($admin->get_post_escaped('keywords') );
$parent				= $admin->get_post_escaped('parent');
$target				= $admin->get_post_escaped('target');
$template			= $admin->get_post_escaped('template');
$menu				= $admin->get_post_escaped('menu');
$language			= $admin->get_post_escaped('language');
$visibility			= $admin->get_post_escaped('visibility');
$searching			= $admin->get_post('searching') ? '1' : '0';
$admin_groups		= $admin->get_post_escaped('admin_groups');
$viewing_groups		= $admin->get_post_escaped('viewing_groups');
$request			= $admin->get_post_escaped('request_from');


// Validate data
if ( $page_title == '' || substr($page_title,0,1)=='.' )
{
	$admin->print_error('Please enter a page title');
}
if ( $menu_title == '' || substr($menu_title,0,1)=='.' )
{
	$admin->print_error('Please enter a menu title');
}


// Get existing perms
$results			= $database->query('SELECT `parent`, `link`, `position`, `admin_groups`, `admin_users` FROM `' . TABLE_PREFIX . 'pages` WHERE `page_id`=' . $page_id);
$results_array		= $results->fetchRow();

$old_parent			= $results_array['parent'];
$old_link			= $results_array['link'];
$old_position		= $results_array['position'];
$old_admin_groups	= explode(',', str_replace('_', '', $results_array['admin_groups']));
$old_admin_users	= explode(',', str_replace('_', '', $results_array['admin_users']));


$in_old_group		= false;
foreach ( $admin->get_groups_id() as $cur_gid )
{
	if ( in_array($cur_gid, $old_admin_groups) )
	{
		$in_old_group = true;
	}
}
if ( (!$in_old_group) && !is_numeric( array_search($admin->get_user_id(), $old_admin_users) ) )
{
	$admin->print_error('You do not have permissions to modify this page');
}

// Setup admin groups
$admin_groups[]		= 1;
$admin_groups		= implode(',', $admin_groups);
// Setup viewing groups
$viewing_groups[]	= 1;
$viewing_groups		= implode(',', $viewing_groups);

// If needed, get new order
if ( $parent != $old_parent )
{
	// Include ordering class
	require( LEPTON_PATH . '/framework/class.order.php' );

	$order			= new order(TABLE_PREFIX.'pages', 'position', 'page_id', 'parent');
	// Get new order
	$position		= $order->get_new( $parent );

	// Clean new order
	$order->clean($parent);
}
else
{
	$position		= $old_position;
}

// Work out level and root parent
if ( $parent != '0' )
{
	$level			= level_count( $parent )+1;
	$root_parent	= root_parent( $parent );
}
else
{
	$level			= '0';
	$root_parent	= '0';
}

// Work-out what the link should be
if ( $parent == '0' )
{
	$link			 = '/' . page_filename( $page_link );
	// rename menu titles: index && intro to prevent clashes with intro page feature and Lepton core file /pages/index.php
	$link			.= ( $link == '/index' || $link == '/intro' ) ? '_' .$page_id : '';
}
else
{
	$parent_titles			= array_reverse(get_parent_titles($parent));
	$parent_section			 = '';
	foreach ( $parent_titles AS $parent_title )
	{
		$parent_section		.= page_filename( $parent_title ) . '/';
	}
	if($parent_section == '/')
	{
		$parent_section		 = '';
	}
	$link			= '/' . $parent_section . page_filename( $page_link );
}

$filename			= LEPTON_PATH . PAGES_DIRECTORY . $link . PAGE_EXTENSION;


// Check if a page with same page filename exists
$get_same_page		= $database->query( 'SELECT `page_id`,`page_title` FROM `' . TABLE_PREFIX . 'pages` WHERE `link` = "' . $link . '" AND `page_id` != ' . $page_id);

if ( $get_same_page->numRows() > 0 )
{
	$admin->print_error('A page with the same or similar url exists');
}


// Update page with new order
$database->query('UPDATE `' . TABLE_PREFIX . 'pages` SET `parent`=' . $parent . ', `position`=' . $position . ' WHERE `page_id`=' . $page_id);

// Get page trail
$page_trail		= get_page_trail( $page_id );

// Update page settings in the pages table
$sql	= 'UPDATE `' . TABLE_PREFIX . 'pages` SET ';
$sql	.= '`page_title` = "'.$page_title.'", ';
$sql	.= '`menu_title` = "'.$menu_title.'", ';
$sql	.= '`link` = "'.$link.'" ';

if ( $request != 'ajax' )
{
	$sql	.= ', `parent` = '.$parent.', ';
	$sql	.= '`menu` = '.$menu.', ';
	$sql	.= '`level` = '.$level.', ';
	$sql	.= '`page_trail` = "'.$page_trail.'", ';
	$sql	.= '`root_parent` = '.$root_parent.', ';
	$sql	.= '`template` = "'.$template.'", ';
	$sql	.= '`target` = "'.$target.'", ';
	$sql	.= '`description` = "'.$description.'", ';
	$sql	.= '`keywords` = "'.$keywords.'", ';
	$sql	.= '`position` = '.$position.', ';
	$sql	.= '`visibility` = "'.$visibility.'", ';
	$sql	.= '`searching` = '.$searching.', ';
	if ($language != '') $sql	.= '`language` = "'.$language.'", ';
	$sql	.= '`admin_groups` = "'.$admin_groups.'", ';
	$sql	.= '`viewing_groups` = "'.$viewing_groups.'"';
}

$sql	.= 'WHERE `page_id` = '.$page_id;


$database->query($sql);

if ( $database->is_error() )
{
	$admin->print_error($database->get_error(), ADMIN_URL . '/pages/settings.php?page_id=' . $page_id );
}
// Clean old order if needed
if ( $parent != $old_parent )
{
	$order->clean($old_parent);
}

/* BEGIN page "access file" code */

// Create a new file in the /pages dir if title changed
if ( !is_writable( LEPTON_PATH . PAGES_DIRECTORY . '/') )
{
	$admin->print_error('Error creating access file in the pages directory(page), (insufficient privileges)');
}
else
{
	$old_filename	= LEPTON_PATH.PAGES_DIRECTORY . $old_link . PAGE_EXTENSION;

	// First check if we need to create a new file
	if ( ( $old_link != $link ) || (!file_exists($old_filename) ) )
	{
		// Delete old file
		$old_filename		= LEPTON_PATH.PAGES_DIRECTORY . $old_link . PAGE_EXTENSION;
		if ( file_exists( $old_filename ) )
		{
			unlink( $old_filename );
		}

		// Create access file
		create_access_file( $filename, $page_id, $level );

		// Move a directory for this page
		if ( file_exists( LEPTON_PATH . PAGES_DIRECTORY . $old_link . '/') && is_dir( LEPTON_PATH . PAGES_DIRECTORY . $old_link . '/' ) )
		{
			rename( LEPTON_PATH . PAGES_DIRECTORY . $old_link . '/', LEPTON_PATH . PAGES_DIRECTORY . $link . '/' );
		}

		// Update any pages that had the old link with the new one
		$old_link_len	= strlen($old_link);
		$sql			= '';

		$query_subs		= $database->query("SELECT page_id,link,level FROM " . TABLE_PREFIX . "pages WHERE link LIKE '%$old_link/%' ORDER BY LEVEL ASC");

		if ( $query_subs->numRows() > 0 )
		{
			while ( $sub = $query_subs->fetchRow() )
			{
				// Double-check to see if it contains old link
				if ( substr($sub['link'], 0, $old_link_len) == $old_link )
				{
					// Get new link
					$replace_this		= $old_link;
					$old_sub_link_len	= strlen( $sub['link'] );
					$new_sub_link		= $link . '/' . substr( $sub['link'], $old_link_len + 1, $old_sub_link_len );

					// Work out level
					$new_sub_level		= level_count( $sub['page_id'] );

					// Update level and link
					$database->query("UPDATE " . TABLE_PREFIX . "pages SET link = '$new_sub_link', level = '$new_sub_level' WHERE page_id = '" . $sub['page_id'] . "' LIMIT 1");

					// Re-write the access file for this page
					$old_subpage_file	= LEPTON_PATH . PAGES_DIRECTORY . $new_sub_link . PAGE_EXTENSION;

					if ( file_exists( $old_subpage_file ) )
					{
						unlink( $old_subpage_file );
					}
					create_access_file( LEPTON_PATH . PAGES_DIRECTORY . $new_sub_link . PAGE_EXTENSION, $sub['page_id'], $new_sub_level);
				}
			}
		}
	}
}

// Function to fix page trail of subs
function fix_page_trail($parent,$root_parent)
{
	// Get objects and vars from outside this function
	global $admin, $database;

	// Get page list from database
	$get_pages	= $database->query("SELECT page_id FROM " . TABLE_PREFIX . "pages WHERE parent = '$parent'");

	// Insert values into main page list
	if ( $get_pages->numRows() > 0 )
	{
		while ( $page = $get_pages->fetchRow() )
		{
			// Fix page trail
			$database->query("UPDATE " . TABLE_PREFIX . "pages SET " . 
				($root_parent != 0 ? "root_parent = '$root_parent', " : "")
				. " page_trail = '" . get_page_trail( $page['page_id'] ) . "' WHERE page_id = '" . $page['page_id'] . "'");
			// Run this query on subs
			fix_page_trail( $page['page_id'], $root_parent );
		}
	}
}

// Fix sub-pages page trail
fix_page_trail( $page_id, $root_parent );

/* END page "access file" code */

// Check if there is a db error, otherwise say successful
if ( $database->is_error() )
{
	$admin->print_error($database->get_error(), ADMIN_URL . '/pages/settings.php?page_id=' . $page_id );
}
else
{
	$admin->print_success('Page settings saved successfully', ADMIN_URL . '/pages/settings.php?page_id=' . $page_id );
}

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>