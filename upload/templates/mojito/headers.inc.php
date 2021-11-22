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
 *   @package         mojito
 *
 */

if (defined('CAT_PATH')) {	
	include(CAT_PATH.'/framework/class.secure.php'); 
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'framework/class.secure.php')) { 
		include($root.'framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}


$mod_headers = array(
	'frontend' => array(
		'meta' => array(
			array()
		),
		'css' => array(
			array(
				'media'		=> 'all',
				'file'		=> 'templates/mojito/css/default/template.css'
			)
		),
		'jquery' => array(
			array(
				'core'			=> true
			)
		),
		'js' => array(
			'/js/frontend.js',
			'/js/modernizr.custom.62906.js'
		)
	)
);

global $page_id;
$variant  = CAT_Helper_Page::getPageSettings($page_id,'internal','template_variant');
if(!$variant)
    $variant = ( defined('DEFAULT_TEMPLATE_VARIANT') && DEFAULT_TEMPLATE_VARIANT != '' )
             ? DEFAULT_TEMPLATE_VARIANT
             : 'default';

if ( $variant != 'default' && file_exists(CAT_PATH.'/templates/mojito/css/'.$variant.'/template.css') ) {
    $mod_headers['frontend']['css'] = array(
        array(
    		'media'		=> 'all',
    		'file'		=> 'templates/mojito/css/'.$variant.'/template.css'
	    )
    );
}