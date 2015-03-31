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

$backend = CAT_Backend::getInstance('start','start',false,false);
$user    = CAT_Users::getInstance();

$action = CAT_Helper_Validate::sanitizePost('action');
switch($action) {
    case 'hide':
        CAT_Helper_Dashboard::hideWidget(CAT_Helper_Validate::sanitizePost('widget'));
        break;
    case 'show':
        CAT_Helper_Dashboard::showWidget(CAT_Helper_Validate::sanitizePost('widget'));
        break;
    case 'reorder':
        // column is 0-based in the HTML, but 1-based in the code
        CAT_Helper_Dashboard::reorderColumn(
            (CAT_Helper_Validate::sanitizePost('column')+1),
            CAT_Helper_Validate::sanitizePost('order')
        );
        break;
    case 'move':
        CAT_Helper_Dashboard::moveWidget(CAT_Helper_Validate::sanitizePost('items'));
        break;
}

echo json_encode(array(
    'success' => true,
    'message' => 'ok'
));