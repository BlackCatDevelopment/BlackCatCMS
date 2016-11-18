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
 *   @copyright       2013 - 2016, Black Cat Development
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

// check perms and page dir
backend_pages_prechecks('pages_add');

// get form data
$options = backend_pages_getoptions();

// =============================================================
// ! Check if user has permission to add a page at this position
// =============================================================
if ( $options['parent'] != 0 )
{
    if ( !CAT_Helper_Page::getPagePermission($options['parent'],'admin') )
    {
        CAT_Object::json_error($backend->lang()->translate('You do not have the permission add a page here.'));
        exit();
    }
}
elseif ( ! $users->checkPermission('pages', 'pages_add_l0', false) == true )
{
    CAT_Object::json_error($backend->lang()->translate('You do not have the permission add a page here.'));
    exit();
}

// ===================
// ! Check group perms
// ===================
if ( !in_array(1, $users->get_groups_id()) )
{
    $admin_perm_ok = false;
    foreach ($options['admin_groups'] as $adm_group)
        if ( in_array( $adm_group, $users->get_groups_id() ) )
            $admin_perm_ok = true;

    if ( $admin_perm_ok == false )
    {
        CAT_Object::json_error($backend->lang()->translate('You do not have the permission add a page here.'));
        exit();
    }

    $admin_perm_ok = false;
    foreach ($options['viewing_groups'] as $view_group)
        if ( in_array( $view_group, $users->get_groups_id() ) )
            $admin_perm_ok = true;

    if ($admin_perm_ok == false)
    {
        CAT_Object::json_error($backend->lang()->translate('You do not have the permission add a page here.'));
        exit();
    }
}

// always add admin group to the list of admin_groups
if(!in_array('1',$options['admin_groups']))
    $options['admin_groups'][] = 1;

$options['admin_groups']
    = is_array( $options['admin_groups'] )
    ? implode(',', array_unique($options['admin_groups']))
    : $options['admin_groups']
    ;
$options['viewing_groups']
    = is_array($options['viewing_groups'])
    ? implode(',', array_unique($options['viewing_groups']))
    : $options['viewing_groups']
    ;

// check titles
if(CAT_Helper_Page::sanitizeTitles($options)===false)
{
    CAT_Object::json_error($backend->lang()->translate('Please enter a menu title'));
    exit();
}

// changes the values in the options array
CAT_Helper_Page::sanitizeLink($options);
CAT_Helper_Page::sanitizeTemplate($options);
CAT_Helper_Page::sanitizeLanguage($options);

// Check if page already exists; checks access file, directory, and database
if(CAT_Helper_Page::exists($options['link']))
{
    CAT_Object::json_error($backend->lang()->translate('A page with the same or similar link exists'));
    exit();
}

// ========================
// ! Validate page position
// ========================
require(CAT_PATH . '/framework/class.order.php');
$order = new order(CAT_TABLE_PREFIX.'pages', 'position', 'page_id', 'parent');
// First clean order
$order->clean($options['parent']);
// Get new order
$options['position'] = $order->get_new($options['parent']);

// ================================
// ! Insert page into pages table
// ================================
$page_id = CAT_Helper_Page::addPage($options);
if ( !$page_id )
{
    CAT_Object::json_error($backend->lang()->translate('Unable to create the page: ') . $backend->db()->getError());
    exit();
}

// Work out root parent
$root_parent    = CAT_Helper_Page::getRootParent($page_id);
// Work out page trail
$page_trail     = CAT_Helper_Page::getPageTrail($page_id);

$result = CAT_Helper_Page::updatePage($page_id,array(
    'root_parent' => $root_parent,
    'page_trail'  => $page_trail,
));
if (!$result)
{
    // try to recover = delete page
    CAT_Helper_Page::deletePage($page_id);
    CAT_Object::json_error($backend->db()->getError());
    exit();
}

// ====================
// ! Create access file
// ====================
$result = CAT_Helper_Page::createAccessFile($options['link'], $page_id, $options['level']);
if (!$result)
{
    // try to recover = delete page
    CAT_Helper_Page::deletePage($page_id);
    CAT_Object::json_error($backend->lang()->translate('Error creating access file in the pages directory, cannot open file'));
    exit();
}

$module               = $val->sanitizePost('type');

// ==========================================
// ! Add new record into the sections table
// ==========================================
$backend->db()->query(sprintf(
    "INSERT INTO `%ssections` (`page_id`,`position`,`module`,`block`) VALUES ('%d','1', '%s','1')",
    CAT_TABLE_PREFIX, $page_id, $module
));

// ======================
// ! Get the section id
// ======================
$section_id = $backend->db()->lastInsertId();

//
// ! Keep old modules happy
//
$admin =& $backend;

// ======================================================
// ! Include the selected modules add file if it exists
// ======================================================
if ( file_exists(CAT_PATH . '/modules/' . $module . '/add.php') )
    require CAT_PATH . '/modules/' . $module . '/add.php';

// ==============================
// ! Check if there is a db error
// ==============================
if ( $backend->db()->isError() )
{
    CAT_Object::json_error($backend->db()->getError());
    exit();
}
else
{
    // print success and redirect
    $ajax    = array(
        'message'    => $backend->lang()->translate( 'Page added successfully' ),
        'url'        => CAT_ADMIN_URL . '/pages/modify.php?page_id='. $page_id,
        'success'    => true
    );
    echo json_encode( $ajax );
    exit();
}
exit();


