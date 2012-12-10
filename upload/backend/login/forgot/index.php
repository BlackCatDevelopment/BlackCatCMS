<?php

/**
 * This file is part of LEPTON2 Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author			LEPTON2 Project
 * @copyright		2012, LEPTON2 Project
 * @link			http://lepton2.org
 * @license			http://www.gnu.org/licenses/gpl.html
 * @license_terms	please see LICENSE and COPYING files in your package
 *
 */
 
// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {	
	include(LEPTON_PATH.'/framework/class.secure.php'); 
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

// Include the database class file and initiate an object
require( LEPTON_PATH . '/framework/class.admin.php' );
$admin		= new admin('Start', 'start', false, false);
$database	= new database();

header('Content-type: application/json');

$ajax		= array();

// Check if the user has already submitted the form, otherwise show it
if ( $admin->get_post('email') != '' )
{
	$email			= htmlspecialchars( $admin->get_post('email'), ENT_QUOTES );

	// Check if the email exists in the database
	$results		= $database->query( "SELECT user_id,username,display_name,email,last_reset,password FROM " . TABLE_PREFIX . "users WHERE email = '" . $admin->add_slashes( $admin->get_post('email') ) . "'" );
	if ( $results->numRows() > 0 )
	{
		// Get the id, username, email, and last_reset from the above db query
		$results_array		= $results->fetchRow();

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

			$database->query("UPDATE " . TABLE_PREFIX . "users SET password = '".md5($new_pass)."', last_reset = '".time()."' WHERE user_id = '" . $results_array['user_id'] . "'");
			if ( $database->is_error() )
			{
				// Error updating database
				$database->query("UPDATE " . TABLE_PREFIX . "users SET password = '" . $old_pass . "' WHERE user_id = '" . $results_array['user_id'] . "'");
				$ajax	= array(
					'message'	=> $database->get_error(),
					'success'	=> false
				);
				print json_encode( $ajax );
				exit();
			}
			else
			{
				// Setup email to send
				$mail_to		= $email;
				$mail_subject	= $admin->lang->translate('Your LEPTON login details...');
				// Replace placeholders from language variable with values
				$values	= array(
					'LOGIN_DISPLAY_NAME'		=> $results_array['display_name'],
					'LOGIN_WEBSITE_TITLE'		=> WEBSITE_TITLE,
					'LOGIN_NAME'				=> $results_array['username'],
					'LOGIN_PASSWORD'			=> $new_pass
				);
				$mail_message	= $admin->lang->translate('
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
					$database->query("UPDATE " . TABLE_PREFIX . "users SET password = '" . $old_pass . "' WHERE user_id = '" . $results_array['user_id'] . "'");
					$ajax	= array(
						'message'	=> $admin->lang->translate('Your username and password have been sent to your email address'),
						'success'	=> true
					);
					print json_encode( $ajax );
					exit();
				}
				else
				{
					$database->query("UPDATE " . TABLE_PREFIX . "users SET password = '" . $old_pass . "' WHERE user_id = '" . $results_array['user_id'] . "'");
					$ajax	= array(
						'message'	=> $admin->lang->translate('Unable to email password, please contact system administrator'),
						'success'	=> false
					);
					print json_encode( $ajax );
					exit();
				}
			}
		}
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