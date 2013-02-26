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
 * @license			http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */
 
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

#if(empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on")
#{
#    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
#    exit();
#}

require_once(CAT_PATH . "/framework/class.login.php");

$salt = md5(microtime());
// ================================================ 
// ! we want difference hashes for the two fields   
// ================================================ 
$username_fieldname		= 'username_'.substr($salt, 0, 7);
$password_fieldname		= 'password_'.substr($salt, -7);

$thisApp = new Login(
	array(
	'MAX_ATTEMPTS'			=> MAX_ATTEMPTS,
	'WARNING_URL'			=> CAT_THEME_URL . '/templates/warning.html',
	'USERNAME_FIELDNAME'	=> $username_fieldname,
	'PASSWORD_FIELDNAME'	=> $password_fieldname,
	'MIN_USERNAME_LEN'		=> AUTH_MIN_LOGIN_LENGTH,
	'MAX_USERNAME_LEN'		=> AUTH_MAX_LOGIN_LENGTH,
	'MIN_PASSWORD_LEN'		=> AUTH_MIN_PASS_LENGTH,
	'MAX_PASSWORD_LEN'		=> AUTH_MAX_PASS_LENGTH,
	'LOGIN_URL'				=> CAT_ADMIN_URL.'/login/index.php',
	'DEFAULT_URL'			=> CAT_ADMIN_URL.'/start/index.php',
	'TEMPLATE_DIR'			=> CAT_THEME_PATH.'/templates',
	'TEMPLATE_FILE'			=> 'login.lte',
	'FRONTEND'				=> false,
	'REDIRECT_URL'			=> CAT_ADMIN_URL . '/start/index.php',
	'FORGOTTEN_DETAILS_APP'	=> CAT_ADMIN_URL . '/login/forgot/index.php',
	'USERS_TABLE'			=> CAT_TABLE_PREFIX . 'users',
	'GROUPS_TABLE'			=> CAT_TABLE_PREFIX . 'groups',
	'OUTPUT'				=> true,
	'PAGE_ID'				=> '',
	true,
	)
);
?>