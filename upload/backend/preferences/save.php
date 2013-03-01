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
 * @license			http://www.gnu.org/licenses/gpl.html
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

require_once(CAT_PATH.'/framework/class.admin.php');
$admin		= new admin('Preferences');

$user       = CAT_Users::getInstance();
$val        = CAT_Helper_Validate::getInstance();

$js_back	= 'javascript: history.go(-1);'; // Create a javascript back link
$extended   = $user->getExtendedOptions();
$err_msg = array();

// Get entered values and validate all
// ================================================= 
// ! remove any dangerouse chars from display_name   
// ================================================= 
$display_name = $val->add_slashes(strip_tags(trim($val->sanitizePost('display_name'))));
$display_name = ( $display_name == '' ) ? $user->get_display_name() : $display_name;

// ================================================================================== 
// ! check that display_name is unique in whoole system (prevents from User-faking)   
// ================================================================================== 
$sql	 = 'SELECT COUNT(*) FROM `' . CAT_TABLE_PREFIX . 'users` '
         . 'WHERE `user_id` <> ' . (int)$user->get_user_id()
         . ' AND `display_name` LIKE "' . $display_name . '"';

if( $database->get_one( $sql ) > 0 )
{
	$err_msg[]		= $admin->lang->translate( 'The username you entered is already taken' );
}
// ============================================ 
// ! language must be 2 upercase letters only   
// ============================================ 
$language			= strtoupper( $val->sanitizePost('language') );
$language			= preg_match('/^[A-Z]{2}$/', $language)		? $language : DEFAULT_LANGUAGE;

// ===================================== 
// ! email should be validatet by core   
// ===================================== 
$email		= $val->sanitizePost('email') == null ? '' : $val->sanitizePost('email');
if ( !$val->validate_email($email) )
{
	$email			= '';
	$err_msg[]		= $admin->lang->translate( 'The email address you entered is invalid' );
}
else
{
	// check that email is unique
	$email		= $val->add_slashes($email);
	$sql		 = 'SELECT COUNT(*) FROM `'.CAT_TABLE_PREFIX.'users` ';
	$sql		.= 'WHERE `user_id` <> ' . (int)$user->get_user_id() . ' AND `email` LIKE "' . $email . '"';
	if( $database->get_one($sql) > 0 )
	{
		$err_msg[]		= $admin->lang->translate( 'The email you entered is already in use' );
	}
}

// ===================================================== 
// ! receive password vars and calculate needed action   
// ===================================================== 
$current_password		= $val->sanitizePost('current_password');
$new_password_1			= $val->sanitizePost('new_password_1');
$new_password_2			= $val->sanitizePost('new_password_2');
$current_password		= $current_password == null								? '' : $current_password;
$new_password_1			= $new_password_1 == null || $new_password_1 == ''		? '' : $new_password_1;
$new_password_2			= $new_password_2 == null || $new_password_2 == ''		? '' : $new_password_2;
if ( $current_password == '' )
{
	$err_msg[]			= $admin->lang->translate( 'The current password you entered is empty' );
}
else
{
	// if new_password is empty, still let current one
	if ( $new_password_1 == '' )
	{
		$new_password_1		= $current_password;
		$new_password_2		= $current_password;
	}
	else
	{
		// check for invalid chars
		$pattern			= '/[^'.$admin->password_chars.']/';
		if ( preg_match( $pattern, $new_password_1 ) )
		{
			$err_msg[]			= $admin->lang->translate( 'Invalid password chars used, valid chars are: {{valid_chars}}', array( 'valid_chars'	=> $admin->password_chars) );
		}
		if ( strlen($new_password_1) < AUTH_MIN_PASS_LENGTH )
		{
			$err_msg[]			= $admin->lang->translate( 'The password you entered was too short' );
		}
		if ( $new_password_1 != $new_password_2 )
		{
			$err_msg[]			= $admin->lang->translate( 'The passwords you entered do not match' );
		}
	}
}
$current_password		= md5($current_password);
$new_password_1			= md5($new_password_1);
$new_password_2			= md5($new_password_2);

// ======================================================================================= 
// ! if no validation errors, try to update the database, otherwise return errormessages   
// ======================================================================================= 
if ( sizeof($err_msg) == 0 )
{

    $user_id = (int)$user->get_user_id();

    // --- save basics ---
	$sql	 = 'UPDATE `'.CAT_TABLE_PREFIX.'users` '
            .  'SET `display_name` = "' . $display_name . '", '
	        .  '`password` = "' . $new_password_1 . '", '
	        .  '`email` = "' . $email . '", '
	        .  '`language` = "' . $language . '" '
	        .  'WHERE `user_id` = ' . $user_id
            .  ' AND `password` = "' . $current_password . '"'
            ;

	if ( $database->query($sql) )
	{
		$sql_info = mysql_info();
		if ( preg_match('/matched: *([1-9][0-9]*)/i', $sql_info) != 1 )
		{
			// if the user_id or password doesn't match
			$admin->print_error( 'The (current) password you entered is incorrect' , $js_back );
		}
		else
		{
			// update successful
            // --- save additional settings ---
            $database->query( 'DELETE FROM `'.CAT_TABLE_PREFIX.'users_options` WHERE `user_id` = ' . $user_id );
            foreach( $extended as $opt => $check )
            {
                $value = $val->sanitizePost($opt);
#$admin->print_error( "OPT -$opt- VAL -$value- CHECK -$check- VALID -" . call_user_func($check,$value) . "-\n<br />" );
                if ( ! call_user_func($check,$value) ) continue;
                $sql = 'INSERT INTO `'.CAT_TABLE_PREFIX.'users_options` '
                     . 'VALUES ( "'.$user_id.'", "'.$opt.'", "'.$value.'" )'
                     ;
#$admin->print_error( $sql );
                $database->query($sql);
            }

			$_SESSION['DISPLAY_NAME']		= $display_name;
			$_SESSION['LANGUAGE']			= $language;
			$_SESSION['EMAIL']				= $email;
			$_SESSION['TIMEZONE_STRING']	= $timezone_string;
			date_default_timezone_set($timezone_string);

			// ====================== 
			// ! Update date format   
			// ====================== 
			if ( $date_format != '' )
			{
				$_SESSION['DATE_FORMAT']	= $date_format;
				if ( isset($_SESSION['USE_DEFAULT_DATE_FORMAT']) )
				{
					unset($_SESSION['USE_DEFAULT_DATE_FORMAT']);
				}
			}
			else
			{
				$_SESSION['USE_DEFAULT_DATE_FORMAT']	= true;
				if ( isset($_SESSION['DATE_FORMAT']) )
				{
					unset($_SESSION['DATE_FORMAT']);
				}
			}
			// ====================== 
			// ! Update time format   
			// ====================== 
			if ( $time_format != '' )
			{
				$_SESSION['TIME_FORMAT']		= $time_format;
				if ( isset($_SESSION['USE_DEFAULT_TIME_FORMAT']) )
				{
					unset($_SESSION['USE_DEFAULT_TIME_FORMAT']);
				}
			}
			else
			{
				$_SESSION['USE_DEFAULT_TIME_FORMAT'] = true;
				if ( isset($_SESSION['TIME_FORMAT']) )
				{
					unset($_SESSION['TIME_FORMAT']);
				}
			}

			// ==================== 
			// ! Set initial page   
			// ==================== 
			require_once( CAT_PATH . '/modules/initial_page/classes/c_init_page.php' );
			$ref	= new c_init_page( $database );
			$ref->update_user( $_SESSION['USER_ID'], $admin->get_post('init_page_select') );
			unset($ref);
		
			$admin->print_success( 'Details saved successfully' );
		}
	}
	else
	{
		$err_msg	= $admin->lang->translate( 'invalid database UPDATE call in ' ) .__FILE__.'::'.__FUNCTION__. $admin->lang->translate( 'before line ' ).__LINE__;
		$admin->print_error( $err_msg, $js_back );
	}
}
else
{
	$admin->print_error( $err_msg, $js_back );
}

?>