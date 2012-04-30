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



require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Preferences');
$js_back = "javascript: history.go(-1);"; // Create a javascript back link
include_once( ADMIN_PATH.'/interface/timezones.php' );

function save_preferences( &$admin, &$database)
{
	global $MESSAGE, $timezone_table;
	$err_msg = array();

// Get entered values and validate all
	// remove any dangerouse chars from display_name
	$display_name     = $admin->add_slashes(strip_tags(trim($admin->get_post('display_name'))));
	$display_name     = ( $display_name == '' ? $admin->get_display_name() : $display_name );
	// check that display_name is unique in whoole system (prevents from User-faking)
	$sql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'users` ';
	$sql .= 'WHERE `user_id` <> '.(int)$admin->get_user_id().' AND `display_name` LIKE "'.$display_name.'"';
	if( $database->get_one($sql) > 0 ){ $err_msg[] = $MESSAGE['USERS_USERNAME_TAKEN']; }
// language must be 2 upercase letters only
	$language         = strtoupper($admin->get_post('language'));
	$language         = (preg_match('/^[A-Z]{2}$/', $language) ? $language : DEFAULT_LANGUAGE);
	$user_time = true;
// timezone must match a value in the table
	$timezone_string = DEFAULT_TIMEZONESTRING;
	if (in_array($admin->get_post('timezone_string'), $timezone_table)) {
		$timezone_string = $admin->get_post('timezone_string');
	} 
// date_format must be a key from /interface/date_formats
	$date_format      = $admin->get_post('date_format');
	$date_format_key  = str_replace(' ', '|', $date_format);
	$user_time = true;
	include( ADMIN_PATH.'/interface/date_formats.php' );
	$date_format = (array_key_exists($date_format_key, $DATE_FORMATS) ? $date_format : 'system_default');
	$date_format = ($date_format == 'system_default' ? '' : $date_format);
	unset($DATE_FORMATS);
// time_format must be a key from /interface/time_formats	
	$time_format      = $admin->get_post('time_format');
	$time_format_key  = str_replace(' ', '|', $time_format);
	$user_time = true;
	include( ADMIN_PATH.'/interface/time_formats.php' );
	$time_format = (array_key_exists($time_format_key, $TIME_FORMATS) ? $time_format : 'system_default');
	$time_format = ($time_format == 'system_default' ? '' : $time_format);
	unset($TIME_FORMATS);
// email should be validatet by core
	$email = ( $admin->get_post('email') == null ? '' : $admin->get_post('email') );
	if( !$admin->validate_email($email) )
	{
		$email = '';
		$err_msg[] = $MESSAGE['USERS_INVALID_EMAIL'];
	}else {
	// check that email is unique in whoole system
		$email = $admin->add_slashes($email);
		$sql  = 'SELECT COUNT(*) FROM `'.TABLE_PREFIX.'users` ';
		$sql .= 'WHERE `user_id` <> '.(int)$admin->get_user_id().' AND `email` LIKE "'.$email.'"';
		if( $database->get_one($sql) > 0 ){ $err_msg[] = $MESSAGE['USERS_EMAIL_TAKEN']; }
	}
// receive password vars and calculate needed action
	$current_password = $admin->get_post('current_password');
	$current_password = ($current_password == null ? '' : $current_password);
	$new_password_1   = $admin->get_post('new_password_1');
	$new_password_1   = (($new_password_1 == null || $new_password_1 == '') ? '' : $new_password_1);
	$new_password_2   = $admin->get_post('new_password_2');
	$new_password_2   = (($new_password_2 == null || $new_password_2 == '') ? '' : $new_password_2);
	if($current_password == '')
	{
		$err_msg[] = $MESSAGE['PREFERENCES_CURRENT_PASSWORD_INCORRECT'];
	}else {
	// if new_password is empty, still let current one
		if( $new_password_1 == '' )
		{
			$new_password_1 = $current_password;
			$new_password_2 = $current_password;
		}

	// check for invalid chars
		if( $new_password_1 != $current_password )
		{
			$pattern = '/[^'.$admin->password_chars.']/';
			if( preg_match($pattern, $new_password_1) )
			{
				$err_msg[] = $MESSAGE['PREFERENCES_INVALID_CHARS'];
			}
		}
	// is password lenght matching min_pass_lenght ?
		if( $new_password_1 != $current_password && strlen($new_password_1) < AUTH_MIN_PASS_LENGTH )
		{
			$err_msg[] = $MESSAGE['USERS_PASSWORD_TOO_SHORT'];
		}
	// password_1 matching password_2 ?
		if( $new_password_1 != $new_password_2 )
		{
			$err_msg[] = $MESSAGE['USERS_PASSWORD_MISMATCH'];
		}
	}
	$current_password = md5($current_password);
	$new_password_1   = md5($new_password_1);
	$new_password_2   = md5($new_password_2);
// if no validation errors, try to update the database, otherwise return errormessages
	if(sizeof($err_msg) == 0)
	{
		$sql  = 'UPDATE `'.TABLE_PREFIX.'users` ';
		$sql .= 'SET `display_name` = "'.$display_name.'", ';
		$sql .=     '`password` = "'.$new_password_1.'", ';
		$sql .=     '`email` = "'.$email.'", ';
		$sql .=     '`language` = "'.$language.'", ';
		$sql .=  	"`timezone_string` = '$timezone_string', ";
		$sql .=     '`date_format` = "'.$date_format.'", ';
		$sql .=     '`time_format` = "'.$time_format.'" ';
		$sql .= 'WHERE `user_id` = '.(int)$admin->get_user_id().' AND `password` = "'.$current_password.'"';
		if( $database->query($sql) )
		{
			$sql_info = mysql_info();
			if(preg_match('/matched: *([1-9][0-9]*)/i', $sql_info) != 1)
			{  // if the user_id and password dosn't match
				$err_msg[] = $MESSAGE['PREFERENCES_CURRENT_PASSWORD_INCORRECT'];
			}else {
				// update successfull, takeover values into the session
				$_SESSION['DISPLAY_NAME'] = $display_name;
				$_SESSION['LANGUAGE'] = $language;
				$_SESSION['EMAIL'] = $email;
			    // Set timezone
				$_SESSION['TIMEZONE_STRING'] = $timezone_string;
				date_default_timezone_set($timezone_string);
				// Update date format
				if($date_format != '') {
					$_SESSION['DATE_FORMAT'] = $date_format;
					if(isset($_SESSION['USE_DEFAULT_DATE_FORMAT'])) { unset($_SESSION['USE_DEFAULT_DATE_FORMAT']); }
				} else {
					$_SESSION['USE_DEFAULT_DATE_FORMAT'] = true;
					if(isset($_SESSION['DATE_FORMAT'])) { unset($_SESSION['DATE_FORMAT']); }
				}
				// Update time format
				if($time_format != '') {
					$_SESSION['TIME_FORMAT'] = $time_format;
					if(isset($_SESSION['USE_DEFAULT_TIME_FORMAT'])) { unset($_SESSION['USE_DEFAULT_TIME_FORMAT']); }
				} else {
					$_SESSION['USE_DEFAULT_TIME_FORMAT'] = true;
					if(isset($_SESSION['TIME_FORMAT'])) { unset($_SESSION['TIME_FORMAT']); }
				}
			}
		}else {
			$err_msg[] = 'invalid database UPDATE call in '.__FILE__.'::'.__FUNCTION__.'before line '.__LINE__;
		}
	}
	return ( (sizeof($err_msg) > 0) ? implode('<br />', $err_msg) : '' );
}
$retval = save_preferences($admin, $database);
if( $retval == '')
{
	require_once(WB_PATH."/modules/initial_page/classes/c_init_page.php");
	$ref = new c_init_page( $database );
	$ref->update_user( $_SESSION['USER_ID'], $_POST['init_page_select'] );
	unset($ref);

	$admin->print_success($MESSAGE['PREFERENCES_DETAILS_SAVED']);
	$admin->print_footer();

} else {
	$admin->print_error($retval, $js_back);
}

?>