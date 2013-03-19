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
 *   @deprecated      Please note that we are going to remove this file
 *
 */

if (defined('CAT_PATH')) {
	if (defined('CAT_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
	$subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));	$dir = $_SERVER['DOCUMENT_ROOT'];
	$inc = false;
	foreach ($subs as $sub) {
		if (empty($sub)) continue; $dir .= '/'.$sub;
		if (file_exists($dir.'/framework/class.secure.php')) {
			include($dir.'/framework/class.secure.php'); $inc = true;	break;
		}
	}
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

/**
 * Constants used in field 'statusflags' of table 'users'
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

define('URL_HELP', 'http://blackcat-cms.org/');

