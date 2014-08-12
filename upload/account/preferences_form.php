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
 *   @copyright       2014, Black Cat Development
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

$user      = CAT_Users::getInstance();
$val       = CAT_Helper_Validate::getInstance();
$show_form = true;

// load language file
$user->lang()->addFile(LANGUAGE);

if(!$user->is_authenticated())
    die( header( 'Location: '.CAT_Registry::get('LOGIN_URL')."\n\n" ) );

// enable CSRFMagic
//CAT_Helper_Protect::getInstance()->enableCSRFMagic();

$submit_ok = false;
$message   = false;
$save      = $val->sanitizePost('save');
$wbcompat  = (defined('WB2COMPAT') && WB2COMPAT===true) ? true : false;

if ( $save && ( $save == 'account_settings' ) )
{
	$query  = "SELECT `password` from `:prefix:users` where `user_id`=:id AND `password`=:pw";
	$result = $database->query($query,array('id'=>$user->get_user_id(),'pw'=>md5($val->sanitizePost('current_password'))));
	if ( $result->rowCount() == 1 )
		$submit_ok = true;
	unset($query);
	unset($result);
	unset($_POST['save']);
}

if (true === $submit_ok)
{
	$errors = array();

    $timezone_string
        = ( CAT_Helper_DateTime::checkTZ($val->sanitizePost('timezone_string')) === true )
        ? $val->sanitizePost('timezone_string')
        : DEFAULT_TIMEZONESTRING;

    $language
        = ( $user->lang()->checkLang($val->sanitizePost( 'language', 'string', true )) === true )
        ? $val->sanitizePost( 'language', 'string', true )
        : NULL;

	// email should be validatet by core
	$email = $val->validate_email($val->sanitizePost('email'));
	if( !$email )
	{
		$errors[] = $user->lang()->translate('The email address you entered is invalid');

	} else {
		$sql  = 'SELECT COUNT(*) FROM `:prefix:users` WHERE `user_id`<>:id AND `email` LIKE :email';
		if( $database->query($sql,array('id'=>(int)$user->get_user_id(),'email'=>$email))->fetchColumn() > 0 )
			$errors[] = $user->lang()->translate('The email you entered is already in use');
	}

	$display_name = strip_tags($val->sanitizePost( 'display_name', 'string', true ));

	$pattern = array(
		'/[^A-Za-z0-9@\.\ _-]/'
	);

	$display_name = preg_replace( $pattern,	"",	$display_name );

	if ( strlen($display_name) < AUTH_MIN_LOGIN_LENGTH ) {
		$errors[] = $user->lang()->translate('The username you entered was too short');
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
		'display_name'	  => $display_name,		# not empty - min AUTH_MIN_LOGIN_LENGTH chars
		'language'		  => $language,
		'email'			  => $email,			# not empty and valid
		'timezone_string' => $timezone_string,
		'time_format'	  => $time_format,
		'date_format'	  => $date_format
	);

	if (
           $val->sanitizePost('new_password')
        && ( $val->sanitizePost('new_password2') && ($val->sanitizePost('new_password') === $val->sanitizePost('new_password2')) )
    ) {
		if ($val->sanitizePost('new_password') != "")
            $fields['password'] = md5($val->sanitizePost('new_password'));
	}

    // save
	if (!count($errors))
        $errors = $user->setUserOptions( $user->get_user_id(), $fields );

    // update session data
    if(!count($errors))
    {
		if (isset($fields['password']))
            unset($fields['password']);
		foreach($fields as $k=>$v)
            $_SESSION[ strtoupper($k) ] = $v;

		$_SESSION['CAT_TIMEZONE_STRING'] = $timezone_string;
		date_default_timezone_set($timezone_string);

		if ( $_SESSION['CAT_TIME_FORMAT'] != '' )
        {
			if(isset($_SESSION['USE_DEFAULT_TIME_FORMAT'])) unset($_SESSION['USE_DEFAULT_TIME_FORMAT']);
		}
        else
        {
			$_SESSION['USE_DEFAULT_TIME_FORMAT'] = true;
			unset($_SESSION['CAT_TIME_FORMAT']);
		}

		if ( $_SESSION['CAT_DATE_FORMAT'] != '' )
        {
			if(isset($_SESSION['USE_DEFAULT_DATE_FORMAT'])) unset($_SESSION['USE_DEFAULT_DATE_FORMAT']);
		}
        else
        {
			$_SESSION['USE_DEFAULT_DATE_FORMAT'] = true;
			unset($_SESSION['CAT_DATE_FORMAT']);
		}
	}

	if (count($errors) > 0)
    {
		$message = implode("<br />", $errors );
    }
	else
    {
		$message   = $user->lang()->translate('Details saved successfully')."!<br /><br />";
        $show_form = false;
    }
}
unset($submit_ok);

// get available languages, mark currently used
$languages = CAT_Helper_Addons::get_addons(((isset($language)&&$language!==LANGUAGE)?$language:LANGUAGE),'language');

global $parser;
$parser->setPath( CAT_PATH . '/templates/' . DEFAULT_TEMPLATE . '/templates/' . CAT_Registry::get('DEFAULT_THEME_VARIANT') ); // if there's a template for this in the current frontend template
$parser->setFallbackPath(dirname(__FILE__).'/templates/default'); // fallback to default dir
$parser->output('account_preferences_form',
    array(
        'show_form'             => $show_form,
        'languages'             => $languages,
        'timezones'             => CAT_Helper_DateTime::getTimezones(),
        'current_tz'            => CAT_Helper_DateTime::getTimezone(),
        'date_formats'          => CAT_Helper_DateTime::getDateFormats(),
        'current_df'            => CAT_Helper_DateTime::getDefaultDateFormatShort(),
        'time_formats'          => CAT_Helper_DateTime::getTimeFormats(),
        'current_tf'            => CAT_Helper_DateTime::getDefaultTimeFormat(),
        'PREFERENCES_URL'       => PREFERENCES_URL,
        'USER_ID'               => $user->get_user_id(),
        'DISPLAY_NAME'          => $user->get_display_name(),
        'GET_EMAIL'             => $user->get_email(),
        'RESULT_MESSAGE'        => $message,
        'AUTH_MIN_LOGIN_LENGTH' => AUTH_MIN_LOGIN_LENGTH,
    )
);

unset( $_SESSION['result_message'] );
