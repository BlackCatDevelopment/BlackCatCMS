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
 *   @author          Bianka Martinovic, BlackBird Webprogrammierung
 *   @copyright       2014, Bianka Martinovic
 *   @link            http://www.webbird.de
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Module
 *   @package         lib_wblib
 *
 */

if (defined('CAT_PATH')) {
	include(CAT_PATH.'/framework/class.secure.php');
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

$module_directory    = 'lib_wblib';
$module_name         = 'wblib2 Library for BlackCat CMS';
$module_function     = 'library';
$module_version      = '0.7';
$module_platform     = '1.x';
$module_requirements = 'PHP 5.32 or higher';
$module_author 		 = 'Bianka Martinovic, BlackBird Webprogrammierung';
$module_home		 = 'http://webbird.de';
$module_license 	 = 'GNU General Public License';
$module_description  = 'wblib2 Library for BlackCat CMS';
$module_guid         = '1584B381-9E23-43D5-94CA-41ECA6200138';

?>