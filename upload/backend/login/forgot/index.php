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
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
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

$backend = CAT_Backend::getInstance('Start', 'start');
$val     = CAT_Helper_Validate::getInstance();

header('Content-type: application/json');

$ajax	 = array();

// Check if the user has already submitted the form, otherwise show it
$email = htmlspecialchars($val->sanitizePost('email',null,true), ENT_QUOTES );
if ( $email )
{
	// Check if the email exists in the database
	$results = $backend->db()->query(sprintf(
        "SELECT user_id,username,display_name,email,last_reset,password FROM `%susers` WHERE email = '%s'",
        CAT_TABLE_PREFIX,$email
    ));
	if ( $results->numRows() > 0 )
	{
		// Get the id, username, email, and last_reset from the above db query
		$results_array		= $results->fetchRow(MYSQL_ASSOC);

/*
		// Check if the password has been reset in the last 2 hours
		$last_reset			= $results_array['last_reset'];
		$time_diff			= time() - $last_reset; // Time since last reset in seconds
		$time_diff			= $time_diff / 60 / 60; // Time since last reset in hours

		if ( $time_diff < 2 )
		{
			// Tell the user that their password cannot be reset more than once per hour
			$ajax	= array(
				'message'	=> $admin->lang->translate('Password cannot be reset more than once per hour, sorry'),
				'success'	=> false
			);
			print json_encode( $ajax );
			exit();
		}
		else
		{
*/
			$old_pass		= $results_array['password'];

			// Generate a random password then update the database
			$new_pass		= '';
			$salt			= "abcdefghijklmnpqrstuvwxyz0123456789";
			srand((double)microtime()*1000000);
			for ($i = 1; $i <= 7; $i++)
			{
				$num		 = rand() % 35;
				$tmp		 = substr($salt, $num, 1);
				$new_pass	.= $tmp;
			}

			$backend->db()->query(sprintf(
                "UPDATE `%susers` SET password = '%s', last_reset = '%s' WHERE user_id = '%d'",
                CAT_TABLE_PREFIX, md5($new_pass), time(), $results_array['user_id']
            ));
			if ( $backend->db()->is_error() )
			{
				// Error updating database
				$backend->db()->query(sprintf(
                    "UPDATE `%susers` SET password = '%s' WHERE user_id = '%d'",
                    CAT_TABLE_PREFIX, $old_pass, $results_array['user_id']
                ));
				$ajax	= array(
					'message'	=> $database->get_error(),
					'success'	=> false
				);
				print json_encode( $ajax );
				exit();
			}
			else
			{
$ajax	= array(
	'message'	=> $new_pass,
	'success'	=> false
);
print json_encode( $ajax );
exit();
				// Setup email to send
				$mail_to		= $email;
				$mail_subject	= $backend->lang()->translate('Your login details...');
				// Replace placeholders from language variable with values
				$values	= array(
					'LOGIN_DISPLAY_NAME'		=> $results_array['display_name'],
					'LOGIN_WEBSITE_TITLE'		=> WEBSITE_TITLE,
					'LOGIN_NAME'				=> $results_array['username'],
					'LOGIN_PASSWORD'			=> $new_pass
				);
				$mail_message	= $backend->lang()->translate('
Hello {{LOGIN_DISPLAY_NAME}},

This mail was sent because the \'forgot password\' function has been applied to your account.

Your new \'{{LOGIN_WEBSITE_TITLE}}\' login details are:

Username:	{{LOGIN_NAME}}
Password:	{{LOGIN_PASSWORD}}

Your password has been reset to the one above.
This means that your old password will no longer work anymore!
If you\'ve got any questions or problems within the new login-data
you should contact the website-team or the admin of \'{{LOGIN_WEBSITE_TITLE}}\'.
Please remember to clean you browser-cache before using the new one to avoid unexpected fails.

Regards
------------------------------------
This message was automatic generated

', $values );

				// Try sending the email
				if ( $admin->mail( SERVER_EMAIL, $mail_to, $mail_subject, $mail_message ) )
				{
					$database->query("UPDATE " . CAT_TABLE_PREFIX . "users SET password = '" . $old_pass . "' WHERE user_id = '" . $results_array['user_id'] . "'");
					$ajax	= array(
						'message'	=> $admin->lang->translate('Your username and password have been sent to your email address'),
						'success'	=> true
					);
					print json_encode( $ajax );
					exit();
				}
				else
				{
					$database->query("UPDATE " . CAT_TABLE_PREFIX . "users SET password = '" . $old_pass . "' WHERE user_id = '" . $results_array['user_id'] . "'");
					$ajax	= array(
						'message'	=> $admin->lang->translate('Unable to email password, please contact system administrator'),
						'success'	=> false
					);
					print json_encode( $ajax );
					exit();
				}
			}
/*
		}
*/
	}
	else
	{
		$ajax	= array(
			'message'	=> $admin->lang->translate('The email that you entered cannot be found in the database'),
			'success'	=> false
		);
		print json_encode( $ajax );
		exit();
	}
}
else
{
	$ajax	= array(
		'message'	=> $admin->lang->translate('You must enter an email address'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
exit();
?>