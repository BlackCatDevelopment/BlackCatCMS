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
 */
 
// include class.secure.php to protect this file and the whole CMS!
if (defined('CAT_PATH')) {
	include(CAT_PATH . '/framework/class.secure.php');
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

require_once(CAT_PATH . '/framework/class.admin.php');
$admin	= new admin('Pages', 'pages_add', false );

header('Content-type: application/json');

if ( !$admin->get_permission('pages_add') )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('You don\'t have the permission to add a page.'),
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
$page_title			= htmlspecialchars($admin->get_post_escaped('page_title') );
$menu_title			= htmlspecialchars($admin->get_post_escaped('menu_title') );
$description		= htmlspecialchars($admin->add_slashes($admin->get_post('description')) );
$keywords			= htmlspecialchars($admin->add_slashes($admin->get_post('keywords')) );
$parent				= $admin->get_post_escaped('parent');
$target				= $admin->get_post_escaped('target');
$template			= $admin->get_post_escaped('template');
$menu				= ( $admin->get_post_escaped('menu') != '') ? $admin->get_post_escaped('menu') : 1;
$language			= $admin->get_post_escaped('language');
$visibility			= $admin->get_post_escaped('visibility');
$searching			= $admin->get_post_escaped('searching') ? '1' : '0';
$admin_groups		= $admin->get_post_escaped('admin_groups');
$viewing_groups		= $admin->get_post_escaped('viewing_groups');
$module				= $admin->get_post('type');

// ============================= 
// ! add Admin and view groups   
// ============================= 
$admin_groups[]		= 1;
$viewing_groups[]	= 1;

// ============================================== 
// ! Check if user has permission to add a page   
// ============================================== 
if ( $parent != 0 )
{
	if ( !$admin->get_page_permission( $parent,'admin' ) )
	{
		$ajax	= array(
			'message'	=>  $admin->lang->translate('You do not have permissions to modify this page.'),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}
}
elseif ( !$admin->get_permission('pages_add_l0','system') )
{
	$ajax	= array(
		'message'	=>  $admin->lang->translate('You do not have permissions to modify this page'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// ======================= 
// ! Validate menu_title   
// ======================= 
if ($menu_title == '' || substr($menu_title,0,1)=='.')
{
	$ajax	= array(
		'message'	=>  $admin->lang->translate( 'Please enter a page title' ),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// ========================================================= 
// ! Set page_title to menu_title if page_title is not set   
// ========================================================= 
$page_title		= $page_title == '' ? $menu_title : $page_title;

// ======================================================= 
// ! Check to see if page created has needed permissions   
// ======================================================= 
if ( !in_array(1, $admin->get_groups_id()) )
{
	$admin_perm_ok = false;

	foreach ($admin_groups as $adm_group)
	{
		if ( in_array( $adm_group, $admin->get_groups_id() ) )
		{
			$admin_perm_ok = true;
		}
	}
	if ( $admin_perm_ok == false )
	{
		$ajax	= array(
			'message'	=>  $admin->lang->translate( 'You do not have permissions to modify this page' ),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}

	$admin_perm_ok = false;

	foreach ($viewing_groups as $view_group)
	{
		if ( in_array( $view_group, $admin->get_groups_id() ) )
		{
			$admin_perm_ok = true;
		}
	}
	if ($admin_perm_ok == false)
	{
		$ajax	= array(
			'message'	=>  $admin->lang->translate( 'You do not have permissions to modify this page' ),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}
}

$admin_groups		= implode(',', $admin_groups);
$viewing_groups		= implode(',', $viewing_groups);

// ====================================================== 
// ! Work-out what the link and page filename should be   
// ====================================================== 
if ( $parent == '0' )
{
	$link = '/'.page_filename($menu_title);
	// =================================================================================================================== 
	// ! rename menu titles: index && intro to prevent clashes with intro page feature and WB core file /pages/index.php   
	// =================================================================================================================== 
	if( $link == '/index' || $link == '/intro' )
	{
		$link	.= '_0';
		$filename	= CAT_PATH . PAGES_DIRECTORY .'/' . page_filename($menu_title) . '_0' . PAGE_EXTENSION;
	}
	else
	{
		$filename	= CAT_PATH . PAGES_DIRECTORY . '/' . page_filename($menu_title) . PAGE_EXTENSION;
	}
}
else
{
	$parent_section		= '';
	$parent_titles		 = array_reverse( get_parent_titles($parent) );
	foreach ( $parent_titles as $parent_title )
	{
		$parent_section	.= page_filename($parent_title).'/';
	}
	if($parent_section == '/') { $parent_section = ''; }
	$link = '/' . $parent_section . page_filename($menu_title);
	$filename = CAT_PATH . PAGES_DIRECTORY . '/' . $parent_section . page_filename($menu_title) . PAGE_EXTENSION;
	make_dir(CAT_PATH . PAGES_DIRECTORY.'/'.$parent_section);
	
	/**
	 *
	 */
	$source = CAT_ADMIN_PATH . "/pages/master_index.php";
	copy( $source, CAT_PATH . PAGES_DIRECTORY . '/' . $parent_section . "/index.php" );
}

// ================================================== 
// ! Check if a page with same page filename exists   
// ================================================== 
$get_same_page = $database->query("SELECT page_id FROM ".CAT_TABLE_PREFIX."pages WHERE link = '$link'");
if ( $get_same_page->numRows() > 0 || file_exists(CAT_PATH . PAGES_DIRECTORY.$link.PAGE_EXTENSION) || file_exists(CAT_PATH . PAGES_DIRECTORY.$link.'/') )
{
	$ajax	= array(
		'message'	=> $admin->lang->translate( 'A page with the same or similar title exists' ),
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
$order->clean($parent);
// Get new order
$position = $order->get_new($parent);

// ================================================================================================ 
// ! Work-out if the page parent (if selected) has a seperate template or language to the default   
// ================================================================================================ 
if ( $language == '' || $template == '')
{
	$query_parent = $database->query("SELECT template, language FROM ".CAT_TABLE_PREFIX."pages WHERE page_id = '$parent'");
	if ( $query_parent->numRows() > 0 )
	{
		$fetch_parent		= $query_parent->fetchRow();
		$template			= ( $template == '' ) ? $fetch_parent['template'] : $template;
		$language			= ( $language == '' ) ? $fetch_parent['language'] : $language;
	}
	else
	{
		$fetch_parent		= $query_parent->fetchRow();
		$template			= ( $template == '' ) ? '' : $template;
		$language			= ( $language == '' ) ? DEFAULT_LANGUAGE : $language;
	}
}

// ================================ 
// ! Insert page into pages table   
// ================================ 
$sql	 = 'INSERT INTO `'.CAT_TABLE_PREFIX.'pages` SET ';
$sql	.= '`parent` = '.$parent.', ';
$sql	.= '`target` = "'.$target.'", ';
$sql	.= '`page_title` = "'.$page_title.'", ';
$sql	.= '`menu_title` = "'.$menu_title.'", ';
$sql	.= '`description` = "'.$description.'", ';
$sql	.= '`keywords` = "'.$keywords.'", ';
$sql	.= '`template` = "'.$template.'", ';
$sql	.= '`visibility` = "'.$visibility.'", ';
$sql	.= '`position` = '.$position.', ';
$sql	.= '`menu` = '.$menu.', ';
$sql	.= '`language` = "'.$language.'", ';
$sql	.= '`searching` = '.$searching.', ';
$sql	.= '`modified_when` = '.time().', ';
$sql	.= '`modified_by` = '.$admin->get_user_id().', ';
$sql	.= '`admin_groups` = "'.$admin_groups.'", ';
$sql	.= '`viewing_groups` = "'.$viewing_groups.'"';

$database->query($sql);

if ( $database->is_error() )
{
	$ajax	= array(
		'message'	=> $database->get_error(),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// Get the page id
$page_id		= $database->get_one("SELECT LAST_INSERT_ID()");
// Work out level
$level			= level_count($page_id);
// Work out root parent
$root_parent	= root_parent($page_id);
// Work out page trail
$page_trail		= get_page_trail($page_id);

// ======================================= 
// ! Update page with new level and link   
// ======================================= 
$sql	 = 'UPDATE `'.CAT_TABLE_PREFIX.'pages` SET ';
$sql	.= '`root_parent` = '.$root_parent.', ';
$sql	.= '`level` = '.$level.', ';
$sql	.= '`link` = "'.$link.'", ';
$sql	.= '`page_trail` = "'.$page_trail.'"';
$sql	.= 'WHERE `page_id` = '.$page_id;
$database->query($sql);

if ( $database->is_error() )
{
	$ajax	= array(
		'message'	=> $database->get_error(),
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
$database->query("INSERT INTO " . CAT_TABLE_PREFIX . "sections (page_id,position,module,block) VALUES ('$page_id','$position', '$module','1')");

// ====================== 
// ! Get the section id   
// ====================== 
$section_id	= $database->get_one("SELECT LAST_INSERT_ID()");

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
		'message'	=> $admin->lang->translate( 'Page added successfully' ),
		'url'		=> CAT_ADMIN_URL . '/pages/modify.php?page_id='. $page_id,
		'success'	=> true
	);
	print json_encode( $ajax );
	exit();
}
exit();
?>