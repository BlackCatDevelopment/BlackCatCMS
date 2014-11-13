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
 *   @package         blackcat
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

$pg          = CAT_Helper_Page::getInstance();
$widget_name = $pg->lang()->translate('Maintenance mode');

if(CAT_Registry::get('MAINTENANCE_MODE') == true) {
    echo '<span style="color:#c00;font-weight:900;">',
         '<span class="icon icon-warning" style="font-size:2em;margin-right:5px;"></span>',
         $pg->lang()->translate('Please note: The system is in maintenance mode!'),
         '</span><br /><span style="font-style:italic;margin-left:2.5em;font-size:0.9em;">',
         $pg->lang()->translate('To disable, go to Settings -> System settings -> Maintenance mode -> set to "off".'),
         '</span>';
}
else
{
    echo '<span class="icon icon-checkmark" style="font-size:1.2em;margin-right:5px;"></span>',
         $pg->lang()->translate('Maintenance mode is off.');
}