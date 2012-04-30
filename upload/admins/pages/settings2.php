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
if(!isset($_POST['page_id']) OR !is_numeric($_POST['page_id']))
{
	header("Location: index.php");
	exit(0);
} else {
	$page_id = $_POST['page_id'];
}

require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Pages', 'pages_settings');

// Include the WB functions file
require_once(WB_PATH.'/framework/functions.php');

// Get values
$page_link = htmlspecialchars($admin->add_slashes($admin->get_post('link')));
$page_title = htmlspecialchars($admin->get_post_escaped('page_title') );
$menu_title = htmlspecialchars($admin->get_post_escaped('menu_title') );
$description = htmlspecialchars($admin->add_slashes($admin->get_post('description')) );
$keywords = htmlspecialchars($admin->add_slashes($admin->get_post('keywords')) );
$parent = $admin->get_post_escaped('parent');
$visibility = $admin->get_post_escaped('visibility');
$template = $admin->get_post_escaped('template');
$target = $admin->get_post_escaped('target');
$admin_groups = $admin->get_post_escaped('admin_groups');
$viewing_groups = $admin->get_post_escaped('viewing_groups');
$searching = $admin->get_post_escaped('searching');
$language = $admin->get_post_escaped('language');
$menu = $admin->get_post_escaped('menu');

// Validate data
if($page_title == '' || substr($page_title,0,1)=='.')
{
	$admin->print_error($MESSAGE['PAGES_BLANK_PAGE_TITLE']);
}
if($menu_title == '' || substr($menu_title,0,1)=='.')
{
	$admin->print_error($MESSAGE['PAGES_BLANK_MENU_TITLE']);
}

// Get existing perms

$sql = 'SELECT `parent`,`link`,`position`,`admin_groups`,`admin_users` FROM `'.TABLE_PREFIX.'pages` WHERE `page_id`='.$page_id;
$results = $database->query($sql);

$results_array = $results->fetchRow();
$old_parent = $results_array['parent'];
$old_link = $results_array['link'];
$old_position = $results_array['position'];
$old_admin_groups = explode(',', str_replace('_', '', $results_array['admin_groups']));
$old_admin_users = explode(',', str_replace('_', '', $results_array['admin_users']));


$in_old_group = FALSE;
foreach($admin->get_groups_id() as $cur_gid)
{
    if (in_array($cur_gid, $old_admin_groups))
    {
	$in_old_group = TRUE;
    }
}
if((!$in_old_group) AND !is_numeric(array_search($admin->get_user_id(), $old_admin_users)))
{
	$admin->print_error($MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
}

// Setup admin groups
$admin_groups[] = 1;
$admin_groups = implode(',', $admin_groups);

// Setup viewing groups
$viewing_groups[] = 1;
$viewing_groups = implode(',', $viewing_groups);

// If needed, get new order
if($parent != $old_parent)
{
	// Include ordering class
	require(WB_PATH.'/framework/class.order.php');
	$order = new order(TABLE_PREFIX.'pages', 'position', 'page_id', 'parent');
	// Get new order
	$position = $order->get_new($parent);
	// Clean new order
	$order->clean($parent);
} else {
	$position = $old_position;
}

// Work out level and root parent
if ($parent!='0')
{
	$level = level_count($parent)+1;
	$root_parent = root_parent($parent);
}
else {
	$level = '0';
	$root_parent = '0';
}

// Work-out what the link should be
if($parent == '0')
{
	$link = '/'.page_filename($page_link);
	// rename menu titles: index && intro to prevent clashes with intro page feature and WB core file /pages/index.php
	if($link == '/index' || $link == '/intro')
    {
		$link .= '_' .$page_id;
		$filename = WB_PATH.PAGES_DIRECTORY.'/'.page_filename($page_link).'_'.$page_id .PAGE_EXTENSION;
	} else {
		$filename = WB_PATH.PAGES_DIRECTORY.'/'.page_filename($page_link).PAGE_EXTENSION;
	}
} else {
	$parent_section = '';
	$parent_titles = array_reverse(get_parent_titles($parent));
	foreach($parent_titles AS $parent_title)
    {
		$parent_section .= page_filename($parent_title).'/';
	}
	if($parent_section == '/')
    {
      $parent_section = '';
    }
	$link = '/'.$parent_section.page_filename($page_link);
	$filename = WB_PATH.PAGES_DIRECTORY.'/'.$parent_section.page_filename($page_link).PAGE_EXTENSION;
}

// Check if a page with same page filename exists
$sql = 'SELECT `page_id`,`page_title` FROM `'.TABLE_PREFIX.'pages` WHERE `link` = "'.$link.'" AND `page_id` != '.$page_id;
$get_same_page = $database->query($sql);

if($get_same_page->numRows() > 0)
{
	$admin->print_error($MESSAGE['PAGES_PAGE_EXISTS']);
}

// Update page with new order
$sql = 'UPDATE `'.TABLE_PREFIX.'pages` SET `parent`='.$parent.', `position`='.$position.' WHERE `page_id`='.$page_id.'';
$database->query($sql);

// Get page trail
$page_trail = get_page_trail($page_id);

// Update page settings in the pages table
$sql  = 'UPDATE `'.TABLE_PREFIX.'pages` SET ';
$sql .= '`parent` = '.$parent.', ';
$sql .= '`page_title` = "'.$page_title.'", ';
$sql .= '`menu_title` = "'.$menu_title.'", ';
$sql .= '`menu` = '.$menu.', ';
$sql .= '`level` = '.$level.', ';
$sql .= '`page_trail` = "'.$page_trail.'", ';
$sql .= '`root_parent` = '.$root_parent.', ';
$sql .= '`link` = "'.$link.'", ';
$sql .= '`template` = "'.$template.'", ';
$sql .= '`target` = "'.$target.'", ';
$sql .= '`description` = "'.$description.'", ';
$sql .= '`keywords` = "'.$keywords.'", ';
$sql .= '`position` = '.$position.', ';
$sql .= '`visibility` = "'.$visibility.'", ';
$sql .= '`searching` = '.$searching.', ';
$sql .= '`language` = "'.$language.'", ';
$sql .= '`admin_groups` = "'.$admin_groups.'", ';
$sql .= '`viewing_groups` = "'.$viewing_groups.'"';
$sql .= 'WHERE `page_id` = '.$page_id;
$database->query($sql);

$target_url = ADMIN_URL.'/pages/settings.php?page_id='.$page_id;
if($database->is_error())
{
	$admin->print_error($database->get_error(), $target_url );
}
// Clean old order if needed
if($parent != $old_parent)
{
	$order->clean($old_parent);
}

/* BEGIN page "access file" code */

// Create a new file in the /pages dir if title changed
if(!is_writable(WB_PATH.PAGES_DIRECTORY.'/'))
{
	$admin->print_error($MESSAGE['PAGES_CANNOT_CREATE_ACCESS_FILE']);
} else {
    $old_filename = WB_PATH.PAGES_DIRECTORY.$old_link.PAGE_EXTENSION;
	// First check if we need to create a new file
	if(($old_link != $link) || (!file_exists($old_filename)))
    {
		// Delete old file
		$old_filename = WB_PATH.PAGES_DIRECTORY.$old_link.PAGE_EXTENSION;
		if(file_exists($old_filename))
        {
			unlink($old_filename);
		}
		// Create access file
		create_access_file($filename,$page_id,$level);
		// Move a directory for this page
		if(file_exists(WB_PATH.PAGES_DIRECTORY.$old_link.'/') AND is_dir(WB_PATH.PAGES_DIRECTORY.$old_link.'/'))
        {
			rename(WB_PATH.PAGES_DIRECTORY.$old_link.'/', WB_PATH.PAGES_DIRECTORY.$link.'/');
		}
		// Update any pages that had the old link with the new one
		$old_link_len = strlen($old_link);
        $sql = '';
		$query_subs = $database->query("SELECT page_id,link,level FROM ".TABLE_PREFIX."pages WHERE link LIKE '%$old_link/%' ORDER BY LEVEL ASC");

		if($query_subs->numRows() > 0)
        {
			while($sub = $query_subs->fetchRow())
            {
				// Double-check to see if it contains old link
				if(substr($sub['link'], 0, $old_link_len) == $old_link)
                {
					// Get new link
					$replace_this = $old_link;
					$old_sub_link_len =strlen($sub['link']);
					$new_sub_link = $link.'/'.substr($sub['link'],$old_link_len+1,$old_sub_link_len);
					// Work out level
					$new_sub_level = level_count($sub['page_id']);
					// Update level and link
					$database->query("UPDATE ".TABLE_PREFIX."pages SET link = '$new_sub_link', level = '$new_sub_level' WHERE page_id = '".$sub['page_id']."' LIMIT 1");
					// Re-write the access file for this page
					$old_subpage_file = WB_PATH.PAGES_DIRECTORY.$new_sub_link.PAGE_EXTENSION;
					if(file_exists($old_subpage_file))
                    {
						unlink($old_subpage_file);
					}
					create_access_file(WB_PATH.PAGES_DIRECTORY.$new_sub_link.PAGE_EXTENSION, $sub['page_id'], $new_sub_level);
				}
			}
		}
	}
}

// Function to fix page trail of subs
function fix_page_trail($parent,$root_parent)
{
	// Get objects and vars from outside this function
	global $admin, $template, $database, $TEXT, $MESSAGE;
	// Get page list from database
	$query = "SELECT page_id FROM ".TABLE_PREFIX."pages WHERE parent = '$parent'";
	$get_pages = $database->query($query);
	// Insert values into main page list
	if($get_pages->numRows() > 0)
    {
		while($page = $get_pages->fetchRow())
        {
			// Fix page trail

			$database->query("UPDATE ".TABLE_PREFIX."pages SET ".($root_parent != 0 ?"root_parent = '$root_parent', ":"")." page_trail = '".get_page_trail($page['page_id'])."' WHERE page_id = '".$page['page_id']."'");
			// Run this query on subs
			fix_page_trail($page['page_id'],$root_parent);
		}
	}
}

// Fix sub-pages page trail
fix_page_trail($page_id,$root_parent);

/* END page "access file" code */

$pagetree_url = ADMIN_URL.'/pages/index.php';
$target_url = ADMIN_URL.'/pages/settings.php?page_id='.$page_id;
// Check if there is a db error, otherwise say successful
if($database->is_error())
{
	$admin->print_error($database->get_error(), $target_url );
} else {
	$admin->print_success($MESSAGE['PAGES_SAVED_SETTINGS'], $target_url );
}

// Print admin footer
$admin->print_footer();

?>