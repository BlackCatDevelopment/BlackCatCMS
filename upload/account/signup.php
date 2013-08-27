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
 * @license         http://www.gnu.org/licenses/gpl.html
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

$val  = CAT_Helper_Validate::getInstance();
$user = CAT_Users::getInstance();
$id   = $user->get_user_id();

if(!( intval(FRONTEND_SIGNUP) && (  0 == ($id ? $id : 0) )))
{
	if(INTRO_PAGE) {
		header('Location: '.CAT_URL.PAGES_DIRECTORY.'/index.php');
		exit(0);
	} else {
		header('Location: '.CAT_URL.'/index.php');
		exit(0);
	}
}

// Required page details
$page_id          = 0;
$page_description = '';
$page_keywords    = '';
CAT_Registry::register( 'PAGE_ID', 0, true );
CAT_Registry::register( 'ROOT_PARENT', 0, true );
CAT_Registry::register( 'PARENT', 0, true );
CAT_Registry::register( 'LEVEL', 0, true );
CAT_Registry::register( 'PAGE_TITLE', $val->lang()->translate('Sign-up'), true );
CAT_Registry::register( 'MENU_TITLE', $val->lang()->translate('Sign-up'), true );
CAT_Registry::register( 'MODULE', '', true );
CAT_Registry::register( 'VISIBILITY', 'public', true );

CAT_Registry::register( 'PAGE_CONTENT', CAT_PATH . '/account/signup_form.php', true );

// Set auto authentication to false
$auto_auth = false;

// Include the index (wrapper) file
require( CAT_PATH . '/index.php' );

?>