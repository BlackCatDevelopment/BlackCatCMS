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

// test $_GET querystring can only be 1 (ctoken)
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
$user  = CAT_Users::getInstance();

global $parser;
$tpl_data = array();

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
		$tpl_data['values'][$row['name']]		= ($row['name'] != 'catmailer_smtp_password') ? htmlspecialchars($row['value']) : $row['value'];
	}
}

// =========================================================================== 
// ! Query current settings in the db, then loop through them and print them   
// =========================================================================== 
if ( ($res_search = $database->query('SELECT * FROM `'.CAT_TABLE_PREFIX.'search` WHERE `extra` = \'\' ')) && ($res_search->numRows() > 0) )
{
	while ( $row = $res_search->fetchRow() )
	{
		$tpl_data['search'][$row['name']]		= htmlspecialchars(($row['value']));
	}
}

// ============================= 
// ! Add setting to $tpl_data   
// ============================= 
$tpl_data['DISPLAY_ADVANCED']						= $user->checkPermission('Settings','settings_advanced');

$tpl_data['values']['pages_directory']				= trim(PAGES_DIRECTORY);
$tpl_data['values']['media_directory']				= trim(MEDIA_DIRECTORY);
$tpl_data['values']['page_extension']				= PAGE_EXTENSION;
$tpl_data['values']['page_spacer']					= PAGE_SPACER;
$tpl_data['values']['sec_anchor']					= SEC_ANCHOR;
$tpl_data['values']['DATABASE_TYPE']				= '';
$tpl_data['values']['DATABASE_HOST']				= '';
$tpl_data['values']['DATABASE_USERNAME']			= '';
$tpl_data['values']['DATABASE_NAME']				= '';
$tpl_data['values']['CAT_TABLE_PREFIX']				= CAT_TABLE_PREFIX;
$tpl_data['values']['catmailer_smtp_host']          = defined('CATMAILER_SMTP_HOST')     ? CATMAILER_SMTP_HOST     : '';
$tpl_data['values']['catmailer_smtp_username']      = defined('CATMAILER_SMTP_USERNAME') ? CATMAILER_SMTP_USERNAME : '';
$tpl_data['values']['catmailer_smtp_password']      = defined('CATMAILER_SMTP_PASSWORD') ? CATMAILER_SMTP_PASSWORD : '';

$tpl_data['MULTIPLE_MENUS']						    = (defined('MULTIPLE_MENUS') && MULTIPLE_MENUS == true) ? true : false;
$tpl_data['PAGE_LANGUAGES']						    = (defined('PAGE_LANGUAGES') && PAGE_LANGUAGES == true) ? true : false;
$tpl_data['SMART_LOGIN']							= (defined('SMART_LOGIN') && SMART_LOGIN == true) ? true : false;
$tpl_data['GD_EXTENSION']							= (extension_loaded('gd') && function_exists('imageCreateFromJpeg')) ? true : false;
$tpl_data['SECTION_BLOCKS']						    = (defined('SECTION_BLOCKS') && SECTION_BLOCKS == true) ? true : false;
$tpl_data['HOMEPAGE_REDIRECTION']					= (defined('HOMEPAGE_REDIRECTION') && HOMEPAGE_REDIRECTION == true) ? true : false;
$tpl_data['MANAGE_SECTIONS']						= (MANAGE_SECTIONS) ? true : false;
$tpl_data['OPERATING_SYSTEM']						= OPERATING_SYSTEM;
$tpl_data['WORLD_WRITEABLE_SELECTED']				= (STRING_FILE_MODE == '0666' && STRING_DIR_MODE == '0777') ? true : false;
$tpl_data['CATMAILER_ROUTINE']						= CATMAILER_ROUTINE;
$tpl_data['CATMAILER_SMTP_AUTH']					= CATMAILER_SMTP_AUTH;
$tpl_data['INTRO_PAGE']							    = INTRO_PAGE ? true : false;
$tpl_data['FRONTEND_LOGIN']						    = FRONTEND_LOGIN ? true : false;
$tpl_data['HOME_FOLDERS']							= HOME_FOLDERS;
$tpl_data['SEARCH']								    = SEARCH;
$tpl_data['PAGE_LEVEL_LIMIT']						= PAGE_LEVEL_LIMIT;
$tpl_data['PAGE_TRASH']							    = PAGE_TRASH;
$tpl_data['ER_LEVEL']								= ER_LEVEL;
$tpl_data['DEFAULT_CHARSET']						= DEFAULT_CHARSET;
$tpl_data['values']['server_email']				    = SERVER_EMAIL;
$tpl_data['values']['wb_default_sendername']		= CATMAILER_DEFAULT_SENDERNAME;

// ==========================
// ! Specials
// ==========================

// format installation date and time
$tpl_data['values']['installation_time']           = date($admin->get_helper('DateTime')->getDefaultDateFormatShort(),INSTALLATION_TIME)
                                                    . ' '
                                                    . date($admin->get_helper('DateTime')->getDefaultTimeFormat(),INSTALLATION_TIME);

// get page statistics
if(($result = $database->query('SELECT visibility, count(*) AS count FROM '.CAT_TABLE_PREFIX.'pages GROUP BY visibility')) && $result->numRows() > 0 )
{
    while ( $row = $result->fetchRow(MYSQL_ASSOC) )
	{
        $tpl_data['values']['pages_count'][] = $row;
    }
}

// get installed mailer libs
$tpl_data['CATMAILER_LIBS'] = array();
$mailer_libs = CAT_Helper_Addons::getInstance()->getLibraries('mail');
if ( count($mailer_libs) )
{
    foreach ( $mailer_libs as $item )
    {
        $tpl_data['CATMAILER_LIBS'][] = $item;
    }
}

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
		$tpl_data['languages'][$counter]['CODE']	= $l_codes[$l_name];
		$tpl_data['languages'][$counter]['NAME']	= $l_name;
		// $tpl_data['languages']['CODE'] = true;
		// =========================== 
		// ! Check if it is selected   
		// =========================== 
		$tpl_data['languages'][$counter]['SELECTED'] = (DEFAULT_LANGUAGE == $l_codes[$l_name]) ? true : false;
		$counter++;
	}
}

// ================================== 
// ! Insert default timezone values   
// ================================== 
$timezone_table = CAT_Helper_DateTime::getTimezones();
$counter=0;

foreach( $timezone_table as $title )
{
	$tpl_data['timezones'][$counter] = array(
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
	$tpl_data['charsets'][$counter] = array(
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
$DATE_FORMATS = CAT_Helper_DateTime::getDateFormats();
$counter=0;
foreach ( $DATE_FORMATS AS $format => $title )
{
	#$format = str_replace('|', ' ', $format); // Add's white-spaces (not able to be stored in array key)
	$tpl_data['dateformats'][$counter] = array(
		'NAME'			=> $title,
		'VALUE'			=> ( $format != 'system_default' ) ? $format : '',
		'SELECTED'		=> ( DEFAULT_DATE_FORMAT == $format ) ? true : false
	);
	$counter++;
}

// =========================== 
// ! Insert time format list   
// =========================== 
$TIME_FORMATS = $admin->get_helper('DateTime')->getTimeFormats();
$counter=0;
foreach ( $TIME_FORMATS AS $format => $title )
{
	$format = str_replace('|', ' ', $format); // Add's white-spaces (not able to be stored in array key)
	$tpl_data['timeformats'][$counter] = array(
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
	$tpl_data['er_levels'][$counter] = array(
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
$tpl_data['groups']				= $admin->users->get_groups(FRONTEND_SIGNUP , '', false);
$tpl_data['templates']				= $addons->get_addons( DEFAULT_TEMPLATE , 'template', 'template' );
$tpl_data['backends']				= $addons->get_addons( DEFAULT_THEME , 'template', 'theme' );
$tpl_data['wysiwyg']				= $addons->get_addons( WYSIWYG_EDITOR , 'module', 'wysiwyg' );
$tpl_data['search_templates']		= $addons->get_addons( $tpl_data['search']['template'] , 'template', 'template' );

array_unshift (
	$tpl_data['wysiwyg'],
	array(
		'NAME'			=> $admin->lang->translate('None'),
		'VALUE'			=> false,
		'SELECTED'		=> ( !defined('WYSIWYG_EDITOR') || WYSIWYG_EDITOR == 'none' ) ? true : false
	)
);

array_unshift (
	$tpl_data['search_templates'],
	array(
		'NAME'			=> $TEXT['SYSTEM_DEFAULT'],
		'VALUE'			=> false,
		'SELECTED'		=> ( ($tpl_data['search']['template'] == '') || $tpl_data['search']['template'] == DEFAULT_TEMPLATE ) ? true : false
	)
);

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_settings_index.lte', $tpl_data);

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>