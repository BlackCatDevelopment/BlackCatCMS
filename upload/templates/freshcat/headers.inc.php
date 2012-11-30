<?php
/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
  *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
	include(WB_PATH.'/framework/class.secure.php');
} else {
	$oneback = "../";
	$root = $oneback;
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= $oneback;
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) {
		include($root.'/framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php

$mod_headers = array(
	'backend' => array(
		'meta' => array(
			array(
			)
		),
		'css' => array(
			array(
				'media'		=> 'screen',
				'file'		=> 'css/basic.css'
			)
		),
		'jquery' => array(
			array(
				'core'			=> true,
				'ui'			=> true,
				'ui-components'	=> array ( 'widget' , 'mouse', 'position' , 'resizable' , 'sortable' , 'autocomplete' , 'button' , 'dialog' , 'tabs' ),
				'ui-effects'	=> array ( 'fade' ),
				'all'			=> array ( 'jquery.highlight', 'jquery.cookies', 'tag-it', 'jquery.form' , 'jquery.livesearch' , 'jquery.smarttruncation' )
			)
		),
		'js' => array(
			array(
				'all'			=> array( 'jquery.fc_set_tab_list.js' , 'jquery.fc_toggle_element.js' , 'jquery.fc_resize_elements.js', 'jquery.fc_show_popup.js' , 'general.js', 'pages_tree.js' ),
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