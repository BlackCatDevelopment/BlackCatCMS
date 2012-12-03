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

// =================================================== 
// ! Include the class.admin.php and WB functions file
// =================================================== 
require_once ( LEPTON_PATH . '/framework/class.admin.php' );
$admin		= new admin('Pages', 'pages_settings', false);

if ( !$admin->get_permission('pages_settings') )
{
	header("Location: index.php");
	exit(0);
}

// =============== 
// ! Get page id   
// =============== 
if ( !is_numeric( $admin->get_get('page_id') ) )
{
	header("Location: index.php");
	exit(0);
}
else
{
	$page_id	= $admin->get_get('page_id');
}
header('Content-type: application/json');

require_once( LEPTON_PATH . '/framework/functions-utf8.php' );


// =============================================================== 
// ! Get perms & Check if there is an error and get page details   
// =============================================================== 
$results		= $database->query('SELECT * FROM `' . TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $page_id);
$results_array	= $results->fetchRow( MYSQL_ASSOC );

if ( $database->is_error() )
{
	$admin->print_error( $database->get_error() );
}
if ( $results->numRows() == 0 )
{
	$admin->print_error( 'Page not found' );
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
	$admin->print_error('You do not have permissions to modify this page');
}

// ========================================================= 
// ! Get display name of person who last modified the page   
// ========================================================= 
$user									= $admin->get_user_details( $results_array['modified_by'] );

// ============================================= 
// ! Add result_array to the template variable   
// ============================================= 
$ajax	= array(
		'page_id'					=> $results_array['page_id'],
		'page_title'				=> $results_array['page_title'],
//		'link'						=> $admin->page_link($results_array['link']),
		'short_link'				=> substr( $results_array['link'], strripos( $results_array['link'], '/' ) + 1 ),
		'menu_title'				=> $results_array['menu_title'],
		'parent'					=> $results_array['parent'],
		'description'				=> $results_array['description'],
		'keywords'					=> $results_array['keywords'],//explode( ',', $results_array['keywords']),
		'parent'					=> $results_array['parent'],
		'menu'						=> $results_array['menu'],
		'visibility'				=> $results_array['visibility'],
		'template'					=> $results_array['template'],
		'language'					=> $results_array['language'],
		'target'					=> $results_array['target'],
		'modified_when'				=> ($results_array['modified_when'] != 0) ? date(TIME_FORMAT.', '.DATE_FORMAT, $results_array['modified_when']) : 'Unknown',
		'searching'					=> $results_array['searching'] == 0 ? false : true,
		'visibility'				=> $results_array['visibility'],

		'display_name'				=> $user['display_name'],
		'username'					=> $user['username'],

		'DISPLAY_MENU_LIST'			=> MULTIPLE_MENUS	!= false ? true : false,
		'DISPLAY_LANGUAGE_LIST'		=> PAGE_LANGUAGES	!= false ? true : false,
		'DISPLAY_SEARCHING'			=> SEARCH			!= false ? true : false,

		'admin_groups'				=> explode(',', str_replace('_', '', $results_array['admin_groups'])),
		'viewing_groups'			=> explode(',', str_replace('_', '', $results_array['viewing_groups']))
);

// ====================================== 
// Get Page Extension (Filename Suffix)   
// ====================================== 
$settings						= $database->query( "SELECT value FROM ".TABLE_PREFIX."settings WHERE name = 'page_extension'" );
$settings_array					= $settings->fetchRow( MYSQL_ASSOC );
$ajax['PAGE_EXTENSION']			= $settings_array['value'];


// ==================== 
// ! Return values 	
// ==================== 

print json_encode( $ajax );

?>