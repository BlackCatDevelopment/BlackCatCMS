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
    $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));    $dir = $_SERVER['DOCUMENT_ROOT'];
    $inc = false;
    foreach ($subs as $sub) {
        if (empty($sub)) continue; $dir .= '/'.$sub;
        if (file_exists($dir.'/framework/class.secure.php')) {
            include($dir.'/framework/class.secure.php'); $inc = true;    break;
        }
	}
    if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

$backend = CAT_Backend::getInstance('Pages', 'pages_settings', false);
$val     = CAT_Helper_Validate::getInstance();
$users   = CAT_Users::getInstance();

header('Content-type: application/json');

// Get page id
$page_id		= $val->sanitizePost('page_id','numeric');
if ( !$page_id )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You sent an invalid value.'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

if ( !$users->checkPermission('Pages','pages_settings') )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You do not have permissions to modify this page'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// Include the WB functions file
require_once( CAT_PATH . '/framework/functions.php' );

// Get values
$options = array(
    'parent'         => ( $val->sanitizePost('parent','numeric',true) ? $val->sanitizePost('parent','numeric',true) : 0 ),
    'target'         => $val->sanitizePost('target',NULL,true),
    'page_title'     => htmlspecialchars($val->sanitizePost('page_title',NULL,true) ),
    'menu_title'     => htmlspecialchars($val->sanitizePost('menu_title',NULL,true) ),
    'description'    => htmlspecialchars($val->sanitizePost('description',NULL,true) ),
    'keywords'       => htmlspecialchars($val->sanitizePost('keywords',NULL,true)    ),
    'template'       => $val->sanitizePost('template',NULL,true),
    'visibility'     => $val->sanitizePost('visibility',NULL,true),
    'position'       => 0,
    'menu'           => ( ( $val->sanitizePost('menu',NULL,true) != '') ? $val->sanitizePost('menu',NULL,true) : 1 ),
    'language'       => $val->sanitizePost('language',NULL,true),
    'searching'      => $val->sanitizePost('searching',NULL,true) ? '1' : '0',
    'modified_when'  => time(),
    'modified_by'    => $users->get_user_id(),
    'admin_groups'	 => $val->sanitizePost('admin_groups',NULL,true),
    'viewing_groups' => $val->sanitizePost('viewing_groups',NULL,true),
    'level'          => 0,
    'root_parent'    => 0,
);

$page_link			= htmlspecialchars($val->sanitizePost('page_link',NULL,true));
$page_groups		= htmlspecialchars($val->sanitizePost('page_groups',NULL,true));

// =======================
// ! Validate menu_title
// =======================
if ($options['menu_title'] == '' || substr($options['menu_title'],0,1)=='.')
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate( 'Please enter a menu title' ),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$options['page_title'] = ( $options['page_title'] == '' )
                       ? $options['menu_title']
                       : $options['page_title'];

// Get existing page data
$page               = CAT_Helper_Page::getPage($page_id);
$old_parent			= $page['parent'];
$old_link			= $page['link'];
$old_position		= $page['position'];
$old_admin_groups	= explode(',', str_replace('_', '', $page['admin_groups']));
$old_admin_users	= explode(',', str_replace('_', '', $page['admin_users']));
$in_old_group		= false;

foreach ( $users->get_groups_id() as $cur_gid )
{
	if ( in_array($cur_gid, $old_admin_groups) )
	{
		$in_old_group = true;
	}
}
if ( (!$in_old_group) && !is_numeric( array_search($users->get_user_id(), $old_admin_users) ) )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You do not have permissions to modify this page'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// Setup admin groups
$admin_groups[]		= 1;
$admin_groups		= implode(',', $options['admin_groups']);
// Setup viewing groups
$viewing_groups[]	= 1;
$viewing_groups		= implode(',', $options['viewing_groups']);

// If needed, get new order
if ( $options['parent'] != $old_parent )
{
	// Include ordering class
	require( CAT_PATH . '/framework/class.order.php' );

	$order			= new order(CAT_TABLE_PREFIX.'pages', 'position', 'page_id', 'parent');
	// Get new order
	$options['position'] = $order->get_new( $options['parent'] );
	// Clean new order
	$order->clean($options['parent']);
}
else
{
	$options['position'] = $old_position;
}

// Work out level and root parent
if ( $options['parent'] != '0' )
{
	$options['level']       = CAT_Helper_Page::properties($options['parent'],'level') + 1;
	$options['root_parent'] = CAT_Helper_Page::getRootParent($options['parent']);
}

// Work-out what the link should be
if ( $options['parent'] == '0' )
{
	$options['link']  = '/' . CAT_Helper_Page::getFilename($page_link);
	// rename menu titles: index && intro to prevent clashes with intro page feature and Lepton core file /pages/index.php
	$options['link'] .= ( $options['link'] == '/index' || $options['link'] == '/intro' ) ? '_' .$page_id : '';
}
else
{
	$parent_titles	= array_reverse(CAT_Helper_Page::getParentTitles($options['parent']));
	$parent_section			 = '';
	foreach ( $parent_titles AS $parent_title )
	{
		$parent_section		.= CAT_Helper_Page::getFilename( $parent_title ) . '/';
	}
	if($parent_section == '/')
	{
		$parent_section		 = '';
	}
	$options['link']			= '/' . $parent_section . CAT_Helper_Page::getFilename( $page_link );
}

$filename = CAT_PATH . PAGES_DIRECTORY . $options['link'] . PAGE_EXTENSION;

// ==================================================
// ! Check if a page with same page filename exists
// ==================================================
if ( $options['link'] !== $old_link )
{
    $get_same_page = $backend->db()->query(sprintf(
        "SELECT page_id FROM `%spages` WHERE link = '%s'",
        CAT_TABLE_PREFIX, $options['link']
    ));
    if ( $get_same_page->numRows() > 0 || file_exists(CAT_PATH . PAGES_DIRECTORY.$options['link'].PAGE_EXTENSION) || file_exists(CAT_PATH . PAGES_DIRECTORY.$options['link'].'/') )
    {
	$ajax	= array(
    		'message'	=>$backend->lang()->translate( 'A page with the same or similar link exists' ),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
    }
}

// Get page trail
$options['page_trail'] = CAT_Helper_Page::getPageTrail($options['parent']).','.$page_id;

if ( !CAT_Helper_Page::updatePage($page_id,$options) )
{
	$ajax	= array(
		'message'	=> 'Database error: '.$backend->db()->get_error(),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
// Clean old order if needed
if ( $options['parent'] != $old_parent )
{
	$order->clean($old_parent);
}

// Create a new file in the /pages dir if title changed
if ( !is_writable( CAT_PATH . PAGES_DIRECTORY . '/') )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('Error creating access file in the pages directory, (insufficient privileges)'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
else
{
	$old_filename	= CAT_PATH.PAGES_DIRECTORY . $old_link . PAGE_EXTENSION;

	// First check if we need to create a new file
	if ( ( $old_link != $options['link'] ) || (!file_exists($old_filename) ) )
	{
		// Delete old file
		$old_filename		= CAT_PATH.PAGES_DIRECTORY . $old_link . PAGE_EXTENSION;
		if ( file_exists( $old_filename ) )
		{
			unlink( $old_filename );
		}

		// Create access file
		CAT_Helper_Page::createAccessFile( $filename, $page_id, $options['level'] );

		// Move a directory for this page
		if ( file_exists( CAT_PATH . PAGES_DIRECTORY . $old_link . '/') && is_dir( CAT_PATH . PAGES_DIRECTORY . $old_link . '/' ) )
		{
			rename( CAT_PATH . PAGES_DIRECTORY . $old_link . '/', CAT_PATH . PAGES_DIRECTORY . $options['link'] . '/' );
		}

		// Update any pages that had the old link with the new one
		$old_link_len	= strlen($old_link);
		$sql			= '';

		$query_subs		= $database->query("SELECT page_id,link,level FROM " . CAT_TABLE_PREFIX . "pages WHERE link LIKE '%$old_link/%' ORDER BY LEVEL ASC");

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
					$new_sub_link		= $options['link'] . '/' . substr( $sub['link'], $old_link_len + 1, $old_sub_link_len );

					// Work out level
					$new_sub_level		= level_count( $sub['page_id'] );

					// Update level and link
					$database->query("UPDATE " . CAT_TABLE_PREFIX . "pages SET link = '$new_sub_link', level = '$new_sub_level' WHERE page_id = '" . $sub['page_id'] . "' LIMIT 1");

					// Re-write the access file for this page
					$old_subpage_file	= CAT_PATH . PAGES_DIRECTORY . $new_sub_link . PAGE_EXTENSION;

					if ( file_exists( $old_subpage_file ) )
					{
						unlink( $old_subpage_file );
					}
					create_access_file( CAT_PATH . PAGES_DIRECTORY . $new_sub_link . PAGE_EXTENSION, $sub['page_id'], $new_sub_level);
				}
			}
		}
	}
}

// Fix sub-pages page trail
CAT_Helper_Page::updatePageTrail( $page_id, $options['root_parent'] );

// Check if there is a db error, otherwise say successful
if ( CAT_Helper_Page::getInstance()->db()->is_error() )
{
	$ajax	= array(
		'message'		=> CAT_Helper_Page::getInstance()->db()->get_error(),
		'success'		=> false
	);
	print json_encode( $ajax );
	exit();
}
else
{
	$ajax	= array(
		'message'		=> $backend->lang()->translate('Page settings saved successfully'),
		'menu_title'	=> $options['menu_title'],
		'page_title'	=> $options['page_title'],
		'visibility'	=> $options['visibility'],
		'parent'		=> $options['parent'],
		'position'		=> $options['position'],
		'success'		=> true
	);
	print json_encode( $ajax );
	exit();
}
exit();
?>