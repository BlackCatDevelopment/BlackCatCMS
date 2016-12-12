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

// while this is not really 'secure' (as $_SERVER can be hacked), it's still
// better than nothing...
if(!defined('CAT_BACKEND_REQ_SSL') || CAT_BACKEND_REQ_SSL === false)
{
    echo json_encode( array( 'success' => false ) );
    exit;
}
if(isset($_SERVER['OPENSSL_CONF']) && preg_match('~SSL~',$_SERVER['SERVER_SOFTWARE']))
{
    try {
        $SSL_Check = @fsockopen("ssl://".$_SERVER['HTTP_HOST'], 443, $errno, $errstr, 30);
        if (!$SSL_Check) {
            echo json_encode( array( 'success' => false ) );
        } else {
            fclose($SSL_Check);
    echo json_encode( array( 'success' => true ) );
        }
    } catch( Exception $e ) {
        echo json_encode( array( 'success' => false ) );
    }
}
else
{
    echo json_encode( array( 'success' => false ) );
}