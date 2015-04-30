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
 *   @copyright       2015, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
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

header('Content-type: application/json');

$section_name       = 'Start';
$section_permission = 'start';
$function           = 'start';

$module = CAT_Helper_Validate::sanitizePost('module');

if($module)
{
    if($module != 'backend')
    {
        // get the module type to evaluate the section name
        $properties = CAT_Helper_Addons::getAddonDetails($module);
        if($properties['type'] != 'module')
        {
            CAT_Object::json_error('You sent an invalid value');
        }
        $function = $properties['function'];
    }
    switch($function)
    {
        case 'start':
            break;
        case 'tool':
            $section_name = 'admintools';
            $section_permission = 'admintools';
            break;
        case 'page':
            $section_name = 'pages';
            $section_permission = 'pages';
            break;
        default:
            CAT_Object::json_error('Invalid type '.$properties['type']);
    }
}

$backend = CAT_Backend::getInstance($section_name,$section_permission,false,false);
$result  = CAT_Helper_Dashboard::manageWidgets();

CAT_Object::json_result($result,( $result === true ? 'Success' : 'Error' ));
