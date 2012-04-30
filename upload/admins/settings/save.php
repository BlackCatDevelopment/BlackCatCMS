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


// prevent this file from being accessed directly in the browser (would set all entries in DB settings table to '')
if (!isset ($_POST['default_language']) || $_POST['default_language'] == '')
{
    die( header('Location: index.php'));
}

/**
 *	Find out if the user was view advanced options or not
 *
 */
$advanced = ($_POST['advanced'] == 'yes') ? '?advanced=yes' : '';
$submit = isset ($_POST['submit']) && ($_POST['submit'] == $TEXT['SAVE']) ? 'save' : 'advanced';

require_once (WB_PATH.'/framework/class.admin.php');
/**
 *	Getting the admin-instance and print the "admin header"
 *
 */
if ($advanced == '') {
	$admin = new admin('Settings', 'settings_basic');
} else {
	$admin = new admin('Settings', 'settings_advanced');
}

/**
 *	Create a back link
 *
 */
$js_back = ADMIN_URL.'/settings/index.php'.$advanced;

function save_settings(&$admin, &$database)
{
    global $MESSAGE, $HEADING, $TEXT, $timezone_table;
	
	//	The following line seems to be obsolete, as there is no file "tan.php"
	//	if(file_exists('tan.php')) include('tan.php');

    $err_msg	= array();
    $bool_array	= array('true', '1'); // # 1.0 ???

    /**
     *	Find out if the user was view advanced options or not
     *	M.f.i.	As this test has happend before!
     *
     */
    $advanced = ($_POST['advanced'] == 'yes') ? '?advanced=yes' : '';
    unset ($_POST['advanced']);
    $submit = isset ($_POST['submit']) && ($_POST['submit'] == $TEXT['SAVE']) ? 'save' : '';
    unset ($_POST['submit']);
    
    /** 
     *	Obsolete FTAN test, as we've checked the FTAN before calling this function.
     *	And we can't do the test twice ...
     *
     *	@removed	1.0.0	2010-12-27	by Aldus
     *
     */
    
    global $old_settings, $settings;
    $settings = array();
    $old_settings = array();
	
	/**
	 *	Query current settings in the db, then loop through them to get old values
	 *
	 */
    $sql = 'SELECT `name`, `value` FROM `'.TABLE_PREFIX.'settings` ORDER BY `name`';
    if ($res_settings = $database->query($sql) ) {
        while( false !== ($row = $res_settings->fetchRow( MYSQL_ASSOC ) ) ) {
            $old_settings[$row['name']] = $row['value'];
            /**
             *	set only isset $_POST, special checks later
             *	WARNING: Dietmar-Code structure begins here to became MCD (aka 'mad cow disease')!
             *
             */
            $settings[$row['name']] = $admin->get_post($row['name']);
        }
    }
    else
    {
        $err_msg[] = $MESSAGE['SETTINGS_UNABLE_OPEN_CONFIG'];
    }

    $allow_tags_in_fields = array('website_header', 'website_footer');

	$allow_empty_values = array('website_description','website_keywords','website_header','website_footer','sec_anchor','pages_directory');

    // language must be 2 upercase letters only
    $default_language = strtoupper( $admin->get_post('default_language'));
    $settings['default_language'] = (preg_match('/^[A-Z]{2}$/', $default_language) ? $default_language : $old_settings['default_language']);
    // without default value
    $user_time = false;
    
	// timezone must match a value in the table
	$default_timezone_string = DEFAULT_TIMEZONESTRING;
	if (in_array($admin->get_post('default_timezone_string'), $timezone_table)) {
		$default_timezone_string = $admin->get_post('default_timezone_string');
	} 

	// date_format must be a key from /interface/date_formats
    $default_date_format = $admin->get_post('default_date_format');
    $date_format_key = str_replace(' ', '|', $default_date_format);
    include (ADMIN_PATH.'/interface/date_formats.php');
    $settings['default_date_format'] = (array_key_exists($date_format_key, $DATE_FORMATS) ? $default_date_format : $old_settings['default_date_format']);
    unset ($DATE_FORMATS);
    // time_format must be a key from /interface/time_formats
    $time_format = $admin->get_post('default_time_format');
    $time_format_key = str_replace(' ', '|', $time_format);
    include (ADMIN_PATH.'/interface/time_formats.php');
    $settings['default_time_format'] = (array_key_exists($time_format_key, $TIME_FORMATS) ? $time_format : $old_settings['default_time_format']);
    unset ($TIME_FORMATS);
    // charsets must be a key from /interface/charsets
    $char_set = ($admin->get_post('default_charset'));
    include (ADMIN_PATH.'/interface/charsets.php');
    $settings['default_charset'] = (array_key_exists($char_set, $CHARSETS) ? $char_set : $old_settings['default_charset']);
    unset ($CHARSETS);
    //  error reporting values validation
    require (ADMIN_PATH.'/interface/er_levels.php');
    $settings['er_level'] = isset ($settings['er_level']) && (array_key_exists($settings['er_level'], $ER_LEVELS)) ? intval($settings['er_level']) : $old_settings['er_level'];
    unset ($ER_LEVELS);
    //  count groups_id and <> 1, do it with sql statement if groups were added
    $settings['frontend_login'] = (isset ($settings['frontend_login'])) ? ($settings['frontend_login']) : $old_settings['frontend_login'];
    if (isset ($settings['frontend_signup']))
    {
		$gid = (int)$settings['frontend_signup'];
		if ($gid == 0) {
			$settings['frontend_signup'] = 0;  // no frontend_signup allowed
		} else {
			$sql = "SELECT * FROM ".TABLE_PREFIX."groups WHERE group_id = $gid";
			if (($result = $database->query($sql)) && ($result->numRows() > 0)) {
				$settings['frontend_signup'] = $gid;
			} else {
				$settings['frontend_signup'] = $old_settings['frontend_signup'];
			}
		}
    }
    // bools checks
    $settings['home_folders'] = isset ($settings['home_folders']) ? ($settings['home_folders']) : $old_settings['home_folders'];
    $settings['homepage_redirection'] = isset ($settings['homepage_redirection']) ? ($settings['homepage_redirection']) : $old_settings['homepage_redirection'];
    $settings['intro_page'] = isset ($settings['intro_page']) ? ($settings['intro_page']) : $old_settings['intro_page'];
    $settings['manage_sections'] = isset ($settings['manage_sections']) ? ($settings['manage_sections']) : $old_settings['manage_sections'];
    $settings['multiple_menus'] = isset ($settings['multiple_menus']) ? ($settings['multiple_menus']) : $old_settings['multiple_menus'];
    $settings['page_languages'] = isset ($settings['page_languages']) ? ($settings['page_languages']) : $old_settings['page_languages'];
    $settings['section_blocks'] = isset ($settings['section_blocks']) ? ($settings['section_blocks']) : $old_settings['section_blocks'];
    $settings['page_trash'] = isset ($settings['page_trash']) ? ($settings['page_trash']) : $old_settings['page_trash'];
    //  we have to check two situations a) is the POST set b) is vakue within the area
    $page_level_limit = isset ($settings['page_level_limit']) ? intval($settings['page_level_limit']) : $old_settings['page_level_limit'];
    $settings['page_level_limit'] = ($page_level_limit <= 10) ? $page_level_limit : $old_settings['page_level_limit'];
    //  do the same
    $redirect_timer = isset ($settings['redirect_timer']) ? intval($settings['redirect_timer']) : $old_settings['redirect_timer'];
    $settings['redirect_timer'] = (($redirect_timer >= -1) && ($redirect_timer <= 10000)) ? $redirect_timer : $old_settings['redirect_timer'];
	// validate Leptoken lifetime
    $leptoken_lifetime = isset ($settings['leptoken_lifetime']) ? $settings['leptoken_lifetime'] : $old_settings['leptoken_lifetime'];
    $settings['leptoken_lifetime'] = ($leptoken_lifetime > -1) ? $leptoken_lifetime : $old_settings['leptoken_lifetime'];
	// validate maximum logon attempts
    $max_attempts = isset ($settings['max_attempts']) ? intval($settings['max_attempts']) : $old_settings['max_attempts'];
    $settings['max_attempts'] = ($max_attempts > 0) ? $max_attempts : $old_settings['max_attempts'];
    //  check templates
    $settings['default_theme'] = isset ($settings['default_theme']) ? ($settings['default_theme']) : $old_settings['default_theme'];
    $settings['default_template'] = isset ($settings['default_template']) ? ($settings['default_template']) : $old_settings['default_template'];
    $settings['app_name'] = isset ($settings['app_name']) ? $settings['app_name'] : $old_settings['app_name'];

    $settings['sec_anchor'] = isset ($settings['sec_anchor']) ? $settings['sec_anchor'] : $old_settings['sec_anchor'];
/**
 *	M.f.i.	Pages_directory could be emty
 */
	$settings['pages_directory'] = isset ($settings['pages_directory']) ? '/'.$settings['pages_directory'] : $old_settings['pages_directory'];
	$bad = array('"','`','!','@','#','$','%','^','&','*','=','+','|',';',':',',','?'	);
	$settings['pages_directory'] = str_replace($bad, '', $settings['pages_directory']);
	$settings['pages_directory'] = str_replace('\\', '/', $settings['pages_directory']);
	$pattern = '#[/][a-z,0-9_-]+#';
	preg_match($pattern, $settings['pages_directory'], $array);
	$settings['pages_directory'] = (isset($array['0']) ? $array['0'] : "");

    if(!empty($settings['sec_anchor']))
	{
		// must begin with a letter
		$pattern = '/^[a-z][a-z_0-9]*$/i';
		if(!preg_match($pattern, $settings['sec_anchor'], $array))
		{
			$err_msg[] = $TEXT['SEC_ANCHOR'].' '.$TEXT['INVALID_SIGNS'];
		}
	}

    // Work-out file mode
    if ($advanced == '')
    {
    // Check if should be set to 777 or left alone
        if (isset ($_POST['world_writeable']) && $_POST['world_writeable'] == 'true')
        {
            $settings['string_file_mode'] = '0666';
            $settings['string_dir_mode'] = '0777';
        }
    }
    else
    {
        $settings['string_dir_mode'] = '0755';
        $settings['string_file_mode'] = '0644';
    }

	include WB_PATH.'/framework/backend_switch.php';

    // check home folder settings
    // remove home folders for all users if the option is changed to "false"
    if ( $settings['home_folders'] == 'false' && $old_settings['home_folders'] == 'true' ) {
        $sql = 'UPDATE `'.TABLE_PREFIX.'users` ';
        $sql .= 'SET `home_folder` = \'\';';
        if (!$database->query($sql))
        {
            $err_msg[] = $database->get_error();
        }
        
    }
    
    // check webmailer settings
    // email should be validatet by core
    // Work-out which wbmailer routine should be checked
    if ((isset ($settings['server_email'])) && (!$admin->validate_email($settings['server_email'])))
    {
        $err_msg[] = $TEXT['WBMAILER_DEFAULT_SENDER_MAIL'];
    }
    $wbmailer_default_sendername = (isset ($settings['wbmailer_default_sendername'])) ? $settings['wbmailer_default_sendername'] : $old_settings['wbmailer_default_sendername'];
    if (($wbmailer_default_sendername <> ''))
    {
        $settings['wbmailer_default_sendername'] = $wbmailer_default_sendername;
    }
    else
    {
        $err_msg[] = $MESSAGE['MOD_FORM_REQUIRED_FIELDS'].': '.$TEXT['WBMAILER_DEFAULT_SENDER_NAME'];
    }
    $wbmailer_routine = isset ($settings['wbmailer_routine']) ? $settings['wbmailer_routine'] : $old_settings['wbmailer_routine'];
    if (($wbmailer_routine == 'smtp'))
    {
    // Work-out return the 1th mail domain from a poassible textblock
        $pattern = '#https?://([A-Z0-9][^:][A-Z.0-9_-]+[a-z]{2,6})#ix';
        $wbmailer_smtp_host = (isset ($settings['wbmailer_smtp_host'])) ? $settings['wbmailer_smtp_host'] : $old_settings['wbmailer_smtp_host'];
        if (preg_match($pattern, $wbmailer_smtp_host, $array))
        {
            $wbmailer_smtp_host = $array [0];
        }
        if ((isset ($wbmailer_smtp_host)))
        {
            if ((isset ($wbmailer_smtp_host)) && ($wbmailer_smtp_host != ''))
            {
                $settings['wbmailer_smtp_host'] = $wbmailer_smtp_host;
            }
            else
            {
                $err_msg[] = $MESSAGE['MOD_FORM_REQUIRED_FIELDS'].': '.$TEXT['WBMAILER_SMTP_HOST'];
            }
        }
        // Work-out if SMTP authentification should be checked
        $wbmailer_smtp_auth = isset ($settings['wbmailer_smtp_auth']) && ($settings['wbmailer_smtp_auth'] == 'true') ? 'true' : 'false';
        $settings['wbmailer_smtp_auth'] = $wbmailer_smtp_auth;
        if (($wbmailer_smtp_auth == 'true') && ($settings['wbmailer_routine'] == 'smtp'))
        {
        // later change min and max lenght with variables
            $pattern = '/^[a-zA-Z0-9_]{4,30}$/';
            $wbmailer_smtp_username = (isset ($settings['wbmailer_smtp_username'])) ? $settings['wbmailer_smtp_username'] : $old_settings['wbmailer_smtp_username'];
            if (($wbmailer_smtp_username == '') && !preg_match($pattern, $wbmailer_smtp_username))
            {
                $err_msg[] = $TEXT['WBMAILER_SMTP'].': '.$MESSAGE['LOGIN_AUTHENTICATION_FAILED'];
            }
            else
            {
                $settings['wbmailer_smtp_username'] = $wbmailer_smtp_username;
            }
            // receive password vars and calculate needed action
            $pattern = '/[^'.$admin->password_chars.']/';
            $current_password = $admin->get_post('wbmailer_smtp_password');
            $current_password = ($current_password == null ? '' : $current_password);
            if (($current_password == ''))
            {
                $err_msg[] = $TEXT['WBMAILER_SMTP'].': '.$MESSAGE['LOGIN_AUTHENTICATION_FAILED'];
            }
            elseif (preg_match($pattern, $current_password))
            {
                $err_msg[] = $MESSAGE['PREFERENCES_INVALID_CHARS'];
            }
        }
    }

    // if no validation errors, try to update the database, otherwise return errormessages
    if (sizeof($err_msg) == 0)
    {
    // Query current settings in the db, then loop through them and update the db with the new value
        $sql = 'SELECT `name` FROM `'.TABLE_PREFIX.'settings` ';
        $sql .= 'ORDER BY `name`';
        $results = $database->query($sql);
        while ($row = $results->fetchRow())
        {
        // get fieldname from table and store it
            $setting_name = $row['name'];
            // set saved POST value from stored fieldname
            $value = $settings[$row['name']];
            if (!in_array($setting_name, $allow_tags_in_fields))
            {
                $value = strip_tags($value);
            }

            $passed = in_array($setting_name, $allow_empty_values);

            if ((trim($value) <> '') || $passed == true )
            {
                $value = trim($admin->add_slashes($value));
                $sql = 'UPDATE `'.TABLE_PREFIX.'settings` ';
                $sql .= 'SET `value` = \''.$value.'\' ';
                $sql .= 'WHERE `name` <> \'wb_version\' ';
                $sql .= 'AND `name` = \''.$setting_name.'\' ';

                if ($database->query($sql))
                {
                    $sql_info = mysql_info();
                    if (preg_match('/matched: *([1-9][0-9]*)/i', $sql_info) != 1)
                    {
                        $err_msg[] = $MESSAGE['SETTINGS_UNABLE_WRITE_CONFIG'];
                    }
                }
            }
        }
        // Query current search settings in the db, then loop through them and update the db with the new value
        $sql = 'SELECT `name`, `value` FROM `'.TABLE_PREFIX.'search` ';
        $sql .= 'WHERE `extra` = ""';
        $res_search = $database->query($sql);
        while ($row = $res_search->fetchRow())
        {
            $old_value = $row['value'];
            $post_name = 'search_'.$row['name'];
            $value = $admin->get_post($post_name);
            // hold old value if post is empty
            if (isset ($value))
            {
            // check search template
                $value = (($value == '') && ($setting_name == 'template')) ? $settings['default_template'] : $admin->get_post($post_name);
                $value = (($admin->get_post($post_name) == '') && ($setting_name != 'template')) ? $value : $admin->get_post($post_name);
                $value = $admin->add_slashes($value);
                $sql = 'UPDATE `'.TABLE_PREFIX.'search` ';
                $sql .= 'SET `value` = "'.$value.'" ';
                $sql .= 'WHERE `name` = "'.$row['name'].'" ';
                $sql .= 'AND `extra` = ""';
                if ($database->query($sql))
                {
                    $sql_info = mysql_info();
                    if (preg_match('/matched: *([1-9][0-9]*)/i', $sql_info) != 1)
                    {
                    // if the user_id and password dosn't match
                        $err_msg[] = $HEADING['SEARCH_SETTINGS'].': '.$MESSAGE['PAGES_NOT_SAVED'];
                    }
                }
            }
        }
    }
    return ((sizeof($err_msg) > 0) ? implode('<br />', $err_msg) : '');
}

if ($submit == 'advanced')
{   // if Javascript is disabled
    $admin->print_success($TEXT['REDIRECT_AFTER'].' '.$MENU['SETTINGS'], $js_back );
    exit ();
}
$retval = save_settings($admin, $database);
if ($retval == '')
{
    $admin->print_success($MESSAGE['SETTINGS_SAVED'], $js_back );
}
else
{
    $admin->print_error($retval, $js_back);
}
$admin->print_footer();
?>