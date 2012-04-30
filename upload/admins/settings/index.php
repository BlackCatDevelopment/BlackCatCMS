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
// end include class.secure.php


function build_settings(&$admin, &$database) {
	global $HEADING, $TEXT, $MESSAGE;

	// check if current user is admin
	$curr_user_is_admin = ( in_array(1, $admin->get_groups_id()) );

	// Include the WB functions file
	require_once(WB_PATH.'/framework/functions.php');
	require_once(WB_PATH.'/framework/functions-utf8.php');

	// check if theme language file exists for the language set by the user (e.g. DE, EN)
	$lang = (file_exists(THEME_PATH .'/languages/'.LANGUAGE .'.php')) ? LANGUAGE : 'EN';
	// only a theme language file exists for the language, load it
	if(file_exists(THEME_PATH .'/languages/'.$lang .'.php'))
	{
		include_once(THEME_PATH .'/languages/'.$lang .'.php');
	}
	if(file_exists('tan.php')) include('tan.php');
	// Create new template object
	$tpl = new Template(THEME_PATH.'/templates');
	$tpl->debug = false;

	$tpl->set_file('page',        'settings.htt');
	$tpl->set_block('page',       'main_block', 'main');

	$tpl->set_block('main_block', 'template_list_block',         'template_list');
	$tpl->set_block('main_block', 'timezone_list_block',         'timezone_list');
	$tpl->set_block('main_block', 'language_list_block',         'language_list');
	$tpl->set_block('main_block', 'date_format_list_block',      'date_format_list');
	$tpl->set_block('main_block', 'time_format_list_block',      'time_format_list');
	$tpl->set_block('main_block', 'theme_list_block',            'theme_list');
	$tpl->set_block('main_block', 'search_template_list_block',  'search_template_list');
	$tpl->set_block('main_block', 'group_list_block',            'group_list');
	$tpl->set_block('main_block', 'charset_list_block',          'charset_list');
	$tpl->set_block('main_block', 'error_reporting_list_block',  'error_reporting_list');
	$tpl->set_block('main_block', 'editor_list_block',           'editor_list');
	$tpl->set_block('main_block', 'page_level_limit_list_block', 'page_level_limit_list');

	$tpl->set_block('main_block', 'show_checkbox_1_block',       'show_checkbox_1');
	$tpl->set_block('main_block', 'show_checkbox_2_block',       'show_checkbox_2');
	$tpl->set_block('main_block', 'show_checkbox_3_block',       'show_checkbox_3');
	$tpl->set_block('main_block', 'show_page_level_limit_block', 'show_page_level_limit');
	$tpl->set_block('main_block', 'show_php_error_level_block',  'show_php_error_level');
	$tpl->set_block('main_block', 'show_charset_block',          'show_charset');
	$tpl->set_block('main_block', 'show_wysiwyg_block',          'show_wysiwyg');
	$tpl->set_block('main_block', 'show_search_block',           'show_search');
	$tpl->set_block('main_block', 'show_redirect_timer_block',   'show_redirect_timer');
	$tpl->set_block('main_block', 'show_access_menu_block',      'show_access_menu');
	$tpl->set_block('main_block', 'show_access_block',           'show_access');
	$tpl->set_block('main_block', 'show_admin_block',            'show_admin');
	$tpl->set_block('main_block', 'mailer_settings_block',       'mailer_settings');
	$tpl->set_block('mailer_settings_block', 'mailer_menu_block',           'mailer_menu');
	$tpl->set_block('mailer_settings_block', 'smtp_mailer_settings_block',  'smtp_mailer_settings');

		$tpl->set_block('main_block', 'search_footer_block', 'search_footer');
		$tpl->set_block('main_block', 'access_footer_block', 'access_footer');
		$tpl->set_block('main_block', 'send_testmail_block', 'send_testmail');
    $tmp_var = array();
    $settings = array();
// Query current settings in the db, then loop through them and print them
    $sql  = 'SELECT `name`, `value` FROM `'.TABLE_PREFIX.'settings` ';
	$sql .= 'ORDER BY `name`';
    if($res_settings = $database->query( $sql ))
	{
	    while ($row = $res_settings->fetchRow( )) {
            $settings[$row['name']] = ( $row['name'] != 'wbmailer_smtp_password' ) ? htmlspecialchars($row['value']) : $row['value'];
            $tmp_var[strtoupper($row['name'])] = $row['value'];
		}
		$tpl->set_var($tmp_var);
	}

// Do the same for settings stored in config file as with ones in db
	$database_type = '';

// Tell the browser whether or not to show advanced options
	$is_advanced = (isset($_GET['advanced']) && ($_GET['advanced'] == 'yes'));

// Insert permissions values
	if($admin->get_permission('settings_advanced') != true)
	{
		$tpl->set_var('DISPLAY_ADVANCED_BUTTON', 'hide');
	} else {
		$tpl->set_var('DISPLAY_ADVANCED_BUTTON', 'tabs-hide');
	}

	if( $is_advanced )
	{
		$tpl->set_var('DISPLAY_ADVANCED', '');
		$tpl->set_var('ADVANCED', 'yes');
		$tpl->set_var('JS_ADVANCED', 'no');
		$tpl->set_var('ADVANCED_BUTTON', '&lt;&lt; '.$TEXT['HIDE_ADVANCED']);
		$tpl->set_var('ADVANCED_LINK', 'index.php?advanced=no');

	} else {
		$tpl->set_var('DISPLAY_ADVANCED', ' style="display: none;"');
		$tpl->set_var('ADVANCED', 'no');
		$tpl->set_var('JS_ADVANCED', 'yes');
		$tpl->set_var('ADVANCED_BUTTON', $TEXT['SHOW_ADVANCED'].' &gt;&gt;');
		$tpl->set_var('ADVANCED_LINK', 'index.php?advanced=yes');
	}

    $tmp_var = array();
	$search_array = array();
// Query current settings in the db, then loop through them and print them
	$sql  = 'SELECT * FROM `'.TABLE_PREFIX.'search` ';
	$sql .= 'WHERE `extra` = \'\' ';
	if( ($res_search = $database->query($sql)) && ($res_search->numRows() > 0) ) {

		while($row = $res_search->fetchRow())
		{
            $search_array[$row['name']] = htmlspecialchars(($row['value']));
			$tmp_var['SEARCH_'.strtoupper($row['name'])] = $row['value'];
		}
        $tpl->set_var($tmp_var);
		$search_template = $search_array['template'];
	}

	if($is_advanced)
	{
		$tpl->parse('show_search', 'show_search_block', false);
	}else {
		$tpl->set_block('show_search', '');
	}

	$tpl->set_var(array(
				'PAGES_DIRECTORY'   => trim(PAGES_DIRECTORY),
				'MEDIA_DIRECTORY'   => MEDIA_DIRECTORY,
				'PAGE_EXTENSION'    => PAGE_EXTENSION,
				'PAGE_SPACER'       => PAGE_SPACER,
				'WB_PATH'           => WB_PATH,
				'WB_URL'            => WB_URL,
				'THEME_URL'         => THEME_URL,
				'ADMIN_PATH'        => ADMIN_PATH,
				'ADMIN_URL'         => ADMIN_URL,
				'DATABASE_TYPE'     => '',
				'DATABASE_HOST'     => '',
				'DATABASE_USERNAME' => '',
				'DATABASE_NAME'     => '',
				'TABLE_PREFIX'      => TABLE_PREFIX,
				'FORM_NAME'         => 'settings',
				'ACTION_URL'        => 'save.php'
				));

	if(isset($TEXT['TOGGLE_TABS']))
	{
		$expand = array( $TEXT['SHOW_TABS'], $TEXT['HIDE_TABS'], $TEXT['TOGGLE_TABS']);
		$cookie = (isset($_COOKIE['ShowTabber'])) ? (bool)$_COOKIE['ShowTabber'] : 2;
	    $tpl->set_var('TEXT_TOGGLE_TABS', $expand[$cookie]);
	}

// Insert language values
    $sql  = 'SELECT * FROM `'.TABLE_PREFIX.'addons` ';
	$sql .= 'WHERE `type` = \'language\' ';
	$sql .= 'ORDER BY `name`';
	if(($result = $database->query($sql)) && $result->numRows() > 0 )
	{
		while($addon = $result->fetchRow()) {
			$l_codes[$addon['name']] = $addon['directory'];
			$l_names[$addon['name']] = entities_to_7bit($addon['name']); // sorting-problem workaround
		}
		asort($l_names);
		foreach($l_names as $l_name=>$v) {
			// Insert code and name
			$tpl->set_var(array(
						'CODE' => $l_codes[$l_name],
						'NAME' => $l_name,
						'FLAG' => THEME_URL.'/images/flags/'.strtolower($l_codes[$l_name]),
						));
			// Check if it is selected
			if(DEFAULT_LANGUAGE == $l_codes[$l_name]) {
				$tpl->set_var('SELECTED', ' selected="selected"');
			} else {
				$tpl->set_var('SELECTED', '');
			}
			$tpl->parse('language_list', 'language_list_block', true);
		}
	} 

// Insert default timezone values
	require(ADMIN_PATH.'/interface/timezones.php');
	foreach( $timezone_table as $title ) {
		$tpl->set_var('NAME',     $title);
		$tpl->set_var('SELECTED', (DEFAULT_TIMEZONE_STRING == $title) ? ' selected="selected"' : '' );   
		$tpl->parse('timezone_list', 'timezone_list_block', true);
	}


// Insert default charset values
	require(ADMIN_PATH.'/interface/charsets.php');
	foreach($CHARSETS AS $code => $title)
	{
		$tpl->set_var('VALUE', $code);
		$tpl->set_var('NAME', $title);
		if(DEFAULT_CHARSET == $code) {
			$tpl->set_var('SELECTED', ' selected="selected"');
		} else {
			$tpl->set_var('SELECTED', '');
		}
		$tpl->parse('charset_list', 'charset_list_block', true);
	}

// set TZ to current system default
	$old_tz = date_default_timezone_get();
	date_default_timezone_set(DEFAULT_TIMEZONE_STRING);
// Insert date format list
	require(ADMIN_PATH.'/interface/date_formats.php');
	foreach($DATE_FORMATS AS $format => $title) {
		$format = str_replace('|', ' ', $format); // Add's white-spaces (not able to be stored in array key)
		if($format != 'system_default') {
			$tpl->set_var('VALUE', $format);
		} else {
			$tpl->set_var('VALUE', '');
		}
		$tpl->set_var('NAME', $title);
		if(DEFAULT_DATE_FORMAT == $format) {
			$tpl->set_var('SELECTED', ' selected="selected"');
		} else {
			$tpl->set_var('SELECTED', '');
		}
		$tpl->parse('date_format_list', 'date_format_list_block', true);
	}

// Insert time format list
	require(ADMIN_PATH.'/interface/time_formats.php');
	foreach($TIME_FORMATS AS $format => $title)
	{
		$format = str_replace('|', ' ', $format); // Add's white-spaces (not able to be stored in array key)
		if($format != 'system_default') {
			$tpl->set_var('VALUE', $format);
		} else {
			$tpl->set_var('VALUE', '');
		}
		$tpl->set_var('NAME', $title);
		if(DEFAULT_TIME_FORMAT == $format) {
			$tpl->set_var('SELECTED', ' selected="selected"');
		} else {
			$tpl->set_var('SELECTED', '');
		}
		$tpl->parse('time_format_list', 'time_format_list_block', true);
	}

// set TZ back to user default
	date_default_timezone_set($old_tz);

// Insert templates
	$sql  = 'SELECT * FROM `'.TABLE_PREFIX.'addons` ';
	$sql .= 'WHERE `type` = \'template\' ';
	$sql .= 'AND `function` != \'theme\' ';
	$sql .= 'ORDER BY `name`';
	if( ($result = $database->query($sql)) && ($result->numRows() > 0) )
	{
		while($addon = $result->fetchRow( MYSQL_ASSOC ))
		{
			$depricated = ($addon['function']=="" ? " style='color:#FF0000;'" : ""); 

			$tpl->set_var('FILE', $addon['directory']);
			$tpl->set_var('NAME', $addon['name'].($addon['function']=="" ? " !" : ""));
			$selected =(( $addon['directory'] == DEFAULT_TEMPLATE) ? ' selected="selected"' : '');
			
			$tpl->set_var('SELECTED', $selected.$depricated);
			$tpl->parse('template_list', 'template_list_block', true);
		}
	}

// Insert backend theme
	$sql  = 'SELECT * FROM `'.TABLE_PREFIX.'addons` ';
	$sql .= 'WHERE `type` = \'template\' ';
	$sql .= 'AND `function` = \'theme\' ';
	$sql .= 'ORDER BY `name`';
	if( ($result = $database->query($sql)) && ($result->numRows() > 0) )
	{
		while($addon = $result->fetchRow())
		{
			$tpl->set_var('FILE', $addon['directory']);
			$tpl->set_var('NAME', $addon['name']);
			if(($addon['directory'] == DEFAULT_THEME) ? $selected = ' selected="selected"' : $selected = '');
			$tpl->set_var('SELECTED', $selected);
			$tpl->parse('theme_list', 'theme_list_block', true);
		}
	} else {

	}

// Insert WYSIWYG modules
	$file='none';
	$module_name=$TEXT['NONE'];
	$tpl->set_var('FILE', $file);
	$tpl->set_var('NAME', $module_name);
	$selected = (!defined('WYSIWYG_EDITOR') || $file == WYSIWYG_EDITOR) ? ' selected="selected"' : '';
	$tpl->set_var('SELECTED', $selected);
	$tpl->parse('editor_list', 'editor_list_block', true);

	$sql  = 'SELECT * FROM `'.TABLE_PREFIX.'addons` ';
	$sql .= 'WHERE `type` = \'module\' ';
	$sql .= 'AND `function` = \'wysiwyg\' ';
	$sql .= 'ORDER BY `name`';
	if( ($result = $database->query($sql)) && ($result->numRows() > 0) )
	{
		while($addon = $result->fetchRow())
	    {
			$tpl->set_var('FILE', $addon['directory']);
			$tpl->set_var('NAME', $addon['name']);
			$selected = (!defined('WYSIWYG_EDITOR') || $addon['directory'] == WYSIWYG_EDITOR) ? ' selected="selected"' : '';
			$tpl->set_var('SELECTED', $selected);
			$tpl->parse('editor_list', 'editor_list_block', true);
		}
	}

// Insert templates for search settings
	$search_template = ( ($search_template == DEFAULT_TEMPLATE) || ($search_template == '') ) ? '' : $search_template;
	$selected = ( ($search_template == '') ) ?  ' selected="selected"' : $selected = '';

	$tpl->set_var(array(
	        'FILE' => '',
	        'NAME' => $TEXT['SYSTEM_DEFAULT'],
	        'SELECTED' => $selected
	    ));
	$tpl->parse('search_template_list', 'search_template_list_block', true);

	$sql  = 'SELECT * FROM `'.TABLE_PREFIX.'addons` ';
	$sql .= 'WHERE `type` = \'template\' ';
	$sql .= 'AND `function` = \'template\' ';
	$sql .= 'ORDER BY `name`';
    if( ($result = $database->query($sql)) && ($result->numRows() > 0) )
	{
		while($addon = $result->fetchRow())
	    {
			$tpl->set_var('FILE', $addon['directory']);
			$tpl->set_var('NAME', $addon['name']);
	        $selected = ($addon['directory'] == $search_template) ? ' selected="selected"' : '';
			$tpl->set_var('SELECTED', $selected);
			$tpl->parse('search_template_list', 'search_template_list_block', true);
		}
	}

// Insert default error reporting values
	require(ADMIN_PATH.'/interface/er_levels.php');
	foreach($ER_LEVELS AS $value => $title)
	{
		$tpl->set_var('VALUE', $value);
		$tpl->set_var('NAME', $title);
	    $selected = (ER_LEVEL == $value) ? ' selected="selected"' : '';
	    $tpl->set_var('SELECTED', $selected);
		$tpl->parse('error_reporting_list', 'error_reporting_list_block', true);
	}

	// Insert page level limits
	for($i = 1; $i <= 10; $i++)
	{
		$tpl->set_var('NUMBER', $i);
		if(PAGE_LEVEL_LIMIT == $i)
	    {
			$tpl->set_var('SELECTED', ' selected="selected"');
		} else {
			$tpl->set_var('SELECTED', '');
		}
		$tpl->parse('page_level_limit_list', 'page_level_limit_list_block', true);
	}

	// Work-out if multiple menus feature is enabled
	if(defined('MULTIPLE_MENUS') && MULTIPLE_MENUS == true)
	{
		$tpl->set_var('MULTIPLE_MENUS_ENABLED', ' checked="checked"');
	} else {
		$tpl->set_var('MULTIPLE_MENUS_DISABLED', ' checked="checked"');
	}

	// Work-out if page languages feature is enabled
	if(defined('PAGE_LANGUAGES') && PAGE_LANGUAGES == true)
	{
	        $tpl->set_var('PAGE_LANGUAGES_ENABLED', ' checked="checked"');
	} else {
	        $tpl->set_var('PAGE_LANGUAGES_DISABLED', ' checked="checked"');
	}

	/* Make's sure GD library is installed */
	if(extension_loaded('gd') && function_exists('imageCreateFromJpeg'))
	{
		$tpl->set_var('GD_EXTENSION_ENABLED', '');
	} else {
		$tpl->set_var('GD_EXTENSION_ENABLED', ' style="display: none;"');
	}

	// Work-out if section blocks feature is enabled
	if(defined('SECTION_BLOCKS') && SECTION_BLOCKS == true)
	{
		$tpl->set_var('SECTION_BLOCKS_ENABLED', ' checked="checked"');
	} else {
		$tpl->set_var('SECTION_BLOCKS_DISABLED', ' checked="checked"');
	}

	// Work-out if homepage redirection feature is enabled
	if(defined('HOMEPAGE_REDIRECTION') && HOMEPAGE_REDIRECTION == true)
	{
		$tpl->set_var('HOMEPAGE_REDIRECTION_ENABLED', ' checked="checked"');
	} else {
		$tpl->set_var('HOMEPAGE_REDIRECTION_DISABLED', ' checked="checked"');
	}

	// Work-out if manage sections feature is enabled
	if(MANAGE_SECTIONS)
	{
		$tpl->set_var('MANAGE_SECTIONS_ENABLED', ' checked="checked"');
	} else {
		$tpl->set_var('MANAGE_SECTIONS_DISABLED', ' checked="checked"');
	}

// work-out advanced settings
	if( $is_advanced )
	{
		$tpl->set_var('ADVANCED_FILE_PERMS_ID', 'file_perms_box');
		$tpl->set_var('BASIC_FILE_PERMS_ID', 'show');
	} else {
		$tpl->set_var('BASIC_FILE_PERMS_ID', 'file_perms_box');
		$tpl->set_var('ADVANCED_FILE_PERMS_ID', 'show');
	}

// Work-out which server os should be checked
	if (OPERATING_SYSTEM == 'linux')	{
		$tpl->set_var('LINUX_SELECTED', ' checked="checked"');
		$tpl->set_var('show77', 'block');
	} elseif(OPERATING_SYSTEM == 'windows') {
		$tpl->set_var('WINDOWS_SELECTED', ' checked="checked"');
		$tpl->set_var('show77', 'none');
	}

// Work-out if 777 permissions are set
	$tpl->set_var('WORLD_WRITEABLE_SELECTED', (STRING_FILE_MODE == '0777' && STRING_DIR_MODE == '0777') ? ' checked="checked"' : '');
    $checked = ' checked="checked"';

	// Work-out which wbmailer routine should be checked
	if(WBMAILER_ROUTINE == 'phpmail')
	{
		$tpl->set_var('PHPMAIL_SELECTED', ' checked="checked"');
		$tpl->set_var('SMTP_VISIBILITY', ' style="display: none;"');
		$tpl->set_var('SMTP_VISIBILITY_AUTH', ' style="display: none;"');
	} elseif(WBMAILER_ROUTINE == 'smtp')
	{
		$tpl->set_var('SMTPMAIL_SELECTED', ' checked="checked"');
		$tpl->set_var('SMTP_VISIBILITY', '');
	}

	// Work-out if SMTP authentification should be checked
	if(WBMAILER_SMTP_AUTH)
	{
		$tpl->set_var('SMTP_AUTH_SELECTED', ' checked="checked"');
		if(WBMAILER_ROUTINE == 'smtp')
	    {
			$tpl->set_var('SMTP_VISIBILITY_AUTH', '');

		} else {
			$tpl->set_var('SMTP_VISIBILITY_AUTH', ' style="display: none;"');
		}
	} else {
		$tpl->set_var('SMTP_VISIBILITY_AUTH', ' style="display: none;"');
	}

	// Work-out if intro feature is enabled
	if(INTRO_PAGE)
	{
		$tpl->set_var('INTRO_PAGE_ENABLED', ' checked="checked"');
	} else {
		$tpl->set_var('INTRO_PAGE_DISABLED', ' checked="checked"');
	}

	// Work-out if frontend login feature is enabled
	if(FRONTEND_LOGIN)
	{
		$tpl->set_var('PRIVATE_ENABLED', ' checked="checked"');
	} else {
		$tpl->set_var('PRIVATE_DISABLED', ' checked="checked"');
	}

	// Work-out if page trash feature is disabled, in-line, or separate
	if(PAGE_TRASH == 'disabled')
	{
		$tpl->set_var('PAGE_TRASH_DISABLED', ' checked="checked"');
		$tpl->set_var('DISPLAY_PAGE_TRASH_SEPARATE', 'display: none;');
	} elseif(PAGE_TRASH == 'inline')
	{
		$tpl->set_var('PAGE_TRASH_INLINE', ' checked="checked"');
		$tpl->set_var('DISPLAY_PAGE_TRASH_SEPARATE', 'display: none;');
	} elseif(PAGE_TRASH == 'separate')
	{
		$tpl->set_var('PAGE_TRASH_SEPARATE', ' checked="checked"');
		$tpl->set_var('DISPLAY_PAGE_TRASH_SEPARATE', 'display: inline;');
	}

	// Work-out if media home folde feature is enabled
	if(HOME_FOLDERS)
	{
		$tpl->set_var('HOME_FOLDERS_ENABLED', ' checked="checked"');
	} else {
		$tpl->set_var('HOME_FOLDERS_DISABLED', ' checked="checked"');
	}

	// Insert search select
	if(SEARCH == 'private')
	{
		$tpl->set_var('PRIVATE_SEARCH', ' selected="selected"');
	} elseif(SEARCH == 'registered') {
		$tpl->set_var('REGISTERED_SEARCH', ' selected="selected"');
	} elseif(SEARCH == 'none') {
		$tpl->set_var('NONE_SEARCH', ' selected="selected"');
	}
// Insert Server Email value into template
	$tpl->set_var('SERVER_EMAIL', SERVER_EMAIL);

// Insert groups into signup list
	// $results = $database->query("SELECT group_id, name FROM ".TABLE_PREFIX."groups WHERE group_id != '1'");
	$sql  = 'SELECT `group_id`, `name` FROM `'.TABLE_PREFIX.'groups` ';
	$sql .= 'WHERE `group_id` != 1';
	if( ($result = $database->query($sql)) && ($result->numRows() > 0) )
	{
		while($groups = $result->fetchRow())
	    {
			$tpl->set_var('ID', $groups['group_id']);
			$tpl->set_var('NAME', $groups['name']);
			if(FRONTEND_SIGNUP == $groups['group_id'])
	        {
				$tpl->set_var('SELECTED', ' selected="selected"');
			} else {
				$tpl->set_var('SELECTED', '');
			}
			$tpl->parse('group_list', 'group_list_block', true);
		}
	} else {
		$tpl->set_var('ID', 'disabled');
		$tpl->set_var('NAME', $MESSAGE['GROUPS_NO_GROUPS_FOUND']);
		$tpl->parse('group_list', 'group_list_block', true);
	}

// Insert language headings
	$tpl->set_var(array(
		'HEADING_GENERAL_SETTINGS' => $HEADING['GENERAL_SETTINGS'],
		'HEADING_DEFAULT_SETTINGS' => $HEADING['DEFAULT_SETTINGS'],
		'HEADING_ADVANCE_SETTINGS' => $TEXT['VISIBILITY'],
		'HEADING_SEARCH_SETTINGS' => $HEADING['SEARCH_SETTINGS'],
		'HEADING_SECURITY_SETTINGS' => $HEADING['SECURITY_SETTINGS'],
		'HEADING_SERVER_SETTINGS' => $HEADING['SERVER_SETTINGS'],
		'HEADING_WBMAILER_SETTINGS' => $HEADING['WBMAILER_SETTINGS'],
		'HEADING_ADMINISTRATION_TOOLS' => $HEADING['ADMINISTRATION_TOOLS']
		));

// Insert language text and messages
	$tpl->set_var(array(
		'TEXT_WEBSITE_TITLE' => $TEXT['WEBSITE_TITLE'],
		'TEXT_WEBSITE_DESCRIPTION' => $TEXT['WEBSITE_DESCRIPTION'],
		'TEXT_WEBSITE_KEYWORDS' => $TEXT['WEBSITE_KEYWORDS'],
		'TEXT_WEBSITE_HEADER' => $TEXT['WEBSITE_HEADER'],
		'TEXT_WEBSITE_FOOTER' => $TEXT['WEBSITE_FOOTER'],
		'TEXT_HEADER' => $TEXT['HEADER'],
		'TEXT_FOOTER' => $TEXT['FOOTER'],
		'TEXT_VISIBILITY' => $TEXT['VISIBILITY'],
		'TEXT_RESULTS_HEADER' => $TEXT['RESULTS_HEADER'],
		'TEXT_RESULTS_LOOP' => $TEXT['RESULTS_LOOP'],
		'TEXT_RESULTS_FOOTER' => $TEXT['RESULTS_FOOTER'],
		'TEXT_NO_RESULTS' => $TEXT['NO_RESULTS'],
		'TEXT_TEXT' => $TEXT['TEXT'],
		'TEXT_DEFAULT' => $TEXT['DEFAULT'],
		'TEXT_LANGUAGE' => $TEXT['LANGUAGE'],
		'TEXT_TIMEZONE' => $TEXT['TIMEZONE'],
		'TEXT_CHARSET' => $TEXT['CHARSET'],
		'TEXT_DATE_FORMAT' => $TEXT['DATE_FORMAT'],
		'TEXT_TIME_FORMAT' => $TEXT['TIME_FORMAT'],
		'TEXT_TEMPLATE' => $TEXT['TEMPLATE'],
		'TEXT_THEME' => $TEXT['THEME'],
		'TEXT_LEPTOKEN_LIFETIME' => $TEXT['LEPTOKEN_LIFETIME'],
		'HELP_LEPTOKEN_LIFETIME' => $TEXT['HELP_LEPTOKEN_LIFETIME'],
		'TEXT_MAX_ATTEMPTS' => $TEXT['MAX_ATTEMPTS'],
		'HELP_MAX_ATTEMPTS' => $TEXT['HELP_MAX_ATTEMPTS'],
		'TEXT_WYSIWYG_EDITOR' => $TEXT['WYSIWYG_EDITOR'],
		'TEXT_PAGE_LEVEL_LIMIT' => $TEXT['PAGE_LEVEL_LIMIT'],
		'TEXT_INTRO_PAGE' => $TEXT['INTRO_PAGE'],
		'TEXT_FRONTEND' => $TEXT['FRONTEND'],
		'TEXT_LOGIN' => $TEXT['LOGIN'],
		'TEXT_REDIRECT_AFTER' => $TEXT['REDIRECT_AFTER'],
		'TEXT_SIGNUP' => $TEXT['SIGNUP'],
		'TEXT_PHP_ERROR_LEVEL' => $TEXT['PHP_ERROR_LEVEL'],
		'TEXT_PAGES_DIRECTORY' => $TEXT['PAGES_DIRECTORY'],
		'TEXT_MEDIA_DIRECTORY' => $TEXT['MEDIA_DIRECTORY'],
		'TEXT_PAGE_EXTENSION' => $TEXT['PAGE_EXTENSION'],
		'TEXT_PAGE_SPACER' => $TEXT['PAGE_SPACER'],
		'TEXT_RENAME_FILES_ON_UPLOAD' => $TEXT['ALLOWED_FILETYPES_ON_UPLOAD'],
		'TEXT_APP_NAME' => $TEXT['APP_NAME'],
		'TEXT_SESSION_IDENTIFIER' => $TEXT['SESSION_IDENTIFIER'],
		'TEXT_SEC_ANCHOR' => $TEXT['SEC_ANCHOR'],
		'TEXT_SERVER_OPERATING_SYSTEM' => $TEXT['SERVER_OPERATING_SYSTEM'],
		'TEXT_LINUX_UNIX_BASED' => $TEXT['LINUX_UNIX_BASED'],
		'TEXT_WINDOWS' => $TEXT['WINDOWS'],
		'TEXT_ADMIN' => $TEXT['ADMIN'],
		'TEXT_TYPE' => $TEXT['TYPE'],
		'TEXT_DATABASE' => $TEXT['DATABASE'],
		'TEXT_HOST' => $TEXT['HOST'],
		'TEXT_USERNAME' => $TEXT['USERNAME'],
		'TEXT_PASSWORD' => $TEXT['PASSWORD'],
		'TEXT_NAME' => $TEXT['NAME'],
		'TEXT_TABLE_PREFIX' => $TEXT['TABLE_PREFIX'],
		'TEXT_SAVE' => $TEXT['SAVE'],
		'TEXT_RESET' => $TEXT['RESET'],
		'TEXT_CHANGES' => $TEXT['CHANGES'],
		'TEXT_ENABLED' => $TEXT['ENABLED'],
		'TEXT_DISABLED' => $TEXT['DISABLED'],
		'TEXT_MANAGE_SECTIONS' => $HEADING['MANAGE_SECTIONS'],
		'TEXT_MANAGE' => $TEXT['MANAGE'],
		'TEXT_SEARCH' => $TEXT['SEARCH'],
		'TEXT_PUBLIC' => $TEXT['PUBLIC'],
		'TEXT_PRIVATE' => $TEXT['PRIVATE'],
		'TEXT_REGISTERED' => $TEXT['REGISTERED'],
		'TEXT_NONE' => $TEXT['NONE'],
		'TEXT_FILES' => strtoupper(substr($TEXT['FILES'], 0, 1)).substr($TEXT['FILES'], 1),
		'TEXT_DIRECTORIES' => $TEXT['DIRECTORIES'],
		'TEXT_FILESYSTEM_PERMISSIONS' => $TEXT['FILESYSTEM_PERMISSIONS'],
		'TEXT_CUSTOM' => $TEXT['CUSTOM'],
		'TEXT_USER' => $TEXT['USER'],
		'TEXT_GROUP' => $TEXT['GROUP'],
		'TEXT_OTHERS' => $TEXT['OTHERS'],
		'TEXT_READ' => $TEXT['READ'],
		'TEXT_WRITE' => $TEXT['WRITE'],
		'TEXT_EXECUTE' => $TEXT['EXECUTE'],
		'TEXT_MULTIPLE_MENUS' => $TEXT['MULTIPLE_MENUS'],
		'TEXT_HOMEPAGE_REDIRECTION' => $TEXT['HOMEPAGE_REDIRECTION'],
		'TEXT_SECTION_BLOCKS' => $TEXT['SECTION_BLOCKS'],
		'TEXT_PLEASE_SELECT' => $TEXT['PLEASE_SELECT'],
		'TEXT_PAGE_TRASH' => $TEXT['PAGE_TRASH'],
		'TEXT_PAGE_LANGUAGES' => $TEXT['PAGE_LANGUAGES'],
		'TEXT_INLINE' => $TEXT['INLINE'],
		'TEXT_SEPARATE' => $TEXT['SEPARATE'],
		'TEXT_HOME_FOLDERS' => $TEXT['HOME_FOLDERS'],
		'TEXT_WYSIWYG_STYLE' => $TEXT['WYSIWYG_STYLE'],
		'TEXT_WORLD_WRITEABLE_FILE_PERMISSIONS' => $TEXT['WORLD_WRITEABLE_FILE_PERMISSIONS'],
		'TEXT_WBMAILER_DEFAULT_SETTINGS_NOTICE' => $TEXT['WBMAILER_DEFAULT_SETTINGS_NOTICE'],
		'TEXT_WBMAILER_DEFAULT_SENDER_MAIL' => $TEXT['WBMAILER_DEFAULT_SENDER_MAIL'],
		'TEXT_WBMAILER_DEFAULT_SENDER_NAME' => $TEXT['WBMAILER_DEFAULT_SENDER_NAME'],
		'TEXT_WBMAILER_NOTICE' => $TEXT['WBMAILER_NOTICE'],
		'TEXT_WBMAILER_FUNCTION' => $TEXT['WBMAILER_FUNCTION'],
		'TEXT_WBMAILER_SMTP_HOST' => $TEXT['WBMAILER_SMTP_HOST'],
		'TEXT_WBMAILER_PHP' => $TEXT['WBMAILER_PHP'],
		'TEXT_WBMAILER_SMTP' => $TEXT['WBMAILER_SMTP'],
		'TEXT_WBMAILER_SMTP_AUTH' => $TEXT['WBMAILER_SMTP_AUTH'],
		'TEXT_WBMAILER_SMTP_AUTH_NOTICE' => $TEXT['WBMAILER_SMTP_AUTH_NOTICE'],
		'TEXT_WBMAILER_SMTP_USERNAME' => $TEXT['WBMAILER_SMTP_USERNAME'],
		'TEXT_WBMAILER_SMTP_PASSWORD' => $TEXT['WBMAILER_SMTP_PASSWORD'],
		'TEXT_WBMAILER_SENDTESTMAIL' => $TEXT['WBMAILER_SEND_TESTMAIL'],
		'MODE_SWITCH_WARNING' => $MESSAGE['SETTINGS_MODE_SWITCH_WARNING'],
		'WORLD_WRITEABLE_WARNING' => $MESSAGE['SETTINGS_WORLD_WRITEABLE_WARNING'],
		'TEXT_MODULE_ORDER' => $TEXT['MODULE_ORDER'],
		'TEXT_MAX_EXCERPT' => $TEXT['MAX_EXCERPT'],
		'TEXT_TIME_LIMIT' => $TEXT['TIME_LIMIT']
		));

	if ( $curr_user_is_admin )
	{
		$tpl->parse('show_admin', 'show_admin_block', true);
		$tpl->parse('show_access_menu', 'show_access_menu_block', true);
		$tpl->parse('mailer_menu', 'mailer_menu_block', true);
		$tpl->parse('mailer_settings', 'mailer_settings_block', true);
        if($is_advanced) {
			$tpl->parse('show_access', 'show_access_block', true);
			$tpl->parse('smtp_mailer_settings','smtp_mailer_settings_block', true);
        } else {
			$tpl->set_block('show_access', '');
			$tpl->set_block('smtp_mailer_settings', '');
        }
		$tpl->parse('search_footer', 'search_footer_block', true);
		$tpl->parse('access_footer', 'access_footer_block', true);
		$tpl->parse('send_testmail', 'send_testmail_block', true);
		
	}else {
	
		$tpl->set_block('search_footer_block', '');
		$tpl->set_block('access_footer_block', '');
		$tpl->set_block('send_testmail_block', '');
		
		$tpl->set_block('show_admin', '');
		$tpl->set_block('show_access_menu', '');
		$tpl->set_block('mailer_menu', '');
		$tpl->set_block('mailer_settings', '');
		$tpl->set_block('show_access', '');
		$tpl->set_block('smtp_mailer_settings', '');
	}

	if($is_advanced)
	{
		$tpl->parse('show_page_level_limit', 'show_page_level_limit_block', true);
		$tpl->parse('show_checkbox_1',       'show_checkbox_1_block', true);
	 	$tpl->parse('show_checkbox_2',       'show_checkbox_2_block', true);
		$tpl->parse('show_checkbox_3',       'show_checkbox_3_block', true);
		$tpl->parse('show_php_error_level',  'show_php_error_level_block', true);
		$tpl->parse('show_charset',          'show_charset_block', true);
		$tpl->parse('show_wysiwyg',          'show_wysiwyg_block', true);
		$tpl->parse('show_redirect_timer',   'show_redirect_timer_block', true);
	}else {
		$tpl->set_block('show_page_level_limit', '');
		$tpl->set_block('show_checkbox_1', '');
		$tpl->set_block('show_checkbox_2', '');
		$tpl->set_block('show_checkbox_3', '');
		$tpl->set_block('show_php_error_level', '');
		$tpl->set_block('show_charset', '');
		$tpl->set_block('show_wysiwyg', '');
		$tpl->set_block('show_redirect_timer', '');
	}

	// Parse template objects output
	$tpl->parse('main', 'main_block', false);
	$output = $tpl->finish($tpl->pparse('output', 'page'));
}

// test $_GET querystring can only be 1 or 2 (leptoken and may be advanced)
if(isset($_GET) && sizeof($_GET) > 2) {
 die('Acess denied');
}

// test if valid $admin-object already exists (bit complicated about PHP4 Compatibility)
if( !(isset($admin) && is_object($admin) && (get_class($admin) == 'admin')) )
{
	require_once( WB_PATH.'/framework/class.admin.php' );
}

//
if( (isset($_GET['advanced']) && ($_GET['advanced'] == 'no')) || (!isset($_GET['advanced']))) {
	$admin = new admin('Settings', 'settings_basic');
} elseif(isset($_GET['advanced']) && $_GET['advanced'] == 'yes') {
	$admin = new admin('Settings', 'settings_advanced');
}

print build_settings($admin, $database);

$admin->print_footer();

?>