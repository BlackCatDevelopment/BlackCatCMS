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

if ( !FRONTEND_LOGIN )
{
	if ( INTRO_PAGE )
	{
		die( header( 'Location: ' . CAT_URL . PAGES_DIRECTORY . '/index.php' ) );
	}
	else
	{
		die( header( 'Location: ' . CAT_URL . '/index.php' ) );
	}
}

if ( CAT_Users::getInstance()->is_authenticated() === false )
{
	die( header( 'Location: ' . CAT_URL . '/account/login.php' ) );
}

// Required page details
$page_id          = 0;
$page_description = '';
$page_keywords    = '';
define( 'PAGE_ID', 0 );
define( 'ROOT_PARENT', 0 );
define( 'PARENT', 0 );
define( 'LEVEL', 0 );
define( 'PAGE_TITLE', CAT_Helper_I18n::getInstance()->translate('Preferences') );
define( 'MENU_TITLE', CAT_Helper_I18n::getInstance()->translate('Preferences') );
define( 'MODULE', '' );
define( 'VISIBILITY', 'public' );

/**
 *	Set the page content include file
 *
 */
define( 'PAGE_CONTENT', CAT_PATH . '/account/preferences_form.php' );

/**
 *	Include the index (wrapper) file
 *
 */
require( CAT_PATH . '/index.php' );
