<?php

/**
 * This file is part of Black Cat CMS Core, released under the GNU GPL
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
 * @reformatted     2011-10-04
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

include_once( CAT_PATH . '/framework/timezones.php' );
require_once( CAT_PATH . '/framework/class.wb.php' );
$wb_inst = new wb();
if ( $wb_inst->is_authenticated() == false )
{
	die( header( 'Location: ' . CAT_URL . '/account/login.php' ) );
}

$submit_ok = false;
if ( isset( $_POST[ 'save' ] ) && ( $_POST[ 'save' ] == 'account_settings' ) )
{
	if ( isset( $_SESSION[ 'wb_apf_hash' ] ) && ( $_SESSION[ 'wb_apf_hash' ] === $_POST[ 'hash' ] ) )
	{
		if ( ( TIME() - $_POST[ 'r_time' ] ) <= ( 60 * 5 ) )
		{
			/**
			 *	user-password correct?
			 *
			 */
			$user_id = $_SESSION[ 'USER_ID' ];
			$query   = "SELECT `password` from `" . CAT_TABLE_PREFIX . "users` where `user_id`='" . $user_id . "' AND `password`='" . md5( $_POST[ 'current_password' ] ) . "'";
			$result  = $database->query( $query );
			if ( $result->numRows() == 1 )
			{
				$submit_ok = true;
			}
			unset( $user_id );
			unset( $query );
			unset( $result );
			unset( $_POST[ 'save' ] );
		}
	}
}

if (true === $submit_ok) {
	unset($_SESSION['wb_apf_hash']);
	unset($_POST['hash']);
	
	$errors = array();
	
	// timezone must match a value in the table
	//global $timezone_table;
	$timezone_string = $wb_inst->get_timezone_string();
	if (in_array($_POST['timezone_string'], $timezone_table)) {
		$timezone_string = $_POST['timezone_string'];
	}
	
	// language must be 2 upercase letters only
	$language         = strtoupper($wb_inst->get_post('language'));
	$language         = (preg_match('/^[A-Z]{2}$/', $language) ? $language : DEFAULT_LANGUAGE);

	// email should be validatet by core
	$email = ( $wb_inst->get_post('email') == null ? '' : $wb_inst->get_post('email') );
	if( !$wb_inst->validate_email($email) )
	{
		$email = '';
		$errors[]  = $MESSAGE['USERS_INVALID_EMAIL'];
	
	} else {
	// check that email is unique in whoole system
		$email = $wb_inst->add_slashes($email);
		$sql  = 'SELECT COUNT(*) FROM `'.CAT_TABLE_PREFIX.'users` ';
		$sql .= 'WHERE `user_id` <> '.(int)$wb_inst->get_user_id().' AND `email` LIKE "'.$email.'"';
		if( $database->get_one($sql) > 0 ){
			$errors[] = $MESSAGE['USERS_EMAIL_TAKEN'];
		}
	}
	
	$display_name = $wb_inst->add_slashes( $wb_inst->get_post('display_name') );
	
	$pattern = array(
		'/[^A-Za-z0-9@\.\ _-]/'
	);
	
	$display_name = preg_replace( $pattern,	"",	$display_name );
	
	if ( strlen($display_name) < AUTH_MIN_LOGIN_LENGTH ) {
		$errors[] = $MESSAGE['USERS_USERNAME_TOO_SHORT'];
	}
	
	// date_format must be a key from /interface/date_formats
	$date_format      = $wb_inst->get_post('date_format');
	$date_format_key  = str_replace(' ', '|', $date_format);
	$user_time = true;
	include( CAT_ADMIN_PATH.'/interface/date_formats.php' );
	$date_format = (array_key_exists($date_format_key, $DATE_FORMATS) ? $date_format : 'system_default');
	$date_format = ($date_format == 'system_default' ? '' : $date_format);
	unset($DATE_FORMATS);
	
	// time_format must be a key from /interface/time_formats	
	$time_format      = $wb_inst->get_post('time_format');
	$time_format_key  = str_replace(' ', '|', $time_format);
	$user_time = true;
	include( CAT_ADMIN_PATH.'/interface/time_formats.php' );
	$time_format = (array_key_exists($time_format_key, $TIME_FORMATS) ? $time_format : 'system_default');
	$time_format = ($time_format == 'system_default' ? '' : $time_format);
	unset($TIME_FORMATS);
	
	$fields = array(
		'display_name'	=> $display_name,		# not empty - min AUTH_MIN_LOGIN_LENGTH chars
		'language'		=> $language,
		'email'			=> $email,				# not empty and valid
		'timezone_string'	=> $timezone_string,
		'time_format'	=> $time_format,
		'date_format'	=> $date_format
	);
	
	if (isset($_POST['new_password']) && (isset($_POST['new_password2'])) && ($_POST['new_password'] === $_POST['new_password2'])) {
		if ($_POST['new_password'] != "") $fields['password'] = md5($_POST['new_password']);
	}
	
	if (count($errors) == 0) {
		$q = "UPDATE `".CAT_TABLE_PREFIX."users` SET ";
		foreach($fields as $key=>$value) $q .= "`".$key."`='".mysql_real_escape_string($value)."', ";
		$q = substr($q, 0, -2) . " WHERE `user_id`='".$_SESSION['USER_ID']."'";
	
		$database->query( $q );
		if ($database->is_error()) {
			$errors[] = $database->get_error()."<br /><br />Query was:".$q."<br /><br />";
		} else {
			if (isset($fields['password'])) unset($fields['password']);
			foreach($fields as $k=>$v) $_SESSION[ strtoupper($k) ] = $v;
		
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

?>