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
 * @version         $Id$
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
require_once(LEPTON_PATH.'/framework/class.admin.php');
$admin = new admin('Pages', 'pages_settings');

if (!$admin->get_permission('pages_settings')){
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
	$page_id = $admin->get_get('page_id');
}

require_once(LEPTON_PATH.'/framework/functions-utf8.php');

global $parser;
$data_dwoo = array();


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
	$admin->print_error('Page not found');
}

$old_admin_groups	= explode(',', $results_array['admin_groups']);
$old_admin_users	= explode(',', $results_array['admin_users']);
$in_old_group = false;
foreach ( $admin->get_groups_id() as $cur_gid )
{
	if ( in_array($cur_gid, $old_admin_groups) )
	{
		$in_old_group = true;
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
$data_dwoo['PAGE_ID']					= $results_array['page_id'];
$data_dwoo['PAGE_TITLE']				= $results_array['page_title'];
$data_dwoo['PAGE_LINK']					= $admin->page_link($results_array['link']);
$data_dwoo['PAGE_SHORT_LINK']			= substr( $results_array['link'], strripos( $results_array['link'], '/' ) + 1 );
$data_dwoo['MENU_TITLE']				= $results_array['menu_title'];
$data_dwoo['DESCRIPTION']				= $results_array['description'];
$data_dwoo['KEYWORDS']					= explode( ',', $results_array['keywords']);
$data_dwoo['VISIBILITY']				= $results_array['visibility'];
$data_dwoo['TOP_SELECTED']				= $results_array['target'];
$data_dwoo['MODIFIED_WHEN']				= ($results_array['modified_when'] != 0) ? date(TIME_FORMAT.', '.DATE_FORMAT, $results_array['modified_when']) : 'Unknown';
$data_dwoo['SEARCHING_DISABLED']		= $results_array['searching'] == 0 ? true : false;

$data_dwoo['MODIFIED_BY']				= $user['display_name'];
$data_dwoo['MODIFIED_BY_USERNAME']		= $user['username'];

$data_dwoo['DISPLAY_MENU_LIST']			= MULTIPLE_MENUS	!= false ? true : false;
$data_dwoo['DISPLAY_LANGUAGE_LIST']		= PAGE_LANGUAGES	!= false ? true : false;
$data_dwoo['DISPLAY_SEARCHING']			= SEARCH			!= false ? true : false;

// ========================================================= 
// ! Work-out if we should show the "manage sections" link   
// ========================================================= 
$query_sections	= $database->query('SELECT `section_id` FROM `'.TABLE_PREFIX.'sections` WHERE `page_id`='.$page_id.' AND `module`="menu_link"');
$data_dwoo['MANAGE_SECTIONS']			= ( ( $query_sections->numRows() == 0 ) && MANAGE_SECTIONS == 'enabled' ) ? $HEADING['MANAGE_SECTIONS'] : false;

// ====================================== 
// Get Page Extension (Filename Suffix)   
// ====================================== 
$settings						= $database->query( "SELECT value FROM ".TABLE_PREFIX."settings WHERE name = 'page_extension'" );
$settings_array					= $settings->fetchRow( MYSQL_ASSOC );
$data_dwoo['PAGE_EXTENSION']	= $settings_array['value'];

// ============================================== 
// ! Add variables for viewers- and admins-groups
// ============================================== 
$admin_groups		= explode(',', str_replace('_', '', $results_array['admin_groups']));
$viewing_groups		= explode(',', str_replace('_', '', $results_array['viewing_groups']));

//$get_groups			= $database->query('SELECT * FROM `'.TABLE_PREFIX.'groups`');
// ================================= 
// ! Add permissions to $data_dwoo   
// ================================= 
$data_dwoo['permission']['pages']			= $admin->get_permission('pages') ? true : false;
$data_dwoo['permission']['pages_add']		= $admin->get_permission('pages_add') ? true : false;
$data_dwoo['permission']['pages_add_l0']	= $admin->get_permission('pages_add_l0') ? true : false;
$data_dwoo['permission']['pages_modify']	= $admin->get_permission('pages_modify') ? true : false;
$data_dwoo['permission']['pages_delete']	= $admin->get_permission('pages_delete') ? true : false;
$data_dwoo['permission']['pages_settings']	= $admin->get_permission('pages_settings') ? true : false;
$data_dwoo['permission']['pages_intro']		= ( $admin->get_permission('pages_intro') != true || INTRO_PAGE != 'enabled' ) ? false : true;

// ================== 
// ! Templates list   
// ================== 
require_once(LEPTON_PATH . '/framework/class.pages.php');
$pages = new pages( $data_dwoo['permission'] );

$pages->current_page['id']					= $page_id;
$pages->current_page['parent']				= $results_array['parent'];



// ========================== 
// ! Insert language values   
// ========================== 
if ( PAGE_LANGUAGES != false )
{
	$data_dwoo['LANGUAGES']			= $pages->get_addons( $results_array['language'] , 'language' );
}

// ====================================================== 
// ! Work-out if we should check for existing page_code   
// ====================================================== 
/* 
 ** Copied from old  - not sure, for what we need this code
 */
$field_sql							= $database->query('DESCRIBE `'.TABLE_PREFIX.'pages` `page_code`');
$field_set							= $field_sql->numRows();

$data_dwoo['page_list']				= $pages->pages_list(0 , 0);
$data_dwoo['groups']				= $pages->get_groups( $viewing_groups, $admin_groups );
$data_dwoo['templates']				= $pages->get_addons( $results_array['template'] , 'template', 'template' );
$data_dwoo['TEMPLATE_MENU']			= $pages->get_template_menus( $results_array['template'], $results_array['menu'] );

// ==================== 
// ! Parse the header 	
// ==================== 
$parser->output('backend_pages_settings.lte', $data_dwoo);

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>