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
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         droplets
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

$mod_headers = array();

if ( file_exists( CAT_PATH.'/modules/libraryadmin/plugins/lytebox' ) ) {
    $mod_headers = array(
		'backend' => array(
		    'css' => array(
				array(
					'media'		=> 'screen',
					'file'		=> '/modules/libraryadmin/plugins/lytebox/lytebox.css',
				)
			),
			'js' => array(
                '/modules/libraryadmin/plugins/lytebox/lytebox.js'
			),
		),
	);
}
elseif ( file_exists( CAT_PATH.'/modules/lib_jquery/plugins/SlimBox2' ) ) {
    $mod_headers = array(
		'backend' => array(
		    'css' => array(
				array(
					'media'		=> 'screen',
					'file'		=> '/modules/lib_jquery/plugins/FancyBox/jquery.fancybox-1.3.4.css',
				)
			),
			'js' => array(
                '/modules/lib_jquery/plugins/FancyBox/jquery.fancybox-1.3.4.pack.js',
			),
		),
	);
}

?>