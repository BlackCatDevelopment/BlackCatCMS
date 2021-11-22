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
 *   @copyright       2016, Black Cat Development
 *   @link            https://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         menu_link
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

$module_directory = 'menu_link';
$module_name = 'Menu Link';
$module_function = 'page';
$module_version = '2.8.3';
$module_platform = '1.x';
$module_author = 'Ryan Djurovich, thorn, Black Cat Development';
$module_license = 'GNU General Public License';
$module_description = 'This module allows you to insert a link into the menu.';
$module_guid      = '452f0da3-3bc1-43bc-b2ad-491ae8494c6e';


/* History:
2.8 - June 2009
- Improved the pagelist (thorn)
- Added different redirect types 301 or 302 (thorn)
- Set platform version 2.8

2.7 - 24. Jan. 2008 - doc
- added language support, changed platform to 2.7

2.6.1.1 - 16. Jan. 2008 - thorn
- added table mod_menu_link
- added install.php, delete.php, add.php
- changed wb/index.php: redirect if page is menu_link
- removed special-handling of menu_link in: admin/pages/settings2.php

*/
?>