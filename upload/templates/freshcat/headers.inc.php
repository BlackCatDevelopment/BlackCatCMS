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
 *   @package         freshcat
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

?>