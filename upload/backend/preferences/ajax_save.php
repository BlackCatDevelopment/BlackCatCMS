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
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (defined('CAT_PATH')) {	
	include(CAT_PATH.'/framework/class.secure.php'); 
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

header('Content-type: application/json');

$backend    = CAT_Backend::getInstance('user','preferences',false,false);
$user       = CAT_Users::getInstance();
$val        = CAT_Helper_Validate::getInstance();

$extended   = $user->getExtendedOptions();
$err_msg    = array();

// =================================================
// ! remove any dangerouse chars from display_name   
// ================================================= 
$display_name = $val->add_slashes(strip_tags(trim($val->sanitizePost('display_name'))));
$display_name = ( $display_name == '' ) ? $user->get_display_name() : $display_name;

// ================================================================================== 
// ! check that display_name is unique in whole system (prevents from User-faking)
// ================================================================================== 
$sql = sprintf(
    'SELECT COUNT(*) FROM `%susers` WHERE `user_id` <> %d AND `display_name` LIKE "%s"',
    CAT_TABLE_PREFIX, (int)$user->get_user_id(), $display_name
);

if( $backend->db()->get_one( $sql ) > 0 )
{
	$err_msg[]		= $backend->lang->translate( 'The username you entered is already taken' );
}
// ============================================ 
// ! language must be 2 uppercase letters only
// ============================================ 
$language = strtoupper( $val->sanitizePost('language') );
$language = $backend->lang()->checkLang($language)
          ? $language
          : CAT_Registry::get('DEFAULT_LANGUAGE');

// ================ 
// ! validate email
// ================ 
$email	  = $val->sanitizePost('email') == null
          ? 'x'
          : $val->sanitizePost('email');
if ( !$val->validate_email($email) )
{
	$email			= '';
	$err_msg[]		= $backend->lang()->translate( 'The email address you entered is invalid' );
}
else
{
	// check that email is unique
	$email = $val->add_slashes($email);
	$sql   = sprintf(
        'SELECT COUNT(*) FROM `%susers` WHERE `user_id` <> %d AND `email` LIKE "%s"',
        CAT_TABLE_PREFIX, (int)$user->get_user_id(), $email
    );
	if( $backend->db()->get_one($sql) > 0 )
	{
		$err_msg[] = $backend->lang()->translate( 'The email you entered is already in use' );
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
	$err_msg[]			= $backend->lang()->translate( 'You must enter your current password to save your changes' );
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
        if ( ! $user->validatePassword($new_password_1) )
        {
            $err_msg[] = $user->getPasswordError();
        }
	}
}

$current_password		= md5($current_password);
$new_password_1			= md5($new_password_1);
$new_password_2			= md5($new_password_2);

// ======================================================================================= 
// ! if no validation errors, try to update the database, otherwise return errormessages   
// ======================================================================================= 
if (!count($err_msg))
{

    $user_id = $user->get_user_id();

    // --- save basics ---
	$sql	 = sprintf(
        'UPDATE `%susers` SET `display_name` = "%s", '
	        .  '`password` = "%s", '
	        .  '`email` = "%s", '
	        .  '`language` = "%s" '
	        .  'WHERE `user_id` = %d '
            .  'AND `password` = "%s"',
        CAT_TABLE_PREFIX, $display_name, $new_password_1, $email, $language, $user_id, $current_password
    );

	if ( $backend->db()->query($sql) )
	{
		$sql_info = mysql_info();
		if ( preg_match('/matched: *([1-9][0-9]*)/i', $sql_info) != 1 )
		{
			// if the user_id or password doesn't match
			$backend->print_error( 'The (current) password you entered is incorrect' , $js_back );
		}
		else
		{
			// update successful
            // --- save additional settings ---
            $backend->db()->query( 'DELETE FROM `'.CAT_TABLE_PREFIX.'users_options` WHERE `user_id` = ' . $user_id );
            foreach( $extended as $opt => $check )
            {
                $value = $val->sanitizePost($opt);
//echo "OPT -$opt- VAL -$value- CHECK -$check- VALID -" . call_user_func($check,$value) . "-\n<br />";
                if ( $check && ! call_user_func($check,$value) ) continue;
                $sql = 'INSERT INTO `%susers_options` '
                     . 'VALUES ( "%d", "%s", "%s" )'
                     ;
                $backend->db()->query(sprintf($sql,CAT_TABLE_PREFIX,$user_id,$opt,$value));
            }

			$_SESSION['DISPLAY_NAME']		= $display_name;
			$_SESSION['LANGUAGE']			= $language;
			$_SESSION['EMAIL']				= $email;
			$_SESSION['TIMEZONE_STRING']	= $timezone_string;

			date_default_timezone_set($timezone_string);

			// ====================== 
			// ! Update date format   
			// ====================== 
            $date_format = $val->sanitizePost('date_format');
			if ( $date_format != '' )
			{
				$_SESSION['DATE_FORMAT'] = $date_format;
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
            $time_format = $val->sanitizePost('time_format');
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
            $new_init_page = $val->sanitizePost('init_page_select');
            if($new_init_page)
            {
    			require_once( CAT_PATH . '/modules/initial_page/classes/c_init_page.php' );
    			$ref	= new c_init_page( $backend->db() );
    			$ref->update_user( $_SESSION['USER_ID'], $new_init_page );
    			unset($ref);
            }
		}
	}
	else
	{
		$err_msg	= $backend->lang()->translate( 'invalid database UPDATE call in ' ) .__FILE__.'::'.__FUNCTION__. $backend->lang()->translate( 'before line ' ).__LINE__;
	}
}

$ajax	= array(
	'message'	=> ( count($err_msg) ? implode("\n", $err_msg) : $backend->lang()->translate('Details saved successfully') ),
	'success'	=> ( count($err_msg) ? false : true )
);
print json_encode( $ajax );
exit();