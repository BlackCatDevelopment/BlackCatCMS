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

$val     = CAT_Helper_Validate::getInstance();
$errors  = array();
$message = NULL;
$form    = true;

global $parser;
$parser->setPath(CAT_PATH.'/templates/'.DEFAULT_TEMPLATE.'/'); // if there's a template for this in the current frontend template
$parser->setFallbackPath(dirname(__FILE__).'/templates/default'); // fallback to default dir

// check ASP protection
if (
       ENABLED_ASP
	&& $val->sanitizePost('username')
	&& ( // form faked? Check the honeypot-fields.
	     (
		      ! $val->sanitizePost('submitted_when')
		   || ! $val->fromSession('submitted_when')
		 )
		 ||
		 (
		      $val->sanitizePost('submitted_when') != $val->fromSession('submitted_when')
		 )
		 ||
		 (
             $val->sanitizePost('email-address')
		 )
		 ||
		 (
             $val->sanitizePost('name')
	 	 )
		 ||
		 (
		     $val->sanitizePost('full_name')
		 )
	)
) {
	exit( header( "Location: " . CAT_URL . PAGES_DIRECTORY . "" ) );
}

// handle registration
if ( $val->sanitizePost('username') )
{
    $users        = CAT_Users::getInstance();

    $groups_id    = FRONTEND_SIGNUP;
    $active       = 1;
    $username     = strtolower( strip_tags( $val->sanitizePost( 'username', 'scalar', true ) ) );
    $display_name = strip_tags( $val->sanitizePost( 'display_name', 'scalar', true ) );
    $email        = $val->sanitizePost( 'email', NULL, true );

    // validate username
    if ( ! $users->validateUsername($username) )
    {
        $errors[] = $val->lang()->translate('Invalid chars for username found')
                  . ' - ' . $val->lang()->translate('or') . ' - '
                  . $val->lang()->translate('The username you entered was too short');
    }
    // Check if username already exists
    if ( $users->checkUsernameExists($username) )
        $errors[] = $val->lang()->translate('The username you entered is already taken');


    // validate email
    if ( ! $email )
        $errors[] = $val->lang()->translate('Please enter your email address');
    elseif ( ! $val->validate_email($email) )
        $errors[] = $val->lang()->translate('The email address you entered is invalid');
    // Check if the email already exists
    if ( $users->checkEmailExists($email) )
        $errors[] = $val->lang()->translate('The email you entered is already in use');

    if ( $groups_id == "" )
        $errors[] = $val->lang()->translate('No group was selected');

    // check Captcha
    if ( ENABLED_CAPTCHA )
    {
    	if ( ! $val->sanitizePost('captcha') )
    	{
            $errors[] = $val->lang()->translate('The verification number (also known as Captcha) that you entered is incorrect. If you are having problems reading the Captcha, please email to: <a href="mailto:{{SERVER_EMAIL}}">{{SERVER_EMAIL}}</a>', array('SERVER_EMAIL'=>SERVER_EMAIL));
        }
        else
        {
    		// Check for a mismatch
    		if ( $val->sanitizePost('captcha') != $val->fromSession('captcha') )
    		{
    			$errors[] = $val->lang()->translate('The verification number (also known as Captcha) that you entered is incorrect. If you are having problems reading the Captcha, please email to: <a href="mailto:{{SERVER_EMAIL}}">{{SERVER_EMAIL}}</a>', array('SERVER_EMAIL'=>SERVER_EMAIL));
    		}
    	}
    }
    if ( isset( $_SESSION['captcha'] ) )
    {
    	unset( $_SESSION['captcha'] );
    }

    if ( ! count($errors) )
    {
        // Generate a random password
        $new_pass     = $users->generateRandomString(8);
        $md5_password = md5( $new_pass );

        $result = $users->createUser($groups_id, $active, $username, $md5_password, $display_name, $email );

        if ( ! is_bool($result) )
        {
            $errors[] = $val->lang()->translate('Unable to create user account. Please contact the administrator.');
        }
        else
        {
        	// Setup email to send
        	$mail_to      = $email;
        	$mail_subject = $val->lang()->translate('Your login details...');
            $mail_message = $parser->get(
                'account_signup_mail_body',
                array(
              		'LOGIN_DISPLAY_NAME'  => $display_name,
            		'LOGIN_WEBSITE_TITLE' => WEBSITE_TITLE,
            		'LOGIN_NAME'          => $username,
            		'LOGIN_PASSWORD'      => $new_pass,
                    'SERVER_EMAIL'        => SERVER_EMAIL,
                )
            );

        	// Try sending the email
            if ( ! CAT_Helper_Mail::getInstance()->sendMail( SERVER_EMAIL, $mail_to, $mail_subject, $mail_message, CATMAILER_DEFAULT_SENDERNAME ) )
	{
        		$database->query( "DELETE FROM " . CAT_TABLE_PREFIX . "users WHERE username = '$username'" );
        		$errors[] = $val->lang()->translate('Unable to email password, please contact system administrator');
	}
            else
	{
                $message = $val->lang()->translate('Registration process completed!<br /><br />You should receive an eMail with your login data. If not, please contact {{SERVER_EMAIL}}.',array('SERVER_EMAIL'=>SERVER_EMAIL));
                $form    = false;
            }
	}
    }

    if ( count($errors) )
        $message = implode('<br />', $errors);
}


$t = time();
$_SESSION['submitted_when'] = $t;

@include CAT_PATH.'/include/captcha/captcha.php';
ob_start();
    call_captcha();
    $captcha = ob_get_clean();

$parser->output(
    'account_signup_form',
    array(
        'form'           => $form,
        'submitted_when' => $t,
        'captcha'        => $captcha,
        'message'        => $message,
        'ENABLED_ASP'    => ENABLED_ASP,
        'username'       => $val->sanitizePost('username'),
        'display_name'   => $val->sanitizePost('display_name'),
        'email'          => $val->sanitizePost('email'),
    )
);

