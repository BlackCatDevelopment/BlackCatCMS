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

function cmsplink(&$content)
{
	$regexp = array( '/\[cmsplink([0-9]+)\]/isU' );
    // for backward compatibility with WB
    if(defined('WB_PREPROCESS_PREG')) $regexp[] = WB_PREPROCESS_PREG;
    foreach($regexp as $preg) {
		if(preg_match_all( $preg, $content, $ids ) ) {
			$new_ids = array_unique($ids[1]);
			foreach($new_ids as $key => &$page_id) {
				$link = CAT_Helper_Page::properties($page_id,'link');
				if( !is_null($link) ) {
					$content = str_replace(
						$ids[0][ $key ],
						CAT_Helper_Page::getLink($link),
						$content
					);
				}
			}
		}
    }
}   // end function cmsplink()