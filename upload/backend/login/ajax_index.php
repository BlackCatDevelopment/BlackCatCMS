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
 *   @copyright       2014, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

define('CAT_LOGIN_PHASE',1);

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

if ( CAT_Helper_Validate::getInstance()->fromSession('ATTEMPTS') > MAX_ATTEMPTS )
{
    $redirect = CAT_URL.'/templates/'.DEFAULT_THEME.'/templates/warning.html';
    $ajax	= array(
    	'url'			=> $redirect,
    	'success'		=> true,
    	'message'		=> NULL
    );
}
else
{
    $redirect = CAT_Users::getInstance()->handleLogin();
    $error    = CAT_Users::getInstance()->loginError();
    $ajax	= array(
    	'url'			=> $redirect,
    	'success'		=> CAT_Users::getInstance()->is_authenticated(),
    	'message'		=> ( ($redirect===false||$error) ? $error : NULL ),
        'timer'         => ini_get('session.gc_maxlifetime'),
    );
}

header('Content-type: application/json');
print json_encode( $ajax );
exit();
