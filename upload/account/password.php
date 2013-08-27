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

// Get the values entered
$current_password = $_POST[ 'current_password' ];
$new_password     = $_POST[ 'new_password' ];
$new_password2    = $_POST[ 'new_password2' ];

// Create a javascript back link
$js_back  = "javascript: history.go(-1);";

// Get existing password
$database = new database();
$query    = "SELECT user_id FROM " . CAT_TABLE_PREFIX . "users WHERE user_id = '" . $wb->get_user_id() . "' AND password = '" . md5( $current_password ) . "'";
$results  = $database->query( $query );

// Validate values
if ( $results->numRows() == 0 )
{
	$wb->print_error( $MESSAGE[ 'PREFERENCES_CURRENT_PASSWORD_INCORRECT' ], $js_back, false );
}
if ( strlen( $new_password ) < AUTH_MIN_PASS_LENGTH )
{
	$wb->print_error( $MESSAGE[ 'USERS_PASSWORD_TOO_SHORT' ], $js_back, false );
}
if ( $new_password != $new_password2 )
{
	$wb->print_error( $MESSAGE[ 'USERS_PASSWORD_MISMATCH' ], $js_back, false );
}

// MD5 the password
$md5_password = md5( $new_password );

// Update the database
$database = new database();
$query    = "UPDATE " . CAT_TABLE_PREFIX . "users SET password = '$md5_password' WHERE user_id = '" . $wb->get_user_id() . "'";
$database->query( $query );
if ( $database->is_error() )
{
	$wb->print_error( $database->get_error, 'index.php', false );
}
else
{
	$wb->print_success( $MESSAGE[ 'PREFERENCES_PASSWORD_CHANGED' ], CAT_URL . '/account/preferences.php' );
}


?>