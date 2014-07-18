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
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

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

header('Content-type: application/json');
include 'functions.php';

// =================
// ! Get the page id
// =================
$page_id = $val->sanitizePost('page_id','numeric');
if ( !$page_id )
{
    $ajax    = array(
        'message'    => $backend->lang()->translate('You sent an invalid value.'),
        'success'    => false
    );
    print json_encode( $ajax );
    exit();
}

// check perms and page dir
backend_pages_prechecks('pages_settings');

// get form data
$options = backend_pages_getoptions();

// check titles
if(CAT_Helper_Page::sanitizeTitles($options)===false)
{
    $ajax    = array(
        'message'    => $backend->lang()->translate( 'Please enter a menu title' ),
        'success'    => false
    );
    print json_encode( $ajax );
    exit();
}

// ========================
// ! Get existing page data
// ========================
$page             = CAT_Helper_Page::getPage($page_id);
$old_parent       = $page['parent'];
$old_link         = $page['link'];
$old_position     = $page['position'];
$old_admin_groups = explode(',', str_replace('_', '', $page['admin_groups']));
$old_admin_users  = explode(',', str_replace('_', '', $page['admin_users']));
$in_old_group     = false;

// check if user is in old group, so he's allowed to modify this page
foreach ( $users->get_groups_id() as $cur_gid )
    if ( in_array($cur_gid, $old_admin_groups) )
        $in_old_group = true;

if ( (!$in_old_group) && !is_numeric( array_search($users->get_user_id(), $old_admin_users) ) )
{
    $ajax    = array(
        'message'    => $backend->lang()->translate('You do not have permissions to modify this page'),
        'success'    => false
    );
    print json_encode( $ajax );
    exit();
}

// Setup admin groups
$admin_groups[]        = 1;
$admin_groups        = implode(',', $options['admin_groups']);
// Setup viewing groups
$viewing_groups[]    = 1;
$viewing_groups        = implode(',', $options['viewing_groups']);

// If needed, get new order
if ( $options['parent'] != $old_parent )
{
    require( CAT_PATH . '/framework/class.order.php' );
    $order            = new order(CAT_TABLE_PREFIX.'pages', 'position', 'page_id', 'parent');
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
}

$options['root_parent']
    = ($options['level'] == 1)
    ? $options['parent']
    : CAT_Helper_Page::getRootParent($options['parent'])
    ;

// changes the values in the options array
CAT_Helper_Page::sanitizeLink($options);
CAT_Helper_Page::sanitizeTemplate($options);
CAT_Helper_Page::sanitizeLanguage($options);

// Check if page already exists; checks access file, directory, and database
if ( $options['link'] !== $old_link )
{
    if(CAT_Helper_Page::exists($options['link']))
    {
        $ajax    = array(
            'message'    => $backend->lang()->translate('A page with the same or similar link exists'),
            'success'    => false
        );
        print json_encode( $ajax );
        exit();
    }
}

// we use reset() to reload the page tree
CAT_Helper_Page::reset();

// Get page trail
$options['page_trail'] = CAT_Helper_Page::getPageTrail($options['parent'],true).','.$page_id;
if(substr($options['page_trail'],0,1)==0)
    $options['page_trail'] = str_replace('0,','',$options['page_trail']);

// ==================================================
// ! save page
// ==================================================
if ( CAT_Helper_Page::updatePage($page_id,$options) === false )
{
    $ajax    = array(
        'message'    => 'Database error: '.$backend->db()->getError(),
        'success'    => false
    );
    print json_encode( $ajax );
    exit();
}

// Clean old order if needed
if ( $options['parent'] != $old_parent )
    $order->clean($old_parent);

// additional settings
$template_variant = $val->sanitizePost('template_variant',NULL,true);
if($template_variant)
    CAT_Helper_Page::updatePageSettings($page_id,array('template_variant' => $template_variant));

//=====================
// ! Move (rename) page
//=====================
if ( $options['link'] !== $old_link )
{
    // if a directory exists, rename it; if this fails, we need to recover
    // the changes!
    if ( is_dir( CAT_PATH . PAGES_DIRECTORY . $old_link ) )
    {
        if( !CAT_Helper_Directory::moveDirectory(
            CAT_PATH.PAGES_DIRECTORY.$old_link,
            CAT_PATH.PAGES_DIRECTORY.$options['link'],
            true )
        ) {
            CAT_Helper_Page::updatePage($page_id,$page);
            $ajax    = array(
                'message'    => 'Unable to move the directory',
                'success'    => false
            );
            print json_encode( $ajax );
            exit();
        }
    }
    // delete old file
    $old_filename = CAT_Helper_Directory::sanitizePath(CAT_PATH.PAGES_DIRECTORY.$old_link.PAGE_EXTENSION);
    if ( file_exists( $old_filename ) )
        unlink( $old_filename );
    // create new access file
    $result = CAT_Helper_Page::createAccessFile($options['link'], $page_id, $options['level']);
    // Update child pages
    $old_link_len = strlen($old_link);
    $query_subs   = $database->query(sprintf(
        "SELECT `page_id`, `parent`, `link`, `level` FROM `%spages` WHERE `page_trail` LIKE '%s,%%' ORDER BY LEVEL ASC",
        CAT_TABLE_PREFIX, $options['page_trail']
    ));
    if ( is_object($query_subs) && $query_subs->numRows() > 0 )
    {
        while ( $sub = $query_subs->fetchRow(MYSQL_ASSOC) )
        {
            // Double-check to see if it contains old link
            if ( substr($sub['link'], 0, $old_link_len) == $old_link )
            {
                // Get new link
                $replace_this     = $old_link;
                $old_sub_link_len = strlen( $sub['link'] );
                $new_sub_link     = $options['link'] . '/' . substr( $sub['link'], $old_link_len + 1, $old_sub_link_len );
                // Work out level
                $new_sub_level    = (count(explode('/',$new_sub_link))-2);
                $root_parent      = $options['root_parent'] == '0' ? $page_id : $options['root_parent'];
                // Update link and level
                $database->query(sprintf(
                    "UPDATE `%spages` SET link='%s', level='%s', root_parent='%s' WHERE page_id='%s' LIMIT 1",
                    CAT_TABLE_PREFIX, $new_sub_link, $new_sub_level, $root_parent, $sub['page_id']
                ));
                // we use reset() to reload the page tree
                CAT_Helper_Page::reset();
                // update trail
                $database->query(sprintf(
                    "UPDATE `%spages` SET page_trail='%s' WHERE page_id='%s' LIMIT 1",
                    CAT_TABLE_PREFIX, CAT_Helper_Page::getPageTrail($sub['page_id']), $sub['page_id']
                ));
                // Re-write the access file for this page
                $old_subpage_file    = CAT_PATH.PAGES_DIRECTORY.$new_sub_link.PAGE_EXTENSION;
                // remove old file
                if ( file_exists( $old_subpage_file ) )
                    unlink( $old_subpage_file );
                // create new
                CAT_Helper_Page::createAccessFile( $new_sub_link, $sub['page_id']);
            }
        }
    }
   	// check if source directory is empty now
    $source_dir = pathinfo(CAT_PATH.PAGES_DIRECTORY.$old_link,PATHINFO_DIRNAME);
    if ( CAT_Helper_Directory::is_empty($source_dir,true) )
        CAT_Helper_Directory::removeDirectory($source_dir);
}

// ==============================
// ! Check if there is a db error
// ==============================
if ( CAT_Helper_Page::getInstance()->db()->isError() )
{
	$ajax	= array(
		'message'		=> CAT_Helper_Page::getInstance()->db()->getError(),
		'success'		=> false
	);
	print json_encode( $ajax );
	exit();
}
else
{
	$ajax	= array(
		'message'		=> $backend->lang()->translate('Page settings saved successfully'),
		'menu_title'	=> htmlspecialchars_decode($options['menu_title'],ENT_QUOTES),
		'page_title'	=> htmlspecialchars_decode($options['page_title'],ENT_QUOTES),
		'visibility'	=> $options['visibility'],
		'parent'		=> $options['parent'],
		'position'		=> $options['position'],
		'success'		=> true
	);
	print json_encode( $ajax );
	exit();
}