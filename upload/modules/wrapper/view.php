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
 *   @category        CAT_Module
 *   @package         wrapper
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

global $parser;

// get url
$get_settings   = $database->query( "SELECT url,height,width,wtype FROM " . CAT_TABLE_PREFIX . "mod_wrapper WHERE section_id = '$section_id'" );
$fetch_settings = $get_settings->fetchRow( MYSQL_ASSOC );
$url            = $fetch_settings[ 'url' ];

if ( !isset($fetch_settings['wtype']) || $fetch_settings['wtype'] == '' || $fetch_settings['wtype'] == '0' ) {
    $fetch_settings['wtype'] = 'object';
}

if ( !file_exists(CAT_PATH.'/modules/wrapper/htt/'.$fetch_settings['wtype'].'.tpl') ) {
	echo "ERROR: No such type!<br />";
}
else {
	$data = array(
	    'MOD_WRAPPER' => $MOD_WRAPPER,
	    'SETTINGS'    => $fetch_settings
	);
	$parser->setPath( CAT_PATH.'/modules/wrapper/htt' );
	$parser->output( $fetch_settings['wtype'].'.tpl', $data );
}