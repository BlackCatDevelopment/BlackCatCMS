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

if ( !FRONTEND_LOGIN )
{
	if ( INTRO_PAGE )
	{
		die( header( 'Location: ' . CAT_URL . PAGES_DIRECTORY . '/index.php' ) );
	}
	else
	{
		die( header( 'Location: ' . CAT_URL . '/index.php' ) );
	}
}

if ( CAT_Users::getInstance()->is_authenticated() === false )
{
	die( header( 'Location: ' . CAT_URL . '/account/login.php' ) );
}

$submit_ok = false;
$val       = CAT_Helper_Validate::getInstance();
$user      = CAT_Users::getInstance();
$save      = $val->sanitizePost('save');

if ( $save && ( $save == 'account_settings' ) )
{
	if ( $val->fromSession('wb_apf_hash') && ( $val->fromSession('wb_apf_hash') === $val->sanitizePost('hash') ) )
	{
		if ( ( TIME() - $val->sanitizePost('r_time') ) <= ( 60 * 5 ) )
		{
			/**
			 *	user-password correct?
			 *
			 */
			$query   = "SELECT `password` from `" . CAT_TABLE_PREFIX . "users` where `user_id`='" . $user->get_user_id()
                     . "' AND `password`='" . md5( $val->sanitizePost('current_password') ) . "'";
			$result  = $database->query( $query );
			if ( $result->numRows() == 1 )
			{
				$submit_ok = true;
			}
			unset( $query );
			unset( $result );
			unset( $_POST['save'] );
		}
	}
}

if (true === $submit_ok) {
	unset($_SESSION['wb_apf_hash']);
	unset($_POST['hash']);
	
	$errors = array();
	
    $timezone_string
        = ( CAT_Helper_DateTime::checkTZ($val->sanitizePost('timezone_string')) === true )
        ? $val->sanitizePost('timezone_string')
        : DEFAULT_TIMEZONESTRING;

    $language
        = ( $wb->lang->checkLang($val->sanitizePost( 'language', 'string', true )) === true )
        ? $val->sanitizePost( 'language', 'string', true )
        : NULL;

	// email should be validatet by core
	$email = $val->validate_email('email');
	if( !$email )
	{
		$errors[] = $wb->lang->translate('The email address you entered is invalid');
	
	} else {
		$email = $val->add_slashes($email);
		$sql  = 'SELECT COUNT(*) FROM `'.CAT_TABLE_PREFIX.'users` ';
		$sql .= 'WHERE `user_id` <> '.(int)$user->get_user_id().' AND `email` LIKE "'.$email.'"';
		if( $database->get_one($sql) > 0 ){
			$errors[] = $wb->lang->translate('The email you entered is already in use');
		}
	}
	
	$display_name = strip_tags($val->sanitizePost( 'display_name', 'string', true ));
	
	$pattern = array(
		'/[^A-Za-z0-9@\.\ _-]/'
	);
	
	$display_name = preg_replace( $pattern,	"",	$display_name );
	
	if ( strlen($display_name) < AUTH_MIN_LOGIN_LENGTH ) {
		$errors[] = $wb->lang->translate('The username you entered was too short');
	}
	
    $date_format
        = ( CAT_Helper_DateTime::checkDateformat($val->sanitizePost( 'date_format', 'string', true )) === true )
        ? $val->sanitizePost( 'date_format', 'string', true )
        : NULL;

    $time_format
        = ( CAT_Helper_DateTime::checkTimeformat($val->sanitizePost( 'time_format', 'string', true )) === true )
        ? $val->sanitizePost( 'time_format', 'string', true )
        : NULL;
	
	$fields = array(
		'display_name'	=> $display_name,		# not empty - min AUTH_MIN_LOGIN_LENGTH chars
		'language'		=> $language,
		'email'			=> $email,				# not empty and valid
		'timezone_string'	=> $timezone_string,
		'time_format'	=> $time_format,
		'date_format'	=> $date_format
	);
	
	if (
           $val->sanitizePost('new_password')
        && ( $val->sanitizePost('new_password2') && ($val->sanitizePost('new_password') === $val->sanitizePost('new_password2')) )
    ) {
		if ($val->sanitizePost('new_password') != "")
            $fields['password'] = md5($val->sanitizePost('new_password'));
	}
	
	if (!count($errors))
    {
		$q = "UPDATE `".CAT_TABLE_PREFIX."users` SET ";
		foreach($fields as $key=>$value)
            $q .= "`".$key."`='".mysql_real_escape_string($value)."', ";
		$q = substr($q, 0, -2) . " WHERE `user_id`='".$user->get_user_id()."'";
	
		$database->query( $q );
		if ($database->is_error())
        {
			$errors[] = $database->get_error();
		}
        else
        {
			if (isset($fields['password']))
                unset($fields['password']);
			foreach($fields as $k=>$v)
                $_SESSION[ strtoupper($k) ] = $v;
		
			// Update timezone
			$_SESSION['TIMEZONE_STRING'] = $timezone_string;
			date_default_timezone_set($timezone_string);
		
			/**
			 *	Update time format
			 *
			 */
			if ( $_SESSION['TIME_FORMAT'] != '' ) {
				if(isset($_SESSION['USE_DEFAULT_TIME_FORMAT'])) unset($_SESSION['USE_DEFAULT_TIME_FORMAT']);
			} else {
				$_SESSION['USE_DEFAULT_TIME_FORMAT'] = true;
				unset($_SESSION['TIME_FORMAT']);
			}
		
			/**
			 *	Update date format
			 *
			 */
			if ( $_SESSION['DATE_FORMAT'] != '' ) {
				if(isset($_SESSION['USE_DEFAULT_DATE_FORMAT'])) unset($_SESSION['USE_DEFAULT_DATE_FORMAT']);
			} else {
				$_SESSION['USE_DEFAULT_DATE_FORMAT'] = true;
				unset($_SESSION['DATE_FORMAT']);
			}
		}
	}
	if (count($errors) > 0) {
		$_SESSION['result_message'] = implode("<br />", $errors );
	} else {
		$_SESSION['result_message'] = $MESSAGE['PREFERENCES_DETAILS_SAVED']."!<br /><br />";
	}
} else {
	$_SESSION['result_message'] = "";
}
unset($submit_ok);

// Required page details
$page_id          = 0;
$page_description = '';
$page_keywords    = '';
define( 'PAGE_ID', 0 );
define( 'ROOT_PARENT', 0 );
define( 'PARENT', 0 );
define( 'LEVEL', 0 );
define( 'PAGE_TITLE', CAT_Helper_I18n::getInstance()->translate('Preferences') );
define( 'MENU_TITLE', CAT_Helper_I18n::getInstance()->translate('Preferences') );
define( 'MODULE', '' );
define( 'VISIBILITY', 'public' );

/**
 *	Set the page content include file
 *
 */
define( 'PAGE_CONTENT', CAT_PATH . '/account/preferences_form.php' );

/**
 *	Include the index (wrapper) file
 *
 */
require( CAT_PATH . '/index.php' );