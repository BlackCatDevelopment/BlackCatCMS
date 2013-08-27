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

$users   = CAT_Users::getInstance();
$val     = CAT_Helper_Validate::getInstance();
$backend = CAT_Backend::getInstance('Pages', 'pages_settings');

if (!$users->checkPermission('pages','pages_settings')){
	$backend->print_error( 'You do not have permissions to modify this page' );
}

// ===============
// ! Get page id
// ===============
$page_id = $val->sanitizeGet('page_id','numeric');
if ( ! $page_id )
{
	$backend->print_error( 'Missing page ID!' );
}

require_once(CAT_PATH.'/framework/functions-utf8.php');

global $parser;
$tpl_data = array();

// =============================================================== 
// ! Get perms & Check if there is an error and get page details   
// =============================================================== 
$page = CAT_Helper_Page::getPage($page_id);
if(!$page || count($page)==0)
{
	$backend->print_error('Page not found');
}

$old_admin_groups	= explode(',', $page['admin_groups']);
$old_admin_users	= explode(',', $page['admin_users']);
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

//
// ! delete link
//
if ( $val->sanitizeGet('del') )
{
    list( $lang, $page_id ) = explode( '_', $val->sanitizeGet('del') );
    CAT_Helper_Page::deleteLanguageLink($page_id,$lang);
}

$arrh = CAT_Helper_Array::getInstance();

// ===========================
// ! find already linked pages
// ===========================
$items = CAT_Helper_Page::getInstance($page_id)->getLinkedByLanguage($page_id);


// =========================
// ! get installed languages
// =========================
$addons = CAT_Helper_Addons::getInstance();
$avail  = $addons->get_addons( $page['language'] , 'language' );
// skip current lang
foreach( $avail as $i => &$l )
{
    if ( $l['VALUE'] == $page['language'] )
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
$pages_list = CAT_Helper_Page::getPages(CAT_Backend::isBackend());
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
$deleted = CAT_Helper_Page::getPagesByVisibility('deleted');
foreach($deleted as $item)
{
    $arrh->ArrayRemove( $item['page_id'], $pages_list, 'page_id' );
}

// =========================================================
// ! Get display name of person who last modified the page
// =========================================================
$user							  = $users->get_user_details( $page['modified_by'] );

// =============================================
// ! Add result_array to the template variable
// =============================================
$tpl_data['CUR_TAB']              = 'lang';
$tpl_data['PAGE_HEADER']          = $backend->lang()->translate('Modify language mappings');
$tpl_data['PAGE_ID']			  = $page_id;
$tpl_data['PAGE_LINK']			  = CAT_Helper_Page::getLink($page['link']);
$tpl_data['PAGE_TITLE']			  = $page['page_title'];
$tpl_data['AVAILABLE_LANGS']      = $avail;
$tpl_data['AVAILABLE_PAGES']      = $pages_list;
$tpl_data['PAGE_LINKS']           = ( ( is_array($items) && count($items) ) ? $items : NULL );

$tpl_data['MODIFIED_BY']		  = $user['display_name'];
$tpl_data['MODIFIED_BY_USERNAME'] = $user['username'];
$tpl_data['MODIFIED_WHEN']		  = ($page['modified_when'] != 0)
                                  ? $modified_ts = CAT_Helper_DateTime::getDateTime($page['modified_when'])
                                  : false;

$tpl_data['PAGES']
    = CAT_Helper_ListBuilder::getInstance()->reset()
                                           ->config(array('space' => '|-- '))
                                           ->dropdown( '', $pages_list, 0, false, true );


// ====================
// ! Parse the header 	
// ==================== 
$parser->output('backend_pages_lang_settings', $tpl_data);

// ====================== 
// ! Print admin footer   
// ====================== 
$backend->print_footer();

?>