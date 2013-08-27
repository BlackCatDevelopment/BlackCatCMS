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

global $wb;
// Create a javascript back link
$js_back = "javascript: history.go(-1);";

$val = CAT_Helper_Validate::getInstance();

// Get and sanitize entered values
$display_name = strip_tags($val->sanitizePost( 'display_name', 'string', true ));
$date_format  = ( CAT_Helper_DateTime::checkDateformat($val->sanitizePost( 'date_format', 'string', true )) === true )
              ? $val->sanitizePost( 'date_format', 'string', true )
              : NULL;
$time_format  = ( CAT_Helper_DateTime::checkTimeformat($val->sanitizePost( 'time_format', 'string', true )) === true )
              ? $val->sanitizePost( 'time_format', 'string', true )
              : NULL;
$language     = ( $wb->lang->checkLang($val->sanitizePost( 'language', 'string', true )) === true )
              ? $val->sanitizePost( 'language', 'string', true )
              : NULL;
$timezone_string = ( CAT_Helper_DateTime::checkTZ($val->sanitizePost('timezone_string')) === true )
                 ? $val->sanitizePost('timezone_string')
                 : DEFAULT_TIMEZONESTRING;

// Update the database
$database = new database();
$query    = "UPDATE " . CAT_TABLE_PREFIX . "users
			SET %s = '%s'
			WHERE user_id = '%s'";

foreach ( array( 'display_name','date_format','time_format','language','timezone_string' ) as $key )
{
    $item = ${$key};
    if ( $item !== NULL )
    {
        $database->query( sprintf($query,$key,$item,$wb->get_user_id()) );
        if ( $database->is_error() )
        {
	$wb->print_error( $database->get_error, 'index.php', false );
        }
    }
}

$wb->print_success( 'Details saved successfully',CAT_URL.'/account/preferences.php' );

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
