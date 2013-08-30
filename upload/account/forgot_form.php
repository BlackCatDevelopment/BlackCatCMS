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

$val          = CAT_Helper_Validate::getInstance();
$email        = $val->sanitizePost('email',NULL,true);
$display_form = true;
$msg_class    = 'info';

global $parser;
$parser->setPath(CAT_PATH.'/templates/'.DEFAULT_TEMPLATE.'/'); // if there's a template for this in the current frontend template
$parser->setFallbackPath(dirname(__FILE__).'/templates/default'); // fallback to default dir

// no mailer lib installed?
if(count(CAT_Helper_Addons::getLibraries('mail'))==0)
{
    $parser->output('account_forgot_form',
        array(
            'message_class' => 'highlight',
            'display_form'  => false,
            'message'       => $val->lang()->translate(
                'Sorry, but the system is unable to use mail to send your details. Please contact the administrator.'
            ),
            'contact'       => (
                   ( CAT_Registry::exists('SERVER_EMAIL',false) && CAT_Registry::get('SERVER_EMAIL') != 'admin@yourdomain.tld' && $val->validate_email(CAT_Registry::get('SERVER_EMAIL')) )
                ? '<br />[ <a href="mailto:'.CAT_Registry::get('SERVER_EMAIL').'">'.$val->lang()->translate('Send eMail').'</a> ]'
                : ''
            ),
        )
    );
    exit;
}

// Check if the user has already submitted the form, otherwise show it
if ( $email && $val->sanitize_email($email) )
    list($result,$message) = CAT_Users::handleForgot($email);
else
	$email = '';

if ( !isset( $message ) )
{
	$message = $val->lang()->translate('Please enter your email address below');
}

$parser->output('account_forgot_form',
    array(
        'message_class' => $msg_class,
        'email'         => $email,
        'display_form'  => $display_form,
        'message'       => $message,
    )
);