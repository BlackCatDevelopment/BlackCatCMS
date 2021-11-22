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
 *   @copyright       2013, 2016, Black Cat Development
 *   @link            http://www.blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         lib_dwoo
 *
 *   Usage: cat_submenu(<PAGE_ID>[, option: value, option: value, ... ])
 *
 *   Shows submenu for page <PAGE_ID> (children of that page)
 *   If no page_id is given (set NULL if you need to pass additional options)
 *   the current page will be used
 *
 *   Please note: The interface (=how to call this) changed with BC v1.2!
 *
 */

if (defined('CAT_PATH')) {
    include(CAT_PATH.'/framework/class.secure.php');
} else {
    $oneback = "../";
    $root = $oneback;
    $level = 1;
    while (($level < 10) && (!file_exists($root.'framework/class.secure.php'))) {
        $root .= $oneback;
        $level += 1;
    }
    if (file_exists($root.'framework/class.secure.php')) {
        include($root.'framework/class.secure.php');
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}

function Dwoo_Plugin_cat_submenu()
{
    $attr = func_get_args();
    // first attr is $Dwoo
    array_shift($attr);
    // second attr is $page_id
    $page_id = array_shift($attr);
    return CAT_Helper_Menu::subMenu($page_id,$attr);
}