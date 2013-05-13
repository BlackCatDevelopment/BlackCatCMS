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
 *   @link            http://www.blackcat-cms.org
 *   @license			http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

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

require_once(CAT_PATH . '/framework/class.admin.php');
$admin = new admin('Preferences');

$val      = CAT_Helper_Validate::getInstance();
$user     = CAT_Users::getInstance();
$user_id  = $val->fromSession('USER_ID','numeric');
$group_id = $val->fromSession('GROUP_ID','numeric');

global $parser;
$tpl_data	= array();

include_once(CAT_PATH . '/framework/functions-utf8.php');

// ========================= 
// ! Initial page addition   
// ========================= 
require_once( CAT_PATH . '/modules/initial_page/classes/c_init_page.php' );
$ref		= new c_init_page( $database );
$info		= $ref->get_user_info( $user_id );

$options	= array(
	'pages'			=> true,
	'tools'			=> $group_id == 1 ? true : false,
	'backend_pages' => $group_id == 1 ? true : false
);

$tpl_data['INIT_PAGE_SELECT'] = $ref->get_single_user_select( $user_id, 'init_page_select', $info['init_page'], $options);
$tpl_data['INIT_PAGE_LABEL']  = $ref->get_language();


// ============================================================= 
// ! read user-info from table users and assign it to template   
// ============================================================= 
$sql  = 'SELECT `display_name`, `username`, `email`, `statusflags` FROM `'.CAT_TABLE_PREFIX.'users` WHERE `user_id` = '.(int)$user->get_user_id();

$res_user	= $database->query($sql);
if ($res_user->numRows() > 0)
{
	if( ($rec_user = $res_user->fetchRow()) )
	{
		$tpl_data['DISPLAY_NAME']	= $rec_user['display_name'];
		$tpl_data['USERNAME']		= $rec_user['username'];
		$tpl_data['EMAIL']			= $rec_user['email'];
	}
}


// =============================== 
// ! insert link to user-profile   
// =============================== 
$tpl_data['USER_ID']	= $user->get_user_id();

if ( $admin->bit_isset($rec_user['statusflags'], USERS_PROFILE_ALLOWED) )
{
	$tpl_data['PROFILE_ACTION_URL']			    = CAT_ADMIN_URL.'/profiles/index.php';
	$tpl_data['show_cmd_profile_edit']			= true;
	$tpl_data['show_cmd_profile_edit_block']	= true;
}
else
{
	$tpl_data['show_cmd_profile_edit']			= true;
	$tpl_data['show_cmd_profile_edit_block']	= false;
}

// ============================================================================ 
// ! read available languages from table addons and assign it to the template   
// ============================================================================ 
$addons = CAT_Helper_Addons::getInstance();
$tpl_data['languages'] = $addons->get_addons( LANGUAGE , 'language', false, 'directory' );

// ================================== 
// ! Insert default timezone values   
// ================================== 
$counter	= 0;
$timezone_table = CAT_Helper_DateTime::getTimezones();
foreach ($timezone_table as $title)
{
	$tpl_data['timezones'][$counter]['NAME']		= $title;
	$tpl_data['timezones'][$counter]['SELECTED']	= $admin->get_timezone_string() == $title	? true : false;
	$counter++;
}

// =========================== 
// ! Insert date format list   
// =========================== 
$DATE_FORMATS = CAT_Helper_DateTime::getDateFormats();
$USE_DEFAULT  = $val->fromSession('USE_DEFAULT_DATE_FORMAT');
$counter=0;
foreach ( $DATE_FORMATS AS $format => $title )
{
	$format = str_replace('|', ' ', $format); // Add's white-spaces (not able to be stored in array key)
	$tpl_data['dateformats'][$counter]['VALUE']	= ( $format != 'system_default' ) ? $format : 'system_default';
	$tpl_data['dateformats'][$counter]['NAME']	= $title;
	$tpl_data['dateformats'][$counter]['SELECTED']
        =    ( DATE_FORMAT      == $format && ! $USE_DEFAULT )
          || ( 'system_default' == $format &&   $USE_DEFAULT )
        ? true
        : false;
	$counter++;
}

// =========================== 
// ! Insert time format list   
// =========================== 
$TIME_FORMATS = CAT_Helper_DateTime::getTimeFormats();
$USE_DEFAULT  = $val->fromSession('USE_DEFAULT_TIME_FORMAT');
$counter	= 0;
foreach ( $TIME_FORMATS AS $format => $title )
{
	$format		= str_replace('|', ' ', $format); // Add's white-spaces (not able to be stored in array key)
	$tpl_data['timeformats'][$counter]['VALUE']		= $format != 'system_default' ? $format : 'system_default';
	$tpl_data['timeformats'][$counter]['NAME']		= $title;
	$tpl_data['timeformats'][$counter]['SELECTED']
        =    ( TIME_FORMAT      == $format && ! $USE_DEFAULT )
          || ( 'system_default' == $format &&   $USE_DEFAULT )
		? true
        : false;
	$counter++;
}



// ============== 
// ! Print page   
// ============== 
$parser->output( 'backend_preferences_index.tpl', $tpl_data );

$backend->print_footer();

?>