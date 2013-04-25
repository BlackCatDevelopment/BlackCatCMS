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
 * @license         http://www.gnu.org/licenses/gpl.html
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

$val = CAT_Helper_Validate::getInstance();

// prevent this file from being accessed directly in the browser (would set all entries in DB settings table to '')
if (!$val->sanitizePost('default_language') || $val->sanitizePost('default_language') == '')
{
	die( header('Location: index.php'));
}

// Create a javascript back link
global $js_back;
$js_back = "javascript: history.go(-1);";

/**
 *	Find out if the user was view advanced options or not
 *
 */

require_once (CAT_PATH.'/framework/class.admin.php');
/**
 *	Getting the admin-instance and print the "admin header"
 *
 */
$admin = new admin('Settings', 'settings_advanced');

function save_settings(&$admin)
{
	
	global $old_settings, $settings;
	$settings = array();
	$old_settings = array();
    $val          = CAT_Helper_Validate::getInstance();
	
	/**
	 *	Query current settings in the db, then loop through them to get old values
	 *
	 */
	$sql = 'SELECT `name`, `value` FROM `'.CAT_TABLE_PREFIX.'settings` ORDER BY `name`';
	if (false !== ($res_settings = $val->db()->query($sql))) {
		while( false !== ($row = $res_settings->fetchRow( MYSQL_ASSOC ) ) ) {
			$old_settings[$row['name']] = $row['value'];
			$settings[$row['name']]     = $val->sanitizePost($row['name']);
		}
	}
	else
	{
		$err_msg[] = $admin->lang->translate('Unable to load old settings');
	}

	$allow_tags_in_fields = array('website_header', 'website_footer');

	$allow_empty_values = array('website_header','website_footer','sec_anchor','pages_directory');

	// language must be 2 upercase letters only
	$default_language = strtoupper($val->sanitizePost('default_language'));
	$settings['default_language']
        = $admin->lang->checkLang($default_language)
        ? $default_language
        : $old_settings['default_language'];

    // check date format
	$settings['default_date_format']
        = CAT_Helper_DateTime::checkDateformat($val->sanitizePost('default_date_format'))
        ? $val->sanitizePost('default_date_format')
        : $old_settings['default_date_format'];

    // check time format
    $settings['default_time_format']
        = CAT_Helper_DateTime::checkTimeformat($val->sanitizePost('default_time_format'))
        ? $val->sanitizePost('default_time_format')
        : $old_settings['default_date_format'];

	// charsets must be a key from /interface/charsets
    $CHARSETS = $admin->lang->getCharsets();
	$settings['default_charset']
        = (array_key_exists($char_set,$CHARSETS)
        ? $char_set
        : $old_settings['default_charset']);

	//  error reporting values validation
    $ER_LEVELS = CAT_Registry::get('ER_LEVELS','array');
	$settings['er_level']
        = (isset ($settings['er_level']) && (array_key_exists($settings['er_level'], $ER_LEVELS)))
        ? intval($settings['er_level'])
        : $old_settings['er_level'];

	//  count groups_id and <> 1, do it with sql statement if groups were added
	$settings['frontend_login'] = $settings['frontend_login']=='' ? 'false' : 'true';
	if (isset ($settings['frontend_signup']))
	{
		$sql = 'SELECT count(*) AS `tcount` FROM '.CAT_TABLE_PREFIX.'groups ';
		if (($result = $val->db()->query($sql)) && ($result->numRows() > 0))
		{
			$row = $result->fetchRow();
			$settings['frontend_signup']
                = ($settings['frontend_signup'] > 1) && ($settings['frontend_signup'] <= $row['tcount'])
                ? intval($settings['frontend_signup'])
                : $old_settings['frontend_signup']
                ;
		}
	}
	// bools checks
    foreach ( array(
        'home_folders'  , 'homepage_redirection', 'intro_page'    , 'manage_sections',
        'multiple_menus', 'page_languages'      , 'section_blocks', 'page_trash',
        'users_allow_mailaddress' ) as $key )
    {
	    $settings[$key] = ( $settings[$key] == '' ) ? 'false' : 'true';
    }
	$settings['page_trash'] = isset ($settings['page_trash']) ? ($settings['page_trash']) : $old_settings['page_trash'];


    //  we have to check two situations a) is the POST set b) is value within the area
	$page_level_limit = isset ($settings['page_level_limit']) ? intval($settings['page_level_limit']) : $old_settings['page_level_limit'];
	$settings['page_level_limit'] = ($page_level_limit <= 10) ? $page_level_limit : $old_settings['page_level_limit'];
	//  do the same
	$redirect_timer = isset ($settings['redirect_timer']) ? intval($settings['redirect_timer']) : $old_settings['redirect_timer'];
	$settings['redirect_timer'] = (($redirect_timer >= -1) && ($redirect_timer <= 10000)) ? $redirect_timer : $old_settings['redirect_timer'];
	// validate token lifetime
	$token_lifetime = isset ($settings['token_lifetime']) ? $settings['token_lifetime'] : $old_settings['token_lifetime'];
	$settings['token_lifetime'] = ($token_lifetime > -1) ? $token_lifetime : $old_settings['token_lifetime'];
	// validate maximum logon attempts
	$max_attempts = isset ($settings['max_attempts']) ? intval($settings['max_attempts']) : $old_settings['max_attempts'];
	$settings['max_attempts'] = ($max_attempts > 0) ? $max_attempts : $old_settings['max_attempts'];
	//  check templates
	$settings['default_theme'] = isset ($settings['default_theme']) ? ($settings['default_theme']) : $old_settings['default_theme'];
	$settings['default_template'] = isset ($settings['default_template']) ? ($settings['default_template']) : $old_settings['default_template'];
	$settings['app_name'] = isset ($settings['app_name']) ? $settings['app_name'] : $old_settings['app_name'];

	$settings['sec_anchor'] = isset ($settings['sec_anchor']) ? $settings['sec_anchor'] : $old_settings['sec_anchor'];

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
			$err_msg[] = $admin->lang->translate('Section-Anchor text').' '.$admin->lang->translate('must begin with a letter or has invalid signs');
		}
	}

	// Work-out file mode
	// Check if should be set to 777 or left alone
	if ($val->sanitizePost('world_writeable') == 'true')
	{
		$settings['string_file_mode'] = '0666';
		$settings['string_dir_mode'] = '0777';
	}
	else {
		$settings['string_file_mode'] = '0644';
		$settings['string_dir_mode'] = '0755';
	}

	// check home folder settings
	// remove home folders for all users if the option is changed to "false"
	if ( !isset($settings['home_folders']) && $old_settings['home_folders'] == 'true' ) {
		$sql = 'UPDATE `'.CAT_TABLE_PREFIX.'users` ';
		$sql .= 'SET `home_folder` = \'\';';
		if (!$val->db()->query($sql))
		{
			$err_msg[] = $val->db()->get_error();
		}
	}
	
	// check webmailer settings

	// email should be validated by core
	// Work-out which wbmailer routine should be checked
	if ((isset ($settings['server_email'])) && (!$val->validate_email($settings['server_email'])))
	{
		$err_msg[] = $admin->lang->translate('Default Sender Name');
	}
	$catmailer_default_sendername = (isset ($settings['catmailer_default_sendername'])) ? $settings['catmailer_default_sendername'] : $old_settings['catmailer_default_sendername'];
	if (($catmailer_default_sendername <> ''))
	{
		$settings['catmailer_default_sendername'] = $catmailer_default_sendername;
	}
	else
	{
		$err_msg[] = $MESSAGE['MOD_FORM_REQUIRED_FIELDS'].': '.$admin->lang->translate('Default Sender Name');
	}
	$catmailer_routine = isset ($settings['catmailer_routine']) ? $settings['catmailer_routine'] : $old_settings['catmailer_routine'];
	if (($catmailer_routine == 'smtp'))
	{
	// Work-out return the 1th mail domain from a poassible textblock
		$pattern = '#https?://([A-Z0-9][^:][A-Z.0-9_-]+[a-z]{2,6})#ix';
		$catmailer_smtp_host = (isset ($settings['catmailer_smtp_host'])) ? $settings['catmailer_smtp_host'] : $old_settings['catmailer_smtp_host'];
		if (preg_match($pattern, $catmailer_smtp_host, $array))
		{
			$catmailer_smtp_host = $array [0];
		}
		if ((isset ($catmailer_smtp_host)))
		{
			if ((isset ($catmailer_smtp_host)) && ($catmailer_smtp_host != ''))
			{
				$settings['catmailer_smtp_host'] = $catmailer_smtp_host;
			}
			else
			{
				$err_msg[] = $admin->lang->translate('You must enter details for the following fields').': '.$admin->lang->translate('SMTP Host');
			}
		}
		// Work-out if SMTP authentification should be checked
		$settings['catmailer_smtp_auth'] = $settings['catmailer_smtp_auth']=='' ? 'false' : 'true';
		if ($settings['catmailer_smtp_auth'] == 'true')
		{
		// later change min and max lenght with variables
			$pattern = '/^[a-zA-Z0-9_]{4,30}$/';
			$catmailer_smtp_username = (isset ($settings['catmailer_smtp_username'])) ? $settings['catmailer_smtp_username'] : $old_settings['catmailer_smtp_username'];
			if (($catmailer_smtp_username == '') && !preg_match($pattern, $catmailer_smtp_username))
			{
				$err_msg[] = $admin->lang->translate('SMTP').': '.$admin->lang->translate('Username or password incorrect');
			}
			else
			{
				$settings['catmailer_smtp_username'] = $catmailer_smtp_username;
			}
			// receive password vars and calculate needed action
			$pattern = '/[^'.$admin->password_chars.']/';
			$current_password = $val->sanitizePost('catmailer_smtp_password');
			$current_password = ($current_password == null ? '' : $current_password);
			if (($current_password == ''))
			{
				$err_msg[] = $admin->lang->translate('SMTP').': '.$admin->lang->translate('Username or password incorrect');
			}
			elseif (preg_match($pattern, $current_password))
			{
				$err_msg[] = $admin->lang->translate('Invalid password chars used, valid chars are: a-z\A-Z\0-9\_\-\!\#\*\+');
			}
		}
		// If SMTP-Authentification is disabled delete USER and PASSWORD for securityreasons
		else {
			$settings['catmailer_smtp_username'] = '-';
			$settings['catmailer_smtp_password'] = '-';
		}
	}

	// if no validation errors, try to update the database, otherwise return errormessages
	if (sizeof($err_msg) == 0)
	{
	// Query current settings in the db, then loop through them and update the db with the new value
		$sql = 'SELECT `name` FROM `'.CAT_TABLE_PREFIX.'settings` ';
		$sql .= 'ORDER BY `name`';
		$results = $val->db()->query($sql);
		while (false !== ($row = $results->fetchRow()))
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
				$value = trim($val->add_slashes($value));
				$sql = 'UPDATE `'.CAT_TABLE_PREFIX.'settings` ';
				$sql .= 'SET `value` = \''.$value.'\' ';
				$sql .= 'WHERE `name` <> \'cat_version\' ';
				$sql .= 'AND `name` = \''.$setting_name.'\' ';

				if ($val->db()->query($sql))
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
		$sql = 'SELECT `name`, `value` FROM `'.CAT_TABLE_PREFIX.'search` ';
		$sql .= 'WHERE `extra` = ""';
		$res_search = $val->db()->query($sql);
		while (false !== ($row = $res_search->fetchRow()))
		{
			$old_value = $row['value'];
			$post_name = 'search_'.$row['name'];
			$value = $val->sanitizePost($post_name);
			// hold old value if post is empty
			if (isset ($value))
			{
			// check search template
				$value = (($value == '') && ($setting_name == 'template')) ? $settings['default_template'] : $val->sanitizePost($post_name);
				$value = (($val->sanitizePost($post_name) == '') && ($setting_name != 'template')) ? $value : $val->sanitizePost($post_name);
				$value = $val->add_slashes($value);
				$sql = 'UPDATE `'.CAT_TABLE_PREFIX.'search` ';
				$sql .= 'SET `value` = "'.$value.'" ';
				$sql .= 'WHERE `name` = "'.$row['name'].'" ';
				$sql .= 'AND `extra` = ""';
				if ($val->db()->query($sql))
				{
					$sql_info = mysql_info();
					if (preg_match('/matched: *([1-9][0-9]*)/i', $sql_info) != 1)
					{
					// if the user_id and password dosn't match
						$err_msg[] = $admin->lang->translate('Search settings').': '.$admin->lang->translate('Error saving page');
					}
				}
			}
		}
	}
	return ((sizeof($err_msg) > 0) ? implode('<br />', $err_msg) : '');
}
/*if ($submit == 'advanced')
{   // if Javascript is disabled
	$admin->print_success($TEXT['REDIRECT_AFTER'].' '.$MENU['SETTINGS'], $js_back );
	exit ();
}*/
$retval = save_settings($admin);
if ($retval == '')
{
	$admin->print_success($admin->lang->translate('Settings saved'), $js_back );
}
else
{
	$admin->print_error($retval, $js_back);
}
$backend->print_footer();
?>