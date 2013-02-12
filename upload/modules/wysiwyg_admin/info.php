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
 *   @package         wysiwyg_admin
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

if ( !defined('CAT_PATH')) die(header('Location: ../../index.php'));

$module_directory	= 'wysiwyg_admin';
$module_name		= 'WYSIWYG Admin';
$module_function	= 'tool';
$module_version		= '2.0';
$module_platform	= '2.x';
$module_author		= 'Black Cat Development';
$module_license		= '<a href="http://www.gnu.org/licenses/" target="_blank">GNU GPL</a>';
$module_license_terms = '-';
$module_description	= 'WYSIWYG Admin allows to manage some editor options, which are skin, toolbar, height and width by default. There may be additional options defined by the editor itself.';
$module_guid		= 'D15D8D69-1B67-4994-85A3-1F9E067DBCC1';
