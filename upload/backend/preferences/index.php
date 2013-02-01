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


require_once(CAT_PATH . '/framework/class.admin.php');
$admin = new admin('Preferences');

global $parser;
$data_dwoo	= array();

include_once(CAT_PATH . '/framework/functions-utf8.php');

// ========================= 
// ! Initial page addition   
// ========================= 
require_once( CAT_PATH . '/modules/initial_page/classes/c_init_page.php' );
$ref		= new c_init_page( $database );
$info		= $ref->get_user_info( $_SESSION['USER_ID'] );

$options	= array(
	'pages'			=> true,
	'tools'			=> $_SESSION['GROUP_ID'] == 1 ? true : false,
	'backend_pages' => $_SESSION['GROUP_ID'] == 1 ? true : false
);

$data_dwoo['INIT_PAGE_SELECT']		= $ref->get_single_user_select( $_SESSION['USER_ID'], 'init_page_select', $info['init_page'], $options);
$data_dwoo['INIT_PAGE_LABEL']		= $ref->get_language();
/*
	*
	* FOR WHAT IS THIS NEEDED? - creativecat
	*
*/
# $tpl->set_var('INIT_PAGE_PARAM', $info['page_param']);


// ============================================================= 
// ! read user-info from table users and assign it to template   
// ============================================================= 
$sql  = 'SELECT `display_name`, `username`, `email`, `statusflags` FROM `'.CAT_TABLE_PREFIX.'users` WHERE `user_id` = '.(int)$admin->get_user_id();

$res_user	= $database->query($sql);
if ($res_user->numRows() > 0)
{
	if( ($rec_user = $res_user->fetchRow()) )
	{
		$data_dwoo['DISPLAY_NAME']	= $rec_user['display_name'];
		$data_dwoo['USERNAME']		= $rec_user['username'];
		$data_dwoo['EMAIL']			= $rec_user['email'];
	}
}



/*
	*
	* FOR WHAT IS THIS NEEDED? - creativecat
	*
*/
// =============================== 
// ! insert link to user-profile   
// =============================== 
$data_dwoo['USER_ID']	= $admin->get_user_id();
// $tpl->set_block('main_block', 'show_cmd_profile_edit_block', 'show_cmd_profile_edit');
if ( $admin->bit_isset($rec_user['statusflags'], USERS_PROFILE_ALLOWED) )
{
	$data_dwoo['PROFILE_ACTION_URL']			= CAT_ADMIN_URL.'/profiles/index.php';
	$data_dwoo['show_cmd_profile_edit']			= true;
	$data_dwoo['show_cmd_profile_edit_block']	= true;
}
else
{
	$data_dwoo['show_cmd_profile_edit']			= true;
	$data_dwoo['show_cmd_profile_edit_block']	= false;
}

// ============================================================================ 
// ! read available languages from table addons and assign it to the template   
// ============================================================================ 
require_once(CAT_PATH . '/framework/class.pages.php');
$pages = new pages();
$data_dwoo['languages']				= $pages->get_addons( LANGUAGE , 'language', false, false, 'directory' );

// ================================== 
// ! Insert default timezone values   
// ================================== 
$counter	= 0;
foreach ($timezone_table as $title)
{
	$data_dwoo['timezones'][$counter]['NAME']		= $title;
	$data_dwoo['timezones'][$counter]['SELECTED']	= $admin->get_timezone_string() == $title	? true : false;
	$counter++;
}

// =========================== 
// ! Insert date format list   
// =========================== 
include_once( CAT_ADMIN_PATH.'/interface/date_formats.php' );
$counter=0;
foreach ( $DATE_FORMATS AS $format => $title )
{
	$format = str_replace('|', ' ', $format); // Add's white-spaces (not able to be stored in array key)
	$data_dwoo['dateformats'][$counter]['VALUE']		= $format != 'system_default'	? $format : 'system_default';
	$data_dwoo['dateformats'][$counter]['NAME']			= $title;
	$data_dwoo['dateformats'][$counter]['SELECTED']		=
		( DATE_FORMAT == $format && !isset($_SESSION['USE_DEFAULT_DATE_FORMAT']) ) ||
		( 'system_default' == $format && isset($_SESSION['USE_DEFAULT_DATE_FORMAT']) )	?
		true : false;
	$counter++;
}

// =========================== 
// ! Insert time format list   
// =========================== 
include_once(CAT_ADMIN_PATH.'/interface/time_formats.php' );
$counter	= 0;
foreach ( $TIME_FORMATS AS $format => $title )
{
	$format		= str_replace('|', ' ', $format); // Add's white-spaces (not able to be stored in array key)
	$data_dwoo['timeformats'][$counter]['VALUE']		= $format != 'system_default' ? $format : 'system_default';
	$data_dwoo['timeformats'][$counter]['NAME']			= $title;
	$data_dwoo['timeformats'][$counter]['SELECTED']		=
		( TIME_FORMAT == $format && !isset($_SESSION['USE_DEFAULT_TIME_FORMAT']) ) ||
		( 'system_default' == $format && isset($_SESSION['USE_DEFAULT_TIME_FORMAT']) )
		? true : false;

	$counter++;
}



// ============== 
// ! Print page   
// ============== 
$parser->output( 'backend_preferences_index.lte', $data_dwoo );

$admin->print_footer();

?>