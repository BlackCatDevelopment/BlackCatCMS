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
 *   @link            http://www.blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         lib_dwoo
 *
 */

// include class.secure.php to protect this file and the whole CMS!
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
// end include class.secure.php

require_once CAT_PATH.'/framework/functions.php';

function Dwoo_Plugin_last_modified(Dwoo $dwoo, $page_id = false) {
	global $backend;
	if ( is_numeric( $page_id ) )
	{
		$sql	= "SELECT `modified_when` FROM `%spages` WHERE `page_id` = %d";
		$t		= CAT_Helper_Page::getInstance()->db()->get_one( sprintf( $sql, CAT_TABLE_PREFIX, intval($page_id) ) );

	}
	else {
		$sql	= "SELECT `modified_when` FROM `%spages` WHERE `visibility`= public OR `visibility`= hidden ORDER BY `modified_when` DESC LIMIT 0,1";
		$t		= CAT_Helper_Page::getInstance()->db()->get_one( sprintf( $sql, CAT_TABLE_PREFIX ) );
	}
	return CAT_Helper_DateTime::getInstance()->getDate($t);
}

?>