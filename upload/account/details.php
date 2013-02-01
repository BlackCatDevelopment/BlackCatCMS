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

global $wb;
include_once( CAT_PATH . '/framework/timezones.php' );

// Get entered values
$display_name = $wb->add_slashes( strip_tags( $wb->get_post( 'display_name' ) ) );
$language     = $wb->get_post_escaped( 'language' );
$date_format  = $wb->get_post_escaped( 'date_format' );
$time_format  = $wb->get_post_escaped( 'time_format' );

// timezone must match a value in the table
$timezone_string = DEFAULT_TIMEZONESTRING;
if ( in_array( $admin->get_post( 'timezone_string' ), $timezone_table ) )
{
	$timezone_string = $admin->get_post( 'timezone_string' );
}

// Create a javascript back link
$js_back = "javascript: history.go(-1);";

// Update the database
$database = new database();
$query    = "UPDATE " . CAT_TABLE_PREFIX . "users
			SET display_name = '$display_name', language = '$language', timezone_string = '$timezone_string', date_format = '$date_format', time_format = '$time_format'
			WHERE user_id = '" . $wb->get_user_id() . "'";
$database->query( $query );
if ( $database->is_error() )
{
	$wb->print_error( $database->get_error, 'index.php', false );
}
else
{
	$wb->print_success( $MESSAGE[ 'PREFERENCES_DETAILS_SAVED' ], CAT_URL . '/account/preferences.php' );
	$_SESSION[ 'DISPLAY_NAME' ] = $display_name;
	$_SESSION[ 'LANGUAGE' ]     = $language;
	// Update date format
	if ( $date_format != '' )
	{
		$_SESSION[ 'DATE_FORMAT' ] = $date_format;
		if ( isset( $_SESSION[ 'USE_DEFAULT_DATE_FORMAT' ] ) )
		{
			unset( $_SESSION[ 'USE_DEFAULT_DATE_FORMAT' ] );
		}
	}
	else
	{
		$_SESSION[ 'USE_DEFAULT_DATE_FORMAT' ] = true;
		if ( isset( $_SESSION[ 'DATE_FORMAT' ] ) )
		{
			unset( $_SESSION[ 'DATE_FORMAT' ] );
		}
	}
	// Update time format
	if ( $time_format != '' )
	{
		$_SESSION[ 'TIME_FORMAT' ] = $time_format;
		if ( isset( $_SESSION[ 'USE_DEFAULT_TIME_FORMAT' ] ) )
		{
			unset( $_SESSION[ 'USE_DEFAULT_TIME_FORMAT' ] );
		}
	}
	else
	{
		$_SESSION[ 'USE_DEFAULT_TIME_FORMAT' ] = true;
		if ( isset( $_SESSION[ 'TIME_FORMAT' ] ) )
		{
			unset( $_SESSION[ 'TIME_FORMAT' ] );
		}
	}
	// Update timezone
	$_SESSION[ 'TIMEZONE_STRING' ] = $timezone_string;
}

?>