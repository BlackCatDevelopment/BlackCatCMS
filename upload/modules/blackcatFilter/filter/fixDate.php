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
 *   @category        CAT_Modules
 *   @package         blackcatFilter
 *
 *   This filter fixes dates emitted from old modules (like TOPICS)
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

/**
 * try to fix dates modules produce using the new (strftime) formats with the
 * old date() or gmdate() methods
 * will not work with long formats!
 **/
function fixDate(&$content)
{
    // first, match simple dates (%05.%08.%2013)
    $content = preg_replace( '~\%(\d+)([\.-])\%(\d+)([\.-])\%(\d+)~', '\\1\\2\\3\\4\\5', $content );
    // can't really fix this...
    // '%A,|%d.|%B|%Y' -- %PM,|%05.|%574|%2013
    //                    %AM,|%04.|%413|%2013
    //if ( preg_match_all( '~(\%(A|B)M),\|\%(\d+)\.\|\%(\d+)\|\%(\d+)~i', $content, $matches, PREG_SET_ORDER ) )

}