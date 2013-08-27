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

$backend = CAT_Backend::getInstance('Pages','pages_add',false);
$users   = CAT_Users::getInstance();
$val   = CAT_Helper_Validate::getInstance();

header('Content-type: application/json');

if ( ! $users->checkPermission('pages','pages_add',false) )
{
	$ajax	= array(
		'message'	=>$backend->lang()->translate('You don not have the permission to add a page.'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// ================================= 
// ! Include the WB functions file   
// ================================= 
require_once(CAT_PATH . '/framework/functions.php');

// ============== 
// ! Get values   
// ============== 
$options = array(
    'admin_groups'	 => $val->sanitizePost('admin_groups',NULL,true),
    'description'    => htmlspecialchars($val->sanitizePost('description',NULL,true) ),
    'keywords'       => htmlspecialchars($val->sanitizePost('keywords',NULL,true)    ),
    'language'       => $val->sanitizePost('language',NULL,true),
    'level'          => 0, // just a default here
    'link'           => '', // will be added later
    'menu'           => ( ( $val->sanitizePost('menu',NULL,true) != '') ? $val->sanitizePost('menu',NULL,true) : 1 ),
    'menu_title'     => htmlspecialchars($val->sanitizePost('menu_title',NULL,true) ),
    'modified_by'    => $users->get_user_id(),
    'modified_when'  => time(),
    'page_title'     => htmlspecialchars($val->sanitizePost('page_title',NULL,true) ),
    'parent'         => ( $val->sanitizePost('parent','numeric',true) ? $val->sanitizePost('parent','numeric',true) : 0 ),
    'position'       => 1, // just a default here
    'searching'      => $val->sanitizePost('searching',NULL,true) ? '1' : '0',
    'target'         => $val->sanitizePost('target',NULL,true),
    'template'       => $val->sanitizePost('template',NULL,true),
    'viewing_groups' => $val->sanitizePost('viewing_groups',NULL,true),
    'visibility'     => $val->sanitizePost('visibility',NULL,true),
);

$page_link			= htmlspecialchars($val->sanitizePost('page_link',NULL,true) );
$module				= $val->sanitizePost('type');

// ============================= 
// ! add Admin and view groups   
// ============================= 
$options['admin_groups'][]		= 1;
$options['viewing_groups'][]	= 1;

// ============================================== 
// ! Check if user has permission to add a page   
// ============================================== 
if ( $options['parent'] != 0 )
{
	if ( !CAT_Helper_Page::getPagePermission($options['parent'],'admin') )
	{
		$ajax	= array(
			'message'	=> $backend->lang()->translate('You do not have the permission add a page here.'),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}
}
// *****************************************************************************
// Hier wird LEVEL 0 geprüft, aber wir wissen doch noch gar nicht, ob die Seite
// eine L0-Seite ist???
// *****************************************************************************
elseif ( ! $users->checkPermission('pages_add_l0','system',false) )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You do not have the permission add a page here.'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

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

// ========================================================= 
// ! Set page_title to menu_title if page_title is not set   
// ========================================================= 
$options['page_title'] = $options['page_title'] == '' ? $options['menu_title'] : $options['page_title'];

// ======================================================= 
// ! Check to see if page created has needed permissions   
// ======================================================= 
if ( !in_array(1, $users->get_groups_id()) )
{
	$admin_perm_ok = false;

	foreach ($options['admin_groups'] as $adm_group)
	{
		if ( in_array( $adm_group, $users->get_groups_id() ) )
		{
			$admin_perm_ok = true;
		}
	}
	if ( $admin_perm_ok == false )
	{
		$ajax	= array(
			'message'	=> $backend->lang()->translate( 'You do not have the permission add a page here.' ),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}

	$admin_perm_ok = false;

	foreach ($options['viewing_groups'] as $view_group)
	{
		if ( in_array( $view_group, $users->get_groups_id() ) )
		{
			$admin_perm_ok = true;
		}
	}
	if ($admin_perm_ok == false)
	{
		$ajax	= array(
			'message'	=> $backend->lang()->translate( 'You do not have the permission add a page here.' ),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}
}

$options['admin_groups']		= implode(',', array_unique($options['admin_groups']));
$options['viewing_groups']		= implode(',', array_unique($options['viewing_groups']));

// ====================================================== 
// ! Work-out what the link and page filename should be   
// ====================================================== 
if ( !$options['parent'] || $options['parent'] == '0' )
{
	$options['link'] = '/'.page_filename($options['menu_title']);

	// =================================================================================================================== 
	// ! rename menu titles: index && intro to prevent clashes with intro page feature and WB core file /pages/index.php   
	// =================================================================================================================== 
	if( $options['link'] == '/index' || $options['link'] == '/intro' )
	{
		$options['link']	.= '_0';
		$filename	= CAT_PATH . PAGES_DIRECTORY .'/' . CAT_Helper_Page::getFilename($options['menu_title']) . '_0' . PAGE_EXTENSION;
	}
	else
	{
		$filename	= CAT_PATH . PAGES_DIRECTORY . '/' . CAT_Helper_Page::getFilename($options['menu_title']) . PAGE_EXTENSION;
	}
}
else
{
    // get the titles of the parent pages to create the subdirectory
    $parent_section = '';
    $parent_titles  = array_reverse(CAT_Helper_Page::getParentTitles($options['parent']));
    foreach( $parent_titles as $parent_title )
	{
        $parent_section	.= CAT_Helper_Page::getFilename($parent_title).'/';
	}
    if ($parent_section == '/') $parent_section = '';
    $options['link'] = '/'.$parent_section.CAT_Helper_Page::getFilename($options['menu_title']);
   	$filename = CAT_PATH.PAGES_DIRECTORY.'/'.$parent_section.CAT_Helper_Page::getFilename($options['menu_title']).PAGE_EXTENSION;
	CAT_Helper_Directory::createDirectory(CAT_PATH.PAGES_DIRECTORY.'/'.$parent_section);
    $options['level'] = count($parent_titles);
}

// ================================================== 
// ! Check if a page with same page filename exists   
// ================================================== 
$get_same_page = $backend->db()->query(sprintf(
    "SELECT page_id FROM `%spages` WHERE link = '%s'",
    CAT_TABLE_PREFIX, $options['link']
));
if ( $get_same_page->numRows() > 0 || file_exists(CAT_PATH . PAGES_DIRECTORY.$options['link'].PAGE_EXTENSION) || file_exists(CAT_PATH . PAGES_DIRECTORY.$options['link'].'/') )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate( 'A page with the same or similar link exists' ),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// ============================== 
// ! Include the ordering class   
// ============================== 
require(CAT_PATH . '/framework/class.order.php');
$order = new order(CAT_TABLE_PREFIX.'pages', 'position', 'page_id', 'parent');
// First clean order
$order->clean($options['parent']);
// Get new order
$options['position'] = $order->get_new($options['parent']);

// ================================================================================================ 
// ! Work-out if the page parent (if selected) has a seperate template or language to the default   
// ================================================================================================ 
if ( $options['language'] == '' || $options['template'] == '')
{
	$query_parent = $backend->db()->query(sprintf(
        "SELECT template, language FROM `%spages` WHERE page_id = %d",
        CAT_TABLE_PREFIX, $options['parent']
    ));
	if ( $query_parent->numRows() > 0 )
	{
		$fetch_parent		= $query_parent->fetchRow();
		$options['template'] = ( $options['template'] == '' ) ? $fetch_parent['template'] : $options['template'];
		$options['language'] = ( $options['language'] == '' ) ? $fetch_parent['language'] : $options['language'];
	}
	else
	{
		$fetch_parent		= $query_parent->fetchRow();
		$options['template'] = ( $options['template'] == '' ) ? '' : $options['template'];
		$options['language'] = ( $options['language'] == '' ) ? DEFAULT_LANGUAGE : $options['language'];
	}
}

// ================================ 
// ! Insert page into pages table   
// ================================ 
$page_id = CAT_Helper_Page::addPage($options);

if ( !$page_id )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('Unable to create the page: ') . $backend->db()->get_error(),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// Work out root parent
$root_parent	= CAT_Helper_Page::getRootParent($page_id);
// Work out page trail
$page_trail		= CAT_Helper_Page::getPageTrail($page_id);

// =========================================================
// ! Set page_link
// =========================================================
if ( $page_link && $page_link != pathinfo($options['link'],PATHINFO_FILENAME) )
{
    $options['link'] = sanitize_path(pathinfo($page_link,PATHINFO_DIRNAME).'/'.page_filename($page_link));
    $filename        = sanitize_path(CAT_PATH.PAGES_DIRECTORY.'/'.pathinfo($options['link'],PATHINFO_DIRNAME).'/'.page_filename($page_link).PAGE_EXTENSION);
}

// ======================================= 
// ! Update page with new level and link   
// ======================================= 
$result = CAT_Helper_Page::updatePage($page_id,array(
    'root_parent' => $root_parent,
    'page_trail'  => $page_trail,
));
if (!$result)
{
    // try to recover = delete page
    CAT_Helper_Page::deletePage($page_id);
	$ajax	= array(
		'message'	=> $backend->db()->get_error(),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
// Create a new file in the /pages dir
$result = CAT_Helper_Page::createAccessFile($filename, $page_id, $options['level']);
if (!$result)
{
    // try to recover = delete page
    CAT_Helper_Page::deletePage($page_id);
	$ajax	= array(
		'message'	=> $backend->lang()->translate('Error creating access file in the pages directory, cannot open file'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// ========================================== 
// ! Add new record into the sections table   
// ========================================== 
$backend->db()->query("INSERT INTO " . CAT_TABLE_PREFIX . "sections (page_id,position,module,block) VALUES ('$page_id','1', '$module','1')");

// ====================== 
// ! Get the section id   
// ====================== 
$section_id	= $backend->db()->get_one("SELECT LAST_INSERT_ID()");

// ====================================================== 
// ! Include the selected modules add file if it exists   
// ====================================================== 
if ( file_exists(CAT_PATH . '/modules/' . $module . '/add.php') )
{
	require(CAT_PATH . '/modules/' . $module . '/add.php');
}

// ========================================================== 
// ! Check if there is a db error, otherwise say successful   
// ========================================================== 
if ( $backend->db()->is_error() )
{
	$ajax	= array(
		'message'	=> $backend->db()->get_error(),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
else
{
    // print success and redirect
	$ajax	= array(
		'message'	=> $backend->lang()->translate( 'Page added successfully' ),
		'url'		=> CAT_ADMIN_URL . '/pages/modify.php?page_id='. $page_id,
		'success'	=> true
	);
	print json_encode( $ajax );
	exit();
}
exit();
?>