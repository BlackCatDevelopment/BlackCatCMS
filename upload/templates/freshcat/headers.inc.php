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
    if (defined('CAT_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
    include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
    $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));    $dir = $_SERVER['DOCUMENT_ROOT'];
    $inc = false;
    foreach ($subs as $sub) {
        if (empty($sub)) continue; $dir .= '/'.$sub;
        if (file_exists($dir.'/framework/class.secure.php')) {
            include($dir.'/framework/class.secure.php'); $inc = true;    break;
	}
	}
    if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

$mod_headers = array(
	'backend' => array(
		'meta' => array(
			array(
			)
		),
		'css' => array(
			array(
				'media'		=> 'screen',
				'file'		=> 'templates/freshcat/css/default/basic.css'
			)
		),
		'jquery' => array(
			array(
				'core'			=> true,
				'ui'			=> true,
				'all'			=> array ( 'jquery.highlight', 'jquery.cookies', 'tag-it', 'jquery.form' , 'jquery.livesearch' , 'jquery.smarttruncation', 'cattranslate' )
			)
		),
		'js' => array(
			array(
				'all'			=> array( 'debug.js', 'jquery.fc_set_tab_list.js', 'jquery.fc_toggle_element.js', 'jquery.fc_resize_elements.js', 'jquery.fc_show_popup.js', 'general.js', 'pages_tree.js' ),
				'individual'	=> array (
					'pages'				=> 'backend_pages_modify.js',
					'access'			=> 'backend_users_index.js',
					'addons'			=> 'backend_addons.js',
					'media'				=> 'backend_media.js',
					'preferences'		=> 'backend_preferences.js',
					'settings'			=> 'backend_settings_index.js',
					'login_index'		=> 'login.js'
				)
			)
		)
	)
);

if(CAT_Registry::get('DEFAULT_THEME_VARIANT') != '' && file_exists(CAT_THEME_PATH.'/css/'.CAT_Registry::get('DEFAULT_THEME_VARIANT')))
{
    array_push($mod_headers['backend']['css'], array('file'=>'templates/freshcat/css/'.CAT_Registry::get('DEFAULT_THEME_VARIANT').'/basic.css'));
}

?>