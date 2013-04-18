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

$users = CAT_Users::getInstance();
$val   = CAT_Helper_Validate::getInstance();

// =================================================== 
// ! Include the class.admin.php and WB functions file
// =================================================== 
require_once(CAT_PATH.'/framework/class.admin.php');
$admin = new admin('Pages', 'pages_settings');

if (!$users->checkPermission('pages','pages_settings')){
	header("Location: index.php");
	exit(0);
}

$page_id = $val->sanitizeGet('page_id','numeric');

// =============== 
// ! Get page id   
// =============== 
if ( ! $page_id )
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
$results		= $users->db()->query('SELECT * FROM `' . CAT_TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $page_id);
$results_array	= $results->fetchRow( MYSQL_ASSOC );

if ( $users->db()->is_error() )
{
	$admin->print_error( $users->db()->get_error() );
}
if ( $results->numRows() == 0 )
{
	$admin->print_error('Page not found');
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
	$admin->print_error('You do not have permissions to modify this page');
}

//
// ! delete link
//
if ( $admin->get_get('del') )
{
    list( $lang, $page ) = explode( '_', $val->sanitizeGet('del') );
    $users->db()->query( 'DELETE FROM '.CAT_TABLE_PREFIX.'page_langs WHERE link_page_id = "'.$page.'" AND lang = "'.$lang.'"' );
}

$arrh = CAT_Helper_Array::getInstance();

// ===========================
// ! find already linked pages
// ===========================
$items = CAT_Pages::getInstance($page_id)->getLinkedByLanguage($page_id);


// =========================
// ! get installed languages
// =========================
$addons = CAT_Helper_Addons::getInstance();
$avail  = $addons->get_addons( $results_array['language'] , 'language' );
// skip current lang
foreach( $avail as $i => &$l )
{
    if ( $l['VALUE'] == $results_array['language'] )
    {
        unset($avail[$i]);
        break;
    }
}
// remove already linked languages
if(is_array($items) && count($items)) {
    foreach($items as $item)
    {
    $arrh->ArrayRemove( $item['lang'], $avail, 'VALUE' );
    }
}

// ===========
// ! get pages
// ===========
$pages_list = $admin->pg->getPages();
// skip current page
$arrh->ArrayRemove( $page_id, $pages_list, 'page_id' );
// skip already linked pages
if(is_array($items) && count($items))
{
    foreach($items as $item)
    {
    $arrh->ArrayRemove( $item['link_page_id'], $pages_list, 'page_id' );
    }
}
// skip deleted pages
$deleted = $admin->pg->getPages('deleted');
foreach($deleted as $item)
{
    $arrh->ArrayRemove( $item['page_id'], $pages_list, 'page_id' );
}

// =========================================================
// ! Get display name of person who last modified the page
// =========================================================
$user							  = $users->get_user_details( $results_array['modified_by'] );

// =============================================
// ! Add result_array to the template variable
// =============================================
$tpl_data['CUR_TAB']              = 'lang';
$tpl_data['PAGE_HEADER']          = $admin->lang->translate('Modify language mappings');
$tpl_data['PAGE_ID']			  = $page_id;
$tpl_data['PAGE_LINK']			  = $admin->page_link($results_array['link']);
$tpl_data['PAGE_TITLE']			  = $results_array['page_title'];
$tpl_data['AVAILABLE_LANGS']      = $avail;
$tpl_data['AVAILABLE_PAGES']      = $pages_list;
$tpl_data['PAGE_LINKS']           = ( ( is_array($items) && count($items) ) ? $items : NULL );

$tpl_data['MODIFIED_BY']		  = $user['display_name'];
$tpl_data['MODIFIED_BY_USERNAME'] = $user['username'];
$tpl_data['MODIFIED_WHEN']		  = ($results_array['modified_when'] != 0)
                                  ? $modified_ts = CAT_Helper_DateTime::getDateTime($results_array['modified_when'])
                                  : false;

$tpl_data['PAGES'] #
    = CAT_Helper_ListBuilder::getInstance()->config(array('space' => '|-- '))
                                           ->dropdown( '', $pages_list, 0, false, true );


// ====================
// ! Parse the header 	
// ==================== 
$parser->output('backend_pages_lang_settings.tpl', $tpl_data);

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>