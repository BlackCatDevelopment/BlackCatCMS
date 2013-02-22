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
$data_dwoo = array();


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

//
// ! delete link
//
if ( $admin->get_get('del') )
{
    list( $lang, $page ) = explode( '_', $admin->get_get('del') );
    $database->query( 'DELETE FROM '.CAT_TABLE_PREFIX.'page_langs WHERE link_page_id = "'.$page.'" AND lang = "'.$lang.'"' );
}

$arrh = $admin->get_helper('Array');

// ===========================
// ! find already linked pages
// ===========================
$items          = CAT_Pages::getInstance(array('pages_settings'=>$admin->get_permission('pages_settings')))->getLinkedByLanguage($page_id);


// =========================
// ! get installed languages
// =========================
require CAT_PATH.'/framework/CAT/Helper/Addons.php';
$addons = new CAT_Helper_Addons();
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
$pages_list = $admin->pg->make_list();
// skip current page
$arrh->ArrayRemove( $page_id, $pages_list, 'page_id' );
// skip already linked pages
if(is_array($items) && count($items)) {
    foreach($items as $item)
    {
    $arrh->ArrayRemove( $item['link_page_id'], $pages_list, 'page_id' );
    }
}

// =========================================================
// ! Get display name of person who last modified the page
// =========================================================
$user									= $admin->get_user_details( $results_array['modified_by'] );

// =============================================
// ! Add result_array to the template variable
// =============================================
$data_dwoo['CUR_TAB']                   = 'lang';
$data_dwoo['PAGE_HEADER']               = $admin->lang->translate('Modify language mappings');
$data_dwoo['PAGE_ID']					= $page_id;
$data_dwoo['PAGE_LINK']					= $admin->page_link($results_array['link']);
$data_dwoo['PAGE_TITLE']				= $results_array['page_title'];
$data_dwoo['AVAILABLE_LANGS']           = $avail;
$data_dwoo['AVAILABLE_PAGES']           = $pages_list;
$data_dwoo['PAGE_LINKS']                = ( ( is_array($items) && count($items) ) ? $items : NULL );

$data_dwoo['MODIFIED_BY']				= $user['display_name'];
$data_dwoo['MODIFIED_BY_USERNAME']		= $user['username'];
$data_dwoo['MODIFIED_WHEN']				= ($results_array['modified_when'] != 0) ? $modified_ts = date(TIME_FORMAT.', '.DATE_FORMAT, $results_array['modified_when']) : false;


// ====================
// ! Parse the header 	
// ==================== 
$parser->output('backend_pages_lang_settings.lte', $data_dwoo);

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>