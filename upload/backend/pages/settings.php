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
 *
 */
 

// include class.secure.php to protect this file and the whole CMS!
if (defined('CAT_PATH')) {
	include(CAT_PATH.'/framework/class.secure.php');
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
require_once(CAT_PATH.'/framework/class.admin.php');
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

require_once(CAT_PATH.'/framework/functions-utf8.php');

global $parser;
$tpl_data = array();


// =============================================================== 
// ! Get perms & Check if there is an error and get page details   
// =============================================================== 
$results		= $database->query('SELECT * FROM `' . CAT_TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $page_id);
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
$tpl_data['CUR_TAB']                   = 'settings';
$tpl_data['PAGE_HEADER']               = $admin->lang->translate('Modify Page Settings');
$tpl_data['PAGE_ID']					= $results_array['page_id'];
$tpl_data['PAGE_TITLE']				= $results_array['page_title'];
$tpl_data['PAGE_GROUPS']			    = $results_array['page_groups'];
$tpl_data['PAGE_LINK']					= $admin->page_link($results_array['link']);
$tpl_data['PAGE_SHORT_LINK']			= substr( $results_array['link'], strripos( $results_array['link'], '/' ) + 1 );
$tpl_data['MENU_TITLE']				= $results_array['menu_title'];
$tpl_data['DESCRIPTION']				= $results_array['description'];
$tpl_data['KEYWORDS']					= $results_array['keywords'];
$tpl_data['VISIBILITY']				= $results_array['visibility'];
$tpl_data['TOP_SELECTED']				= $results_array['target'];
$tpl_data['MODIFIED_WHEN']		  = ($results_array['modified_when'] != 0)
                                  ? $modified_ts = CAT_Helper_DateTime::getDateTime($results_array['modified_when'])
                                  : false;
$tpl_data['SEARCHING_DISABLED']		= $results_array['searching'] == 0 ? true : false;

$tpl_data['MODIFIED_BY']				= $user['display_name'];
$tpl_data['MODIFIED_BY_USERNAME']		= $user['username'];

$tpl_data['DISPLAY_MENU_LIST']			= MULTIPLE_MENUS	!= false ? true : false;
$tpl_data['DISPLAY_LANGUAGE_LIST']		= PAGE_LANGUAGES	!= false ? true : false;
$tpl_data['DISPLAY_SEARCHING']			= SEARCH			!= false ? true : false;

// ========================================================= 
// ! Work-out if we should show the "manage sections" link   
// ========================================================= 
$query_sections	= $database->query('SELECT `section_id` FROM `'.CAT_TABLE_PREFIX.'sections` WHERE `page_id`='.$page_id.' AND `module`="menu_link"');
$tpl_data['MANAGE_SECTIONS']			= ( ( $query_sections->numRows() == 0 ) && MANAGE_SECTIONS == 'enabled' ) ? $HEADING['MANAGE_SECTIONS'] : false;

// ====================================== 
// Get Page Extension (Filename Suffix)   
// ====================================== 
$settings						= $database->query( "SELECT value FROM ".CAT_TABLE_PREFIX."settings WHERE name = 'page_extension'" );
$settings_array					= $settings->fetchRow( MYSQL_ASSOC );
$tpl_data['PAGE_EXTENSION']	= $settings_array['value'];

// ============================================== 
// ! Add variables for viewers- and admins-groups
// ============================================== 
$admin_groups		= explode(',', str_replace('_', '', $results_array['admin_groups']));
$viewing_groups		= explode(',', str_replace('_', '', $results_array['viewing_groups']));

//$get_groups			= $database->query('SELECT * FROM `'.CAT_TABLE_PREFIX.'groups`');
// ================================= 
// ! Add permissions to $tpl_data   
// ================================= 
$tpl_data['permission']['pages']			= $admin->get_permission('pages') ? true : false;
$tpl_data['permission']['pages_add']		= $admin->get_permission('pages_add') ? true : false;
$tpl_data['permission']['pages_add_l0']	= $admin->get_permission('pages_add_l0') ? true : false;
$tpl_data['permission']['pages_modify']	= $admin->get_permission('pages_modify') ? true : false;
$tpl_data['permission']['pages_delete']	= $admin->get_permission('pages_delete') ? true : false;
$tpl_data['permission']['pages_settings']	= $admin->get_permission('pages_settings') ? true : false;
$tpl_data['permission']['pages_intro']		= ( $admin->get_permission('pages_intro') != true || INTRO_PAGE != 'enabled' ) ? false : true;

// ================== 
// ! Templates list   
// ==================

require CAT_PATH . '/framework/CAT/Pages.php';
$pg = CAT_Pages::getInstance($page_id);

$pg->current_page['id']					= $page_id;
$pg->current_page['parent']				= $results_array['parent'];

require CAT_PATH.'/framework/CAT/Helper/Addons.php';
$addons = CAT_Helper_Addons::getInstance();

// ========================== 
// ! Insert language values   
// ========================== 
if ( PAGE_LANGUAGES != false )
{
	$tpl_data['LANGUAGES']			= $addons->get_addons( $results_array['language'] , 'language' );
}

// ====================================================== 
// ! Work-out if we should check for existing page_code   
// ====================================================== 
/* 
 ** Copied from old  - not sure, for what we need this code
 */
$field_sql							= $database->query('DESCRIBE `'.CAT_TABLE_PREFIX.'pages` `page_code`');
$field_set							= $field_sql->numRows();

$tpl_data['page_list']				= $pg->pages_list(0 , 0);
$tpl_data['groups']				= $admin->users->get_groups( $viewing_groups, $admin_groups );
$tpl_data['templates']				= $addons->get_addons( $results_array['template'] , 'template', 'template' );
$tpl_data['TEMPLATE_MENU']			= $pg->get_template_menus( $results_array['template'], $results_array['menu'] );

// ==================== 
// ! Parse the header 	
// ==================== 
$parser->output('backend_pages_settings.lte', $tpl_data);

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>