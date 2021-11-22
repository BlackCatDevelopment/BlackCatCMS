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
 *   @copyright       2016, Black Cat Development
 *   @link            https://blackcat-cms.org
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
	while (($level < 10) && (!file_exists($root.'framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'framework/class.secure.php')) { 
		include($root.'framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

$username_fieldname = CAT_Helper_Validate::sanitizePost('username_fieldname');
$username_fieldname	= $username_fieldname ? $username_fieldname : CAT_Helper_Validate::createFieldname('username_');
$password_fieldname = CAT_Helper_Validate::sanitizePost('password_fieldname');
$password_fieldname	= $password_fieldname ? $password_fieldname : CAT_Helper_Validate::createFieldname('password_');

$redirect = CAT_Users::getInstance()->handleLogin(false);
$error    = CAT_Users::getInstance()->loginError();

if ($redirect && $redirect !== -1) {
    die(header('Refresh: 0; URL='.$redirect."\n\n", true, 302));
}

$redirect_url	= CAT_Helper_Validate::sanitizeGet('redirect') != '' ?
		CAT_Helper_Validate::sanitizeGet('redirect') : CAT_Helper_Validate::sanitizePost('redirect');

// get input data
$user = htmlspecialchars(CAT_Helper_Validate::sanitizePost($username_fieldname), ENT_QUOTES);

global $parser;
$parser->setPath(CAT_PATH . '/templates/' . DEFAULT_TEMPLATE . '/templates/' . CAT_Registry::get('DEFAULT_THEME_VARIANT')); // if there's a template for this in the current frontend template
$parser->setFallbackPath(dirname(__FILE__).'/templates/default'); // fallback to default dir
$parser->output(
    'account_login_form',
    array(
        'message'            => $error,
        'user'				 => $user,
        'username_fieldname' => $username_fieldname,
        'password_fieldname' => $password_fieldname,
        'otp' 				 => $redirect==-1 ? true : false,
        'redirect_url'       => ($redirect_url ? $redirect_url : ''),
    )
);
