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

$local = array (
	'pages'				=> 'backend_pages_modify.js',
	'access'			=> 'backend_users_index.js',
	'addons'			=> 'backend_addons.js',
	'media'				=> 'backend_media.js',
	'preferences'		=> 'backend_preferences.js',
	'settings'			=> array('backend_pages_modify.js','backend_settings_index.js'),
	'login_index'		=> 'login.js',
);
$mod_headers = array(
	'backend' => array(
		'meta' => array(
			array( 'name' => 'viewport', 'content' => 'width=device-width, initial-scale=1' ),
		),
		'css' => array(
			array(
				'media'		=> 'screen',
				'file'		=> 'templates/freshcat/css/default/index.css'
			),
            array(
				'media'		=> 'screen',
				'file'		=> 'modules/lib_jquery/plugins/qtip2/qtip2.min.css'
			),
		),
		'jquery' => array(
			array(
				'core'			=> true,
				'ui'			=> true,
				'all'			=> array ( 'jquery.highlight', 'jquery.cookies', 'tag-it', 'qtip2', 'jquery.form' , 'jquery.livesearch' , 'jquery.smarttruncation', 'cattranslate' )
			)
		),
		'js' => array(
			array(
                'debug.js', 'jquery.fc_set_tab_list.js', 'jquery.fc_toggle_element.js', 'jquery.fc_resize_elements.js', 'jquery.fc_show_popup.js', 'general.js', 'pages_tree.js', 'session.js'
			)
		)
	)
);

// get current backend section to add local JS
$page = strtolower(CAT_Backend::getInstance()->section_name);
if(isset($local[$page]))
{
    if(!is_array($local[$page])) $local[$page] = array($local[$page]);
    $mod_headers['backend']['js'][0] = array_merge(
        $mod_headers['backend']['js'][0],
        $local[$page]
    );
}

if($page=='addons')
{
    array_push($mod_headers['backend']['css'], array('file'=>'templates/freshcat/css/default/tabs.css'));
    array_push($mod_headers['backend']['css'], array('file'=>'templates/freshcat/css/default/addons.css'));
}

// check for custom JS for current backend page
if ( CAT_Registry::get('DEFAULT_THEME_VARIANT') == 'custom' )
    if(file_exists(dirname(__FILE__).'/templates/custom/backend_'.$page.'.js'))
        $mod_headers['backend']['js'][0][] = '/custom/backend_'.$page.'.js';
