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
 *   @category        CAT_Modules
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

$module_directory   = 'wrapper';
$module_name        = 'Wrapper';
$module_function    = 'page';
$module_version     = '3.0';
$module_platform    = '2.x';
$module_author      = 'Ryan Djurovich, Dietrich Roland Pehlke, Black Cat Development (last)';
$module_license     = 'GNU General Public License';
$module_description = 'This module allows you to wrap your site around another using an inline frame';
$module_guid        = 'a5830654-06f3-402a-9d25-a03c53fc5574';

/**
 *  3.0     2014-10-07  - added changes for BlackCat CMS v1.1, so this will no
 *                        longer work with WB, LEPTON or BC 1.0.x
 *
 *  2.7.5   2013-10-23  - allow to use CSS (%) for width and height
 *
 *  2.7.4   2013-10-23  - fix for BlackCat CMS
 *
 *	2.7.3	2012-02-09	- added upgrade.php.
 * 
 *	2.7.1	2010-11-02	- Bugfix inside the html-template to get valid output.
 *						  (Remove missplaced '</form>' closing tag)
 *						- Move the html-template inside the "htt" folder.
 *						- Remove WB 2.7 support.
 *
 */
?>