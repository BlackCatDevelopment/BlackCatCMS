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

$backend = CAT_Backend::getInstance('Settings', 'settings', false);
$users   = CAT_Users::getInstance();

header('Content-type: application/json');

if ( !$users->checkPermission('Settings','settings') )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate("Sorry, but you don't have the permissions for this action"),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$tpl = CAT_Helper_Validate::get('_REQUEST','template');

// get template info
$info = CAT_Helper_Addons::checkInfo(CAT_PATH.'/templates/'.$tpl);
if(!$info || !count($info)) {
	$ajax	= array(
		'message'	=> CAT_Helper_Addons::getError(),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$ajax	= array(
	'message'	=> NULL,
    'variants'  => ( isset($info['module_variants']) ? $info['module_variants'] : array() ),
	'success'	=> true
);
print json_encode( $ajax );
exit();
