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

$backend = CAT_Backend::getInstance('Pages','pages_add',false);
$users   = CAT_Users::getInstance();
$val   = CAT_Helper_Validate::getInstance();

header('Content-type: application/json');

if ( ! $users->checkPermission('pages','pages_add',false) )
{
	$ajax	= array(
		'message'	=>$backend->lang()->translate('You don\'t have the permission to add a page.'),
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
    'parent'         => ( $val->sanitizePost('parent','integer',true) ? $val->sanitizePost('parent','integer',true) : 0 ),
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
			'message'	=> $backend->lang()->translate('You do not have permissions to modify this page.'),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}
}
elseif ( ! $users->checkPermission('pages_add_l0','system',false) )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You do not have permissions to modify this page'),
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
			'message'	=> $backend->lang()->translate( 'You do not have permissions to modify this page' ),
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
			'message'	=> $backend->lang()->translate( 'You do not have permissions to modify this page' ),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}
}

$options['admin_groups']		= implode(',', $options['admin_groups']);
$options['viewing_groups']		= implode(',', $options['viewing_groups']);

// ====================================================== 
// ! Work-out what the link and page filename should be   
// ====================================================== 
if ( !$options['parent'] || $options['parent'] == '0' )
{
	$link = '/'.page_filename($options['menu_title']);

	// =================================================================================================================== 
	// ! rename menu titles: index && intro to prevent clashes with intro page feature and WB core file /pages/index.php   
	// =================================================================================================================== 
	if( $link == '/index' || $link == '/intro' )
	{
		$link	.= '_0';
		$filename	= CAT_PATH . PAGES_DIRECTORY .'/' . page_filename($options['menu_title']) . '_0' . PAGE_EXTENSION;
	}
	else
	{
		$filename	= CAT_PATH . PAGES_DIRECTORY . '/' . page_filename($options['menu_title']) . PAGE_EXTENSION;
	}
}
else
{
/*
		$options['parent']_section	= '';
	$options['parent']_titles	= array_reverse( get_parent_titles($options['parent']) );
	foreach ( $options['parent']_titles as $options['parent']_title )
	{
		$options['parent']_section	.= page_filename($options['parent']_title).'/';
	}
	if($options['parent']_section == '/') { $options['parent']_section = ''; }
	$link = '/' . $options['parent']_section . page_filename($options['menu_title']);
	$filename = CAT_PATH . PAGES_DIRECTORY . '/' . $options['parent']_section . page_filename($options['menu_title']) . PAGE_EXTENSION;
	make_dir(CAT_PATH . PAGES_DIRECTORY.'/'.$options['parent']_section);
	
	$source = CAT_ADMIN_PATH . "/pages/master_index.php";
	copy( $source, CAT_PATH . PAGES_DIRECTORY . '/' . $options['parent']_section . "/index.php" );
*/
}

// ================================================== 
// ! Check if a page with same page filename exists   
// ================================================== 
$get_same_page = $backend->db()->query(sprintf(
    "SELECT page_id FROM `%spages` WHERE link = '%s'",
    CAT_TABLE_PREFIX, $link
));
if ( $get_same_page->numRows() > 0 || file_exists(CAT_PATH . PAGES_DIRECTORY.$link.PAGE_EXTENSION) || file_exists(CAT_PATH . PAGES_DIRECTORY.$link.'/') )
{
	$ajax	= array(
		'message'	=>$backend->lang()->translate( 'A page with the same or similar title exists' ),
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
		'message'	=> $backend->db()->get_error(),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}


// Work out level
$level			= level_count($page_id);
// Work out root parent
$root_parent	= root_parent($page_id);
// Work out page trail
$page_trail		= get_page_trail($page_id);

// =========================================================
// ! Set page_link
// =========================================================
if ( $page_link && $page_link != pathinfo($link,PATHINFO_FILENAME) )
{
    $link     = sanitize_path(pathinfo($link,PATHINFO_DIRNAME).'/'.page_filename($page_link));
    $filename = sanitize_path(CAT_PATH.PAGES_DIRECTORY.'/'.pathinfo($link,PATHINFO_DIRNAME).'/'.page_filename($page_link).PAGE_EXTENSION);
}

// ======================================= 
// ! Update page with new level and link   
// ======================================= 
$sql	 = 'UPDATE `%spages` SET ';
$sql	.= '`root_parent` = '.$root_parent.', ';
$sql	.= '`level` = "'.$level.'", ';
$sql	.= '`link` = "'.$link.'", ';
$sql	.= '`page_trail` = "'.$page_trail.'"';
$sql	.= 'WHERE `page_id` = '.$page_id;
$backend->db()->query(sprintf($sql,CAT_TABLE_PREFIX));

if ( $backend->db()->is_error() )
{
	$ajax	= array(
		'message'	=> $backend->db()->get_error(),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
// Create a new file in the /pages dir
create_access_file($filename, $page_id, $level);

// add position 1 to new page
$position	= 1;

// ========================================== 
// ! Add new record into the sections table   
// ========================================== 
$backend->db()->query("INSERT INTO " . CAT_TABLE_PREFIX . "sections (page_id,position,module,block) VALUES ('$page_id','$position', '$module','1')");

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
	$ajax	= array(
		'message'	=>$backend->lang()->translate( 'Page added successfully' ),
		'url'		=> CAT_ADMIN_URL . '/pages/modify.php?page_id='. $page_id,
		'success'	=> true
	);
	print json_encode( $ajax );
	exit();
}
exit();
?>