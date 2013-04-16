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

$val = CAT_Helper_Validate::getInstance();

global $parser;
$parser->setPath(CAT_PATH.'/templates/'.DEFAULT_TEMPLATE.'/'); // if there's a template for this in the current frontend template
$parser->setFallbackPath(dirname(__FILE__).'/templates/default'); // fallback to default dir

$email = $val->sanitizePost('email',NULL,true);
$display_form = true;

// Check if the user has already submitted the form, otherwise show it
if ( $email && $val->sanitize_email($email) )
{
	$email = strip_tags($email);

	// Check if the email exists in the database
	$query   = "SELECT user_id,username,display_name,email,last_reset,password FROM "
             . CAT_TABLE_PREFIX . "users WHERE email = '" . $email . "'";
	$results = $database->query( $query );

	if ( $results->numRows() > 0 )
	{
		// Get the id, username, email, and last_reset from the above db query
		$results_array = $results->fetchRow( MYSQL_ASSOC );

		// Check if the password has been reset in the last 2 hours
		$last_reset = $results_array['last_reset'];
		$time_diff  = time() - $last_reset; // Time since last reset in seconds
		$time_diff  = $time_diff / 60 / 60; // Time since last reset in hours
		if ( $time_diff < 2 )
		{
			// Tell the user that their password cannot be reset more than once per hour
			$message = $val->lang()->translate('Password cannot be reset more than once per hour');
		}
		else
		{
			$old_pass = $results_array['password'];

			/**
			 *	Generate a random password then update the database with it
			 */
			$r = array_merge( range( "a", "z" ), range( 1, 9 ) );
			$r = array_diff( $r, array('i','l','o') );
			for ( $i = 0; $i < 3; $i++ )
			{
				$r = array_merge( $r, $r );
			}
			shuffle( $r );
			$new_pass = implode( "", array_slice( $r, 0,  AUTH_MIN_PASS_LENGTH ) );

			$database->query( "UPDATE " . CAT_TABLE_PREFIX . "users SET password = '" . md5( $new_pass ) . "', last_reset = '" . time() . "' WHERE user_id = '" . $results_array[ 'user_id' ] . "'" );

			if ( $database->is_error() )
			{
				// Error updating database
				$message = $database->get_error();
			}
			else
			{
				// Setup email to send
				$mail_to      = $email;
				$mail_subject = $val->lang()->translate('Your login details...');
                $mail_message = $parser->get('forgotpw_mail_body', array(
                    'LOGIN_DISPLAY_NAME'  => $results_array['display_name'],
                    'LOGIN_WEBSITE_TITLE' => WEBSITE_TITLE,
                    'LOGIN_NAME'          => $results_array['username'],
                    'LOGIN_PASSWORD'      => $new_pass,
                ));

				// Try sending the email
				if ( CAT_Helper_Mail::getInstance('PHPMailer')->sendMail( SERVER_EMAIL, $mail_to, $mail_subject, $mail_message, CATMAILER_DEFAULT_SENDERNAME ) )
				{
					$message      = $val->lang()->translate('Your username and password have been sent to your email address');
					$display_form = false;
				}
				else
				{
					$database->query( "UPDATE " . CAT_TABLE_PREFIX . "users SET password = '" . $old_pass . "' WHERE user_id = '" . $results_array['user_id'] . "'" );
					$message = $val->lang()->translate('Unable to email password, please contact system administrator');
                    $message .= '<br />'.CAT_Helper_Mail::getInstance('PHPMailer')->getError();
				}
			}

		}

	}
	else
	{
		// Email doesn't exist, so tell the user
		$message = $val->lang()->translate('The email that you entered cannot be found in the database');
	}

}
else
{
	$email = '';
}

if ( !isset( $message ) )
{
	$message       = $val->lang()->translate('Please enter your email address below');
	$message_color = '000000';
}
else
{
	$message_color = 'FF0000';
}

$parser->output('account_forgot_form',
    array(
        'message_color' => $message_color,
        'email'         => $email,
        'display_form'  => $display_form,
        'message'       => $message,
    )
);