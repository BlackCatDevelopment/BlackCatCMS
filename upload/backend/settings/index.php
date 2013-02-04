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

// test $_GET querystring can only be 1 (leptoken)
if(isset($_GET) && sizeof($_GET) > 1)
{
	die('Acess denied');
}

// test if valid $admin-object already exists (bit complicated about PHP4 Compatibility)
if ( !(isset($admin) && is_object($admin) && (get_class($admin) == 'admin')) )
{
	require_once( CAT_PATH.'/framework/class.admin.php' );
}

$admin = new admin('Settings', 'settings_advanced');

global $parser;
$data_dwoo = array();

// Include the WB functions file
require_once(CAT_PATH.'/framework/functions.php');
require_once(CAT_PATH.'/framework/functions-utf8.php');

// check if theme language file exists for the language set by the user (e.g. DE, EN)
$lang	= (file_exists(CAT_THEME_PATH .'/languages/'.LANGUAGE .'.php')) ? LANGUAGE : 'EN';
// only a theme language file exists for the language, load it
if ( file_exists(CAT_THEME_PATH .'/languages/'.$lang .'.php') )
{
	include_once(CAT_THEME_PATH .'/languages/'.$lang .'.php');
}

// =========================================================================== 
// ! Query current settings in the db, then loop through them and print them   
// =========================================================================== 
if ( $res_settings = $database->query('SELECT `name`, `value` FROM `'.CAT_TABLE_PREFIX.'settings` ORDER BY `name`'))
{
	while ( $row = $res_settings->fetchRow( ) )
	{
		$data_dwoo['values'][$row['name']]		= ($row['name'] != 'wbmailer_smtp_password') ? htmlspecialchars($row['value']) : $row['value'];
	}
}

// =========================================================================== 
// ! Query current settings in the db, then loop through them and print them   
// =========================================================================== 
if ( ($res_search = $database->query('SELECT * FROM `'.CAT_TABLE_PREFIX.'search` WHERE `extra` = \'\' ')) && ($res_search->numRows() > 0) )
{
	while ( $row = $res_search->fetchRow() )
	{
		$data_dwoo['search'][$row['name']]		= htmlspecialchars(($row['value']));
	}
}

// ============================= 
// ! Add setting to $data_dwoo   
// ============================= 
$data_dwoo['DISPLAY_ADVANCED']						= $admin->get_permission('settings_advanced') != true ? false : true;

$data_dwoo['values']['pages_directory']				= trim(PAGES_DIRECTORY);
$data_dwoo['values']['media_directory']				= trim(MEDIA_DIRECTORY);
$data_dwoo['values']['page_extension']				= PAGE_EXTENSION;
$data_dwoo['values']['page_spacer']					= PAGE_SPACER;
$data_dwoo['values']['sec_anchor']					= SEC_ANCHOR;
$data_dwoo['values']['DATABASE_TYPE']				= '';
$data_dwoo['values']['DATABASE_HOST']				= '';
$data_dwoo['values']['DATABASE_USERNAME']			= '';
$data_dwoo['values']['DATABASE_NAME']				= '';
$data_dwoo['values']['CAT_TABLE_PREFIX']				= CAT_TABLE_PREFIX;

$data_dwoo['MULTIPLE_MENUS']						= (defined('MULTIPLE_MENUS') && MULTIPLE_MENUS == true) ? true : false;
$data_dwoo['PAGE_LANGUAGES']						= (defined('PAGE_LANGUAGES') && PAGE_LANGUAGES == true) ? true : false;
$data_dwoo['SMART_LOGIN']							= (defined('SMART_LOGIN') && SMART_LOGIN == true) ? true : false;
$data_dwoo['GD_EXTENSION']							= (extension_loaded('gd') && function_exists('imageCreateFromJpeg')) ? true : false;
$data_dwoo['SECTION_BLOCKS']						= (defined('SECTION_BLOCKS') && SECTION_BLOCKS == true) ? true : false;
$data_dwoo['HOMEPAGE_REDIRECTION']					= (defined('HOMEPAGE_REDIRECTION') && HOMEPAGE_REDIRECTION == true) ? true : false;
$data_dwoo['MANAGE_SECTIONS']						= (MANAGE_SECTIONS) ? true : false;
$data_dwoo['OPERATING_SYSTEM']						= OPERATING_SYSTEM;
$data_dwoo['WORLD_WRITEABLE_SELECTED']				= (STRING_FILE_MODE == '0666' && STRING_DIR_MODE == '0777') ? true : false;
$data_dwoo['WBMAILER_ROUTINE']						= WBMAILER_ROUTINE;
$data_dwoo['WBMAILER_SMTP_AUTH']					= WBMAILER_SMTP_AUTH;
$data_dwoo['INTRO_PAGE']							= INTRO_PAGE ? true : false;
$data_dwoo['FRONTEND_LOGIN']						= FRONTEND_LOGIN ? true : false;
$data_dwoo['HOME_FOLDERS']							= HOME_FOLDERS;
$data_dwoo['SEARCH']								= SEARCH;
$data_dwoo['PAGE_LEVEL_LIMIT']						= PAGE_LEVEL_LIMIT;
$data_dwoo['PAGE_TRASH']							= PAGE_TRASH;
$data_dwoo['ER_LEVEL']								= ER_LEVEL;
$data_dwoo['DEFAULT_CHARSET']						= DEFAULT_CHARSET;
$data_dwoo['values']['server_email']				= SERVER_EMAIL;
$data_dwoo['values']['wb_default_sendername']		= WBMAILER_DEFAULT_SENDERNAME;

// ========================== 
// ! Insert language values   
// ========================== 
if(($result = $database->query('SELECT * FROM `'.CAT_TABLE_PREFIX.'addons` WHERE `type` = \'language\' ORDER BY `name`')) && $result->numRows() > 0 )
{
	while ( $addon = $result->fetchRow() )
	{
		$l_codes[$addon['name']]	= $addon['directory'];
		$l_names[$addon['name']]	= entities_to_7bit($addon['name']); // sorting-problem workaround
	}
	asort($l_names);
	$counter=0;
	foreach($l_names as $l_name=>$v)
	{
		// ======================== 
		// ! Insert code and name   
		// ======================== 
		$data_dwoo['languages'][$counter]['CODE']	= $l_codes[$l_name];
		$data_dwoo['languages'][$counter]['NAME']	= $l_name;
		// $data_dwoo['languages']['CODE'] = true;
		// =========================== 
		// ! Check if it is selected   
		// =========================== 
		$data_dwoo['languages'][$counter]['SELECTED'] = (DEFAULT_LANGUAGE == $l_codes[$l_name]) ? true : false;
		$counter++;
	}
}

// ================================== 
// ! Insert default timezone values   
// ================================== 
require(CAT_ADMIN_PATH.'/interface/timezones.php');
$counter=0;

foreach( $timezone_table as $title )
{
	$data_dwoo['timezones'][$counter] = array(
		'NAME'			=> $title,
		'SELECTED'		=> ( DEFAULT_TIMEZONE_STRING == $title ) ? true : false
	);
	$counter++;
}

// ================================= 
// ! Insert default charset values   
// ================================= 
require(CAT_ADMIN_PATH.'/interface/charsets.php');
$counter=0;
foreach ( $CHARSETS AS $code => $title )
{
	$data_dwoo['charsets'][$counter] = array(
		'NAME'			=> $title,
		'VALUE'			=> $code,
		'SELECTED'		=> ( DEFAULT_CHARSET == $code ) ? true : false
	);
	$counter++;
}

// ==================================== 
// ! set TZ to current system default   
// ==================================== 
$old_tz = date_default_timezone_get();
date_default_timezone_set(DEFAULT_TIMEZONE_STRING);

// =========================== 
// ! Insert date format list   
// =========================== 
require(CAT_ADMIN_PATH.'/interface/date_formats.php');
$counter=0;
foreach ( $DATE_FORMATS AS $format => $title )
{
	$format = str_replace('|', ' ', $format); // Add's white-spaces (not able to be stored in array key)
	$data_dwoo['dateformats'][$counter] = array(
		'NAME'			=> $title,
		'VALUE'			=> ( $format != 'system_default' ) ? $format : '',
		'SELECTED'		=> ( DEFAULT_DATE_FORMAT == $format ) ? true : false
	);
	$counter++;
}

// =========================== 
// ! Insert time format list   
// =========================== 
require(CAT_ADMIN_PATH.'/interface/time_formats.php');
$counter=0;
foreach ( $TIME_FORMATS AS $format => $title )
{
	$format = str_replace('|', ' ', $format); // Add's white-spaces (not able to be stored in array key)
	$data_dwoo['timeformats'][$counter] = array(
		'NAME'			=> $title,
		'VALUE'			=> ( $format != 'system_default' ) ? $format : '',
		'SELECTED'		=> ( DEFAULT_TIME_FORMAT == $format ) ? true : false
	);
	$counter++;
}

// ========================================= 
// ! Insert default error reporting values   
// ========================================= 
require(CAT_ADMIN_PATH.'/interface/er_levels.php');
$counter = 0;
foreach ( $ER_LEVELS AS $value => $title )
{
	$data_dwoo['er_levels'][$counter] = array(
		'NAME'			=> $title,
		'VALUE'			=> $value,
		'SELECTED'		=> (ER_LEVEL == $value) ? true : false
	);
	$counter++;
}

// =============================== 
// ! set TZ back to user default   
// =============================== 
date_default_timezone_set($old_tz);

require CAT_PATH.'/framework/CAT/Helper/Addons.php';
$addons = new CAT_Helper_Addons();

// ============================ 
// ! Insert groups and addons   
// ============================ 
$data_dwoo['groups']				= $admin->users->get_groups(FRONTEND_SIGNUP , '', false);
$data_dwoo['templates']				= $addons->get_addons( DEFAULT_TEMPLATE , 'template', 'template' );
$data_dwoo['backends']				= $addons->get_addons( DEFAULT_THEME , 'template', 'theme' );
$data_dwoo['wysiwyg']				= $addons->get_addons( WYSIWYG_EDITOR , 'module', 'wysiwyg' );
$data_dwoo['search_templates']		= $addons->get_addons( $data_dwoo['search']['template'] , 'template', 'template' );

array_unshift (
	$data_dwoo['wysiwyg'],
	array(
		'NAME'			=> $TEXT['NONE'],
		'VALUE'			=> false,
		'SELECTED'		=> ( !defined('WYSIWYG_EDITOR') || WYSIWYG_EDITOR == 'none' ) ? true : false
	)
);

array_unshift (
	$data_dwoo['search_templates'],
	array(
		'NAME'			=> $TEXT['SYSTEM_DEFAULT'],
		'VALUE'			=> false,
		'SELECTED'		=> ( ($data_dwoo['search']['template'] == '') || $data_dwoo['search']['template'] == DEFAULT_TEMPLATE ) ? true : false
	)
);

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_settings_index.lte', $data_dwoo);

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>