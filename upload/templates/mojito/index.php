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
 *   @package         mojito
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

//CAT_Helper_Protect::getInstance()->enableCSRFMagic();
if ( CAT_Helper_Validate::getInstance()->sanitizeGet( 'logout' ) == 'true' )
{
	// delete session cookie if set
	if ( isset($_COOKIE[session_name()]) )
	{
		setcookie(session_name(), '', time() - 42000, '/');
	}
	header('Location: ' . CAT_Helper_Page::getLink( $page_id,'link') );
	// delete the session itself
	session_destroy();
} else if ( CAT_Helper_Validate::getInstance()->sanitizePost( 'username_fieldname' ) != ''
	&&  CAT_Helper_Validate::getInstance()->sanitizePost( 'password_fieldname' ) != ''
) {
	header('Location: ' . CAT_Helper_Page::getLink( $page_id,'link') );
}

$dwoodata	= array(
	'is_authenticated'	=> CAT_Users::getInstance()::is_authenticated(),
	'display_name'		=> CAT_Users::getInstance()::get_display_name(),
	'redirect_url'		=> !CAT_Users::getInstance()::is_authenticated() ? 
								CAT_Helper_Page::getLink($page_id,'link') :
								CAT_Helper_Page::getLink($page_id,'link') . '?logout=true',
	'FRONTEND_SIGNUP'	=> FRONTEND_SIGNUP
);

$variant = CAT_Helper_Page::getPageSettings($page_id,'internal','template_variant');
if(!$variant)
    $variant = ( defined('DEFAULT_TEMPLATE_VARIANT') && DEFAULT_TEMPLATE_VARIANT != '' )
             ? DEFAULT_TEMPLATE_VARIANT
             : 'default';

$parser->setPath(CAT_TEMPLATE_DIR.'/templates/'.$variant);
$parser->setFallbackPath(CAT_TEMPLATE_DIR.'/templates/default');
$parser->output('index.tpl',$dwoodata);