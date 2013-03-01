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


// Check if the user has already submitted the form, otherwise show it
if ( isset( $_POST[ 'email' ] ) && $_POST[ 'email' ] != "" && preg_match( "/([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}/i", $_POST[ 'email' ] ) )
{
	$email = strip_tags( $_POST[ 'email' ] );

	// Check if the email exists in the database
	$query   = "SELECT user_id,username,display_name,email,last_reset,password FROM " . CAT_TABLE_PREFIX . "users WHERE email = '" . $wb->add_slashes( $_POST[ 'email' ] ) . "'";
	$results = $database->query( $query );
	if ( $results->numRows() > 0 )
	{
		// Get the id, username, email, and last_reset from the above db query
		$results_array = $results->fetchRow( MYSQL_ASSOC );

		// Check if the password has been reset in the last 2 hours
		$last_reset = $results_array[ 'last_reset' ];
		$time_diff  = time() - $last_reset; // Time since last reset in seconds
		$time_diff  = $time_diff / 60 / 60; // Time since last reset in hours
		if ( $time_diff < 2 )
		{
			// Tell the user that their password cannot be reset more than once per hour
			$message = $MESSAGE[ 'FORGOT_PASS_ALREADY_RESET' ];
		}
		else
		{
			$old_pass = $results_array[ 'password' ];

			/**
			 *	Generate a random password then update the database with it
			 *
			 */
			$r = array_merge( range( "a", "z" ), range( 1, 9 ) );
			$r = array_diff( $r, array(
				'i',
				'l',
				'o'
			) );
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
				$mail_subject = $MESSAGE[ 'SIGNUP2_SUBJECT_LOGIN_INFO' ];

				// Replace placeholders from language variable with values
				$search       = array(
					'{LOGIN_DISPLAY_NAME}',
					'{LOGIN_WEBSITE_TITLE}',
					'{LOGIN_NAME}',
					'{LOGIN_PASSWORD}'
				);
				$replace      = array(
					$results_array[ 'display_name' ],
					WEBSITE_TITLE,
					$results_array[ 'username' ],
					$new_pass
				);
				$mail_message = str_replace( $search, $replace, $MESSAGE[ 'SIGNUP2_BODY_LOGIN_FORGOT' ] );

				// Try sending the email
				if ( $wb->mail( SERVER_EMAIL, $mail_to, $mail_subject, $mail_message ) )
				{
					$message      = $MESSAGE[ 'FORGOT_PASS_PASSWORD_RESET' ];
					$display_form = false;
				}
				else
				{
					$database->query( "UPDATE " . CAT_TABLE_PREFIX . "users SET password = '" . $old_pass . "' WHERE user_id = '" . $results_array[ 'user_id' ] . "'" );
					$message = $MESSAGE[ 'FORGOT_PASS_CANNOT_EMAIL' ];
				}
			}

		}

	}
	else
	{
		// Email doesn't exist, so tell the user
		$message = $MESSAGE[ 'FORGOT_PASS_EMAIL_NOT_FOUND' ];
	}

}
else
{
	$email = '';
}

if ( !isset( $message ) )
{
	$message       = $MESSAGE[ 'FORGOT_PASS_NO_DATA' ];
	$message_color = '000000';
}
else
{
	$message_color = 'FF0000';
}

?>
<h1 style="text-align: center;"><?php
echo CAT_Helper_I18n::getInstance()->translate('Forgot');
?></h1>

<form name="forgot_pass" action="<?php echo CAT_URL . '/account/forgot.php'; ?>" method="post">
	<input type="hidden" name="url" value="{URL}" />
		<table cellpadding="5" cellspacing="0" border="0" align="center" width="500">
		<tr>
			<td height="40" align="center" style="color: #<?php echo $message_color; ?>;" colspan="2">
			<?php echo $message; ?>
			</td>
		</tr>
		<?php
if ( !isset( $display_form ) OR $display_form != false )
{
?>
		<tr>
			<td height="10" colspan="2"></td>
		</tr>
		<tr>
			<td width="165" height="30" align="right"><?php echo $TEXT[ 'EMAIL' ]; ?>:</td>
			<td><input type="text" maxlength="255" name="email" value="<?php echo $email; ?>" style="width: 180px;" /></td>
			<td><input type="submit" name="submit" value="<?php echo $TEXT[ 'SEND_DETAILS' ]; ?>" style="width: 180px; font-size: 10px; color: #003366; border: 1px solid #336699; background-color: #DDDDDD; padding: 3px; text-transform: uppercase;" /></td>
		</tr>
		<?php
}   // if ( !isset( $display_form ) OR $display_form != false )
?>
		</table>
</form>