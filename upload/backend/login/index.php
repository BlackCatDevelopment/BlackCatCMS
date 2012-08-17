<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */
 
// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
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



require_once(WB_PATH."/framework/class.login.php");

$salt = md5(microtime());
// ================================================ 
// ! we want difference hashes for the two fields   
// ================================================ 
$username_fieldname		= 'username_'.substr($salt, 0, 7);
$password_fieldname		= 'password_'.substr($salt, -7);

$thisApp = new Login(
	array(
	'MAX_ATTEMPTS'			=> MAX_ATTEMPTS,
	'WARNING_URL'			=> THEME_URL.'/templates/warning.html',
	'USERNAME_FIELDNAME'	=> $username_fieldname,
	'PASSWORD_FIELDNAME'	=> $password_fieldname,
	'MIN_USERNAME_LEN'		=> AUTH_MIN_LOGIN_LENGTH,
	'MAX_USERNAME_LEN'		=> AUTH_MAX_LOGIN_LENGTH,
	'MIN_PASSWORD_LEN'		=> AUTH_MIN_PASS_LENGTH,
	'MAX_PASSWORD_LEN'		=> AUTH_MAX_PASS_LENGTH,
	'LOGIN_URL'				=> ADMIN_URL.'/login/index.php',
	'DEFAULT_URL'			=> ADMIN_URL.'/start/index.php',
	'TEMPLATE_DIR'			=> THEME_PATH.'/templates',
	'TEMPLATE_FILE'			=> 'login.lte',
	'FRONTEND'				=> false,
	'REDIRECT_URL'			=> ADMIN_URL.'/start/index.php',
	'FORGOTTEN_DETAILS_APP'	=> ADMIN_URL.'/login/forgot/index.php',
	'USERS_TABLE'			=> TABLE_PREFIX.'users',
	'GROUPS_TABLE'			=> TABLE_PREFIX.'groups',
	'PAGE_ID'				=> ''
	)
);
?>