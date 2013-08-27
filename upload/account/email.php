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
$current_password = $wb->get_post( 'current_password' );
$email            = $wb->get_post( 'email' );

// Create a javascript back link
$js_back = "javascript: history.go(-1);";

// Get existing password
$database = new database();
$query    = "SELECT user_id FROM " . CAT_TABLE_PREFIX . "users WHERE user_id = '" . $wb->get_user_id() . "' AND password = '" . md5( $current_password ) . "'";
$results  = $database->query( $query );

if ( $results->numRows() == 0 )
{
	$wb->print_error( $MESSAGE[ 'PREFERENCES_CURRENT_PASSWORD_INCORRECT' ], $js_back, false );
}
// Validate values
if ( !$wb->validate_email( $email ) )
{
	$wb->print_error( $MESSAGE[ 'USERS_INVALID_EMAIL' ], $js_back, false );
}

$email = $wb->add_slashes( $email );

// Update the database
$database = new database();
$query    = "UPDATE " . CAT_TABLE_PREFIX . "users SET email = '$email' WHERE user_id = '" . $wb->get_user_id() . "' AND password = '" . md5( $current_password ) . "'";
$database->query( $query );
if ( $database->is_error() )
{
	$wb->print_error( $database->get_error, 'index.php', false );
}
else
{
	$wb->print_success( $MESSAGE[ 'PREFERENCES_EMAIL_UPDATED' ], CAT_URL . '/account/preferences.php' );
	$_SESSION[ 'EMAIL' ] = $email;
}

?>