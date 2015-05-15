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
 *   @category        CAT_Module
 *   @package         lib_jquery
 *
 */

if (defined('WB_PATH')) {
    if (defined('CAT_PATH')) include(CAT_PATH.'/framework/class.secure.php');
    elseif (defined('LEPTON_PATH')) include(LEPTON_PATH.'/framework/class.secure.php');
}
else {
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

$module_directory = 'lib_jquery';
$module_name = 'jQuery / jQuery UI JavaScript Library';
$module_function = 'library';
$module_version = '2.6';
$module_platform = '1.x';
$module_author = 'BlackBird';
$module_license = 'GNU General Public License';
$module_description = 'This module installs the jQuery JavaScript Library and the jQuery UI Library. You may use it as a lib for your own JavaScripts.';
$module_guid = '8FB09FFD-B11C-4B75-984E-F54082B4DEEA';