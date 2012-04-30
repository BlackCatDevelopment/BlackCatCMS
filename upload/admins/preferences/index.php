<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @version         $Id$
 *
 */
 
// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
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



// put all inside a function to prevent global vars
function build_page( &$admin, &$database )
{
	global $HEADING, $TEXT, $timezone_table;
	
	include_once(WB_PATH.'/framework/functions-utf8.php');
	
	/**
	 *	Initial page addition
	 *
	 */
	
	require_once(WB_PATH."/modules/initial_page/classes/c_init_page.php");
	$ref = new c_init_page( $database );
	$info = $ref->get_user_info( $_SESSION['USER_ID'] );
	
	$options = array(
		'pages'			=> true,
		'tools'			=> ($_SESSION['GROUP_ID'] == 1) ? true : false,
		'backend_pages' => ($_SESSION['GROUP_ID'] == 1) ? true : false
	);
	
	$select = $ref->get_single_user_select( $_SESSION['USER_ID'], 'init_page_select', $info['init_page'], $options);
	
	$initial_page_language = $ref->get_language();
	
// Create new template object, assign template file, start main-block
	$tpl = new Template( THEME_PATH.'/templates' );
	$tpl->set_file( 'page', 'preferences.htt' );
	$tpl->set_block( 'page', 'main_block', 'main' );
// read user-info from table users and assign it to template
	$sql  = 'SELECT `display_name`, `username`, `email`, `statusflags` FROM `'.TABLE_PREFIX.'users` ';
	$sql .= 'WHERE `user_id` = '.(int)$admin->get_user_id();
	if( ($res_user = $database->query($sql)) )
	{
		if( ($rec_user = $res_user->fetchRow()) )
		{
			$tpl->set_var('DISPLAY_NAME', $rec_user['display_name']);
			$tpl->set_var('USERNAME',     $rec_user['username']);
			$tpl->set_var('EMAIL',        $rec_user['email']);
			$tpl->set_var('ADMIN_URL',    ADMIN_URL);
		}
	}
	
// ------------------------
// insert link to user-profile
	$tpl->set_var('USER_ID', $admin->get_user_id() );
	$tpl->set_block('main_block', 'show_cmd_profile_edit_block', 'show_cmd_profile_edit');
	if( $admin->bit_isset($rec_user['statusflags'], USERS_PROFILE_ALLOWED) )
	{
		$tpl->set_var('PROFILE_ACTION_URL', ADMIN_URL.'/profiles/index.php');
		$tpl->parse('show_cmd_profile_edit', 'show_cmd_profile_edit_block', true);
	}else{
		$tpl->parse('show_cmd_profile_edit', '');
	}
// ------------------------

// read available languages from table addons and assign it to the template
	$sql  = 'SELECT * FROM `'.TABLE_PREFIX.'addons` ';
	$sql .= 'WHERE `type` = "language" ORDER BY `directory`';
	if( ($res_lang = $database->query($sql)) != false )
	{
		$tpl->set_block('main_block', 'language_list_block', 'language_list');
		while( $rec_lang = $res_lang->fetchRow( MYSQL_ASSOC ) )
		{
			$tpl->set_var('LANG_CODE',        $rec_lang['directory']);
			$tpl->set_var('LANG_NAME',        $rec_lang['name']);
			$tpl->set_var('LANG_FLAG',        THEME_URL.'/images/flags/'.strtolower($rec_lang['directory']));
			$tpl->set_var('LANG_SELECTED',    (LANGUAGE == $rec_lang['directory'] ? ' selected="selected"' : '') );
			$tpl->parse('language_list', 'language_list_block', true);
		}
	}

// Insert default timezone values
	$user_time = true;
	$tpl->set_block('main_block', 'timezone_list_block', 'timezone_list');
	foreach ($timezone_table as $title) {
		$tpl->set_var('TIMEZONE_NAME',     $title);
		$tpl->set_var('TIMEZONE_SELECTED', ($admin->get_timezone_string() == $title) ? ' selected="selected"' : '' );   
		$tpl->parse('timezone_list', 'timezone_list_block', true);
	}

// Insert date format list
	include_once( ADMIN_PATH.'/interface/date_formats.php' );
	$tpl->set_block('main_block', 'date_format_list_block', 'date_format_list');
	foreach( $DATE_FORMATS AS $format => $title )
	{
		$format = str_replace('|', ' ', $format); // Add's white-spaces (not able to be stored in array key)
		$tpl->set_var( 'DATEFORMAT_VALUE', ($format != 'system_default' ? $format : 'system_default') );
		$tpl->set_var( 'DATEFORMAT_NAME',  $title );
		if( (DATE_FORMAT == $format && !isset($_SESSION['USE_DEFAULT_DATE_FORMAT'])) ||
			('system_default' == $format && isset($_SESSION['USE_DEFAULT_DATE_FORMAT'])) )
		{
			$tpl->set_var('DATEFORMAT_SELECTED', ' selected="selected"');
		}else {
			$tpl->set_var('DATEFORMAT_SELECTED', '');
		}
		$tpl->parse('date_format_list', 'date_format_list_block', true);
	}
// Insert time format list
	include_once( ADMIN_PATH.'/interface/time_formats.php' );
	$tpl->set_block('main_block', 'time_format_list_block', 'time_format_list');
	foreach( $TIME_FORMATS AS $format => $title )
	{
		$format = str_replace('|', ' ', $format); // Add's white-spaces (not able to be stored in array key)
		$tpl->set_var('TIMEFORMAT_VALUE', $format != 'system_default' ? $format : 'system_default' );
		$tpl->set_var('TIMEFORMAT_NAME',  $title);
		if( (TIME_FORMAT == $format && !isset($_SESSION['USE_DEFAULT_TIME_FORMAT'])) ||
		    ('system_default' == $format && isset($_SESSION['USE_DEFAULT_TIME_FORMAT'])) )
		{
			$tpl->set_var('TIMEFORMAT_SELECTED', ' selected="selected"');
		} else {
			$tpl->set_var('TIMEFORMAT_SELECTED', '');
		}
		$tpl->parse('time_format_list', 'time_format_list_block', true);
	}
	
/**
 *	Initial Page addition
 */
$tpl->set_var('INIT_PAGE_SELECT', $select);
# $tpl->set_var('INIT_PAGE_PARAM', $info['page_param']);
$tpl->set_var('INIT_PAGE_LABEL', $initial_page_language['label_default']);

// assign systemvars to template
$tpl->set_var(array( 'ADMIN_URL'  => ADMIN_URL,
	'WB_URL'     => WB_URL,
	'WB_PATH'    => WB_PATH,
	'THEME_URL'  => THEME_URL,
	'ACTION_URL' => ADMIN_URL.'/preferences/save.php'
	)
);
	$tpl->set_var('FORM_NAME', 'preferences_save');
// assign language vars
	$tpl->set_var(array(
		'HEADING_MY_SETTINGS'      => $HEADING['MY_SETTINGS'],
		'HEADING_MY_EMAIL'         => $HEADING['MY_EMAIL'],
		'HEADING_MY_PASSWORD'      => $HEADING['MY_PASSWORD'],
		'TEXT_SAVE'                => $TEXT['SAVE'],
		'TEXT_RESET'               => $TEXT['RESET'],
		'TEXT_DISPLAY_NAME'        => $TEXT['DISPLAY_NAME'],
		'TEXT_USERNAME'            => $TEXT['USERNAME'],
		'TEXT_EMAIL'               => $TEXT['EMAIL'],
		'TEXT_LANGUAGE'            => $TEXT['LANGUAGE'],
		'TEXT_TIMEZONE'            => $TEXT['TIMEZONE'],
		'TEXT_DATE_FORMAT'         => $TEXT['DATE_FORMAT'],
		'TEXT_TIME_FORMAT'         => $TEXT['TIME_FORMAT'],
		'TEXT_CURRENT_PASSWORD'    => $TEXT['CURRENT_PASSWORD'],
		'TEXT_NEW_PASSWORD'        => $TEXT['NEW_PASSWORD'],
		'TEXT_RETYPE_NEW_PASSWORD' => $TEXT['RETYPE_NEW_PASSWORD'],
		'TEXT_NEW_PASSWORD'        => $TEXT['NEW_PASSWORD'],
		'TEXT_RETYPE_NEW_PASSWORD' => $TEXT['RETYPE_NEW_PASSWORD'],
		'TEXT_NEED_CURRENT_PASSWORD' => $TEXT['NEED_CURRENT_PASSWORD'],
		'TEXT_NEED_PASSWORD_TO_CONFIRM' => js_alert_encode($TEXT['NEED_PASSWORD_TO_CONFIRM']),
		'EMPTY_STRING'             => ''
		)
	);
// Parse template for preferences form
	$tpl->parse('main', 'main_block', false);
	$output = $tpl->finish($tpl->parse('output', 'page'));
	return $output;
}
// test if valid $admin-object already exists (bit complicated about PHP4 Compatibility)
if( !(isset($admin) && is_object($admin) && (get_class($admin) == 'admin')) )
{
	require_once( WB_PATH.'/framework/class.admin.php' );
	$admin = new admin('Preferences');
}
echo build_page($admin, $database);
$admin->print_footer();

?>