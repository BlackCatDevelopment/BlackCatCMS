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

/**
 * Please note: This file has no secure code because it is called via AJAX
 * under some circumstances - and as it performs a logout there is no need
 * to protect it...
 **/

// this is not really needed, but just to be really really secure...
if(isset($_SESSION))
    foreach(array_keys($_SESSION) as $key)
        unset($_SESSION[$key]);

// overwrite session array
$_SESSION = array();

// delete session cookie if set
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

if(!isset($_POST['_cat_ajax']) && session_id() !== '') {
    @session_destroy();
}

// redirect to admin login
if(!isset($_POST['_cat_ajax']))
{
    die(header('Location: '.CAT_ADMIN_URL.'/login/index.php'));
}
else {
    header('Content-type: application/json');
    echo json_encode(array(
        'success' => true,
        'message' => 'ok'
    ));
}
