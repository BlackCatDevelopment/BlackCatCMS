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

// =================================================== 
// ! Include the class.admin.php and WB functions file
// =================================================== 
require_once ( CAT_PATH . '/framework/class.admin.php' );
$admin		= new admin('Pages', 'pages_settings', false);

header('Content-type: application/json');

if ( !$admin->get_permission('pages_settings') )
{
	$ajax	= array(
		'message'	=>  $admin->lang->translate('You don\'t have the permission to change page settings.'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// =============== 
// ! Get page id   
// =============== 
if ( !is_numeric( $admin->get_post('page_id') ) )
{
	$ajax	= array(
		'message'	=>  $admin->lang->translate('You send an invalid value'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
else
{
	$page_id	= $admin->get_post('page_id');
}

require_once( CAT_PATH . '/framework/functions-utf8.php' );


// =============================================================== 
// ! Get perms & Check if there is an error and get page details   
// =============================================================== 
$results		= $database->query('SELECT * FROM `' . CAT_TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $page_id);
$results_array	= $results->fetchRow( MYSQL_ASSOC );

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
		'message'	=>  $admin->lang->translate( 'Page not found' ),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$old_admin_groups	= explode(',', $results_array['admin_groups']);
$old_admin_users	= explode(',', $results_array['admin_users']);
$in_old_group		= false;

foreach ( $admin->get_groups_id() as $cur_gid )
{
	if ( in_array($cur_gid, $old_admin_groups) )
	{
		$in_old_group	= true;
	}
}
if ( !$in_old_group && !is_numeric(array_search($admin->get_user_id(), $old_admin_users)) )
{
	$ajax	= array(
		'message'	=>  $admin->lang->translate( 'You do not have permissions to modify this page' ),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// ========================================================= 
// ! Get display name of person who last modified the page   
// ========================================================= 
$user									= $admin->get_user_details( $results_array['modified_by'] );

// ================================= 
// ! Add permissions to $data_dwoo   
// ================================= 
$permission['pages']			= $admin->get_permission('pages') ? true : false;
$permission['pages_add']		= $admin->get_permission('pages_add') ? true : false;
$permission['pages_add_l0']		= $admin->get_permission('pages_add_l0') ? true : false;
$permission['pages_modify']		= $admin->get_permission('pages_modify') ? true : false;
$permission['pages_delete']		= $admin->get_permission('pages_delete') ? true : false;
$permission['pages_settings']	= $admin->get_permission('pages_settings') ? true : false;
$permission['pages_intro']		= ( $admin->get_permission('pages_intro') != true || INTRO_PAGE != 'enabled' ) ? false : true;

require_once(CAT_PATH . '/framework/class.pages.php');
$dropdown	= new pages( $permission );
// list of all parent pages for dropdown parent
$dropdown->current_page['id']	= $page_id;
$dropdown_list		= $dropdown->pages_list( 0 , 0 );


// ============================================= 
// ! Add result_array to the template variable   
// ============================================= 
$ajax	= array(
		'page_id'					=> $results_array['page_id'],
		'page_title'				=> $results_array['page_title'],
		'short_link'				=> substr( $results_array['link'], strripos( $results_array['link'], '/' ) + 1 ),
		'menu_title'				=> $results_array['menu_title'],
		'parent'					=> $results_array['parent'],
		'description'				=> $results_array['description'],
		'keywords'					=> $results_array['keywords'],
		'parent'					=> $results_array['parent'],
		'menu'						=> $results_array['menu'],
		'visibility'				=> $results_array['visibility'],
		'template'					=> $results_array['template'],
		'language'					=> $results_array['language'],
		'target'					=> $results_array['target'],
		'level'						=> $results_array['level'],
		'modified_when'				=> ($results_array['modified_when'] != 0) ? date(TIME_FORMAT.', '.DATE_FORMAT, $results_array['modified_when']) : 'Unknown',
		'searching'					=> $results_array['searching'] == 0 ? false : true,
		'visibility'				=> $results_array['visibility'],

		'display_name'				=> $user['display_name'],
		'username'					=> $user['username'],

		'DISPLAY_MENU_LIST'			=> MULTIPLE_MENUS	!= false ? true : false,
		'DISPLAY_LANGUAGE_LIST'		=> PAGE_LANGUAGES	!= false ? true : false,
		'DISPLAY_SEARCHING'			=> SEARCH			!= false ? true : false,

		'admin_groups'				=> explode(',', str_replace('_', '', $results_array['admin_groups']) ),
		'viewing_groups'			=> explode(',', str_replace('_', '', $results_array['viewing_groups']) ),

		'parent_list'				=> $dropdown_list,
		'PAGE_EXTENSION'			=> $database->get_one("SELECT value FROM " . CAT_TABLE_PREFIX . "settings WHERE name = 'page_extension'")
);

// ==================== 
// ! Return values 	
// ==================== 

print json_encode( $ajax );
exit();
?>