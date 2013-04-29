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

if (!$users->checkPermission('Pages','pages_settings')){
	header("Location: index.php");
	exit(0);
}


// =============== 
// ! Get page id   
// =============== 
$page_id = $val->get('_REQUEST','page_id','numeric');
if ( !$page_id )
{
	header("Location: index.php");
	exit(0);
}

require_once(CAT_PATH.'/framework/functions-utf8.php');

global $parser;
$tpl_data = array();


// =============================================================== 
// ! Get perms & Check if there is an error and get page details   
// =============================================================== 
$results		= $backend->db()->query(sprintf(
    'SELECT * FROM `%spages` WHERE `page_id` = %d',
    CAT_TABLE_PREFIX,$page_id
));
$results_array	= $results->fetchRow(MYSQL_ASSOC);

if ( $backend->db()->is_error() )
{
	$backend->print_error( $backend->db()->get_error() );
}
if ( $results->numRows() == 0 )
{
	$backend->print_error('Page not found');
}

$old_admin_groups	= explode(',', $results_array['admin_groups']);
$old_admin_users	= explode(',', $results_array['admin_users']);
$in_old_group = false;
foreach ( $users->get_groups_id() as $cur_gid )
{
	if ( in_array($cur_gid, $old_admin_groups) )
	{
		$in_old_group = true;
	}
}
if ( !$in_old_group && !is_numeric(array_search($users->get_user_id(), $old_admin_users)) )
{
	$backend->print_error('You do not have permissions to modify this page');
}


// ========================================================= 
// ! Get display name of person who last modified the page   
// ========================================================= 
$user						    = $users->get_user_details( $results_array['modified_by'] );

// ============================================= 
// ! Add result_array to the template variable   
// ============================================= 
$tpl_data['CUR_TAB']                   = 'settings';
$tpl_data['PAGE_HEADER']        = $backend->lang()->translate('Modify Page Settings');
$tpl_data['PAGE_ID']					= $results_array['page_id'];
$tpl_data['PAGE_TITLE']				= $results_array['page_title'];
$tpl_data['PAGE_GROUPS']			    = $results_array['page_groups'];
$tpl_data['PAGE_LINK']		    = $backend->page_link($results_array['link']);
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
$query_sections	= $backend->db()->query(sprintf(
    'SELECT `section_id` FROM `%ssections` WHERE `page_id`=%d AND `module`="menu_link"',
    CAT_TABLE_PREFIX, $page_id
));
$tpl_data['MANAGE_SECTIONS']
    = ( ( $query_sections->numRows() == 0 ) && CAT_Registry::get('MANAGE_SECTIONS') == 'enabled' )
    ? $backend->lang()->translate('Manage Sections')
    : false;

// ====================================== 
// Get Page Extension (Filename Suffix)   
// ====================================== 
$settings					= $backend->db()->query(sprintf(
    "SELECT value FROM `%ssettings` WHERE name = 'page_extension'",
    CAT_TABLE_PREFIX
));
$settings_array					= $settings->fetchRow( MYSQL_ASSOC );
$tpl_data['PAGE_EXTENSION']	= $settings_array['value'];

// ============================================== 
// ! Add variables for viewers- and admins-groups
// ============================================== 
$backend_groups		= explode(',', str_replace('_', '', $results_array['admin_groups']));
$viewing_groups		= explode(',', str_replace('_', '', $results_array['viewing_groups']));

//$get_groups			= $backend->db()->query('SELECT * FROM `'.CAT_TABLE_PREFIX.'groups`');
// ================================= 
// ! Add permissions to $tpl_data   
// ================================= 
$tpl_data['permission']['pages']		  = $users->checkPermission('Pages','pages') ? true : false;
$tpl_data['permission']['pages_add']	  = $users->checkPermission('Pages','pages_add') ? true : false;
$tpl_data['permission']['pages_add_l0']	  = $users->checkPermission('Pages','pages_add_l0') ? true : false;
$tpl_data['permission']['pages_modify']	  = $users->checkPermission('Pages','pages_modify') ? true : false;
$tpl_data['permission']['pages_delete']	  = $users->checkPermission('Pages','pages_delete') ? true : false;
$tpl_data['permission']['pages_settings'] = $users->checkPermission('Pages','pages_settings') ? true : false;
$tpl_data['permission']['pages_intro']	  = ( $users->checkPermission('Pages','pages_intro') != true || INTRO_PAGE != 'enabled' ) ? false : true;

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
$tpl_data['page_list']			= CAT_Helper_Page::getPages();
$tpl_data['groups']				= $users->get_groups( $viewing_groups, $backend_groups );
$tpl_data['templates']				= $addons->get_addons( $results_array['template'] , 'template', 'template' );
$tpl_data['TEMPLATE_MENU']		= CAT_Helper_Template::get_template_menus( $results_array['template'], $results_array['menu'] );

// ==================== 
// ! Parse the header 	
// ==================== 
$parser->output('backend_pages_settings', $tpl_data);

// ====================== 
// ! Print admin footer   
// ====================== 
$backend->print_footer();

?>