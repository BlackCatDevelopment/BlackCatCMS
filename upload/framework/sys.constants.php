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
 * @copyright       2013, Black Cat Development
 * @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
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

/**
 * Constants used in field 'statusflags'of table 'users'      
 */
	define('USERS_DELETED',            1);  // user marked as deleted
	define('USERS_ACTIVE',             2);  // user is activated
	define('USERS_CAN_SETTINGS',       4);  // user can change own settings
	define('USERS_CAN_SELFDELETE',     8);  // user can delete himself
	define('USERS_PROFILE_ALLOWED',   16);  // user can create a profile page
	define('USERS_PROFILE_AVAIL',     32);  // user has fullfilled profile
	define('USERS_DEFAULT_SETTINGS', USERS_ACTIVE | USERS_CAN_SETTINGS);

/**
 * Constants used in module-dispatcher ( i.e. $module_action =  MODULE_DO_START )  
 */
	define('MODULE_DO_START',            0); // default
	define('MODULE_DO_VIEW',             0); // default
	define('MODULE_DO_INSTALL',          1);
	define('MODULE_DO_UNINSTALL',        2);
	define('MODULE_DO_REREGISTER',       4);
	define('MODULE_DO_UPGRADE',          8);

/**
 * Constants used in Auth and Login module
 */
	define('AUTH_MIN_PASS_LENGTH',		6);	// minimum lenght a new password must have
	define('AUTH_MAX_PASS_LENGTH',	  128);	// maximum lenght of a password.
	define('AUTH_MIN_LOGIN_LENGTH',     3);	// minimum lenght a login-name must have
	define('AUTH_MAX_LOGIN_LENGTH',   128);	// maximum lenght a login-name can have

/**
 * Since PHP 5.3 it is possible to trigger Errors with the flag DEPRECATED.
 * In older PHP versions E_USER_DEPRECATED is not defined, so this definition
 * avoid prompting Errors but is not really useable! 
 */	
if (!defined('E_USER_DEPRECATED')) define('E_USER_DEPRECATED', 16384);

define('URL_HELP', 'http://www.blackcat-cms.org/');
	
?>
