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

$backend = CAT_Backend::getInstance('Pages','pages_settings',false);
$users   = CAT_Users::getInstance();

header('Content-type: application/json');

if ( !$users->checkPermission('Pages','pages_settings') )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You don\'t have the permission to change page settings.'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// =============== 
// ! Get page id   
// =============== 
$val     = CAT_Helper_Validate::getInstance();

// ===============
// ! Get page id
// ===============
$page_id = $val->get('_REQUEST','page_id','numeric');
if ( !$page_id )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You sent an invalid value'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

require_once( CAT_PATH . '/framework/functions-utf8.php' );


// =============================================================== 
// ! Get perms & Check if there is an error and get page details   
// =============================================================== 
$results		= $backend->db()->query(sprintf(
    'SELECT * FROM `%spages` WHERE `page_id` = %d',
    CAT_TABLE_PREFIX, $page_id
));

if ( $backend->db()->is_error() )
{
	$ajax	= array(
		'message'	=> $backend->db()->get_error(),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
if ( $results->numRows() == 0 )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate( 'Page not found' ),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$results_array	    = $results->fetchRow(MYSQL_ASSOC);
$old_admin_groups	= explode(',', $results_array['admin_groups']);
$old_admin_users	= explode(',', $results_array['admin_users']);
$in_old_group		= false;

foreach ( $users->get_groups_id() as $cur_gid )
{
	if ( in_array($cur_gid, $old_admin_groups) )
	{
		$in_old_group	= true;
	}
}
if ( !$in_old_group && !is_numeric(array_search($users->get_user_id(), $old_admin_users)) )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate( 'You do not have permissions to modify this page' ),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// ========================================================= 
// ! Get display name of person who last modified the page   
// ========================================================= 
$user							= $users->get_user_details( $results_array['modified_by'] );

// ================================= 
// ! Add permissions to $data_dwoo   
// ================================= 
$permission['pages']			= $users->checkPermission('Pages','pages')          ? true : false;
$permission['pages_add']		= $users->checkPermission('Pages','pages_add')      ? true : false;
$permission['pages_add_l0']		= $users->checkPermission('Pages','pages_add_l0')   ? true : false;
$permission['pages_modify']		= $users->checkPermission('Pages','pages_modify')   ? true : false;
$permission['pages_delete']		= $users->checkPermission('Pages','pages_delete')   ? true : false;
$permission['pages_settings']	= $users->checkPermission('Pages','pages_settings') ? true : false;
$permission['pages_intro']		= ( $users->checkPermission('Pages','pages_intro') != true || INTRO_PAGE != 'enabled' ) ? false : true;

// list of all pages for dropdown, sorted by parent->child
$pages = CAT_Helper_ListBuilder::sort(CAT_Helper_Page::getPages(),0);

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
	'modified_when'				=> ($results_array['modified_when'] != 0) ? CAT_Helper_DateTime::getDate($results_array['modified_when']) : 'Unknown',
	'searching'					=> $results_array['searching'] == 0 ? false : true,
	'visibility'				=> $results_array['visibility'],

	'display_name'				=> $user['display_name'],
	'username'					=> $user['username'],

	'DISPLAY_MENU_LIST'			=> MULTIPLE_MENUS	!= false ? true : false,
	'DISPLAY_LANGUAGE_LIST'		=> PAGE_LANGUAGES	!= false ? true : false,
	'DISPLAY_SEARCHING'			=> SEARCH			!= false ? true : false,

	'admin_groups'				=> explode(',', str_replace('_', '', $results_array['admin_groups']) ),
	'viewing_groups'			=> explode(',', str_replace('_', '', $results_array['viewing_groups']) ),

	'parent_list'				=> $pages,
	'PAGE_EXTENSION'			=> $backend->db()->get_one(sprintf("SELECT value FROM `%ssettings` WHERE name = 'page_extension'",CAT_TABLE_PREFIX)),
);

// ==================== 
// ! Return values 	
// ==================== 

print json_encode( $ajax );
exit();
?>