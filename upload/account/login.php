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

// this one is only used for the frontend!
if (!FRONTEND_LOGIN) { // no frontend login, no preferences
    if (INTRO_PAGE) {
        die(header('Location: '.CAT_URL.PAGES_DIRECTORY.'/index.php'));
    } else {
        die(header('Location: '.CAT_URL.'/index.php'));
    }
}

if(isset($_POST['submit_login'])) {
    if(CAT_Helper_Validate::getInstance()->fromSession('ATTEMPTS') > MAX_ATTEMPTS) {
        header('Location: '.CAT_URL.'/templates/'.DEFAULT_THEME.'/templates/warning.html');
        exit;
    }
    $redirect = CAT_Users::getInstance()->handleLogin(false);
    $error    = CAT_Users::getInstance()->loginError();
    if(empty($error) && !empty($redirect)) {
        header('Location: '.$redirect);
        exit;
    } else {
        // save error into session
        $_SESSION['LOGIN_ERROR'] = $error;
        if(isset($_POST['page_id'])) {
            header('Location: '.CAT_Helper_Page::getLink($_POST['page_id']));
            exit;
        }
    }
} 

CAT_Helper_Page::getVirtualPage('Please login');
// Set the page content include file
define('PAGE_CONTENT', CAT_PATH.'/account/login_form.php');
// Include the index (wrapper) file
require CAT_PATH.'/index.php';
